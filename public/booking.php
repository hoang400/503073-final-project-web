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
                <a href="booking.php" class="active">
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

    <main class="booking-page booking-target">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>

        <!-- Thanh chọn phim (giống ảnh chốt) -->
        <section class="booking-movie-bar glass-panel">
            <label for="movie">
                <i class="fa-solid fa-clapperboard"></i>
                CHỌN PHIM ĐANG CHIẾU:
            </label>
            <select id="movie">
                <option value="">Đang tải danh sách phim...</option>
            </select>
        </section>

        <!-- Hero chi tiết phim -->
        <section id="movieHero" class="booking-hero card" hidden>
            <div class="booking-hero-poster">
                <img id="moviePoster" src="" alt="" loading="lazy">
                <span id="movieAgeBadge" class="age-badge" hidden></span>
            </div>

            <div class="booking-hero-info">
                <div id="movieGenres" class="booking-hero-genres"></div>
                <h1 id="movieTitle"></h1>

                <div class="booking-hero-meta">
                    <span id="movieImdb" class="booking-meta-imdb" hidden>
                        <i class="fa-solid fa-star"></i> <span></span>
                    </span>
                    <span id="movieDuration" class="booking-meta-chip" hidden>
                        <i class="fa-regular fa-clock"></i> <span></span>
                    </span>
                    <span id="movieAge" class="booking-meta-age" hidden></span>
                </div>

                <p id="movieDesc" class="booking-hero-desc"></p>
                <button id="movieDescToggle" type="button" class="booking-desc-toggle" hidden>
                    XEM THÊM <i class="fa-solid fa-chevron-down"></i>
                </button>

                <div class="booking-hero-credits">
                    <div>
                        <small>Đạo diễn:</small>
                        <strong id="movieDirector">—</strong>
                    </div>
                    <div>
                        <small>Biên kịch:</small>
                        <strong id="movieWriters">—</strong>
                    </div>
                    <div>
                        <small>Diễn viên chính:</small>
                        <strong id="movieStars">—</strong>
                    </div>
                </div>

                <a id="movieTrailer" class="booking-trailer" href="#" target="_blank" rel="noopener" hidden>
                    <i class="fa-brands fa-youtube"></i> XEM TRAILER
                </a>
            </div>
        </section>

        <!-- Chọn ngày / giờ / định dạng / cụm rạp -->
        <section class="booking-datebar glass-panel">
            <div class="booking-date-picker">
                <span class="booking-date-label">CHỌN NGÀY:</span>
                <button id="datePrev" type="button" class="booking-date-nav" aria-label="Ngày trước">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div id="dateStrip" class="booking-date-strip" role="tablist" aria-label="Chọn ngày chiếu"></div>
                <button id="dateNext" type="button" class="booking-date-nav" aria-label="Ngày sau">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <div class="booking-showtime-filters">
                <label>
                    <span>GIỜ CHIẾU</span>
                    <select id="showtimeTime" disabled>
                        <option value="">Chọn ngày trước</option>
                    </select>
                </label>
                <label>
                    <span>ĐỊNH DẠNG</span>
                    <select id="formatSelect" disabled>
                        <option value="">—</option>
                    </select>
                </label>
                <label>
                    <span>CỤM RẠP CINESTAR</span>
                    <select id="cinemaSelect" disabled>
                        <option value="">Tất cả rạp</option>
                    </select>
                </label>
            </div>

            <!-- Giữ lại select gốc (ẩn) để logic cũ / các hàm khác vẫn dùng được -->
            <select id="showtime" hidden aria-hidden="true" tabindex="-1"></select>
        </section>

        <div class="booking-target-layout">
            <!-- Cột trái: thông tin vé đặt -->
            <aside class="booking-ticket card">
                <div class="booking-ticket-head">
                    <h2><i class="fa-solid fa-ticket"></i> THÔNG TIN VÉ ĐẶT</h2>
                    <span id="ticketFormat" class="booking-ticket-format" hidden></span>
                </div>

                <dl class="booking-ticket-rows">
                    <div>
                        <dt>Phim:</dt>
                        <dd id="ticketMovie">—</dd>
                    </div>
                    <div>
                        <dt>Suất chiếu:</dt>
                        <dd id="ticketShowtime">—</dd>
                    </div>
                    <div>
                        <dt>Rạp chiếu:</dt>
                        <dd id="ticketCinema">—</dd>
                    </div>
                    <div>
                        <dt>Ghế đã chọn:</dt>
                        <dd id="ticketSeats" class="booking-ticket-seats">Chưa chọn ghế</dd>
                    </div>
                </dl>

                <p id="holdCountdown" class="hold-countdown" hidden>
                    <i class="fa-regular fa-clock"></i>
                    Ghế đang giữ, còn <strong id="holdCountdownTime">05:00</strong> để thanh toán
                </p>

                <div class="booking-ticket-total">
                    <div>
                        <span>TỔNG TIỀN PHẢI THU</span>
                        <small id="ticketCount">(0 ghế, 0 dịch vụ)</small>
                    </div>
                    <strong id="bill">0 VNĐ</strong>
                </div>

                <button id="fnbToggle" type="button" class="booking-fnb-toggle">
                    <i class="fa-solid fa-popcorn"></i>
                    THÊM BỎNG NGÔ &amp; NƯỚC NGỌT
                    <i class="fa-solid fa-chevron-down"></i>
                </button>

                <button id="checkout" class="booking-checkout booking-pay" disabled>
                    THANH TOÁN ĐẶT VÉ
                </button>
            </aside>

            <!-- Cột phải: sơ đồ ghế -->
            <section class="card booking-seat-card">
                <div class="booking-seat-title">
                    <h2>SƠ ĐỒ CHỌN GHẾ NGỒI</h2>
                    <p>Vui lòng chọn ghế phù hợp từ sơ đồ phòng chiếu bên dưới</p>
                </div>

                <div class="screen-area">
                    <div class="screen-light"></div>
                    <div class="screen">MÀN HÌNH</div>
                </div>

                <div class="seat-scroll">
                    <div id="seats" class="seats-container">
                        <p class="booking-placeholder">
                            Chọn suất chiếu để xem sơ đồ ghế.
                        </p>
                    </div>
                </div>

                <div class="seat-legend booking-seat-legend">
                    <div class="legend-item">
                        <span class="legend-seat available"></span>
                        <span>Ghế Thường (<span id="legendStandardPrice">90k</span>)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-seat vip"></span>
                        <span>Ghế VIP (<span id="legendVipPrice">110k</span>)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-seat couple"></span>
                        <span>Ghế Đôi (<span id="legendCouplePrice">200k</span>)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-seat selected"></span>
                        <span>Đã Chọn</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-seat booked"></span>
                        <span>Đã Có Người</span>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        © 2026 CineStar · Đồ án website đặt vé xem phim
    </footer>

                <div id="fnbModal" class="fnb-modal" hidden>
                    <div class="fnb-modal-backdrop" data-close-fnb></div>
                    <div class="fnb-modal-card" role="dialog" aria-modal="true" aria-label="Bắp nước">
                        <div class="fnb-modal-head fnb-modal-head-title">
                            <h3><span aria-hidden="true">🍔</span> BỎNG NGÔ &amp; NƯỚC NGỌT</h3>
                            <button id="fnbClose" type="button" class="fnb-modal-close" aria-label="Đóng">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div id="fnb" class="fnb-modal-list">
                            <p class="booking-placeholder">Đang tải danh sách bắp nước...</p>
                        </div>

                        <div class="fnb-modal-foot fnb-modal-foot-single">
                            <button id="fnbApply" type="button" class="fnb-modal-apply">
                                XÁC NHẬN BỎNG NƯỚC
                            </button>
                        </div>
                    </div>
                </div>

            <div id="vietqrModal" class="fnb-modal" hidden>
                <div class="fnb-modal-backdrop" data-close-vietqr></div>
                <div class="fnb-modal-card vietqr-card" role="dialog" aria-modal="true" aria-label="Thanh toán VietQR">
                    <div class="fnb-modal-head fnb-modal-head-title">
                        <h3><i class="fa-solid fa-border-all" aria-hidden="true"></i> THANH TOÁN VÉ CINESTAR</h3>
                        <button id="vietqrClose" type="button" class="fnb-modal-close" aria-label="Đóng">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="vietqr-body">
                        <dl class="vietqr-rows">
                            <div><dt>Mã đơn:</dt><dd id="vietqrCode" class="vietqr-code">—</dd></div>
                            <div><dt>Phim:</dt><dd id="vietqrMovie">—</dd></div>
                            <div><dt>Ghế:</dt><dd id="vietqrSeats" class="vietqr-seats">—</dd></div>
                            <div><dt>Tổng tiền:</dt><dd id="vietqrTotal" class="vietqr-total">—</dd></div>
                        </dl>
                        <div class="vietqr-frame">
                            <span class="vietqr-brand">VietQR</span>
                            <img id="vietqrImage" src="" alt="Mã VietQR thanh toán" width="220" height="220">
                            <span class="vietqr-banks">napas&nbsp;&nbsp;•&nbsp;&nbsp;MB</span>
                        </div>
                        <p class="vietqr-note">Quét mã QR bằng ứng dụng Ngân hàng để hoàn tất thanh toán</p>
                    </div>
                    <div class="fnb-modal-foot fnb-modal-foot-single">
                        <button id="vietqrViewOrder" type="button" class="fnb-modal-apply vietqr-confirm">ĐÃ HOÀN TẤT THANH TOÁN</button>
                    </div>
                </div>
            </div>

    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
    <script src="js/booking.js?v=8"></script>
</body>
</html>
