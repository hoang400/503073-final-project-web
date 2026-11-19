
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đặt vé xem phim | CineStar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php">
                <span class="brand-icon">
                    <i class="fa-solid fa-film"></i>
                </span>
                <span>
                    <span class="brand-name">CINESTAR<span>.</span></span>
                    <small>YOUR CINEMA EXPERIENCE</small>
                </span>
            </a>

            <nav class="main-nav" aria-label="Điều hướng chính">
                <a href="index.php">
                    <i class="fa-solid fa-house"></i> Trang chủ
                </a>
                <a href="movies.php">
                    <i class="fa-solid fa-clapperboard"></i> Phim
                </a>
                <a href="booking.php" class="active">
                    <i class="fa-solid fa-ticket"></i> Đặt vé
                </a>
                <a href="history.php">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử vé
                </a>
            </nav>

            <div id="account" class="account-area"></div>
        </div>
    </header>

    <main class="booking-page">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>

        <div class="section-heading booking-heading">
            <div>
                <span class="section-eyebrow">CINESTAR BOOKING</span>
                <h1>Đặt vé xem phim</h1>
                <p>Chọn phim, suất chiếu và vị trí ghế yêu thích của bạn.</p>
            </div>
        </div>

        <section class="booking-filter-panel glass-panel">
            <label for="movie">
                <span><i class="fa-solid fa-film"></i> Chọn phim</span>
                <select id="movie">
                    <option value="">Đang tải danh sách phim...</option>
                </select>
            </label>

            <label for="showtime">
                <span><i class="fa-regular fa-clock"></i> Chọn suất chiếu</span>
                <select id="showtime" disabled>
                    <option value="">Chọn phim trước</option>
                </select>
            </label>
        </section>

        <div class="booking-layout">
            <section class="card booking-seat-card">
                <div class="booking-card-heading">
                    <div>
                        <span class="section-eyebrow">STEP 01</span>
                        <h2>Chọn ghế ngồi</h2>
                    </div>
                    <span class="booking-limit">
                        <i class="fa-solid fa-users"></i>
                        Tối đa 8 ghế
                    </span>
                </div>

                <div class="screen-area">
                    <div class="screen-light"></div>
                    <div class="screen">MÀN HÌNH</div>
                    <p>Vị trí màn hình chiếu</p>
                </div>

                <div class="seat-scroll">
                    <div id="seats" class="seats-container">
                        <p class="booking-placeholder">
                            Chọn suất chiếu để xem sơ đồ ghế.
                        </p>
                    </div>
                </div>

                <div class="seat-legend">
                    <div class="legend-item">
                        <span class="legend-seat available"></span>
                        <span>Còn trống</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-seat selected"></span>
                        <span>Đang chọn</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-seat booked"></span>
                        <span>Đã đặt</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-seat vip"></span>
                        <span>Ghế VIP</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-seat couple"></span>
                        <span>Ghế Couple</span>
                    </div>
                </div>

                <p class="booking-note">
                    <i class="fa-solid fa-circle-info"></i>
                    Giá ghế được tính theo loại ghế và giá cơ bản của suất chiếu.
                </p>
            </section>

            <aside class="booking-summary">
                <section class="card">
                    <div class="booking-card-heading">
                        <div>
                            <span class="section-eyebrow">STEP 02</span>
                            <h2>Bắp nước</h2>
                        </div>
                        <i class="fa-solid fa-popcorn booking-heading-icon"></i>
                    </div>

                    <div id="fnb" class="fnb-list">
                        <p class="booking-placeholder">
                            Đang tải danh sách bắp nước...
                        </p>
                    </div>
                </section>

                <section class="card booking-total-card">
                    <span class="section-eyebrow">ORDER SUMMARY</span>
                    <h2>Thông tin thanh toán</h2>

                    <div class="booking-total-row">
                        <span>Tạm tính</span>
                        <strong id="bill">0 VNĐ</strong>
                    </div>

                    <p class="booking-payment-note">
                        Thanh toán demo của đồ án CineStar.
                    </p>

                    <button id="checkout" class="booking-checkout" disabled>
                        <i class="fa-solid fa-ticket"></i>
                        Đặt vé ngay
                    </button>
                </section>
            </aside>
        </div>
    </main>

    <footer class="site-footer">
        © 2026 CineStar · Đồ án website đặt vé xem phim
    </footer>

    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
    <script src="js/booking.js"></script>
</body>
</html>
