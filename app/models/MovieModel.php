<?php
declare(strict_types=1);

final class MovieModel
{
    
    public function findVisible(): array
    {
        $statement = Database::connection()->query(
            "SELECT
                id,
                ten_phim,
                ten_ngan,
                ten_hien_thi_dropdown,
                the_loai,
                thoi_luong,
                poster_url,
                mo_ta,
                trailer_url,
                do_tuoi,
                mo_ta_do_tuoi,
                diem_imdb,
                dao_dien,
                bien_kich,
                dien_vien,
                trang_thai
            FROM phim
            WHERE trang_thai IN ('DANG_CHIEU', 'SAP_CHIEU')
            ORDER BY id DESC"
        );

        return $statement->fetchAll();
    }

}