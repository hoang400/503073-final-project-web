<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo Cáo Doanh Thu | CineStar Admin</title>
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
            <nav class="main-nav" aria-label="Điều hướng chính">
                <a href="../index.php">
                    Trang Chủ
                </a>
                <a href="../movies.php">
                    Phim Đang Chiếu
                </a>
                <a href="../booking.php">
                    Đặt Vé
                </a>
                <a href="../history.php">
                    Lịch Sử Vé
                </a>
                <a href="index.php" class="active">
                    Trang Admin
                </a>
            </nav>
            <div class="account-area">
                <span class="admin-badge">Admin Mode</span>
                <div id="account"></div>
            </div>
        </div>
    </header>
    <nav class="admin-subnav" aria-label="Quản trị">
        <a href="index.php">Dashboard</a>
        <a href="cinemas.php">Rạp chiếu</a>
        <a href="rooms.php">Phòng & Ghế</a>
        <a href="showtimes.php">Suất chiếu</a>
        <a href="concessions.php">Bắp nước</a>
        <a href="reports.php" class="active">Báo cáo</a>
    </nav>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div class="admin-page-head">
            <h1>Báo Cáo Doanh Thu</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <label style="font-size:13px; color:#94a3b8;">
                    Từ ngày
                    <input type="date" id="reportDateFrom" style="width:160px; margin-left:8px;">
                </label>
                <label style="font-size:13px; color:#94a3b8;">
                    Đến ngày
                    <input type="date" id="reportDateTo" style="width:160px; margin-left:8px;">
                </label>
                <button id="filterReportBtn" class="button">Lọc</button>
                <button id="exportCsvBtn" class="button" style="background:#8b5cf6;">Xuất CSV</button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="admin-stat-grid">
            <article class="card admin-stat-card">
                <div class="admin-stat-icon" style="background:#22c55e;">💰</div>
                <div>
                    <p class="admin-stat-label">Tổng doanh thu</p>
                    <h2 class="admin-stat-value" id="totalRevenue">0 VNĐ</h2>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="admin-stat-icon" style="background:#3b82f6;">🎫</div>
                <div>
                    <p class="admin-stat-label">Doanh thu vé</p>
                    <h2 class="admin-stat-value" id="ticketRevenue">0 VNĐ</h2>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="admin-stat-icon" style="background:#f59e0b;">🍿</div>
                <div>
                    <p class="admin-stat-label">Doanh thu bắp nước</p>
                    <h2 class="admin-stat-value" id="concessionRevenue">0 VNĐ</h2>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="admin-stat-icon" style="background:#a855f7;">📦</div>
                <div>
                    <p class="admin-stat-label">Số đơn hàng</p>
                    <h2 class="admin-stat-value" id="orderCount">0</h2>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="admin-stat-icon" style="background:#ec4899;">🎟️</div>
                <div>
                    <p class="admin-stat-label">Vé đã bán</p>
                    <h2 class="admin-stat-value" id="ticketCount">0</h2>
                </div>
            </article>
        </div>

        <!-- Charts Row -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px;">
            <article class="card">
                <h2>Doanh thu theo ngày</h2>
                <canvas id="dailyRevenueChart" height="300"></canvas>
            </article>
            <article class="card">
                <h2>Top 5 Phim Bán Chạy</h2>
                <canvas id="topMoviesChart" height="300"></canvas>
            </article>
        </div>

        <!-- Detail Tables -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
            <article class="card">
                <h2>Doanh Thu Theo Ngày (Chi Tiết)</h2>
                <div class="admin-table-wrap">
                    <table id="dailyRevenueTable" class="admin-table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th style="text-align:right;">Đơn</th>
                                <th style="text-align:right;">Vé</th>
                                <th style="text-align:right;">Vé (VNĐ)</th>
                                <th style="text-align:right;">Bắp (VNĐ)</th>
                                <th style="text-align:right;">Tổng (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody id="dailyRevenueBody"></tbody>
                    </table>
                </div>
            </article>
            <article class="card">
                <h2>Top Phim Theo Doanh Thu</h2>
                <div class="admin-table-wrap">
                    <table id="topMoviesTable" class="admin-table">
                        <thead>
                            <tr>
                                <th>Phim</th>
                                <th style="text-align:center;">Đơn</th>
                                <th style="text-align:center;">Vé</th>
                                <th style="text-align:right;">Doanh thu (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody id="topMoviesBody"></tbody>
                    </table>
                </div>
            </article>
        </div>
    </main>
    <footer>© 2026 CineStar Admin</footer>
    <script src="../js/api.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js?v=3"></script>
</body>
</html>
