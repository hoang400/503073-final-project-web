<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập | CineStar</title>
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