
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineStar Cinema - Trang chủ</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#0f131f',
                            accent: '#6366f1',
                            blue: '#38bdf8'
                        }
                    }
                }
            }
        };
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="home-page">

    
    <header class="site-header glass-panel">
        <div class="header-inner">
            <a href="index.php" class="brand">
                <div class="brand-icon">C</div>
                <div>
                    <span class="brand-name">CINESTAR<span>.</span></span>
                    <small>HỆ THỐNG RẠP RẺ NHẤT VIỆT NAM</small>
                </div>
            </a>

            <nav class="main-nav" aria-label="Điều hướng chính">
                <a href="index.php" class="active">
                    <i class="fa-solid fa-house"></i>
                    Trang Chủ
                </a>

                <a href="movies.php">
                    <i class="fa-solid fa-film"></i>
                    Phim Đang Chiếu
                </a>

                <a href="booking.php">
                    <i class="fa-solid fa-ticket"></i>
                    Đặt Vé
                </a>

                <a href="history.php">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Lịch Sử Vé
                </a>

                <a href="admin/index.php">
                    <i class="fa-solid fa-user-shield"></i>
                    Trang Admin
                </a>
            </nav>

            <div class="header-actions">
                <span class="location-pill">
                    <i class="fa-solid fa-location-arrow"></i>
                    TP. Hồ Chí Minh
                </span>

                <div id="account" class="account-area"></div>
            </div>
        </div>
    </header>

    
    <main class="home-main">

        <div id="notice" class="notice"
            role="status" aria-live="polite"></div>

        <!-- HERO BANNER -->
        <section id="hero" class="hero-banner">
            <div class="hero-content">

                <span class="hero-label">
                    <i class="fa-solid fa-fire"></i>
                    PHIM HOT NHẤT TUẦN
                </span>

                <h1 id="hero-title">
                    Khám phá thế giới điện ảnh
                </h1>

                <p id="hero-description">
                    Những bộ phim hấp dẫn đang chờ bạn tại CineStar.
                </p>

                <div class="hero-meta" id="hero-meta"></div>

                <div class="hero-actions">
                    <a id="hero-booking"
                    href="movies.php"
                    class="primary-button">
                        <i class="fa-solid fa-ticket"></i>
                        ĐẶT VÉ NGAY
                    </a>

                    <a href="movies.php" class="secondary-button">
                        <i class="fa-solid fa-film"></i>
                        XEM TẤT CẢ PHIM
                    </a>
                </div>

            </div>
        </section>

        <!-- FOUR FEATURE CARDS -->
        <section class="feature-grid">

            <article class="feature-card">
                <div class="feature-icon sky">
                    <i class="fa-solid fa-tags"></i>
                </div>

                <h3>Vé Chỉ Từ 45.000 VNĐ</h3>

                <p>
                    Đồng giá ưu đãi đặc biệt cho Học sinh -
                    Sinh viên cả tuần.
                </p>
            </article>

            <article class="feature-card">
                <div class="feature-icon indigo">
                    <i class="fa-solid fa-couch"></i>
                </div>

                <h3>Ghế Đôi Sweetbox</h3>

                <p>
                    Không gian riêng tư thoải mái cho các cặp
                    đôi giá 200k.
                </p>
            </article>

            <article class="feature-card">
                <div class="feature-icon amber">
                    <i class="fa-solid fa-burger"></i>
                </div>

                <h3>Combo Bỏng Nước Giá Rẻ</h3>

                <p>
                    Combo Solo 79k, Combo Couple 109k
                    tiết kiệm tối đa.
                </p>
            </article>

            <article class="feature-card">
                <div class="feature-icon emerald">
                    <i class="fa-solid fa-qrcode"></i>
                </div>

                <h3>Thanh Toán VietQR</h3>

                <p>
                    Quét mã QR chuyển khoản tự động qua
                    ứng dụng Ngân hàng.
                </p>
            </article>

        </section>

    </main>


    <footer class="site-footer">
        <p>
            © 2026 CineStar Cinema Vietnam.
            Hệ thống đặt vé giá rẻ cho sinh viên & giới trẻ.
        </p>
    </footer>

    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
