-- CineStar Sample Data - FIXED
-- Chạy sau schema.sql: mysql -u root -p cinestar < database/seed.sql

USE cinestar;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- NGƯỜI DÙNG
-- Mật khẩu mặc định: "12345678" (đã hash BCrypt)
-- ============================================
INSERT INTO `nguoidung` (`ho_ten`, `email`, `mat_khau`, `so_dien_thoai`, `vai_tro`) VALUES
('Admin CineStar', 'admin@cinestar.vn', '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '0901234567', 'ADMIN'),
('Nhân viên A', 'staff@cinestar.vn', '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '0901234568', 'STAFF'),
('Nguyễn Văn An', 'an.nguyen@email.com', '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '0912345678', 'CUSTOMER'),
('Trần Thị Bình', 'binh.tran@email.com', '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '0912345679', 'CUSTOMER'),
('Lê Hoàng Cường', 'cuong.le@email.com', '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW', '0912345680', 'CUSTOMER');

-- ============================================
-- RẠP CHIẾU
-- ============================================
INSERT INTO `rapchieu` (`ten_rap`, `dia_chi`, `thanh_pho`) VALUES
('CineStar Vincom Center', '72 Lê Thánh Tôn, Bến Nghé, Quận 1', 'TP. Hồ Chí Minh'),
('CineStar Aeon Mall Tân Phú', '30 Bờ Bao Tân Thắng, Sơn Kỳ, Tân Phú', 'TP. Hồ Chí Minh'),
('CineStar Lotte Mart Tây Hồ', '272 Nguyễn Trãi, Thuỷ Khê, Tây Hồ', 'Hà Nội'),
('CineStar Vincom Mega Mall', '200 Nguyễn Xí, Phường 26, Bình Thạnh', 'TP. Hồ Chí Minh');

-- ============================================
-- PHÒNG CHIẾU
-- ============================================
INSERT INTO `phongchieu` (`rap_id`, `ten_phong`, `loai_phong`) VALUES
(1, 'Phòng 1', '2D'), (1, 'Phòng 2', '3D'), (1, 'Phòng 3', 'IMAX'),
(2, 'Phòng 1', '2D'), (2, 'Phòng 2', '3D'),
(3, 'Phòng 1', '2D'), (3, 'Phòng 2', '4DX'),
(4, 'Phòng 1', '2D'), (4, 'Phòng 2', '3D'), (4, 'Phòng 3', 'IMAX');

-- ============================================
-- GHẺ (Procedure tạo 360 ghế)
-- ============================================
DELIMITER $$
CREATE PROCEDURE seed_seats()
BEGIN
    DECLARE v_phong_id INT UNSIGNED DEFAULT 1;
    DECLARE v_hang CHAR(1);
    DECLARE v_so INT UNSIGNED;
    DECLARE v_loai VARCHAR(50);
    
    WHILE v_phong_id <= 9 DO
        SET v_hang = 'A';
        WHILE v_hang <= 'E' DO
            SET v_so = 1;
            WHILE v_so <= 8 DO
                IF v_hang IN ('A','B') THEN
                    SET v_loai = 'VIP';
                ELSEIF v_hang = 'E' AND v_so % 2 = 0 THEN
                    SET v_loai = 'Couple';
                ELSE
                    SET v_loai = 'Thuong';
                END IF;
                
                INSERT INTO `ghe` (`phong_id`, `hang_ghe`, `so_thu_tu`, `loai_ghe`)
                VALUES (v_phong_id, v_hang, v_so, v_loai);
                
                SET v_so = v_so + 1;
            END WHILE;
            SET v_hang = CHAR(ORD(v_hang) + 1);
        END WHILE;
        SET v_phong_id = v_phong_id + 1;
    END WHILE;
END$$
DELIMITER ;

CALL seed_seats();
DROP PROCEDURE seed_seats;

-- ============================================
-- PHIM
-- ============================================
INSERT INTO `phim` (`ten_phim`, `the_loai`, `thoi_luong`, `poster_url`, `mo_ta`, `trailer_url`, `trang_thai`) VALUES
('Lật Mặt 7: Một Điều Ước', 'Hài hước, Tâm lý', 120, 'https://image.tmdb.org/t/p/w500/lat_mat_7.jpg', 'Bộ phim hài hước Việt Nam về gia đình và điều ước.', 'https://youtube.com/watch?v=latmat7', 'DANG_CHIEU'),
('Kẻ Ăn Hồn', 'Hành động, Giả tưởng', 135, 'https://image.tmdb.org/t/p/w500/ke_an_hon.jpg', 'Cuộc chiến giữa thiện và ác trong thế giới siêu nhiên.', 'https://youtube.com/watch?v=keanhon', 'DANG_CHIEU'),
('Siêu Nhân Gấu Trúc', 'Hoạt hình, Gia đình', 95, 'https://image.tmdb.org/t/p/w500/sieu_nhan_gau_truc.jpg', 'Chuyện về chú gấu trúc có siêu năng lực.', 'https://youtube.com/watch?v=gaotruc', 'DANG_CHIEU'),
('Mật Vụ Biển Đông', 'Hành động, Điệp viên', 140, 'https://image.tmdb.org/t/p/w500/mat_vu_bien_dong.jpg', 'Nhiệm vụ bí mật trên biển Đông đầy kịch tính.', 'https://youtube.com/watch?v=matvubien', 'SAP_CHIEU'),
('Tình Yêu Mau Quên', 'Lãng mạn, Tâm lý', 110, 'https://image.tmdb.org/t/p/w500/tinh_yeu_mau_quen.jpg', 'Câu chuyện tình yêu giữa quá khứ và hiện tại.', 'https://youtube.com/watch?v=tinhyeu', 'SAP_CHIEU'),
('Kho Báu Cũ', 'Phiêu lưu, Hài hước', 125, 'https://image.tmdb.org/t/p/w500/kho_bau_cu.jpg', 'Nhóm bạn tìm kiếm kho báu thất lạc.', 'https://youtube.com/watch?v=kho_bau', 'NGUNG_CHIEU');

-- ============================================
-- BẮP NƯỚC
-- ============================================
INSERT INTO `bapnuoc` (`ten_mon`, `loai`, `don_gia`, `hinh_anh`, `trang_thai`) VALUES
('Bắp rang bơ nhỏ', 'Bap', 45000, 'https://example.com/bap_nho.jpg', 1),
('Bắp rang bơ vừa', 'Bap', 65000, 'https://example.com/bap_vua.jpg', 1),
('Bắp rang bơ lớn', 'Bap', 85000, 'https://example.com/bap_lon.jpg', 1),
('Bắp phô mai nhỏ', 'Bap', 55000, 'https://example.com/bap_phomai_nho.jpg', 1),
('Bắp phô mai vừa', 'Bap', 75000, 'https://example.com/bap_phomai_vua.jpg', 1),
('Bắp caramel nhỏ', 'Bap', 60000, 'https://example.com/bap_caramel_nho.jpg', 1),
('Coca-Cola nhỏ', 'Nuoc', 30000, 'https://example.com/coca_nho.jpg', 1),
('Coca-Cola vừa', 'Nuoc', 40000, 'https://example.com/coca_vua.jpg', 1),
('Pepsi nhỏ', 'Nuoc', 30000, 'https://example.com/pepsi_nho.jpg', 1),
('Pepsi vừa', 'Nuoc', 40000, 'https://example.com/pepsi_vua.jpg', 1),
('Nước suối', 'Nuoc', 15000, 'https://example.com/nuoc_suoi.jpg', 1),
('Combo 1 (Bắp vừa + 2 nước)', 'Combo', 120000, 'https://example.com/combo1.jpg', 1),
('Combo 2 (Bắp lớn + 2 nước + 1 bắp phô mai)', 'Combo', 180000, 'https://example.com/combo2.jpg', 1),
('Combo 3 (2 Bắp vừa + 3 nước)', 'Combo', 220000, 'https://example.com/combo3.jpg', 1);

-- ============================================
-- SUẤT CHIẾU (SỬA LỖI DATE_ADD - DÙNG HOUR_MINUTE)
-- ============================================
-- Phim 1 (Lật Mặt 7) - Phòng 1,2,3 (Rạp 1)
INSERT INTO `suatchieu` (`phim_id`, `phong_id`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `gia_ve_co_ban`) VALUES
-- Hôm nay
(1, 1, DATE_ADD(CURDATE(), INTERVAL '10:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '12:00' HOUR_MINUTE), 90000),
(1, 1, DATE_ADD(CURDATE(), INTERVAL '13:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '15:00' HOUR_MINUTE), 90000),
(1, 1, DATE_ADD(CURDATE(), INTERVAL '16:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '18:00' HOUR_MINUTE), 100000),
(1, 1, DATE_ADD(CURDATE(), INTERVAL '19:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '21:00' HOUR_MINUTE), 110000),
(1, 2, DATE_ADD(CURDATE(), INTERVAL '10:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '12:30' HOUR_MINUTE), 100000),
(1, 2, DATE_ADD(CURDATE(), INTERVAL '13:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '15:30' HOUR_MINUTE), 100000),
(1, 2, DATE_ADD(CURDATE(), INTERVAL '16:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '18:30' HOUR_MINUTE), 110000),
(1, 3, DATE_ADD(CURDATE(), INTERVAL '11:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '13:00' HOUR_MINUTE), 130000),
(1, 3, DATE_ADD(CURDATE(), INTERVAL '14:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '16:00' HOUR_MINUTE), 130000),
(1, 3, DATE_ADD(CURDATE(), INTERVAL '17:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '19:00' HOUR_MINUTE), 150000),
(1, 3, DATE_ADD(CURDATE(), INTERVAL '20:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '22:00' HOUR_MINUTE), 150000),
-- Ngày mai
(1, 1, DATE_ADD(CURDATE(), INTERVAL '34:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '36:00' HOUR_MINUTE), 90000),
(1, 1, DATE_ADD(CURDATE(), INTERVAL '37:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '39:00' HOUR_MINUTE), 90000),
(1, 1, DATE_ADD(CURDATE(), INTERVAL '40:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '42:00' HOUR_MINUTE), 100000),
(1, 1, DATE_ADD(CURDATE(), INTERVAL '43:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '45:00' HOUR_MINUTE), 110000),
(1, 2, DATE_ADD(CURDATE(), INTERVAL '34:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '36:30' HOUR_MINUTE), 100000),
(1, 2, DATE_ADD(CURDATE(), INTERVAL '37:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '39:30' HOUR_MINUTE), 100000),
(1, 2, DATE_ADD(CURDATE(), INTERVAL '40:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '42:30' HOUR_MINUTE), 110000),
(1, 3, DATE_ADD(CURDATE(), INTERVAL '35:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '37:00' HOUR_MINUTE), 130000),
(1, 3, DATE_ADD(CURDATE(), INTERVAL '38:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '40:00' HOUR_MINUTE), 130000),
(1, 3, DATE_ADD(CURDATE(), INTERVAL '41:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '43:00' HOUR_MINUTE), 150000),
(1, 3, DATE_ADD(CURDATE(), INTERVAL '44:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '46:00' HOUR_MINUTE), 150000);

-- Phim 2 (Kẻ Ăn Hồn) - Phòng 4,5 (Rạp 2)
INSERT INTO `suatchieu` (`phim_id`, `phong_id`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `gia_ve_co_ban`) VALUES
(2, 4, DATE_ADD(CURDATE(), INTERVAL '10:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '12:15' HOUR_MINUTE), 95000),
(2, 4, DATE_ADD(CURDATE(), INTERVAL '13:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '15:15' HOUR_MINUTE), 95000),
(2, 4, DATE_ADD(CURDATE(), INTERVAL '16:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '18:15' HOUR_MINUTE), 105000),
(2, 4, DATE_ADD(CURDATE(), INTERVAL '19:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '21:15' HOUR_MINUTE), 115000),
(2, 5, DATE_ADD(CURDATE(), INTERVAL '10:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '12:45' HOUR_MINUTE), 105000),
(2, 5, DATE_ADD(CURDATE(), INTERVAL '13:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '15:45' HOUR_MINUTE), 105000),
(2, 5, DATE_ADD(CURDATE(), INTERVAL '16:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '18:45' HOUR_MINUTE), 115000),
(2, 5, DATE_ADD(CURDATE(), INTERVAL '19:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '21:45' HOUR_MINUTE), 125000);

-- Phim 3 (Siêu Nhân Gấu Trúc) - Phòng 6,7 (Rạp 3)
INSERT INTO `suatchieu` (`phim_id`, `phong_id`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `gia_ve_co_ban`) VALUES
(3, 6, DATE_ADD(CURDATE(), INTERVAL '9:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '10:35' HOUR_MINUTE), 75000),
(3, 6, DATE_ADD(CURDATE(), INTERVAL '11:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '12:35' HOUR_MINUTE), 75000),
(3, 6, DATE_ADD(CURDATE(), INTERVAL '14:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '15:35' HOUR_MINUTE), 85000),
(3, 6, DATE_ADD(CURDATE(), INTERVAL '16:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '17:35' HOUR_MINUTE), 85000),
(3, 7, DATE_ADD(CURDATE(), INTERVAL '10:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '11:35' HOUR_MINUTE), 95000),
(3, 7, DATE_ADD(CURDATE(), INTERVAL '13:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '14:35' HOUR_MINUTE), 95000),
(3, 7, DATE_ADD(CURDATE(), INTERVAL '15:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '16:35' HOUR_MINUTE), 105000);

-- Phim 4 (Mật Vụ Biển Đông - Sắp chiếu) - Phòng 8,9,10 (Rạp 4)
INSERT INTO `suatchieu` (`phim_id`, `phong_id`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `gia_ve_co_ban`) VALUES
(4, 8, DATE_ADD(CURDATE(), INTERVAL '82:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '84:20' HOUR_MINUTE), 100000),
(4, 8, DATE_ADD(CURDATE(), INTERVAL '85:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '87:20' HOUR_MINUTE), 100000),
(4, 8, DATE_ADD(CURDATE(), INTERVAL '88:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '90:20' HOUR_MINUTE), 110000),
(4, 8, DATE_ADD(CURDATE(), INTERVAL '91:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '93:20' HOUR_MINUTE), 120000),
(4, 9, DATE_ADD(CURDATE(), INTERVAL '82:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '84:50' HOUR_MINUTE), 110000),
(4, 9, DATE_ADD(CURDATE(), INTERVAL '85:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '87:50' HOUR_MINUTE), 110000),
(4, 9, DATE_ADD(CURDATE(), INTERVAL '88:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '90:50' HOUR_MINUTE), 120000),
(4, 10, DATE_ADD(CURDATE(), INTERVAL '83:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '85:20' HOUR_MINUTE), 140000),
(4, 10, DATE_ADD(CURDATE(), INTERVAL '86:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '88:20' HOUR_MINUTE), 140000),
(4, 10, DATE_ADD(CURDATE(), INTERVAL '89:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '91:20' HOUR_MINUTE), 160000),
(4, 10, DATE_ADD(CURDATE(), INTERVAL '92:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '94:20' HOUR_MINUTE), 160000);

SET FOREIGN_KEY_CHECKS = 1;
