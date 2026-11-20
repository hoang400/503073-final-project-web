<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký | CineStar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=9">
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
    <main class="auth-page">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        <div class="auth-wrap">
        <form id="auth-form" data-mode="register" class="card auth-card"><span class="auth-eyebrow">TÀI KHOẢN CINESTAR</span>
            <h1>Tạo tài khoản</h1>
            <p class="auth-intro">Đăng ký để đặt vé và lưu lại những trải nghiệm điện ảnh của bạn.</p><label>Họ và tên<input name="name" type="text" required minlength="2" maxlength="80"
                    autocomplete="name" placeholder="Nguyễn Văn A"></label><label>Email<input name="email" type="email" required maxlength="254"
                    autocomplete="email" placeholder="ban@example.com"></label><label>Mật khẩu<input name="password" type="password" required
                    minlength="8" maxlength="128" autocomplete="new-password" placeholder="Ít nhất 8 ký tự"></label><label>Xác nhận mật khẩu<input
                    name="confirm" type="password" required minlength="8" maxlength="128"
                    autocomplete="new-password" placeholder="Nhập lại mật khẩu"></label><label class="remember-row"><input type="checkbox" name="remember" value="on"> Ghi nhớ đăng nhập trong 30 ngày</label><button type="submit" class="auth-submit">Đăng ký</button><a id="switch-auth" href="login.php">Đã
                có tài khoản? Đăng nhập</a>
        </form>
        </div>
    </main>
    <footer>© 2026 CineStar • Đồ án website đặt vé xem phim</footer>
    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
    <script src="js/auth.js"></script>
</body>
</html>