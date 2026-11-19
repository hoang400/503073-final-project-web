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

-- GHẾ (Procedure tạo 400 ghế cho 10 phòng)
DELIMITER $$
CREATE PROCEDURE seed_seats()
BEGIN
    DECLARE v_phong_id INT UNSIGNED DEFAULT 1;
    DECLARE v_hang CHAR(1);
    DECLARE v_so INT UNSIGNED;
    DECLARE v_loai VARCHAR(50);
    
    WHILE v_phong_id <= 10 DO
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

-- ============================================
-- PHIM - DỮ LIỆU TỪ FRONTEND ZIP
-- Thứ tự phim được giữ nguyên để khớp phim_id 1-4
-- trong các suất chiếu bên dưới.
-- ============================================
INSERT INTO `phim` (
    `ten_phim`,
    `ten_ngan`,
    `ten_hien_thi_dropdown`,
    `the_loai`,
    `thoi_luong`,
    `poster_url`,
    `mo_ta`,
    `trailer_url`,
    `do_tuoi`,
    `mo_ta_do_tuoi`,
    `diem_imdb`,
    `dao_dien`,
    `bien_kich`,
    `dien_vien`,
    `trang_thai`
) VALUES

(
    'Godzilla x Kong: Đế Chế Mới (Godzilla x Kong: The New Empire)',
    'Godzilla x Kong',
    'Godzilla x Kong: Đế Chế Mới (2D Vietsub)',
    'Hành Động, Viễn Tưởng, Phiêu Lưu',
    115,
    'https://image.tmdb.org/t/p/w780/z1p34vh7dEOnLDmyCrlUVLuoDzd.jpg',
    'Trận chiến hoành tráng giữa các Titan tiếp tục bùng nổ! Sau những sự kiện đối đầu nảy lửa trước đó, Godzilla và Kong đối mặt với một mối đe dọa sinh tồn hoàn toàn mới, khổng lồ và tàn bạo hơn bao giờ hết ẩn giấu sâu bên trong Trái Đất Rỗng (Hollow Earth). Sinh vật cổ đại Skar King cùng đội quân quái thú hung hãn đe dọa hủy diệt toàn bộ hệ sinh thái thế giới loài người.

Để bảo vệ sự sống còn của Trái Đất, hai huyền thoại Godzilla và Kong buộc phải gạt bỏ hiềm khích cũ, hợp sức chiến đấu bên cạnh những trang bị công nghệ đỉnh cao. Tác phẩm mang đến những pha giao tranh hoành tráng, mãn nhãn cùng kỹ xảo đỉnh cao của điện ảnh Hollywood.',
    'https://www.youtube.com/watch?v=lV1OOlGwExM',
    'C13',
    'C13 - Khán giả trên 13 tuổi',
    7.4,
    'Adam Wingard',
    'Terry Rossio, Simon Barrett, Jeremy Slater',
    'Rebecca Hall, Brian Tyree Henry, Dan Stevens',
    'DANG_CHIEU'
),

(
    'Avengers: Hồi Kết (Avengers: Endgame)',
    'Avengers: Endgame',
    'Avengers: Hồi Kết (2D Vietsub)',
    'Hành Động, Sci-Fi, Siêu Anh Hùng',
    181,
    'https://image.tmdb.org/t/p/w780/ulzhLuWrPK07P1YkdWQLZnQh1JL.jpg',
    'Sau cú búng tay tàn khốc của Thanos trong Cuộc Chiến Vô Cực làm biến mất một nửa sinh linh trong vũ trụ, biệt đội Avengers chìm trong đau thương và thất vọng. Những người còn sống sót gồm Iron Man, Captain America, Thor, Black Widow và Hulk phải tìm cách vượt qua nỗi đau mất mát để khôi phục lại trật tự thế giới.

Với sự xuất hiện bất ngờ của Ant-Man và phát kiến du hành thời gian qua Cõi Lượng Tử, các anh hùng quyết định dấn thân vào một sứ mệnh mạo hiểm chưa từng có: du hành về quá khứ để thu thập các Viên Đá Vô Cực trước Thanos. Đây là trận chiến cuối cùng đầy xúc động quyết định số phận vũ trụ.',
    'https://www.youtube.com/watch?v=TcMBFSGVi1c',
    'C13',
    'C13 - Khán giả trên 13 tuổi',
    8.4,
    'Anthony Russo, Joe Russo',
    'Christopher Markus, Stephen McFeely',
    'Robert Downey Jr., Chris Evans, Mark Ruffalo, Scarlett Johansson',
    'DANG_CHIEU'
),

