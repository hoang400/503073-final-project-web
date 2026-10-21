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

Cấu trúc đích theo file thư_mục_dự_án.txt. Web server cần trỏ **Document Root vào public/**; mã backend, cấu hình và SQL nằm bên ngoài thư mục được phục vụ qua web.

Các trang PHP phục vụ giao diện và gọi API; xử lý nghiệp vụ đặt trong controller/service/model. Cấu trúc này giữ các trang riêng, không bắt buộc mọi trang đi qua một entry point duy nhất. Đây là cấu trúc dự kiến, chưa phải kết quả di chuyển mã nguồn.

~~~text
cinestar/
├── public/                     # Thư mục web server phục vụ trực tiếp
│   ├── index.php               # Trang chủ khách hàng
│   ├── movies.php              # Xem, tìm kiếm và lọc phim
│   ├── login.php               # Giao diện đăng nhập
│   ├── register.php            # Giao diện đăng ký tài khoản
│   ├── booking.php             # Chọn suất, ghế, bắp nước và thanh toán demo
│   ├── history.php             # Lịch sử đặt vé của khách hàng
│   ├── order-detail.php        # Chi tiết đơn và mã vé
│   ├── profile.php             # Thông tin cá nhân và đổi mật khẩu
│   ├── admin/                  # Giao diện dành cho quản trị viên
│   │   ├── index.php           # Dashboard tổng quan quản trị
│   │   ├── movies.php          # Quản lý phim, poster và trailer
│   │   ├── cinemas.php         # Quản lý rạp chiếu
│   │   ├── rooms.php           # Quản lý phòng chiếu và sơ đồ ghế
│   │   ├── showtimes.php       # Quản lý suất chiếu và giá vé
│   │   ├── bookings.php        # Tra cứu và quản lý đơn vé
│   │   ├── concessions.php     # Quản lý danh mục và giá bắp nước
│   │   └── reports.php         # Thống kê vé và doanh thu mô phỏng
│   ├── staff/                  # Giao diện dành cho nhân viên
│   │   └── check-in.php        # Nhập mã vé và xác nhận sử dụng vé
│   ├── api/                    # Cửa vào API cho frontend
│   │   └── index.php           # Tiếp nhận yêu cầu, gọi router, trả JSON
│   ├── css/                    # Các file định dạng giao diện
│   │   └── style.css           # CSS dùng chung và responsive
│   └── js/                     # JavaScript giao diện và gọi API
│       ├── main.js             # Menu và tương tác dùng chung
│       ├── auth.js             # Gửi yêu cầu đăng nhập, đăng ký, đăng xuất
│       ├── booking.js          # Chọn ghế, bắp nước và gửi yêu cầu đặt vé
│       ├── history.js          # Tải và hiển thị lịch sử đơn vé
│       └── admin.js            # Tương tác quản trị và gọi API quản lý
├── app/                        # Mã xử lý backend, ngoài Document Root
│   ├── controllers/            # Nhận yêu cầu, gọi service và tạo phản hồi
│   ├── services/               # Nghiệp vụ giữ ghế, đặt vé, thanh toán demo
│   ├── models/                 # Truy vấn MySQL qua PDO
│   ├── middleware/             # Kiểm tra đăng nhập, vai trò và CSRF
│   └── core/                   # Router, kết nối PDO và tiện ích nền tảng
├── config/                     # Cấu hình ứng dụng và cơ sở dữ liệu
├── routes/                     # Khai báo quy tắc định tuyến
│   └── api.php                 # Ánh xạ phương thức/đường dẫn tới controller
├── database/                   # Script tạo cấu trúc và dữ liệu mẫu
│   ├── schema.sql              # Tạo bảng, khóa và ràng buộc theo ERD
│   └── seed.sql                # Thêm dữ liệu demo sau khi chạy schema
├── README.md                   # Tổng quan, công nghệ và cấu trúc dự án
└── Readme.txt                  # Hướng dẫn cài đặt và chạy bản nộp
~~~
