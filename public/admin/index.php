<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | CineStar</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <header>
        <a class="brand" href="../index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Admin navigation">
            <a href="index.php" class="active">Dashboard</a>
            <a href="movies.php">Phim</a>
            <a href="cinemas.php">Rạp</a>
            <a href="rooms.php">Phòng & Ghế</a>
            <a href="showtimes.php">Suất chiếu</a>
            <a href="concessions.php">Bắp nước</a>
            <a href="bookings.php">Đơn hàng</a>
            <a href="reports.php">Báo cáo</a>
        </nav>
        <div id="account"></div>
    </header>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
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
        <div class="dashboard-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:32px;">
            <article class="card stat-card">
                <div class="stat-icon" style="background:#3b82f6;">🎫</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Tổng đơn hàng</p>
                    <h2 id="statOrders" style="margin:8px 0 0; font-size:28px;">0</h2>
                </div>
            </article>
            <article class="card stat-card">
                <div class="stat-icon" style="background:#22c55e;">💰</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Doanh thu vé</p>
                    <h2 id="statTicketRev" style="margin:8px 0 0; font-size:28px;">0 VNĐ</h2>
                </div>
            </article>
            <article class="card stat-card">
                <div class="stat-icon" style="background:#f59e0b;">🍿</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Doanh thu bắp nước</p>
                    <h2 id="statConcessionRev" style="margin:8px 0 0; font-size:28px;">0 VNĐ</h2>
                </div>
            </article>
            <article class="card stat-card">
                <div class="stat-icon" style="background:#a855f7;">🎟️</div>
                <div>
                    <p style="color:#94a3b8; font-size:13px; margin:0;">Vé đã bán</p>
                    <h2 id="statTickets" style="margin:8px 0 0; font-size:28px;">0</h2>
                </div>
            </article>
        </div>

        <!-- Chart -->
        <article class="card" style="margin-bottom:24px;">
            <h2>Doanh thu 7 ngày gần nhất</h2>
            <canvas id="revenueChart" height="300"></canvas>
        </article>

        <!-- Top Movies -->
        <article class="card">
            <h2>Top 5 phim bán chạy</h2>
            <table id="topMoviesTable" style="width:100%; border-collapse:collapse; margin-top:16px;">
                <thead>
                    <tr style="border-bottom:1px solid #334155; text-align:left;">
                        <th style="padding:12px;">Phim</th>
                        <th style="padding:12px;">Đơn</th>
                        <th style="padding:12px;">Vé</th>
                        <th style="padding:12px;">Doanh thu</th>
                    </tr>
                </thead>
                <tbody id="topMoviesBody"></tbody>
            </table>
        </article>
    </main>
    <footer>© 2026 CineStar Admin</footer>
    <script src="../js/api.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>
