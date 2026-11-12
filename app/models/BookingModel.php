<?php
declare(strict_types=1);

final class BookingModel
{
    public function findShowtimes(int $movieId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, phim_id, phong_id, thoi_gian_bat_dau,
                    thoi_gian_ket_thuc, gia_ve_co_ban
             FROM suatchieu
             WHERE phim_id = :movie_id
               AND thoi_gian_bat_dau > NOW()
             ORDER BY thoi_gian_bat_dau"
        );

        $statement->execute(['movie_id' => $movieId]);

        return $statement->fetchAll();
    }

    public function findSeats(int $showtimeId, int $userId): array
    {
    $pdo = Database::connection();

    $statement = $pdo->prepare(
        "SELECT g.id, g.hang_ghe, g.so_thu_tu, g.loai_ghe,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM chitietve cv
                        JOIN datve d ON d.id = cv.dat_ve_id
                        WHERE cv.ghe_id = g.id
                          AND d.suat_chieu_id = :showtime_id
                          AND d.trang_thai_thanh_toan <> 'DaHuy'
                    ) THEN 'BOOKED'

                    WHEN EXISTS (
                        SELECT 1
                        FROM ghedanggiu gd
                        WHERE gd.ghe_id = g.id
                          AND gd.suat_chieu_id = :my_showtime_id
                          AND gd.user_id = :user_id
                          AND gd.expires_at > NOW()
                    ) THEN 'HELD_BY_ME'

                    WHEN EXISTS (
                        SELECT 1
                        FROM ghedanggiu gd
                        WHERE gd.ghe_id = g.id
                          AND gd.suat_chieu_id = :hold_showtime_id
                          AND gd.expires_at > NOW()
                    ) THEN 'HELD'

                    ELSE 'AVAILABLE'
                END AS trang_thai
         FROM ghe g
         JOIN suatchieu s ON s.phong_id = g.phong_id
         WHERE s.id = :seat_showtime_id
         ORDER BY g.hang_ghe, g.so_thu_tu"
    );

    $statement->execute([
        'showtime_id' => $showtimeId,
        'my_showtime_id' => $showtimeId,
        'user_id' => $userId,
        'hold_showtime_id' => $showtimeId,
        'seat_showtime_id' => $showtimeId,
    ]);

    return $statement->fetchAll();
    }
    
    public function findConcessions(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, ten_mon, loai, don_gia, hinh_anh
             FROM bapnuoc
             WHERE trang_thai = 1
             ORDER BY loai, ten_mon"
        );

        return $statement->fetchAll();
    }

    public function holdSeats(
    int $userId,
    int $showtimeId,
    array $seatIds
): array {
    $pdo = Database::connection();
    $seatIds = array_values(array_unique($seatIds));

    if (count($seatIds) < 1 || count($seatIds) > 8) {
        throw new ApiException('Chọn từ 1 đến 8 ghế.', 422);
    }

    $pdo->beginTransaction();

    try {
        $pdo->exec(
            'DELETE FROM ghedanggiu WHERE expires_at <= NOW()'
        );

        $showtime = $pdo->prepare(
            'SELECT id, phong_id
             FROM suatchieu
             WHERE id = :id AND thoi_gian_bat_dau > NOW()
             FOR UPDATE'
        );
        $showtime->execute(['id' => $showtimeId]);
        $showtimeRow = $showtime->fetch();

        if (!$showtimeRow) {
            throw new ApiException('Suất chiếu không hợp lệ hoặc đã bắt đầu.', 404);
        }

        $placeholders = implode(',', array_fill(0, count($seatIds), '?'));

        // Khóa hàng ghế để các yêu cầu đồng thời không giữ/mua trùng ghế.
        $seatQuery = $pdo->prepare(
            "SELECT id
             FROM ghe
             WHERE phong_id = ?
               AND id IN ($placeholders)
             ORDER BY id
             FOR UPDATE"
        );
        $seatQuery->execute([
            (int) $showtimeRow['phong_id'],
            ...$seatIds,
        ]);

        $validSeats = array_map(
            'intval',
            $seatQuery->fetchAll(PDO::FETCH_COLUMN)
        );

        sort($validSeats);
        $requestedSeats = $seatIds;
        sort($requestedSeats);

        if ($validSeats !== $requestedSeats) {
            throw new ApiException('Có ghế không thuộc phòng của suất chiếu.', 422);
        }

        $booked = $pdo->prepare(
            "SELECT COUNT(*)
             FROM chitietve cv
             JOIN datve d ON d.id = cv.dat_ve_id
             WHERE d.suat_chieu_id = ?
               AND d.trang_thai_thanh_toan <> 'DaHuy'
               AND cv.ghe_id IN ($placeholders)"
        );
        $booked->execute([$showtimeId, ...$seatIds]);

        if ((int) $booked->fetchColumn() > 0) {
            throw new ApiException('Có ghế đã được đặt.', 409);
        }

        $heldByOther = $pdo->prepare(
            "SELECT COUNT(*)
             FROM ghedanggiu
             WHERE suat_chieu_id = ?
               AND user_id <> ?
               AND expires_at > NOW()
               AND ghe_id IN ($placeholders)"
        );
        $heldByOther->execute([$showtimeId, $userId, ...$seatIds]);

        if ((int) $heldByOther->fetchColumn() > 0) {
            throw new ApiException('Có ghế đang được khách khác giữ.', 409);
        }

        // Bỏ các ghế khác mà người này đang giữ ở cùng suất.
        $release = $pdo->prepare(
            "DELETE FROM ghedanggiu
             WHERE suat_chieu_id = ?
               AND user_id = ?
               AND ghe_id NOT IN ($placeholders)"
        );
        $release->execute([$showtimeId, $userId, ...$seatIds]);

        $save = $pdo->prepare(
            "INSERT INTO ghedanggiu
                (suat_chieu_id, ghe_id, user_id, expires_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
             ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                expires_at = VALUES(expires_at)"
        );

        foreach ($seatIds as $seatId) {
            $save->execute([$showtimeId, $seatId, $userId]);
        }

        $pdo->commit();

        return [
            'expiresInSeconds' => 300,
            'seatIds' => $seatIds,
        ];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}
    public function checkout(
    int $userId,
    int $showtimeId,
    array $concessionItems
): array {
    $pdo = Database::connection();
    
    $validatedConcessions = [];

    foreach ($concessionItems as $itemId => $quantity) {
        $validatedItemId = filter_var(
            $itemId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $validatedQuantity = filter_var(
            $quantity,
            FILTER_VALIDATE_INT,
            ['options' => [
                'min_range' => 1,
                'max_range' => 20,
            ]]
        );

        if ($validatedItemId === false) {
            throw new ApiException('Mã món bắp nước không hợp lệ.', 422);
        }

        if ($validatedQuantity === false) {
            throw new ApiException('Số lượng mỗi món phải từ 1 đến 20.', 422);
        }

        if (isset($validatedConcessions[$validatedItemId])) {
            throw new ApiException('Danh sách món bắp nước có dữ liệu trùng.', 422);
        }

        $validatedConcessions[$validatedItemId] = $validatedQuantity;
    }

    $concessionItems = $validatedConcessions;
    
    $pdo->beginTransaction();

    try {
        $showtimeQuery = $pdo->prepare(
            'SELECT id, phong_id, gia_ve_co_ban
             FROM suatchieu
             WHERE id = :id
               AND thoi_gian_bat_dau > NOW()
             FOR UPDATE'
        );
        $showtimeQuery->execute(['id' => $showtimeId]);
        $showtime = $showtimeQuery->fetch();

        if (!$showtime) {
            throw new ApiException('Suất chiếu không hợp lệ hoặc đã bắt đầu.', 404);
        }

        $holdQuery = $pdo->prepare(
            'SELECT ghe_id
             FROM ghedanggiu
             WHERE suat_chieu_id = :showtime_id
               AND user_id = :user_id
               AND expires_at > NOW()
             ORDER BY ghe_id
             FOR UPDATE'
        );
        $holdQuery->execute([
            'showtime_id' => $showtimeId,
            'user_id' => $userId,
        ]);

        $seatIds = array_map(
            'intval',
            $holdQuery->fetchAll(PDO::FETCH_COLUMN)
        );

        if (count($seatIds) < 1 || count($seatIds) > 8) {
            throw new ApiException('Không còn ghế hợp lệ đang được giữ.', 409);
        }

        $marks = implode(',', array_fill(0, count($seatIds), '?'));

        $lockSeats = $pdo->prepare(
            "SELECT id
             FROM ghe
             WHERE phong_id = ?
               AND id IN ($marks)
             ORDER BY id
             FOR UPDATE"
        );
        $lockSeats->execute([
            (int) $showtime['phong_id'],
            ...$seatIds,
        ]);

        if (count($lockSeats->fetchAll(PDO::FETCH_COLUMN)) !== count($seatIds)) {
            throw new ApiException('Sơ đồ ghế của suất chiếu đã thay đổi.', 409);
        }

        $soldQuery = $pdo->prepare(
            "SELECT COUNT(*)
             FROM chitietve cv
             JOIN datve d ON d.id = cv.dat_ve_id
             WHERE d.suat_chieu_id = ?
               AND d.trang_thai_thanh_toan <> 'DaHuy'
               AND cv.ghe_id IN ($marks)"
        );
        $soldQuery->execute([$showtimeId, ...$seatIds]);

        if ((int) $soldQuery->fetchColumn() > 0) {
            throw new ApiException('Có ghế đã được đặt.', 409);
        }

        // Tạm dùng giá cơ bản của suất chiếu cho tất cả loại ghế.
        $ticketPriceCents = (int) round(
            (float) $showtime['gia_ve_co_ban'] * 100
        );
        $totalCents = $ticketPriceCents * count($seatIds);

        $selectedConcessions = [];

        foreach ($concessionItems as $itemId => $quantity) {
            $itemQuery = $pdo->prepare(
                'SELECT id, don_gia
                 FROM bapnuoc
                 WHERE id = :id AND trang_thai = 1'
            );
            $itemQuery->execute(['id' => $itemId]);
            $item = $itemQuery->fetch();

            if (!$item) {
                throw new ApiException('Có món bắp nước không còn bán.', 409);
            }

            $priceCents = (int) round((float) $item['don_gia'] * 100);
            $totalCents += $priceCents * $quantity;

            $selectedConcessions[] = [
                'id' => (int) $item['id'],
                'quantity' => $quantity,
                'price' => number_format($priceCents / 100, 2, '.', ''),
            ];
        }

        $orderCode = 'CS' . strtoupper(bin2hex(random_bytes(8)));
        $total = number_format($totalCents / 100, 2, '.', '');

        $orderQuery = $pdo->prepare(
            "INSERT INTO datve
                (user_id, suat_chieu_id, ma_don_hang, tong_tien,
                 phuong_thuc_thanh_toan, trang_thai_thanh_toan)
             VALUES
                (:user_id, :showtime_id, :order_code, :total,
                 'DEMO', 'DaThanhToan')"
        );
        $orderQuery->execute([
            'user_id' => $userId,
            'showtime_id' => $showtimeId,
            'order_code' => $orderCode,
            'total' => $total,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $ticketQuery = $pdo->prepare(
            'INSERT INTO chitietve
                (dat_ve_id, ghe_id, gia_ve, ma_qr_code)
             VALUES (:order_id, :seat_id, :price, :qr_code)'
        );

        foreach ($seatIds as $seatId) {
            $ticketQuery->execute([
                'order_id' => $orderId,
                'seat_id' => $seatId,
                'price' => number_format($ticketPriceCents / 100, 2, '.', ''),
                'qr_code' => bin2hex(random_bytes(24)),
            ]);
        }

        $concessionQuery = $pdo->prepare(
            'INSERT INTO chitietdatbapnuoc
                (dat_ve_id, bap_nuoc_id, so_luong, gia_tien)
             VALUES (:order_id, :item_id, :quantity, :price)'
        );

        foreach ($selectedConcessions as $item) {
            $concessionQuery->execute([
                'order_id' => $orderId,
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        $deleteHolds = $pdo->prepare(
            'DELETE FROM ghedanggiu
             WHERE suat_chieu_id = :showtime_id
               AND user_id = :user_id'
        );
        $deleteHolds->execute([
            'showtime_id' => $showtimeId,
            'user_id' => $userId,
        ]);

        $pdo->commit();

        return [
            'id' => $orderId,
            'code' => $orderCode,
            'total' => (float) $total,
            'seatIds' => $seatIds,
        ];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

}