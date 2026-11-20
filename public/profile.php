<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tài khoản của bạn | CineStar</title>
    <link rel="stylesheet" href="css/style.css?v=6">
</head>

<body>
    <header><a class="brand" href="index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Điều hướng chính"><a href="index.php">Trang chủ</a><a href="movies.php">Phim</a><a
                href="booking.php">Đặt vé</a><a href="history.php">Lịch sử vé</a></nav>
        <div id="account"></div>
    </header>
    <main>
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
    <footer>© 2026 CineStar • Đồ án website đặt vé xem phim</footer>
    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
    <script src="js/profile.js"></script>
</body>
</html>