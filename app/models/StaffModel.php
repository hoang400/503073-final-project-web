<?php
declare(strict_types=1);

final class StaffModel
{
    /**
     * Verify mã QR/vé
     * Trả về: ['status' => 'valid'|'pending'|'paid'|'used'|'cancelled', 'data' => [...]]
     */
    public function verifyTicket(string $qrCode): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT cv.id, cv.ma_qr_code, cv.gia_ve, cv.thoi_gian_su_dung,
                    g.hang_ghe, g.so_thu_tu, g.loai_ghe,
                    d.id as dat_ve_id, d.ma_don_hang, d.trang_thai_thanh_toan, d.thoi_gian_dat,
                    p.ten_phim, s.thoi_gian_bat_dau, s.thoi_gian_ket_thuc,
                    ph.ten_phong, r.ten_rap
             FROM chitietve cv
             JOIN ghe g ON g.id = cv.ghe_id
             JOIN datve d ON d.id = cv.dat_ve_id
             JOIN suatchieu s ON s.id = d.suat_chieu_id
             JOIN phim p ON p.id = s.phim_id
             JOIN phongchieu ph ON ph.id = s.phong_id
             JOIN rapchieu r ON r.id = ph.rap_id
             WHERE cv.ma_qr_code = ?
             LIMIT 1'
        );
        $stmt->execute([$qrCode]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            return ['status' => 'invalid', 'message' => 'Mã vé không tồn tại.'];
        }

        // Kiểm tra trạng thái đơn hàng
        switch ($ticket['trang_thai_thanh_toan']) {
            case 'ChuaThanhToan':
                return ['status' => 'pending', 'message' => 'Vé chưa thanh toán.', 'data' => $ticket];
            case 'DaHuy':
                return ['status' => 'cancelled', 'message' => 'Vé đã bị hủy.', 'data' => $ticket];
            case 'DaThanhToan':
                // Kiểm tra đã sử dụng chưa
                if ($ticket['thoi_gian_su_dung'] !== null) {
                    return ['status' => 'used', 'message' => 'Vé đã được sử dụng.', 'data' => $ticket];
                }
                // Kiểm tra suất chiếu đã bắt đầu chưa (chỉ cho check-in khi suất đã bắt đầu)
                $now = new DateTime();
                $start = new DateTime($ticket['thoi_gian_bat_dau']);
                if ($now < $start) {
                    return ['status' => 'early', 'message' => 'Chưa đến giờ chiếu.', 'data' => $ticket];
                }
                return ['status' => 'valid', 'message' => 'Vé hợp lệ.', 'data' => $ticket];
            default:
                return ['status' => 'invalid', 'message' => 'Trạng thái không xác định.', 'data' => $ticket];
        }
    }

    /**
     * Xác nhận đã vào rạp (check-in)
     * Chỉ cập nhật thoi_gian_su_dung nếu vé valid
     */
    public function confirmCheckin(string $qrCode): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            // Lock row vé
            $stmt = $pdo->prepare(
                'SELECT cv.id, cv.thoi_gian_su_dung, d.trang_thai_thanh_toan
                 FROM chitietve cv
                 JOIN datve d ON d.id = cv.dat_ve_id
                 WHERE cv.ma_qr_code = ?
                 FOR UPDATE'
            );
            $stmt->execute([$qrCode]);
            $ticket = $stmt->fetch();

            if (!$ticket) {
                throw new ApiException('Mã vé không tồn tại.', 404);
            }

            if ($ticket['trang_thai_thanh_toan'] !== 'DaThanhToan') {
                throw new ApiException('Vé chưa thanh toán hoặc đã hủy.', 409);
            }

            if ($ticket['thoi_gian_su_dung'] !== null) {
                throw new ApiException('Vé đã được sử dụng trước đó.', 409);
            }

            // Cập nhật thời gian sử dụng
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare(
                'UPDATE chitietve SET thoi_gian_su_dung = ? WHERE id = ?'
            );
            $stmt->execute([$now, $ticket['id']]);

            $pdo->commit();

            return [
                'success' => true,
                'message' => 'Xác nhận vào rạp thành công.',
                'checkin_time' => $now,
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