(
    'Dune: Hành Tinh Cát - Phần Hai (Dune: Part Two)',
    'Dune: Part Two',
    'Dune: Hành Tinh Cát - Phần Hai (2D Vietsub)',
    'Hành Động, Viễn Tưởng, Phiêu Lưu',
    166,
    'https://image.tmdb.org/t/p/w780/6izwz7rsy95ARzTR3poZ8H6c5pp.jpg',
    'Hành trình tiếp theo của Paul Atreides khi anh cùng mẹ là phu nhân Jessica hội ngộ với người Fremen trên hành tinh sa mạc khắc nghiệt Arrakis. Mối tình nảy nở giữa Paul và Chani cùng sự công nhận của các chiến binh sa mạc đã biến Paul trở thành vị cứu tinh Lisan al-Gaib theo lời tiên tri.

Đứng trước những âm mưu tàn bạo của gia tộc Harkonnen và Hoàng đế vũ trụ, Paul buộc phải đưa ra lựa chọn sinh tử giữa tình yêu cá nhân và định mệnh giải phóng Arrakis. Tác phẩm sở hữu quy mô hoành tráng, âm nhạc ma mị và góc quay choáng ngợp đạt giải Oscar.',
    'https://www.youtube.com/watch?v=Way9Dexny3w',
    'C16',
    'C16 - Khán giả trên 16 tuổi',
    8.6,
    'Denis Villeneuve',
    'Denis Villeneuve, Jon Spaihts',
    'Timothée Chalamet, Zendaya, Rebecca Ferguson, Javier Bardem',
    'DANG_CHIEU'
),

(
    'Spider-Man: Không Còn Nhà (Spider-Man: No Way Home)',
    'Spider-Man: No Way Home',
    'Spider-Man: Không Còn Nhà (2D Lồng Tiếng)',
    'Hành Động, Siêu Anh Hùng, Phiêu Lưu',
    148,
    'https://image.tmdb.org/t/p/w780/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg',
    'Lần đầu tiên trong lịch sử điện ảnh của Người Nhện, danh tính thật Peter Parker bị Mysterio vạch trần trước toàn thế giới. Cuộc sống riêng tư cùng tương lai của Peter và các bạn thân MJ, Ned hoàn toàn bị xáo trộn.

Trong sự bế tắc, Peter đã nhờ đến Doctor Strange thực hiện một câu bùa phép xóa ký ký ức của mọi người. Tuy nhiên, sự cố ma thuật xảy ra đã vô tình xé rách ranh giới Đa Vũ Trụ, kéo theo hàng loạt ác nhân huyền thoại từ các vũ trụ song song trở lại đối đầu với Spider-Man.',
    'https://www.youtube.com/watch?v=JfVOs4VSpmA',
    'C13',
    'C13 - Khán giả trên 13 tuổi',
    8.2,
    'Jon Watts',
    'Chris McKenna, Erik Sommers',
    'Tom Holland, Zendaya, Benedict Cumberbatch, Andrew Garfield',
    'SAP_CHIEU'
);


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


INSERT INTO `suatchieu` (`phim_id`, `phong_id`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `gia_ve_co_ban`) VALUES
(2, 4, DATE_ADD(CURDATE(), INTERVAL '10:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '12:15' HOUR_MINUTE), 95000),
(2, 4, DATE_ADD(CURDATE(), INTERVAL '13:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '15:15' HOUR_MINUTE), 95000),
(2, 4, DATE_ADD(CURDATE(), INTERVAL '16:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '18:15' HOUR_MINUTE), 105000),
(2, 4, DATE_ADD(CURDATE(), INTERVAL '19:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '21:15' HOUR_MINUTE), 115000),
(2, 5, DATE_ADD(CURDATE(), INTERVAL '10:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '12:45' HOUR_MINUTE), 105000),
(2, 5, DATE_ADD(CURDATE(), INTERVAL '13:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '15:45' HOUR_MINUTE), 105000),
(2, 5, DATE_ADD(CURDATE(), INTERVAL '16:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '18:45' HOUR_MINUTE), 115000),
(2, 5, DATE_ADD(CURDATE(), INTERVAL '19:30' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '21:45' HOUR_MINUTE), 125000);


INSERT INTO `suatchieu` (`phim_id`, `phong_id`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`, `gia_ve_co_ban`) VALUES
(3, 6, DATE_ADD(CURDATE(), INTERVAL '9:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '10:35' HOUR_MINUTE), 75000),
(3, 6, DATE_ADD(CURDATE(), INTERVAL '11:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '12:35' HOUR_MINUTE), 75000),
(3, 6, DATE_ADD(CURDATE(), INTERVAL '14:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '15:35' HOUR_MINUTE), 85000),
(3, 6, DATE_ADD(CURDATE(), INTERVAL '16:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '17:35' HOUR_MINUTE), 85000),
(3, 7, DATE_ADD(CURDATE(), INTERVAL '10:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '11:35' HOUR_MINUTE), 95000),
(3, 7, DATE_ADD(CURDATE(), INTERVAL '13:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '14:35' HOUR_MINUTE), 95000),
(3, 7, DATE_ADD(CURDATE(), INTERVAL '15:00' HOUR_MINUTE), DATE_ADD(CURDATE(), INTERVAL '16:35' HOUR_MINUTE), 105000);


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
