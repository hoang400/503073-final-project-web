<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch sử đặt vé | CineStar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=7">
</head>

<body>
    <header><a class="brand" href="index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Điều hướng chính"><a href="index.php">Trang chủ</a><a href="movies.php">Phim</a><a
                href="booking.php">Đặt vé</a><a href="history.php">Lịch sử vé</a></nav>
        <div id="account"></div>
    </header>
    <main class="history-page">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        <section class="history-head">
            <div class="history-title">
                <span class="history-icon" aria-hidden="true"><i class="fa-solid fa-clock-rotate-left"></i></span>
                <div>
                    <h1>LỊCH SỬ ĐẶT VÉ PHIM</h1>
                    <p>Danh sách thông tin các vé đã thanh toán được lưu trực tiếp trên thiết bị</p>
                </div>
            </div>
            <label class="history-search">
                <span class="visually-hidden">Tìm đơn vé</span>
                <input id="order-search" type="search" placeholder="Tìm theo mã hoặc tên phim...">
            </label>
        </section>
        <div id="orders" class="order-list"></div>
    </main>
    <footer>© 2026 CineStar • Đồ án website đặt vé xem phim</footer>
    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
    <script src="js/history.js?v=7"></script>
</body>
</html>
