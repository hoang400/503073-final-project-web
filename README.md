# CineStar — Website đặt vé xem phim và quản lý rạp chiếu phim

- **Repository:** https://github.com/hoang400/503073-final-project-web
- **Nhóm:** Nguyễn Hoàng Khanh (52400075) — Nguyễn Xuân Vương (52400256) — Phạm Xuân Hương (52400195)

## 1. Hệ thống làm được gì

- **Khách hàng:** xem phim đang chiếu/sắp chiếu, xem trailer, chọn suất chiếu, chọn ghế theo sơ đồ phòng, đặt kèm combo bắp nước, thanh toán, xem lịch sử vé và mã vé QR.
- **Quản trị viên:** quản lý phim (thêm/sửa/xóa, đổi trạng thái Đang chiếu/Sắp chiếu/Ngưng chiếu), rạp, phòng và sơ đồ ghế, suất chiếu, bắp nước, đơn vé, xem thống kê doanh thu trên dashboard.
- **Nhân viên:** soát vé tại quầy bằng cách nhập mã đơn/mã vé và xác nhận khách vào rạp.

Công nghệ: PHP thuần (PHP 8, PDO, Session), MySQL, HTML5/CSS3/JavaScript (Tailwind CSS). Không dùng framework, không có file `.env`.

Lưu ý: trong phạm vi đồ án, thanh toán VietQR chỉ mô phỏng (không trừ tiền thật) và soát vé thực hiện bằng cách nhập mã đơn, không quét QR.

## 2. Chuẩn bị môi trường

Cần có:

| Phần mềm | Phiên bản |
|---|---|
| XAMPP (gồm Apache + MySQL + PHP) | Bản mới nhất, PHP 8.x |
| Trình duyệt | Chrome, Firefox hoặc Edge bản mới |
| Git (khuyến nghị) | Bản mới nhất |

Không cần cài thêm Composer, Node.js hay bất kỳ thư viện nào khác. Toàn bộ code PHP chạy trực tiếp, không qua bước build.

## 3. Cài đặt và chạy — làm theo đúng thứ tự

### Bước 1 — Lấy mã nguồn

Mở Git Bash hoặc Command Prompt rồi chạy:

```bash
git clone https://github.com/hoang400/503073-final-project-web.git
```

Giả sử mã nguồn nằm ở `D:\503073-final-project-web`. Các bước dưới đây dùng đường dẫn này làm ví dụ; nếu để ở chỗ khác thì thay bằng đường dẫn thật của .

### Bước 2 — Trỏ Apache vào thư mục `public/`

