<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phim đang chiếu | CineStar</title>
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
        <h1>Danh sách phim</h1>
        <div class="filters">
            <label>
                Tìm phim
                <input
                    id="search"
                    type="search"
                    placeholder="Nhập tên phim…"
                >
            </label>

            <label>
                Thể loại
                <select id="genre"></select>
            </label>

            <label>
                Trạng thái
                <select id="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="DANG_CHIEU">Đang chiếu</option>
                    <option value="SAP_CHIEU">Sắp chiếu</option>
                </select>
            </label>
        </div>
        <div id="movies" class="movie-grid"></div>
    </main>
    <footer>© 2026 CineStar • Đồ án website đặt vé xem phim</footer>
    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
</body>
</html>