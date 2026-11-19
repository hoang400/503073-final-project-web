<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo Cáo Doanh Thu | CineStar Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <header>
        <a class="brand" href="../index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Admin navigation">
            <a href="index.php">Dashboard</a>
            <a href="movies.php">Phim</a>
            <a href="cinemas.php">Rạp</a>
            <a href="rooms.php">Phòng & Ghế</a>
            <a href="showtimes.php">Suất chiếu</a>
            <a href="concessions.php">Bắp nước</a>
            <a href="bookings.php">Đơn hàng</a>
            <a href="reports.php" class="active">Báo cáo</a>
        </nav>
        <div id="account"></div>
    </header>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
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
        <div class="dashboard-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:32px;">
            <article class="card stat-card">
                <div class="stat-icon" style="background:#22c55e;">💰</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Tổng doanh thu</p>
                    <h2 id="totalRevenue" style="margin:8px 0 0; font-size:28px;">0 VNĐ</h2>
                </div>
            </article>
            <article class="card stat-card">
                <div class="stat-icon" style="background:#3b82f6;">🎫</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Doanh thu vé</p>
                    <h2 id="ticketRevenue" style="margin:8px 0 0; font-size:28px;">0 VNĐ</h2>
                </div>
            </article>
            <article class="card stat-card">
                <div class="stat-icon" style="background:#f59e0b;">🍿</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Doanh thu bắp nước</p>
                    <h2 id="concessionRevenue" style="margin:8px 0 0; font-size:28px;">0 VNĐ</h2>
                </div>
            </article>
            <article class="card stat-card">
                <div class="stat-icon" style="background:#a855f7;">📦</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Số đơn hàng</p>
                    <h2 id="orderCount" style="margin:8px 0 0; font-size:28px;">0</h2>
                </div>
            </article>
            <article class="card stat-card">
                <div class="stat-icon" style="background:#ec4899;">🎟️</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Vé đã bán</p>
                    <h2 id="ticketCount" style="margin:8px 0 0; font-size:28px;">0</h2>
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
                <div style="overflow-x:auto;">
                    <table id="dailyRevenueTable" style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid #334155; text-align:left;">
                                <th style="padding:10px;">Ngày</th>
                                <th style="padding:10px; text-align:right;">Đơn</th>
                                <th style="padding:10px; text-align:right;">Vé</th>
                                <th style="padding:10px; text-align:right;">Vé (VNĐ)</th>
                                <th style="padding:10px; text-align:right;">Bắp (VNĐ)</th>
                                <th style="padding:10px; text-align:right;">Tổng (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody id="dailyRevenueBody"></tbody>
                    </table>
                </div>
            </article>
            <article class="card">
                <h2>Top Phim Theo Doanh Thu</h2>
                <div style="overflow-x:auto;">
                    <table id="topMoviesTable" style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid #334155; text-align:left;">
                                <th style="padding:10px;">Phim</th>
                                <th style="padding:10px; text-align:center;">Đơn</th>
                                <th style="padding:10px; text-align:center;">Vé</th>
                                <th style="padding:10px; text-align:right;">Doanh thu (VNĐ)</th>
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
    <script src="../js/admin.js"></script>
</body>
</html>
