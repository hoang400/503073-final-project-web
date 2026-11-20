<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | CineStar</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="../index.php">
                <span class="brand-icon">C</span>
                <span>
                    <span class="brand-name">CINESTAR<span>.</span></span>
                    <small>HỆ THỐNG RẠP RẺ NHẤT VIỆT NAM</small>
                </span>
            </a>
            <nav class="main-nav" aria-label="Admin navigation">
                <a href="index.php" class="active">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
                <a href="movies.php">
                    <i class="fa-solid fa-film"></i> Phim
                </a>
                <a href="cinemas.php">
                    <i class="fa-solid fa-building"></i> Rạp
                </a>
                <a href="rooms.php">
                    <i class="fa-solid fa-door-open"></i> Phòng & Ghế
                </a>
                <a href="showtimes.php">
                    <i class="fa-solid fa-clock"></i> Suất chiếu
                </a>
                <a href="concessions.php">
                    <i class="fa-solid fa-burger"></i> Bắp nước
                </a>
                <a href="bookings.php">
                    <i class="fa-solid fa-ticket"></i> Đơn hàng
                </a>
                <a href="reports.php">
                    <i class="fa-solid fa-chart-line"></i> Báo cáo
                </a>
            </nav>
            <div class="account-area">
                <span class="admin-badge"><i class="fa-solid fa-user-shield"></i> Admin Mode</span>
                <div id="account"></div>
            </div>
        </div>
    </header>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div class="admin-page-head">
            <h1>Dashboard Quản Trị</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <label style="font-size:13px; color:#94a3b8;">
                    Từ ngày
                    <input type="date" id="dateFrom" style="width:160px; margin-left:8px;">
                </label>
                <label style="font-size:13px; color:#94a3b8;">
                    Đến ngày
                    <input type="date" id="dateTo" style="width:160px; margin-left:8px;">
                </label>
                <button id="filterBtn" class="button">Lọc</button>
            </div>
        </div>

        <!-- 4 Cards -->
        <div class="admin-stat-grid">
            <article class="card admin-stat-card">
                <div class="admin-stat-icon" style="background:#22c55e;">💰</div>
                <div>
                    <p class="admin-stat-label">Tổng doanh thu đặt vé</p>
                    <h2 class="admin-stat-value" id="statTotalRevenue" style="color:#22c55e;">0 VNĐ</h2>
                    <p class="admin-stat-sub">Tính từ dữ liệu hệ thống</p>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="admin-stat-icon" style="background:#3b82f6;">🎫</div>
                <div>
                    <p class="admin-stat-label">Tổng số vé đã bán</p>
                    <h2 class="admin-stat-value" id="statOrders" style="color:#38bdf8;">0 Đơn vé</h2>
                    <p class="admin-stat-sub">Cập nhật thời gian thực</p>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="admin-stat-icon" style="background:#a855f7;">🎬</div>
                <div>
                    <p class="admin-stat-label">Số phim đang chiếu</p>
                    <h2 class="admin-stat-value" id="statMoviesShowing" style="color:#c084fc;">0 Phim</h2>
                    <p class="admin-stat-sub">Danh mục phim active</p>
                </div>
            </article>
        </div>

        <!-- Chart -->
        <article class="card admin-section">
            <h2>Doanh thu 7 ngày gần nhất</h2>
            <canvas id="revenueChart" height="300"></canvas>
        </article>

        <!-- Top Movies -->
        <article class="card">
            <h2>Top 5 phim bán chạy</h2>
            <table id="topMoviesTable" class="admin-table" style="margin-top:16px;">
                <thead>
                    <tr style="border-bottom:1px solid #334155; text-align:left;">
                        <th>Phim</th>
                        <th>Đơn</th>
                        <th>Vé</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody id="topMoviesBody"></tbody>
            </table>
        </article>
    </main>
    <footer>© 2026 CineStar Admin</footer>
    <script src="../js/api.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js?v=2"></script>
</body>
</html>
