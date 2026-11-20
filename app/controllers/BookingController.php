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
            'roomName' => $row['ten_phong'] ?? '',
            'roomType' => $row['loai_phong'] ?? '',
            'cinemaName' => $row['ten_rap'] ?? '',
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

        $user = AuthMiddleware::requireAuth();
        $userId = (int) $user['id'];

        $rows = $this->booking->findSeats(
            (int) $showtimeId,
            (int) $userId
        );

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
        $user = AuthMiddleware::requireAuth();
        $userId = (int) $user['id'];

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
        $seenSeatIds = [];
        foreach ($seatIds as $seatId) {
            $id = filter_var(
                $seatId,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($id === false) {
                throw new ApiException('Mã ghế không hợp lệ.', 422);
            }

            if (isset($seenSeatIds[$id])) {
                throw new ApiException('Danh sách ghế có mã ghế bị trùng.', 422);
            }

            $seenSeatIds[$id] = true;
            $validatedSeats[] = (int) $id;
        }

        $data = $this->booking->holdSeats(
            (int) $userId,
            (int) $showtimeId,
            $validatedSeats
        );

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function checkout(): void
    {
        // SỬA: Dùng AuthMiddleware
        $user = AuthMiddleware::requireAuth();
        $userId = (int) $user['id'];

        // ===== IDEMPOTENCY KEY =====
        $idempotencyKey = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? '';
        if ($idempotencyKey === '') {
            throw new ApiException('Thiếu Idempotency-Key.', 422);
        }
        if (strlen($idempotencyKey) > 64 || !preg_match('/^[a-zA-Z0-9_-]+$/', $idempotencyKey)) {
            throw new ApiException('Idempotency-Key không hợp lệ.', 422);
        }

        // Kiểm tra xem key đã được dùng chưa
        $pdo = Database::connection();
        $checkKey = $pdo->prepare(
            'SELECT id FROM datve WHERE idempotency_key = ? AND user_id = ? LIMIT 1'
        );
        $checkKey->execute([$idempotencyKey, $userId]);
        if ($checkKey->fetch()) {
            throw new ApiException('Yêu cầu đã được xử lý (duplicate).', 409);
        }

        // Đọc dữ liệu JSON từ frontend
        $body = readJsonBody();

        $showtimeId = filter_var(
            $body['showtimeId'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($showtimeId === false) {
            throw new ApiException('Mã suất chiếu không hợp lệ.', 422);
        }

        // Kiểm tra danh sách bắp nước
        $concessions = $body['concessions'] ?? null;

        if (!is_array($concessions)) {
            throw new ApiException('Danh sách bắp nước không hợp lệ.', 422);
        }

        $concessionItems = [];

        foreach ($concessions as $item) {
            if (!is_array($item)) {
                throw new ApiException('Thông tin bắp nước không hợp lệ.', 422);
            }

            $itemId = filter_var(
                $item['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $quantity = filter_var(
                $item['quantity'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 20]]
            );

            if ($itemId === false || $quantity === false) {
                throw new ApiException(
                    'Mã món hoặc số lượng bắp nước không hợp lệ.',
                    422
                );
            }

            if (isset($concessionItems[$itemId])) {
                throw new ApiException(
                    'Danh sách bắp nước có món bị trùng.',
                    422
                );
            }

            $concessionItems[$itemId] = $quantity;
        }

        // Giao việc xử lý đặt vé cho Model (truyền thêm idempotencyKey)
        $data = $this->booking->checkout(
            (int) $userId,
            (int) $showtimeId,
            $concessionItems,
            $idempotencyKey
        );

        jsonResponse(['ok' => true, 'data' => $data]);
    }
}
