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
├── public/                     # A/C: giao diện; B: API và bảo vệ trang
│   ├── index.php               # [A] Trang chủ khách hàng
│   ├── movies.php              # [A] Xem, tìm kiếm và lọc phim
│   ├── login.php               # [A] Giao diện đăng nhập
│   ├── register.php            # [A] Giao diện đăng ký tài khoản
│   ├── booking.php             # [A] Chọn suất, ghế, bắp nước, thanh toán demo
│   ├── history.php             # [A] Lịch sử đặt vé của khách hàng
│   ├── order-detail.php        # [A] Chi tiết đơn và mã vé
│   ├── profile.php             # [A] Thông tin cá nhân và đổi mật khẩu
│   ├── admin/                  # [C] Giao diện dành cho quản trị viên
│   │   ├── index.php           # [C] Dashboard tổng quan quản trị
│   │   ├── movies.php          # [C] Quản lý phim, poster và trailer
│   │   ├── cinemas.php         # [C] Quản lý rạp chiếu
│   │   ├── rooms.php           # [C] Quản lý phòng chiếu và sơ đồ ghế
│   │   ├── showtimes.php       # [C] Quản lý suất chiếu và giá vé
│   │   ├── bookings.php        # [C] Tra cứu và quản lý đơn vé
│   │   ├── concessions.php     # [C] Quản lý danh mục và giá bắp nước
│   │   └── reports.php         # [C] Thống kê vé và doanh thu mô phỏng
│   ├── staff/                  # [C] Giao diện dành cho nhân viên
│   │   └── check-in.php        # [C] Nhập mã vé và xác nhận sử dụng vé
│   ├── api/                    # [B] Cửa vào API cho frontend
│   │   └── index.php           # [B] Nhận yêu cầu, gọi router, trả JSON
│   ├── css/                    # [A/C] Các file định dạng giao diện
│   │   └── style.css           # [A/C] CSS dùng chung và responsive
│   └── js/                     # [A/C] JavaScript giao diện và gọi API
│       ├── main.js             # [A] Menu và tương tác dùng chung
│       ├── auth.js             # [A] Gọi API đăng nhập, đăng ký, đăng xuất
│       ├── booking.js          # [A] Chọn ghế, bắp nước và gọi API đặt vé
│       ├── history.js          # [A] Tải và hiển thị lịch sử đơn vé
│       └── admin.js            # [C] Tương tác Admin/Staff và gọi API
├── app/                        # [B] Mã xử lý backend, ngoài Document Root
│   ├── controllers/            # [B] Nhận yêu cầu, gọi service, tạo phản hồi
│   ├── services/               # [B] Nghiệp vụ tài khoản, đặt vé, Admin/Staff
│   ├── models/                 # [B] Truy vấn MySQL qua PDO
│   ├── middleware/             # [B] Kiểm tra đăng nhập, vai trò và CSRF
│   └── core/                   # [B] Router, kết nối PDO, tiện ích nền tảng
├── config/                     # [B] Cấu hình ứng dụng và cơ sở dữ liệu
├── routes/                     # [B] Khai báo quy tắc định tuyến
│   └── api.php                 # [B] Ánh xạ phương thức/đường dẫn tới controller
├── database/                   # [B] Script cấu trúc và dữ liệu mẫu
│   ├── schema.sql              # [B] Tạo bảng, khóa, ràng buộc theo ERD
│   └── seed.sql                # [B] Viết SQL dữ liệu demo; C chuẩn bị nội dung
├── README.md                   # [C] Tổng quan, công nghệ, cấu trúc, phân công
└── Readme.txt                  # [C] Hướng dẫn cài đặt và chạy bản nộp
~~~

A/C dựng giao diện và nối API do B cung cấp. B viết toàn bộ API/controller/service/model cho cả khách hàng, Admin và Staff, đồng thời cung cấp phần kiểm tra PHP để bảo vệ các trang cần đăng nhập/phân quyền. A/C sử dụng phần kiểm tra này khi tích hợp, không tự truy vấn MySQL trong trang giao diện.

Với `style.css`, A phụ trách giao diện khách hàng, C phụ trách Admin/Staff; thống nhất trước khi sửa CSS dùng chung. C tổng hợp tài liệu và tổ chức kiểm thử tích hợp; B cung cấp cách cấu hình, import database và danh sách API; A/C/B cùng kiểm tra luồng hoàn chỉnh.
