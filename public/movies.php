
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phim đang chiếu - CineStar Cinema</title>

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

<body class="min-h-screen flex flex-col antialiased">

    <header class="site-header glass-panel sticky top-0 z-40">
        <div class="header-inner">
            <a href="index.php" class="brand">
                <div class="brand-icon">C</div>
                <div>
                    <span class="brand-name">CINESTAR<span>.</span></span>
                    <small>HỆ THỐNG RẠP CHIẾU PHIM</small>
                </div>
            </a>

            <nav class="main-nav">
                <a href="index.php">
                    <i class="fa-solid fa-house"></i> Trang chủ
                </a>
                <a href="movies.php" class="active">
                    <i class="fa-solid fa-film"></i> Phim đang chiếu
                </a>
                <a href="booking.php">
                    <i class="fa-solid fa-ticket"></i> Đặt vé
                </a>
                <a href="history.php">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử vé
                </a>
            </nav>

            <div id="account" class="account-area"></div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto w-full px-4 lg:px-8 py-8 flex-grow">

        <div id="notice" class="notice" role="status" aria-live="polite"></div>

        <section class="section-heading movies-heading">
            <div>
                <span class="section-eyebrow">CINESTAR CINEMA</span>
                <h1>Phim đang chiếu tại rạp</h1>
                <p>Khám phá thông tin và lựa chọn bộ phim yêu thích của bạn.</p>
            </div>

            <div class="movie-count">
                <i class="fa-solid fa-film"></i>
                <span id="movie-count">Đang tải...</span>
            </div>
        </section>

        <section class="movie-filter-panel glass-panel">
            <label>
                <span><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm phim</span>
                <input
                    id="search"
                    type="search"
                    placeholder="Nhập tên phim bạn muốn tìm..."
                >
            </label>

            <label>
                <span><i class="fa-solid fa-layer-group"></i> Thể loại</span>
                <select id="genre">
                    <option value="">Tất cả thể loại</option>
                </select>
            </label>

            <label>
                <span><i class="fa-solid fa-calendar-check"></i> Trạng thái</span>
                <select id="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="DANG_CHIEU">Đang chiếu</option>
                    <option value="SAP_CHIEU">Sắp chiếu</option>
                </select>
            </label>
        </section>

        <section class="mt-8">
            <div id="movies" class="movie-grid">
                <p class="loading-text">Đang tải danh sách phim...</p>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <p>© 2026 CineStar Cinema Vietnam. Website đặt vé xem phim.</p>
    </footer>

    <script src="js/api.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
