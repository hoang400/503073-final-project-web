<?php
declare(strict_types=1);

final class AdminModel
{
    // ============================================
    // MOVIES
    // ============================================

    public function findMovies(?string $search = null, ?string $status = null): array
    {
        $pdo = Database::connection();
        $sql = "SELECT id, ten_phim, the_loai, thoi_luong, poster_url, mo_ta, trailer_url, trang_thai
                FROM phim";
        $conditions = [];
        $params = [];

        if ($search !== null && $search !== '') {
            $conditions[] = 'ten_phim LIKE ?';
            $params[] = "%{$search}%";
        }

        if ($status !== null && $status !== '') {
            $conditions[] = 'trang_thai = ?';
            $params[] = $status;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findMovieById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, ten_phim, the_loai, thoi_luong, poster_url, mo_ta, trailer_url, trang_thai
             FROM phim WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createMovie(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO phim (ten_phim, the_loai, thoi_luong, poster_url, mo_ta, trailer_url, trang_thai)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['ten_phim'],
            $data['the_loai'],
            $data['thoi_luong'],
            $data['poster_url'] ?? null,
            $data['mo_ta'] ?? null,
            $data['trailer_url'] ?? null,
            $data['trang_thai'] ?? 'SAP_CHIEU',
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function updateMovie(int $id, array $data): void
    {
        $pdo = Database::connection();
        $fields = [];
        $params = [];

        $allowed = ['ten_phim', 'the_loai', 'thoi_luong', 'poster_url', 'mo_ta', 'trailer_url', 'trang_thai'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (!$fields) {
            return;
        }

        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE phim SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public function deleteMovie(int $id): void
    {
        // Soft delete: set status to NGUNG_CHIEU
        $this->updateMovie($id, ['trang_thai' => 'NGUNG_CHIEU']);
    }

    // ============================================
    // CINEMAS (RẠP CHIẾU)
    // ============================================

    public function findCinemas(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, ten_rap, dia_chi, thanh_pho FROM rapchieu ORDER BY id'
        );
        return $stmt->fetchAll();
    }

    public function findCinemaById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, ten_rap, dia_chi, thanh_pho FROM rapchieu WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createCinema(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO rapchieu (ten_rap, dia_chi, thanh_pho) VALUES (?, ?, ?)'
        );
        $stmt->execute([$data['ten_rap'], $data['dia_chi'], $data['thanh_pho']]);
        return (int) $pdo->lastInsertId();
    }

    public function updateCinema(int $id, array $data): void
    {
        $pdo = Database::connection();
        $fields = [];
        $params = [];

        $allowed = ['ten_rap', 'dia_chi', 'thanh_pho'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (!$fields) return;

        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE rapchieu SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public function deleteCinema(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM rapchieu WHERE id = ?');
        $stmt->execute([$id]);
    }

    // ============================================
    // ROOMS (PHÒNG CHIẾU) & SEATS
    // ============================================

    public function findRoomsByCinema(int $cinemaId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, rap_id, ten_phong, loai_phong FROM phongchieu WHERE rap_id = ? ORDER BY ten_phong'
        );
        $stmt->execute([$cinemaId]);
        return $stmt->fetchAll();
    }

    public function findAllRooms(): array
    {
        $stmt = Database::connection()->query(
            'SELECT p.id, p.rap_id, p.ten_phong, p.loai_phong, r.ten_rap
             FROM phongchieu p
             JOIN rapchieu r ON r.id = p.rap_id
             ORDER BY r.ten_rap, p.ten_phong'
        );
        return $stmt->fetchAll();
    }

    public function findRoomById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, rap_id, ten_phong, loai_phong FROM phongchieu WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createRoom(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO phongchieu (rap_id, ten_phong, loai_phong) VALUES (?, ?, ?)'
        );
        $stmt->execute([$data['rap_id'], $data['ten_phong'], $data['loai_phong']]);
        return (int) $pdo->lastInsertId();
    }

    public function updateRoom(int $id, array $data): void
    {
        $pdo = Database::connection();
        $fields = [];
        $params = [];

        $allowed = ['rap_id', 'ten_phong', 'loai_phong'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (!$fields) return;

        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE phongchieu SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public function deleteRoom(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM phongchieu WHERE id = ?');
        $stmt->execute([$id]);
    }

    // Seats management
    public function findSeatsByRoom(int $roomId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, phong_id, hang_ghe, so_thu_tu, loai_ghe
             FROM ghe WHERE phong_id = ? ORDER BY hang_ghe, so_thu_tu'
        );
        $stmt->execute([$roomId]);
        return $stmt->fetchAll();
    }

    public function createSeat(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO ghe (phong_id, hang_ghe, so_thu_tu, loai_ghe) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$data['phong_id'], $data['hang_ghe'], $data['so_thu_tu'], $data['loai_ghe']]);
        return (int) $pdo->lastInsertId();
    }

    public function deleteSeat(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM ghe WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function deleteSeatsByRoom(int $roomId): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM ghe WHERE phong_id = ?');
        $stmt->execute([$roomId]);
    }

    // ============================================
    // SHOWTIMES (SUẤT CHIẾU)
    // ============================================

    public function findShowtimes(?int $movieId = null, ?int $roomId = null, ?string $date = null): array
    {
        $pdo = Database::connection();
        $sql = "SELECT s.id, s.phim_id, s.phong_id, s.thoi_gian_bat_dau, s.thoi_gian_ket_thuc, s.gia_ve_co_ban,
                       p.ten_phim, ph.ten_phong, r.ten_rap
                FROM suatchieu s
                JOIN phim p ON p.id = s.phim_id
                JOIN phongchieu ph ON ph.id = s.phong_id
                JOIN rapchieu r ON r.id = ph.rap_id";
        $conditions = [];
        $params = [];

        if ($movieId !== null) {
            $conditions[] = 's.phim_id = ?';
            $params[] = $movieId;
        }

        if ($roomId !== null) {
            $conditions[] = 's.phong_id = ?';
            $params[] = $roomId;
        }

        if ($date !== null) {
            $conditions[] = 'DATE(s.thoi_gian_bat_dau) = ?';
            $params[] = $date;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY s.thoi_gian_bat_dau';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findShowtimeById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT s.id, s.phim_id, s.phong_id, s.thoi_gian_bat_dau, s.thoi_gian_ket_thuc, s.gia_ve_co_ban,
                    p.ten_phim, ph.ten_phong, r.ten_rap
             FROM suatchieu s
             JOIN phim p ON p.id = s.phim_id
             JOIN phongchieu ph ON ph.id = s.phong_id
             JOIN rapchieu r ON r.id = ph.rap_id
             WHERE s.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Kiểm tra trùng lịch trong cùng phòng
     * @return bool true nếu có conflict
     */
    public function checkShowtimeConflict(int $roomId, string $start, string $end, ?int $excludeId = null): bool
    {
        $pdo = Database::connection();
        $sql = 'SELECT COUNT(*) FROM suatchieu
                WHERE phong_id = ?
                  AND thoi_gian_bat_dau < ?
                  AND thoi_gian_ket_thuc > ?';
        $params = [$roomId, $end, $start];

        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function createShowtime(array $data): int
    {
        $pdo = Database::connection();

        // Check conflict
        if ($this->checkShowtimeConflict($data['phong_id'], $data['thoi_gian_bat_dau'], $data['thoi_gian_ket_thuc'])) {
            throw new ApiException('Suất chiếu bị trùng lịch với suất khác trong cùng phòng.', 409);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO suatchieu (phim_id, phong_id, thoi_gian_bat_dau, thoi_gian_ket_thuc, gia_ve_co_ban)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['phim_id'],
            $data['phong_id'],
            $data['thoi_gian_bat_dau'],
            $data['thoi_gian_ket_thuc'],
            $data['gia_ve_co_ban'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function updateShowtime(int $id, array $data): void
    {
        $pdo = Database::connection();

        // Get current showtime for conflict check
        $current = $this->findShowtimeById($id);
        if (!$current) {
            throw new ApiException('Suất chiếu không tồn tại.', 404);
        }

        $roomId = $data['phong_id'] ?? $current['phong_id'];
        $start = $data['thoi_gian_bat_dau'] ?? $current['thoi_gian_bat_dau'];
        $end = $data['thoi_gian_ket_thuc'] ?? $current['thoi_gian_ket_thuc'];

        if ($this->checkShowtimeConflict($roomId, $start, $end, $id)) {
            throw new ApiException('Suất chiếu bị trùng lịch với suất khác trong cùng phòng.', 409);
        }

        $fields = [];
        $params = [];

        $allowed = ['phim_id', 'phong_id', 'thoi_gian_bat_dau', 'thoi_gian_ket_thuc', 'gia_ve_co_ban'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (!$fields) return;

        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE suatchieu SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public function deleteShowtime(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM suatchieu WHERE id = ?');
        $stmt->execute([$id]);
    }

    // ============================================
    // CONCESSIONS (BẮP NƯỚC)
    // ============================================

    public function findConcessions(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, ten_mon, loai, don_gia, hinh_anh, trang_thai FROM bapnuoc ORDER BY loai, ten_mon'
        );
        return $stmt->fetchAll();
    }

    public function findConcessionById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, ten_mon, loai, don_gia, hinh_anh, trang_thai FROM bapnuoc WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createConcession(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO bapnuoc (ten_mon, loai, don_gia, hinh_anh, trang_thai) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['ten_mon'],
            $data['loai'],
            $data['don_gia'],
            $data['hinh_anh'] ?? null,
            $data['trang_thai'] ?? 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function updateConcession(int $id, array $data): void
    {
        $pdo = Database::connection();
        $fields = [];
        $params = [];

        $allowed = ['ten_mon', 'loai', 'don_gia', 'hinh_anh', 'trang_thai'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (!$fields) return;

        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE bapnuoc SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public function deleteConcession(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM bapnuoc WHERE id = ?');
        $stmt->execute([$id]);
    }

    // ============================================
    // BOOKINGS (ĐƠN HÀNG) - ADMIN
    // ============================================

    public function findBookings(?string $status = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $pdo = Database::connection();
        $sql = "SELECT d.id, d.ma_don_hang, d.tong_tien, d.phuong_thuc_thanh_toan, d.trang_thai_thanh_toan, d.thoi_gian_dat,
                       u.ho_ten as khach_hang, u.email,
                       p.ten_phim, s.thoi_gian_bat_dau, ph.ten_phong, r.ten_rap
                FROM datve d
                JOIN nguoidung u ON u.id = d.user_id
                JOIN suatchieu s ON s.id = d.suat_chieu_id
                JOIN phim p ON p.id = s.phim_id
                JOIN phongchieu ph ON ph.id = s.phong_id
                JOIN rapchieu r ON r.id = ph.rap_id";
        $conditions = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $conditions[] = 'd.trang_thai_thanh_toan = ?';
            $params[] = $status;
        }

        if ($dateFrom !== null) {
            $conditions[] = 'DATE(d.thoi_gian_dat) >= ?';
            $params[] = $dateFrom;
        }

        if ($dateTo !== null) {
            $conditions[] = 'DATE(d.thoi_gian_dat) <= ?';
            $params[] = $dateTo;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY d.thoi_gian_dat DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findBookingDetail(int $id): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT d.id, d.ma_don_hang, d.tong_tien, d.phuong_thuc_thanh_toan, d.trang_thai_thanh_toan, d.thoi_gian_dat,
                    u.ho_ten as khach_hang, u.email, u.so_dien_thoai,
                    p.ten_phim, s.thoi_gian_bat_dau, s.thoi_gian_ket_thuc, ph.ten_phong, r.ten_rap
             FROM datve d
             JOIN nguoidung u ON u.id = d.user_id
             JOIN suatchieu s ON s.id = d.suat_chieu_id
             JOIN phim p ON p.id = s.phim_id
             JOIN phongchieu ph ON ph.id = s.phong_id
             JOIN rapchieu r ON r.id = ph.rap_id
             WHERE d.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        // Tickets
        $tickets = $pdo->prepare(
            'SELECT cv.id, cv.gia_ve, cv.ma_qr_code, cv.thoi_gian_su_dung,
                    g.hang_ghe, g.so_thu_tu, g.loai_ghe
             FROM chitietve cv
             JOIN ghe g ON g.id = cv.ghe_id
             WHERE cv.dat_ve_id = ?
             ORDER BY g.hang_ghe, g.so_thu_tu'
        );
        $tickets->execute([$id]);
        $order['tickets'] = $tickets->fetchAll();

        // Concessions
        $concessions = $pdo->prepare(
            'SELECT b.ten_mon, b.loai, c.so_luong, c.gia_tien
             FROM chitietdatbapnuoc c
             JOIN bapnuoc b ON b.id = c.bap_nuoc_id
             WHERE c.dat_ve_id = ?
             ORDER BY b.ten_mon'
        );
        $concessions->execute([$id]);
        $order['concessions'] = $concessions->fetchAll();

        return $order;
    }

    public function confirmPayment(int $id): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            // Get order
            $stmt = $pdo->prepare('SELECT id, trang_thai_thanh_toan FROM datve WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new ApiException('Đơn hàng không tồn tại.', 404);
            }

            if ($order['trang_thai_thanh_toan'] === 'DaThanhToan') {
                throw new ApiException('Đơn hàng đã được thanh toán.', 409);
            }

            if ($order['trang_thai_thanh_toan'] === 'DaHuy') {
                throw new ApiException('Không thể xác nhận đơn hàng đã hủy.', 409);
            }

            // Update status
            $stmt = $pdo->prepare('UPDATE datve SET trang_thai_thanh_toan = ? WHERE id = ?');
            $stmt->execute(['DaThanhToan', $id]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public function cancelBooking(int $id): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('SELECT id, trang_thai_thanh_toan, suat_chieu_id FROM datve WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new ApiException('Đơn hàng không tồn tại.', 404);
            }

            if ($order['trang_thai_thanh_toan'] === 'DaHuy') {
                throw new ApiException('Đơn hàng đã được hủy.', 409);
            }

            // Update status
            $stmt = $pdo->prepare('UPDATE datve SET trang_thai_thanh_toan = ? WHERE id = ?');
            $stmt->execute(['DaHuy', $id]);

            // Release holds (if any)
            $stmt = $pdo->prepare('DELETE FROM ghedanggiu WHERE suat_chieu_id = ?');
            $stmt->execute([$order['suat_chieu_id']]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    // ============================================
    // REPORTS (BÁO CÁO)
    // ============================================

    public function getRevenueReport(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $pdo = Database::connection();

        $conditions = ["d.trang_thai_thanh_toan = 'DaThanhToan'"];
        $params = [];

        if ($dateFrom !== null) {
            $conditions[] = 'DATE(d.thoi_gian_dat) >= ?';
            $params[] = $dateFrom;
        }

        if ($dateTo !== null) {
            $conditions[] = 'DATE(d.thoi_gian_dat) <= ?';
            $params[] = $dateTo;
        }

        $where = implode(' AND ', $conditions);

        // Tổng doanh thu vé
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(d.tong_tien), 0) as total FROM datve d WHERE {$where}");
        $stmt->execute($params);
        $totalRevenue = (float) $stmt->fetchColumn();

        // Doanh thu vé (chỉ vé)
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(cv.gia_ve), 0) as ticket_revenue
            FROM chitietve cv
            JOIN datve d ON d.id = cv.dat_ve_id
            WHERE {$where}
        ");
        $stmt->execute($params);
        $ticketRevenue = (float) $stmt->fetchColumn();

        // Doanh thu bắp nước
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(c.so_luong * c.gia_tien), 0) as concession_revenue
            FROM chitietdatbapnuoc c
            JOIN datve d ON d.id = c.dat_ve_id
            WHERE {$where}
        ");
        $stmt->execute($params);
        $concessionRevenue = (float) $stmt->fetchColumn();

        // Số đơn
        $stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM datve d WHERE {$where}");
        $stmt->execute($params);
        $orderCount = (int) $stmt->fetchColumn();

        // Số vé
        $stmt = $pdo->prepare("SELECT COUNT(*) as ticket_count FROM chitietve cv JOIN datve d ON d.id = cv.dat_ve_id WHERE {$where}");
        $stmt->execute($params);
        $ticketCount = (int) $stmt->fetchColumn();

        return [
            'total_revenue' => $totalRevenue,
            'ticket_revenue' => $ticketRevenue,
            'concession_revenue' => $concessionRevenue,
            'order_count' => $orderCount,
            'ticket_count' => $ticketCount,
        ];
    }

    public function getRevenueByDate(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $pdo = Database::connection();
        $conditions = ["d.trang_thai_thanh_toan = 'DaThanhToan'"];
        $params = [];

        if ($dateFrom !== null) {
            $conditions[] = 'DATE(d.thoi_gian_dat) >= ?';
            $params[] = $dateFrom;
        }

        if ($dateTo !== null) {
            $conditions[] = 'DATE(d.thoi_gian_dat) <= ?';
            $params[] = $dateTo;
        }

        $where = implode(' AND ', $conditions);

        $stmt = $pdo->prepare("
            SELECT DATE(d.thoi_gian_dat) as date,
                   COUNT(DISTINCT d.id) as orders,
                   COUNT(cv.id) as tickets,
                   COALESCE(SUM(cv.gia_ve), 0) as ticket_revenue,
                   COALESCE(SUM(cbn.so_luong * cbn.gia_tien), 0) as concession_revenue,
                   COALESCE(SUM(d.tong_tien), 0) as total_revenue
            FROM datve d
            LEFT JOIN chitietve cv ON cv.dat_ve_id = d.id
            LEFT JOIN chitietdatbapnuoc cbn ON cbn.dat_ve_id = d.id
            WHERE {$where}
            GROUP BY DATE(d.thoi_gian_dat)
            ORDER BY DATE(d.thoi_gian_dat)
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTopMovies(?string $dateFrom = null, ?string $dateTo = null, int $limit = 10): array
    {
        $pdo = Database::connection();
        $conditions = ["d.trang_thai_thanh_toan = 'DaThanhToan'"];
        $params = [];

        if ($dateFrom !== null) {
            $conditions[] = 'DATE(d.thoi_gian_dat) >= ?';
            $params[] = $dateFrom;
        }

        if ($dateTo !== null) {
            $conditions[] = 'DATE(d.thoi_gian_dat) <= ?';
            $params[] = $dateTo;
        }

        $where = implode(' AND ', $conditions);

        $stmt = $pdo->prepare("
            SELECT p.ten_phim,
                   COUNT(DISTINCT d.id) as orders,
                   COUNT(cv.id) as tickets,
                   COALESCE(SUM(cv.gia_ve), 0) as revenue
            FROM datve d
            JOIN suatchieu s ON s.id = d.suat_chieu_id
            JOIN phim p ON p.id = s.phim_id
            JOIN chitietve cv ON cv.dat_ve_id = d.id
            WHERE {$where}
            GROUP BY p.id, p.ten_phim
            ORDER BY tickets DESC
            LIMIT ?
        ");
        $params[] = $limit;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
