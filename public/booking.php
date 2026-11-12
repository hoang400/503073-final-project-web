<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đặt vé xem phim | CineStar</title>
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
        <h1>Đặt vé xem phim</h1>
        <div class="filters"><label>Phim<select id="movie"></select></label><label>Suất chiếu<select
                    id="showtime"></select></label></div>
        <div class="booking-layout">
            <section class="card">
                <h2>Chọn ghế</h2>
                <div class="screen">MÀN HÌNH</div>
                <div class="seat-scroll">
                    <div id="seats"></div>
                </div>
                <p>Giá vé được áp dụng theo giá cơ bản của từng suất chiếu.</p>
                <p>Ghế mờ: đã đặt • Ghế xanh: đang chọn • Tối đa 8 ghế</p>
            </section>
            <aside class="card">
                <h2>Bắp nước</h2>
                <div id="fnb"></div>
                <h3>Tạm tính</h3>
                <p id="bill"></p><button id="checkout" disabled>Đặt vé · thanh toán demo</button>
            </aside>
        </div>
    </main>
    <footer>© 2026 CineStar • Đồ án website đặt vé xem phim</footer>
    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
    <script src="js/booking.js"></script>
</body>
</html>