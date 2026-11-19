<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trải nghiệm điện ảnh cùng CineStar | CineStar</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header><a class="brand" href="index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Điều hướng chính"><a href="index.php">Trang chủ</a><a href="movies.php">Phim</a><a
                href="booking.php">Đặt vé</a><a href="history.php">Lịch sử vé</a></nav>
        <div id="account"></div>
    </header>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        <section class="hero"><span class="tag">CINESTAR CINEMA</span>
            <h1>Màn ảnh lớn.<br>Trải nghiệm đáng nhớ.</h1>
            <p>Chọn phim yêu thích và đặt chỗ cho buổi hẹn tiếp theo.</p><a class="button" href="movies.php">Khám phá
                phim</a>
        </section>
        <h2>Phim đang chiếu</h2>
        <div id="movies" class="movie-grid"></div>
    </main>
    <footer>© 2026 CineStar • Đồ án website đặt vé xem phim</footer>
    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
</body>
</html>