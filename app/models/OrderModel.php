<?php
declare(strict_types=1);

final class OrderModel
{
    public function findByUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT d.id, d.ma_don_hang, d.tong_tien,
                    d.trang_thai_thanh_toan, d.thoi_gian_dat,
                    p.ten_phim, s.thoi_gian_bat_dau,
                    r.ten_rap, r.dia_chi, pc.ten_phong,
                    GROUP_CONCAT(
                        CONCAT(g.hang_ghe, g.so_thu_tu)
                        ORDER BY g.hang_ghe, g.so_thu_tu
                        SEPARATOR \', \'
                    ) AS ghe_da_chon
             FROM datve d
             JOIN suatchieu s ON s.id = d.suat_chieu_id
             JOIN phim p ON p.id = s.phim_id
             JOIN phongchieu pc ON pc.id = s.phong_id
             JOIN rapchieu r ON r.id = pc.rap_id
             LEFT JOIN chitietve cv ON cv.dat_ve_id = d.id
             LEFT JOIN ghe g ON g.id = cv.ghe_id
             WHERE d.user_id = :user_id
             GROUP BY d.id, d.ma_don_hang, d.tong_tien,
                      d.trang_thai_thanh_toan, d.thoi_gian_dat,
                      p.ten_phim, s.thoi_gian_bat_dau,
                      r.ten_rap, r.dia_chi, pc.ten_phong
             ORDER BY d.thoi_gian_dat DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function findDetail(int $userId, int $orderId): ?array
    {
        $pdo = Database::connection();

        $statement = $pdo->prepare(
            'SELECT d.id, d.ma_don_hang, d.tong_tien,
                    d.phuong_thuc_thanh_toan,
                    d.trang_thai_thanh_toan, d.thoi_gian_dat,
                    p.ten_phim, s.thoi_gian_bat_dau,
                    s.thoi_gian_ket_thuc,
                    r.ten_rap, r.dia_chi, pc.ten_phong
             FROM datve d
             JOIN suatchieu s ON s.id = d.suat_chieu_id
             JOIN phim p ON p.id = s.phim_id
             JOIN phongchieu pc ON pc.id = s.phong_id
             JOIN rapchieu r ON r.id = pc.rap_id
             WHERE d.id = :order_id AND d.user_id = :user_id
             LIMIT 1'
        );
        $statement->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);
        $order = $statement->fetch();

        if (!$order) {
            return null;
        }

        $tickets = $pdo->prepare(
            'SELECT cv.id, cv.gia_ve, cv.ma_qr_code,
                    cv.thoi_gian_su_dung,
                    g.hang_ghe, g.so_thu_tu, g.loai_ghe
             FROM chitietve cv
             JOIN ghe g ON g.id = cv.ghe_id
             WHERE cv.dat_ve_id = :order_id
             ORDER BY g.hang_ghe, g.so_thu_tu'
        );
        $tickets->execute(['order_id' => $orderId]);

        $concessions = $pdo->prepare(
            'SELECT b.ten_mon, b.loai, c.so_luong, c.gia_tien
             FROM chitietdatbapnuoc c
             JOIN bapnuoc b ON b.id = c.bap_nuoc_id
             WHERE c.dat_ve_id = :order_id
             ORDER BY b.ten_mon'
        );
        $concessions->execute(['order_id' => $orderId]);

        $order['tickets'] = $tickets->fetchAll();
        $order['concessions'] = $concessions->fetchAll();

        return $order;
    }

    public function cancelOrder(int $userId, int $orderId): bool
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare(
                'SELECT d.id
                 FROM datve d
                 JOIN suatchieu s ON s.id = d.suat_chieu_id
                 WHERE d.id = :order_id
                 AND d.user_id = :user_id
                 AND d.trang_thai_thanh_toan <> \'DaHuy\'
                 AND s.thoi_gian_bat_dau > NOW()
                 LIMIT 1 FOR UPDATE'
            );
            $check->execute([
                'order_id' => $orderId,
                'user_id' => $userId,
            ]);

            if (!$check->fetch()) {
                $pdo->rollBack();
                return false;
            }

            $update = $pdo->prepare(
                "UPDATE datve
                 SET trang_thai_thanh_toan = 'DaHuy'
                 WHERE id = :order_id"
            );
            $update->execute(['order_id' => $orderId]);

            $pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }
}