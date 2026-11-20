<?php
declare(strict_types=1);

use function App\Middleware\StaffMiddleware;

final class StaffController
{
    public function __construct(private StaffModel $staff)
    {
    }


    // CHECK-IN: Soát vé bằng mã nhập tay
    // POST /staff/check-in
    // Body: { "qr_code": "..." } (mã vé hoặc mã đơn hàng)
    public function verifyTicket(): void
    {
        StaffMiddleware::requireStaff();

        $body = readJsonBody();

        $qrCode = trim($body['qr_code'] ?? '');
        if ($qrCode === '') {
            throw new ApiException('Thiếu mã vé.', 422);
        }

        // Tra cứu là thao tác đọc: vé hợp lệ hay không đều là kết quả
        // tra cứu thành công (trả 200) để frontend hiển thị đúng trạng
        // thái. Trước đây trả 402/404/409... làm api() ném lỗi chung
        // "Không thể thực hiện yêu cầu." và không hiện chi tiết vé.
        $result = $this->staff->verifyTicket($qrCode);

        jsonResponse(['ok' => true, 'data' => $result]);
    }

    // CHECK-IN: Xác nhận vào rạp
    // POST /staff/check-in/confirm
    // Body: { "qr_code": "..." }
    public function confirmCheckin(): void
    {
        StaffMiddleware::requireStaff();

        $body = readJsonBody();

        $qrCode = trim($body['qr_code'] ?? '');
        if ($qrCode === '') {
            throw new ApiException('Thiếu mã vé.', 422);
        }

        $result = $this->staff->confirmCheckin($qrCode);

        jsonResponse(['ok' => true, 'data' => $result]);
    }
}
