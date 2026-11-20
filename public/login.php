<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập | CineStar</title>
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
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        <a class="auth-back" href="index.php">← Quay lại</a>
        <h1>Đăng nhập</h1>
        <form id="auth-form" data-mode="login" class="card auth-card"><span class="tag">TÀI KHOẢN CINESTAR</span>
            <h2>Chào mừng trở lại</h2>
            <p class="auth-intro">Đăng nhập để đặt vé và xem lịch sử của bạn.</p><label>Email<input name="email" type="email" required maxlength="254"
                    autocomplete="email" placeholder="ban@example.com"></label><label>Mật khẩu<input name="password" type="password" required
                    minlength="1" maxlength="128" autocomplete="current-password" placeholder="Nhập mật khẩu"></label><label class="remember-row"><input type="checkbox" name="remember" value="on"> Ghi nhớ đăng nhập trong 30 ngày</label><button type="submit" class="auth-submit">Đăng nhập</button><a
                id="switch-auth" href="register.php">Chưa có tài khoản? Đăng ký</a>
        </form>
    </main>
    <footer>© 2026 CineStar • Đồ án website đặt vé xem phim</footer>
    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
    <script src="js/auth.js"></script>
</body>
</html>