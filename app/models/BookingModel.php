<?php
declare(strict_types=1);

final class BookingModel
{
    public function findShowtimes(int $movieId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT s.id, s.phim_id, s.phong_id, s.thoi_gian_bat_dau,
                    s.thoi_gian_ket_thuc, s.gia_ve_co_ban,
                    ph.ten_phong AS ten_phong,
                    r.ten_rap AS ten_rap
             FROM suatchieu s
             JOIN phongchieu ph ON ph.id = s.phong_id
             JOIN rapchieu r ON r.id = ph.rap_id
             WHERE s.phim_id = :movie_id
               AND s.thoi_gian_bat_dau > NOW()
             ORDER BY s.thoi_gian_bat_dau"
        );

        $statement->execute(['movie_id' => $movieId]);

        return $statement->fetchAll();
    }


    public function findConcessions(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, ten_mon, loai, don_gia, hinh_anh
            FROM bapnuoc
            WHERE trang_thai = 1
            ORDER BY id"
        );

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

    public function holdSeats(int $userId, int $showtimeId, array $seatIds): array
    {
        $pdo = Database::connection();
        $seatIds = array_values(array_unique($seatIds));

        if (count($seatIds) < 1 || count($seatIds) > 8) {
            throw new ApiException('Chọn từ 1 đến 8 ghế.', 422);
        }

        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM ghedanggiu WHERE expires_at <= NOW()');

            $showtime = $pdo->prepare(
                'SELECT id, phong_id, gia_ve_co_ban
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

            // Lock các hàng ghế để tránh race condition
            $seatQuery = $pdo->prepare(
                "SELECT id, loai_ghe
                FROM ghe
                WHERE phong_id = ?
                AND id IN ($placeholders)
                FOR UPDATE"
            );
            $seatQuery->execute(array_merge([$showtimeRow['phong_id']], $seatIds));
            $lockedSeats = $seatQuery->fetchAll(PDO::FETCH_KEY_PAIR);

            if (count($lockedSeats) !== count($seatIds)) {
                throw new ApiException('Có ghế không thuộc phòng chiếu này.', 422);
            }

            // Check ghế đã bán
            $marks = implode(',', array_fill(0, count($seatIds), '?'));
            $soldQuery = $pdo->prepare(
                "SELECT COUNT(*)
                FROM chitietve cv
                JOIN datve d ON d.id = cv.dat_ve_id
                WHERE d.suat_chieu_id = ?
                AND d.trang_thai_thanh_toan <> 'DaHuy'
                AND cv.ghe_id IN ($marks)"
            );
            $soldQuery->execute(array_merge([$showtimeId], $seatIds));

            if ((int) $soldQuery->fetchColumn() > 0) {
                throw new ApiException('Có ghế đã được đặt.', 409);
            }

            // Check ghế đang được người khác giữ
            $holdQuery = $pdo->prepare(
                "SELECT COUNT(*)
                FROM ghedanggiu
                WHERE suat_chieu_id = ?
                AND ghe_id IN ($marks)
                AND expires_at > NOW()
                AND user_id <> ?"
            );
            $holdQuery->execute(array_merge([$showtimeId], $seatIds, [$userId]));

            if ((int) $holdQuery->fetchColumn() > 0) {
                throw new ApiException('Có ghế đang được người khác giữ.', 409);
            }

            // Xóa hold cũ của user này (nếu có)
            $delOld = $pdo->prepare(
                'DELETE FROM ghedanggiu WHERE suat_chieu_id = ? AND user_id = ?'
            );
            $delOld->execute([$showtimeId, $userId]);
            $expiresAtRow = $pdo->query(
                'SELECT DATE_ADD(NOW(), INTERVAL 5 MINUTE) AS expires_at'
            )->fetch();
            $expiresAt = $expiresAtRow['expires_at'];
            $insertHold = $pdo->prepare(
                'INSERT INTO ghedanggiu (suat_chieu_id, ghe_id, user_id, expires_at)
                VALUES (:showtime_id, :seat_id, :user_id, :expires_at)'
            );

            foreach ($seatIds as $seatId) {
                $insertHold->execute([
                    'showtime_id' => $showtimeId,
                    'seat_id' => $seatId,
                    'user_id' => $userId,
                    'expires_at' => $expiresAt,
                ]);
            }

            $pdo->commit();

            return [
                'expiresAt' => $expiresAt,
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
        array $concessionItems,
        string $idempotencyKey 
    ): array {
        $pdo = Database::connection();

        // Lấy seatIds từ holds của user này
        $holdQuery = $pdo->prepare(
            'SELECT ghe_id FROM ghedanggiu
             WHERE suat_chieu_id = ? AND user_id = ? AND expires_at > NOW()'
        );
        $holdQuery->execute([$showtimeId, $userId]);
        $seatIds = $holdQuery->fetchAll(PDO::FETCH_COLUMN);

        if (empty($seatIds)) {
            throw new ApiException('Bạn không giữ ghế nào hoặc đã hết hạn.', 409);
        }

        $seatIds = array_values(array_unique($seatIds));

        if (count($seatIds) < 1 || count($seatIds) > 8) {
            throw new ApiException('Số lượng ghế không hợp lệ.', 422);
        }

        // Validate concessions
        $validatedConcessions = [];
        foreach ($concessionItems as $itemId => $quantity) {
            $id = filter_var($itemId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $qty = filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 20]]);

            if ($id === false || $qty === false) {
                throw new ApiException('Mã món hoặc số lượng bắp nước không hợp lệ.', 422);
            }

            if (isset($validatedConcessions[$id])) {
                throw new ApiException('Danh sách bắp nước có món bị trùng.', 422);
            }
            $validatedConcessions[$id] = $qty;
        }

        $pdo->beginTransaction();

        try {
            // Verify user đang giữ các ghế này
            $marks = implode(',', array_fill(0, count($seatIds), '?'));
            $holdCheck = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM ghedanggiu
                 WHERE suat_chieu_id = ?
                   AND user_id = ?
                   AND ghe_id IN ($marks)
                   AND expires_at > NOW()"
            );
            $holdCheck->execute(array_merge([$showtimeId, $userId], $seatIds));

            if ((int) $holdCheck->fetchColumn() !== count($seatIds)) {
                throw new ApiException('Bạn không giữ ghế này hoặc ghế đã hết hạn.', 409);
            }

            // Check suất chiếu chưa kết thúc
            $showtimeCheck = $pdo->prepare(
                'SELECT id, phong_id, gia_ve_co_ban, thoi_gian_ket_thuc
                 FROM suatchieu
                 WHERE id = ? AND thoi_gian_ket_thuc > NOW()
                 FOR UPDATE'
            );
            $showtimeCheck->execute([$showtimeId]);
            $showtime = $showtimeCheck->fetch();

            if (!$showtime) {
                throw new ApiException('Suất chiếu không hợp lệ hoặc đã kết thúc.', 404);
            }

            // Check ghế đã bán (double check)
            $soldQuery = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM chitietve cv
                 JOIN datve d ON d.id = cv.dat_ve_id
                 WHERE d.suat_chieu_id = ?
                   AND d.trang_thai_thanh_toan <> 'DaHuy'
                   AND cv.ghe_id IN ($marks)"
            );
            $soldQuery->execute(array_merge([$showtimeId], $seatIds));

            if ((int) $soldQuery->fetchColumn() > 0) {
                throw new ApiException('Có ghế đã được đặt.', 409);
            }

            // Lấy loại ghế để tính giá
            $seatPriceQuery = $pdo->prepare(
                "SELECT g.id, g.loai_ghe
                 FROM ghe g
                 JOIN suatchieu s ON s.phong_id = g.phong_id
                 WHERE s.id = ? AND g.id IN ($marks)"
            );
            $seatPriceQuery->execute(array_merge([$showtimeId], $seatIds));
            $seatPrices = $seatPriceQuery->fetchAll(PDO::FETCH_KEY_PAIR);

            $multiplier = ['Thuong' => 1.0, 'VIP' => 1.2, 'Couple' => 1.5];
            $totalCents = 0;
            $seatPriceMap = [];

            foreach ($seatIds as $seatId) {
                $basePrice = (float) $showtime['gia_ve_co_ban'];
                $loaiGhe = $seatPrices[$seatId] ?? 'Thuong';
                $price = $basePrice * ($multiplier[$loaiGhe] ?? 1.0);
                $priceCents = (int) round($price * 100);
                $totalCents += $priceCents;
                $seatPriceMap[$seatId] = number_format($priceCents / 100, 2, '.', '');
            }

            $selectedConcessions = [];

            foreach ($validatedConcessions as $itemId => $quantity) {
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
                     phuong_thuc_thanh_toan, trang_thai_thanh_toan, idempotency_key)
                 VALUES
                    (:user_id, :showtime_id, :order_code, :total,
                     'DEMO', 'DaThanhToan', :idempotency_key)"
            );
            $orderQuery->execute([
                'user_id' => $userId,
                'showtime_id' => $showtimeId,
                'order_code' => $orderCode,
                'total' => $total,
                'idempotency_key' => $idempotencyKey,
            ]);

            $orderId = (int) $pdo->lastInsertId();

            // Insert chi tiết vé với giá theo từng loại ghế
            $ticketQuery = $pdo->prepare(
                'INSERT INTO chitietve
                    (dat_ve_id, ghe_id, gia_ve, ma_qr_code)
                 VALUES (:order_id, :seat_id, :price, :qr_code)'
            );

            foreach ($seatIds as $seatId) {
                $ticketQuery->execute([
                    'order_id' => $orderId,
                    'seat_id' => $seatId,
                    'price' => $seatPriceMap[$seatId],
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

            // Xóa holds sau khi đặt thành công
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