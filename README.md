# CineStar — Website đặt vé xem phim và quản lý rạp

## Tổng quan dự án

CineStar là đồ án môn Lập trình Web và Ứng dụng, hướng tới xây dựng website đặt vé xem phim và quản lý hoạt động rạp chiếu phim.

Khách hàng có thể tra cứu phim, chọn suất chiếu, chọn ghế, đặt bắp nước và theo dõi lịch sử vé. Quản trị viên quản lý phim, rạp, phòng, suất chiếu, đơn vé và báo cáo. Nhân viên kiểm tra mã vé và xác nhận khách vào rạp.

Trong phạm vi đồ án, thanh toán và soát vé được mô phỏng để trình diễn nghiệp vụ.

## Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Giao diện | HTML5, CSS3, JavaScript, Tailwind CSS, Font Awesome |
| Backend dự kiến | PHP thuần, PDO, Session |
| Cơ sở dữ liệu mục tiêu | MySQL |
| Môi trường phát triển | XAMPP, Visual Studio Code, MySQL Workbench |
| Quản lý mã nguồn | Git, GitHub |

Phiên bản hiện có chủ yếu là frontend demo sử dụng localStorage/sessionStorage và đã có schema SQL. Backend PHP và kết nối dữ liệu với MySQL là phần cần triển khai tiếp.

## Cấu trúc thư mục dự án

Cấu trúc đích theo file thư_mục_dự_án.txt:

~~~text
cinestar/
├── index.php
├── movies.php
├── booking.php
├── history.php
├── order-detail.php
├── profile.php
├── admin.php
├── admin/
│   ├── movies.php
│   ├── cinemas.php
│   ├── rooms.php
│   ├── showtimes.php
│   ├── bookings.php
│   ├── concessions.php
│   └── reports.php
├── staff/
│   └── check-in.php
├── css/style.css
├── js/
│   ├── main.js
│   ├── auth.js
│   ├── booking.js
│   ├── history.js
│   └── admin.js
├── api/index.php
├── routes/api.php
├── controllers/
├── services/
├── models/
├── middleware/
├── core/
├── config/
├── database/
│   ├── schema.sql
│   └── seed.sql
└── Readme.txt
~~~

