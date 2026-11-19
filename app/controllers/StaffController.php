<?php
declare(strict_types=1);

use function App\Middleware\StaffMiddleware;

final class StaffController
{
    public function __construct(private StaffModel $staff)
    {
    }


    // CHECK-IN: Verify mã QR
    // POST /staff/check-in
    // Body: { "qr_code": "..." }
    public function verifyTicket(): void
    {
        StaffMiddleware::requireStaff();

        $body = readJsonBody();

        $qrCode = trim($body['qr_code'] ?? '');
        if ($qrCode === '') {
            throw new ApiException('Thiếu mã QR.', 422);
        }

        $result = $this->staff->verifyTicket($qrCode);

        // Map status code for frontend
        $statusMap = [
            'valid' => 200,
            'pending' => 402,     // Payment required
            'used' => 409,        // Conflict
            'cancelled' => 410,   // Gone
            'early' => 425,       // Too early
            'invalid' => 404,     // Not found
        ];

        $statusCode = $statusMap[$result['status']] ?? 200;

        jsonResponse([
            'ok' => $result['status'] === 'valid',
            'data' => $result,
        ], $statusCode);
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
            throw new ApiException('Thiếu mã QR.', 422);
        }

        $result = $this->staff->confirmCheckin($qrCode);

        jsonResponse(['ok' => true, 'data' => $result]);
    }
}
