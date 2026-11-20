<?php
declare(strict_types=1);

final class OrderController
{
    public function __construct(private OrderModel $orders)
    {
    }

    public function index(): void
    {
        $user = AuthMiddleware::requireAuth();
        $userId = (int) $user['id'];

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'code' => $row['ma_don_hang'],
            'movie' => $row['ten_phim'],
            'showtime' => $row['thoi_gian_bat_dau'],
            'total' => (float) $row['tong_tien'],
            'status' => $row['trang_thai_thanh_toan'],
            'createdAt' => $row['thoi_gian_dat'],
            'cinema' => $row['ten_rap'],
            'address' => $row['dia_chi'],
            'room' => $row['ten_phong'],
            'seats' => $row['ghe_da_chon'] ?? '',
        ], $this->orders->findByUser($userId));

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function detail(): void
    {
        $user = AuthMiddleware::requireAuth();
        $userId = (int) $user['id'];

        $query = [];
        parse_str(
            (string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY),
            $query
        );

        $orderId = filter_var(
            $query['order_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($orderId === false) {
            throw new ApiException('Mã đơn hàng không hợp lệ.', 422);
        }

        $order = $this->orders->findDetail($userId, (int) $orderId);

        if ($order === null) {
            throw new ApiException('Không tìm thấy đơn hàng.', 404);
        }

        jsonResponse(['ok' => true, 'data' => $order]);
    }

    public function cancel(): void
    {
        $user = AuthMiddleware::requireAuth();
        $userId = (int) $user['id'];

        $body = readJsonBody();
        $orderId = filter_var(
            $body['order_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($orderId === false) {
            throw new ApiException('Mã đơn hàng không hợp lệ.', 422);
        }

        if (!$this->orders->cancelOrder($userId, (int) $orderId)) {
            throw new ApiException(
                'Không thể hủy đơn này (đơn đã hủy hoặc suất chiếu đã bắt đầu).',
                409
            );
        }

        jsonResponse(['ok' => true, 'data' => ['cancelled' => true]]);
    }
}
