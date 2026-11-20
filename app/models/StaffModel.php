<?php
declare(strict_types=1);

final class StaffModel
{
    private const TICKET_SELECT = 'SELECT cv.id, cv.ma_qr_code, cv.gia_ve, cv.thoi_gian_su_dung,
            g.hang_ghe, g.so_thu_tu, g.loai_ghe,
            d.id as dat_ve_id, d.ma_don_hang, d.trang_thai_thanh_toan, d.thoi_gian_dat,
            p.ten_phim, s.thoi_gian_bat_dau, s.thoi_gian_ket_thuc,
            ph.ten_phong, r.ten_rap, u.ho_ten AS khach_hang
     FROM chitietve cv
     JOIN ghe g ON g.id = cv.ghe_id
     JOIN datve d ON d.id = cv.dat_ve_id
     JOIN suatchieu s ON s.id = d.suat_chieu_id
     JOIN phim p ON p.id = s.phim_id
     JOIN phongchieu ph ON ph.id = s.phong_id
     JOIN rapchieu r ON r.id = ph.rap_id
     JOIN nguoidung u ON u.id = d.user_id';

    /**
     * Soát vé bằng mã nhập tay (không quét QR).
     * Nhận cả mã vé riêng của từng vé (ma_qr_code) và mã đơn hàng
     * (ma_don_hang, VD: CS474322) — mã khách thực tế cầm trên tay vì
     * nó hiện to trong modal QR ở trang lịch sử, còn ma_qr_code là
     * chuỗi hex 48 ký tự nên không thể nhập tay được.
     * Mã đơn hàng trả về danh sách vé của đơn để soát từng vé.
     */
    public function verifyTicket(string $code): array
    {
        $ticket = $this->findTicketByQr($code);

        if ($ticket !== null) {
            $status = $this->ticketStatus($ticket);
            return ['status' => $status['status'], 'message' => $status['message'], 'data' => $ticket];
        }

        $tickets = $this->findTicketsByOrder($code);

        if ($tickets === []) {
            return ['status' => 'invalid', 'message' => 'Mã vé không tồn tại.'];
        }

        return [
            'status' => 'order',
            'message' => 'Tìm thấy đơn hàng.',
            'order_code' => $code,
            'tickets' => array_map(function (array $t): array {
                $status = $this->ticketStatus($t);
                return [
                    'qr_code' => $t['ma_qr_code'],
                    'seat' => $t['hang_ghe'] . $t['so_thu_tu'],
                    'seat_type' => $t['loai_ghe'],
                    'movie' => $t['ten_phim'],
                    'cinema_room' => $t['ten_rap'] . ' / ' . $t['ten_phong'],
                    'showtime' => $t['thoi_gian_bat_dau'],
                    'customer' => $t['khach_hang'],
                    'status' => $status['status'],
                    'message' => $status['message'],
                ];
            }, $tickets),
        ];
    }

    private function findTicketByQr(string $code): ?array
    {
        $stmt = Database::connection()->prepare(
            self::TICKET_SELECT . ' WHERE cv.ma_qr_code = ? LIMIT 1'
        );
        $stmt->execute([$code]);
        $ticket = $stmt->fetch();

        return $ticket ?: null;
    }

    private function findTicketsByOrder(string $code): array
    {
        $stmt = Database::connection()->prepare(
            self::TICKET_SELECT . ' WHERE d.ma_don_hang = ? ORDER BY g.hang_ghe, g.so_thu_tu'
        );
        $stmt->execute([$code]);

        return $stmt->fetchAll();
    }

    /** Trạng thái soát vé của một vé (dùng chung cho cả 2 đường tra cứu). */
    private function ticketStatus(array $ticket): array
    {
        switch ($ticket['trang_thai_thanh_toan']) {
            case 'ChuaThanhToan':
                return ['status' => 'pending', 'message' => 'Vé chưa thanh toán.'];
            case 'DaHuy':
                return ['status' => 'cancelled', 'message' => 'Vé đã bị hủy.'];
            case 'DaThanhToan':
                // Kiểm tra đã sử dụng chưa
                if ($ticket['thoi_gian_su_dung'] !== null) {
                    return ['status' => 'used', 'message' => 'Vé đã được sử dụng.'];
                }
                // Kiểm tra suất chiếu đã bắt đầu chưa (chỉ cho check-in khi suất đã bắt đầu)
                $now = new DateTime();
                $start = new DateTime($ticket['thoi_gian_bat_dau']);
                if ($now < $start) {
                    return ['status' => 'early', 'message' => 'Chưa đến giờ chiếu.'];
                }
                return ['status' => 'valid', 'message' => 'Vé hợp lệ.'];
            default:
                return ['status' => 'invalid', 'message' => 'Trạng thái không xác định.'];
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
