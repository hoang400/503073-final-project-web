<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tài khoản của bạn | CineStar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=8">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php">
                <span class="brand-icon">C</span>
                <span>
                    <span class="brand-name">CINESTAR<span>.</span></span>
                    <small>HỆ THỐNG RẠP RẺ NHẤT VIỆT NAM</small>
                </span>
            </a>

            <nav class="main-nav" aria-label="Điều hướng chính">
                <a href="index.php">
                    <i class="fa-solid fa-house"></i> Trang Chủ
                </a>
                <a href="movies.php">
                    <i class="fa-solid fa-film"></i> Phim Đang Chiếu
                </a>
                <a href="booking.php">
                    <i class="fa-solid fa-ticket"></i> Đặt Vé
                </a>
                <a href="history.php">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử Vé
                </a>
                <a href="admin/index.php">
                    <i class="fa-solid fa-user-shield"></i> Trang Admin
                </a>
            </nav>

            <div id="account" class="account-area"></div>
        </div>
    </header>
    <main class="profile-page">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        <h1>Tài khoản của bạn</h1>

        <div class="two-columns">
            <form id="profile-form" class="card">
                <h2>Thông tin cá nhân</h2><label>Họ và tên<input name="name" type="text" required id="profile-name"
                        minlength="2" maxlength="80"></label><label>Email<input id="profile-email" type="email"
                        readonly></label><label>Số điện thoại<input id="profile-phone" name="phone" type="tel"
                        maxlength="20"></label><button>Lưu thông tin</button>
            </form>
            <form id="password-form" class="card">
                <h2>Đổi mật khẩu</h2><label>Mật khẩu hiện tại<input name="currentPassword" type="password" required
                        autocomplete="current-password"></label><label>Mật khẩu mới<input name="newPassword"
                        type="password" required minlength="8" maxlength="128"
                        autocomplete="new-password"></label><label>Nhập lại mật khẩu mới<input name="confirm"
                        type="password" required minlength="8" maxlength="128"
                        autocomplete="new-password"></label><button>Đổi mật khẩu</button>
            </form>
        </div>
    </main>
    <footer class="site-footer">
        <p>
            © 2026 CineStar Cinema Vietnam.
            Hệ thống đặt vé giá rẻ cho sinh viên & giới trẻ.
        </p>
    </footer>
    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
    <script src="js/profile.js"></script>
</body>
</html>