1. Mở **XAMPP Control Panel**, bấm **Start** ở dòng **Apache** và dòng **MySQL**. Chờ cả hai hiện màu xanh lá (Running).
2. Trong XAMPP Control Panel, bấm nút **Config** ở dòng Apache → chọn **Apache (httpd.conf)**. File cấu hình mở ra trong Notepad.
3. Tìm dòng (bấm `Ctrl + F`, gõ `DocumentRoot`):
   ```apache
   DocumentRoot "C:/xampp/htdocs"
   ```
   Đổi thành đường dẫn tới thư mục `public/` của dự án (chú ý dùng dấu `/`, không dùng `\`):
   ```apache
   DocumentRoot "D:/503073-final-project-web/public"
   ```
4. Ngay dưới đó có khối `<Directory "C:/xampp/htdocs">` — đổi thành:
   ```apache
   <Directory "D:/503073-final-project-web/public">
   ```
5. Lưu file (`Ctrl + S`), đóng Notepad. Quay lại XAMPP Control Panel, bấm **Stop** rồi **Start** lại Apache để nhận cấu hình mới.

Kiểm tra: mở trình duyệt vào http://localhost/ — phải thấy trang chủ CineStar (banner phim Godzilla x Kong). Nếu trình duyệt báo lỗi 404 hoặc hiện danh sách file thì DocumentRoot chưa trỏ đúng — xem lại Bước 2 và mục 6.

### Bước 3 — Tạo database `cinestar` và nạp dữ liệu mẫu

1. Mở trình duyệt vào http://localhost/phpmyadmin (phpMyAdmin chạy kèm XAMPP, không cần mật khẩu).
2. Bấm tab **Databases** (Cơ sở dữ liệu) ở menu trên cùng.
3. Ô **Create database**: gõ `cinestar`, chọn **Collation** là `utf8mb4_unicode_ci`, bấm **Create**.
4. Bấm vào database `cinestar` vừa tạo ở cột trái, rồi bấm tab **Import** ở menu trên cùng.
5. Bấm **Choose File**, chọn file `D:\503073-final-project-web\database\schema.sql`, kéo xuống cuối trang bấm **Import**. Đợi báo thành công — lúc này đã có 11 bảng.
6. Làm lại thao tác Import một lần nữa với file `D:\503073-final-project-web\database\seed.sql`. Đợi báo thành công — lúc này đã có dữ liệu mẫu: 4 cụm rạp, 10 phòng, 4 phim, 76 suất chiếu, 5 tài khoản demo.

Thứ tự bắt buộc: `schema.sql` trước (tạo bảng), `seed.sql` sau (nạp dữ liệu). Import ngược lại sẽ lỗi vì bảng chưa tồn tại.

Cách khác (nếu Import trên phpMyAdmin báo lỗi file quá lớn): mở Command Prompt, chạy:

```bash
cd D:\503073-final-project-web\database
mysql -u root cinestar < schema.sql
mysql -u root cinestar < seed.sql
```

### Bước 4 — Cấu hình kết nối database (thường không cần sửa gì)

File kết nối là `config/database.php`. File này đọc thông số từ biến môi trường, nếu không có thì dùng giá trị mặc định sau — khớp sẵn với XAMPP chuẩn:

| Thông số | Giá trị mặc định | 
|---|---|
| DB_HOST | `127.0.0.1` | 
| DB_PORT | `3306` | 
| DB_NAME | `cinestar` | 
| DB_USER | `root` | 
| DB_PASS | (trống) | 

**Với XAMPP mặc định thì không cần sửa file nào** — chỉ cần database `cinestar` đã được tạo và import xong ở Bước 3 là chạy được ngay. Chỉ chỉnh `config/database.php` khi MySQL trên máy chấm dùng tài khoản hoặc mật khẩu khác `root`/trống.

### Bước 5 — Truy cập hệ thống

| Trang | Địa chỉ |
|---|---|
| Trang khách hàng | http://localhost/ |
| Trang quản trị (Admin) | http://localhost/admin/ |
| Soát vé (Nhân viên) | http://localhost/staff/check-in.php |

## 4. Tài khoản demo (dữ liệu mẫu)

Tất cả tài khoản dưới đây dùng chung mật khẩu **`12345678`**.

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị viên (ADMIN) | admin@cinestar.vn | 12345678 |
| Nhân viên (STAFF) | staff@cinestar.vn | 12345678 |
| Khách hàng (CUSTOMER) | an.nguyen@email.com | 12345678 |
| Khách hàng (CUSTOMER) | binh.tran@email.com | 12345678 |
| Khách hàng (CUSTOMER) | cuong.le@email.com | 12345678 |

## 5. Xử lý lỗi thường gặp

| Triệu chứng | Nguyên nhân thường gặp | Cách khắc phục |
|---|---|---|
| Apache trong XAMPP không Start được, báo lỗi cổng 80 | Cổng 80 đang bị chương trình khác chiếm (thường là Skype, IIS hoặc World Wide Web Publishing Service) | Tắt chương trình đang chiếm cổng 80 rồi Start lại Apache. Hoặc đổi cổng Apache: trong `httpd.conf` sửa `Listen 80` thành `Listen 8080`, Start lại Apache, rồi truy cập bằng http://localhost:8080/ |
| MySQL trong XAMPP không Start được | Cổng 3306 bị chiếm, hoặc dữ liệu MySQL bị lỗi sau khi tắt máy đột ngột | Tắt chương trình chiếm cổng 3306 rồi Start lại. Nếu vẫn lỗi, xem log trong XAMPP Control Panel (nút **Logs** ở dòng MySQL) |
| Trang trắng hoặc báo lỗi kết nối database | Database `cinestar` chưa được tạo, hoặc chưa import `schema.sql`/`seed.sql`, hoặc MySQL chưa chạy | Kiểm tra MySQL đang Running trong XAMPP Control Panel; vào phpMyAdmin xem database `cinestar` có đủ 11 bảng không; import lại theo Bước 3 |
| Báo `Access denied for user 'root'` | MySQL trên máy chấm có đặt mật khẩu cho root | Đặt mật khẩu đúng vào `config/database.php` (xem Bước 4) |
| Trang hiện nhưng mất CSS, giao diện vỡ | Trình duyệt đang dùng CSS/JS cũ trong cache | Bấm `Ctrl + F5` để tải lại trang, bỏ qua cache |
| Vào trang đặt vé không thấy suất chiếu nào | Dữ liệu mẫu đã cũ (suất chiếu trong `seed.sql` được sinh tương đối theo ngày import) | Import lại `seed.sql` (Bước 3) để sinh lại suất chiếu theo ngày hiện tại |
| Bấm đăng nhập xong bị đẩy về trang đăng nhập | Session chưa ghi được, thường do thư mục tạm của PHP không ghi được | Kiểm tra Apache đang chạy; thử đóng trình duyệt rồi đăng nhập lại; nếu vẫn lỗi, xem file log `C:\xampp\apache\logs\error.log` |

Nếu gặp lỗi khác, mở file `C:\xampp\apache\logs\error.log` (dòng lỗi mới nhất ở cuối file) và đối chiếu với các bước ở mục 3.

## 7. Quy tắc nghiệp vụ cần biết.

- **Giá vé cố định theo loại ghế:** Thường 90.000 VNĐ, VIP 110.000 VNĐ, Couple 200.000 VNĐ. Tổng tiền = tiền ghế + combo bắp nước (nếu chọn); không có phí dịch vụ hay thuế.
- **Combo:** Combo Solo 79.000 VNĐ, Combo Couple 109.000 VNĐ.
- **Giữ ghế:** ghế được giữ tạm 5 phút trên server khi khách đang chọn; hết 5 phút ghế tự nhả.
- **Sơ đồ ghế chuẩn:** 90 ghế/phòng (hàng A–G × 12 ghế + hàng H 6 ghế đôi Couple).
- **Xóa phim:** xóa hẳn khỏi database; phim còn suất chiếu thì hệ thống chặn và báo lỗi (không xóa được).
- **Suất chiếu mẫu:** được sinh tương đối theo ngày hiện tại nên sau khi import luôn có suất chiếu đặt được.

## 8. Cấu trúc thư mục

```text
.
├── app/
│   ├── controllers/        # Auth, Movie, Booking, Order, Admin, Staff
│   ├── core/               # Database (PDO Singleton), Router, bootstrap
│   └── models/             # AdminModel, BookingModel, ...
├── config/
│   └── database.php        # Kết nối MySQL (đọc biến môi trường, có mặc định)
├── database/
│   ├── schema.sql          # 11 bảng, khóa ngoại, UNIQUE, CHECK
│   └── seed.sql            # Dữ liệu mẫu: rạp, phòng, ghế, phim, suất chiếu, tài khoản
├── public/                 # DocumentRoot — thư mục duy nhất web server phục vụ
│   ├── index.php           # Trang chủ khách hàng
│   ├── movies.php          # Danh sách phim
│   ├── booking.php         # Đặt vé: chọn suất, ghế, bắp nước, thanh toán
│   ├── history.php         # Lịch sử vé
│   ├── login.php / register.php / profile.php / order-detail.php
│   ├── admin/
│   │   ├── index.php       # Dashboard: thống kê, quản lý phim, quản lý đơn vé
│   │   ├── cinemas.php     # Quản lý rạp
│   │   ├── rooms.php       # Quản lý phòng và ghế
│   │   ├── showtimes.php   # Quản lý suất chiếu
│   │   └── concessions.php # Quản lý bắp nước
│   ├── staff/
│   │   └── check-in.php    # Soát vé
│   ├── api/index.php       # Cửa vào API (JSON)
│   ├── css/style.css
│   └── js/                 # main.js, auth.js, booking.js, history.js, admin.js
└── routes/
    └── api.php             # Định tuyến API
```
