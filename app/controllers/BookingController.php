<?php
declare(strict_types=1);

final class BookingController
{
    public function __construct(private BookingModel $booking)
    {
    }

    public function showtimes(): void
    {
        $query = [];
        parse_str(
            (string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY),
            $query
        );

        $movieId = filter_var(
            $query['movie_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($movieId === false) {
            throw new ApiException('Mã phim không hợp lệ.', 422);
        }

        $rows = $this->booking->findShowtimes($movieId);

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'movieId' => (int) $row['phim_id'],
            'roomId' => (int) $row['phong_id'],
            'startsAt' => $row['thoi_gian_bat_dau'],
            'endsAt' => $row['thoi_gian_ket_thuc'],
            'basePrice' => (float) $row['gia_ve_co_ban'],
        ], $rows);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function seats(): void
    {
        $query = [];
        parse_str(
            (string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY),
            $query
        );

        $showtimeId = filter_var(
            $query['showtime_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($showtimeId === false) {
            throw new ApiException('Mã suất chiếu không hợp lệ.', 422);
        }

        $rows = $this->booking->findSeats($showtimeId);

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'row' => $row['hang_ghe'],
            'number' => (int) $row['so_thu_tu'],
            'type' => $row['loai_ghe'],
            'status' => $row['trang_thai'],
        ], $rows);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function concessions(): void
    {
        $rows = $this->booking->findConcessions();

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['ten_mon'],
            'type' => $row['loai'],
            'price' => (float) $row['don_gia'],
            'image' => $row['hinh_anh'],
        ], $rows);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function holdSeats(): void
    {
    $userId = filter_var(
        $_SESSION['user_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($userId === false) {
        throw new ApiException('Vui lòng đăng nhập để giữ ghế.', 401);
    }

    $body = readJsonBody();

    $showtimeId = filter_var(
        $body['showtimeId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $seatIds = $body['seatIds'] ?? null;

    if (
        $showtimeId === false
        || !is_array($seatIds)
        || count($seatIds) < 1
        || count($seatIds) > 8
    ) {
        throw new ApiException('Thông tin suất chiếu hoặc ghế không hợp lệ.', 422);
    }

    $validatedSeats = [];

    foreach ($seatIds as $seatId) {
        $id = filter_var(
            $seatId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã ghế không hợp lệ.', 422);
        }

        $validatedSeats[] = (int) $id;
    }

    $data = $this->booking->holdSeats(
        (int) $userId,
        (int) $showtimeId,
        $validatedSeats
    );

    jsonResponse(['ok' => true, 'data' => $data]);
    }
    public function checkout(
    int $userId,
    int $showtimeId,
    array $concessionItems
): array {
    $pdo = Database::connection();
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