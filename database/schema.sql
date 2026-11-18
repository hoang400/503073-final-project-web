-- Chạy file này trước: mysql -u root -p < database/schema.sql
-- Sau đó chạy: mysql -u root -p cinestar < database/seed.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- 1. NGƯỜI DÙNG
DROP TABLE IF EXISTS `nguoidung`;
CREATE TABLE `nguoidung` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mat_khau` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `so_dien_thoai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vai_tro` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CUSTOMER',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nguoidung_email` (`email`),
  CONSTRAINT `chk_nguoidung_vai_tro` CHECK ((`vai_tro` in ('ADMIN','CUSTOMER','STAFF')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. PHIM
DROP TABLE IF EXISTS `phim`;
CREATE TABLE `phim` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ten_phim` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `the_loai` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoi_luong` int unsigned NOT NULL,
  `poster_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mo_ta` text COLLATE utf8mb4_unicode_ci,
  `trailer_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trang_thai` enum('SAP_CHIEU','DANG_CHIEU','NGUNG_CHIEU') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SAP_CHIEU',
  PRIMARY KEY (`id`),
  CONSTRAINT `chk_phim_thoi_luong` CHECK ((`thoi_luong` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3. RẠP CHIẾU
DROP TABLE IF EXISTS `rapchieu`;
CREATE TABLE `rapchieu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ten_rap` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dia_chi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thanh_pho` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 4. PHÒNG CHIẾU
DROP TABLE IF EXISTS `phongchieu`;
CREATE TABLE `phongchieu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `rap_id` int unsigned NOT NULL,
  `ten_phong` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loai_phong` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_phongchieu_rap_ten` (`rap_id`,`ten_phong`),
  CONSTRAINT `fk_phongchieu_rap` FOREIGN KEY (`rap_id`) REFERENCES `rapchieu` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 5. GHẾ
DROP TABLE IF EXISTS `ghe`;
CREATE TABLE `ghe` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `phong_id` int unsigned NOT NULL,
  `hang_ghe` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `so_thu_tu` int unsigned NOT NULL,
  `loai_ghe` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ghe_vi_tri` (`phong_id`,`hang_ghe`,`so_thu_tu`),
  CONSTRAINT `fk_ghe_phong` FOREIGN KEY (`phong_id`) REFERENCES `phongchieu` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `chk_ghe_loai` CHECK ((`loai_ghe` in ('Thuong','VIP','Couple'))),
  CONSTRAINT `chk_ghe_so_thu_tu` CHECK ((`so_thu_tu` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 6. SUẤT CHIẾU
DROP TABLE IF EXISTS `suatchieu`;
CREATE TABLE `suatchieu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `phim_id` int unsigned NOT NULL,
  `phong_id` int unsigned NOT NULL,
  `thoi_gian_bat_dau` datetime NOT NULL,
  `thoi_gian_ket_thuc` datetime NOT NULL,
  `gia_ve_co_ban` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_suatchieu_phong_bat_dau` (`phong_id`,`thoi_gian_bat_dau`),
  KEY `idx_suatchieu_phim_bat_dau` (`phim_id`,`thoi_gian_bat_dau`),
  CONSTRAINT `fk_suatchieu_phim` FOREIGN KEY (`phim_id`) REFERENCES `phim` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_suatchieu_phong` FOREIGN KEY (`phong_id`) REFERENCES `phongchieu` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `chk_suatchieu_gia` CHECK ((`gia_ve_co_ban` >= 0)),
  CONSTRAINT `chk_suatchieu_thoi_gian` CHECK ((`thoi_gian_ket_thuc` > `thoi_gian_bat_dau`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 7. GHẺ ĐANG GIỮ
DROP TABLE IF EXISTS `ghedanggiu`;
CREATE TABLE `ghedanggiu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `suat_chieu_id` int unsigned NOT NULL,
  `ghe_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ghedanggiu_suat_ghe` (`suat_chieu_id`,`ghe_id`),
  KEY `idx_ghedanggiu_user` (`user_id`),
  KEY `idx_ghedanggiu_expires` (`expires_at`),
  KEY `fk_ghedanggiu_ghe` (`ghe_id`),
  CONSTRAINT `fk_ghedanggiu_ghe` FOREIGN KEY (`ghe_id`) REFERENCES `ghe` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_ghedanggiu_suat` FOREIGN KEY (`suat_chieu_id`) REFERENCES `suatchieu` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_ghedanggiu_user` FOREIGN KEY (`user_id`) REFERENCES `nguoidung` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 8. ĐẶT VÉ (ĐƠN HÀNG)
DROP TABLE IF EXISTS `datve`;
CREATE TABLE `datve` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `suat_chieu_id` int unsigned NOT NULL,
  `ma_don_hang` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tong_tien` decimal(10,2) NOT NULL DEFAULT '0.00',
  `phuong_thuc_thanh_toan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trang_thai_thanh_toan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ChuaThanhToan',
  `thoi_gian_dat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_datve_ma_don` (`ma_don_hang`),
  KEY `idx_datve_user_thoi_gian` (`user_id`,`thoi_gian_dat`),
  KEY `idx_datve_suat` (`suat_chieu_id`),
  CONSTRAINT `fk_datve_suat` FOREIGN KEY (`suat_chieu_id`) REFERENCES `suatchieu` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_datve_user` FOREIGN KEY (`user_id`) REFERENCES `nguoidung` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `chk_datve_tong_tien` CHECK ((`tong_tien` >= 0)),
  CONSTRAINT `chk_datve_trang_thai` CHECK ((`trang_thai_thanh_toan` in ('ChuaThanhToan','DaThanhToan','DaHuy')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 9. CHI TIẾT VÉ
DROP TABLE IF EXISTS `chitietve`;
CREATE TABLE `chitietve` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `dat_ve_id` int unsigned NOT NULL,
  `ghe_id` int unsigned NOT NULL,
  `gia_ve` decimal(10,2) NOT NULL,
  `ma_qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thoi_gian_su_dung` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chitietve_don_ghe` (`dat_ve_id`,`ghe_id`),
  UNIQUE KEY `uq_chitietve_qr` (`ma_qr_code`),
  KEY `fk_chitietve_ghe` (`ghe_id`),
  CONSTRAINT `fk_chitietve_datve` FOREIGN KEY (`dat_ve_id`) REFERENCES `datve` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_chitietve_ghe` FOREIGN KEY (`ghe_id`) REFERENCES `ghe` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `chk_chitietve_gia` CHECK ((`gia_ve` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 10. BẮP NƯỚC
DROP TABLE IF EXISTS `bapnuoc`;
CREATE TABLE `bapnuoc` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ten_mon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `don_gia` decimal(10,2) NOT NULL,
  `hinh_anh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trang_thai` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  CONSTRAINT `chk_bapnuoc_gia` CHECK ((`don_gia` >= 0)),
  CONSTRAINT `chk_bapnuoc_loai` CHECK ((`loai` in ('Bap','Nuoc','Combo'))),
  CONSTRAINT `chk_bapnuoc_trang_thai` CHECK ((`trang_thai` in (0,1)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 11. CHI TIẾT ĐẶT BẮP NƯỚC
DROP TABLE IF EXISTS `chitietdatbapnuoc`;
CREATE TABLE `chitietdatbapnuoc` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `dat_ve_id` int unsigned NOT NULL,
  `bap_nuoc_id` int unsigned NOT NULL,
  `so_luong` int unsigned NOT NULL,
  `gia_tien` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chitietbapnuoc_don_mon` (`dat_ve_id`,`bap_nuoc_id`),
  KEY `fk_chitietbapnuoc_mon` (`bap_nuoc_id`),
  CONSTRAINT `fk_chitietbapnuoc_datve` FOREIGN KEY (`dat_ve_id`) REFERENCES `datve` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_chitietbapnuoc_mon` FOREIGN KEY (`bap_nuoc_id`) REFERENCES `bapnuoc` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `chk_chitietbapnuoc_gia` CHECK ((`gia_tien` >= 0)),
  CONSTRAINT `chk_chitietbapnuoc_so_luong` CHECK ((`so_luong` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
