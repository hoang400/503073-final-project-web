<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch sử đặt vé | CineStar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=10">
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
                <a href="history.php" class="active">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử Vé
                </a>
                <a href="admin/index.php">
                    <i class="fa-solid fa-user-shield"></i> Trang Admin
                </a>
            </nav>

            <div id="account" class="account-area"></div>
        </div>
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
        <div id="qr-modal" class="qr-modal" hidden>
            <div class="qr-modal-backdrop" data-close-qr></div>
            <div class="qr-modal-card" role="dialog" aria-modal="true" aria-labelledby="qr-modal-title">
                <div class="qr-modal-head">
                    <h2 id="qr-modal-title">MÃ VÀO RẠP SOÁT VÉ</h2>
                    <button type="button" class="qr-modal-close" data-close-qr aria-label="Đóng">×</button>
                </div>
                <div id="qr-modal-body" class="qr-modal-body">Đang tải mã vé…</div>
            </div>
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
    <script src="js/history.js?v=9"></script>
</body>
</html>
