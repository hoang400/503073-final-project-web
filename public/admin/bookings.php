<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Đơn Hàng | CineStar Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                <a href="index.php">
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
                <a href="bookings.php" class="active">
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
        
        <h1>Quản Lý Đơn Hàng</h1>

        <!-- Filters -->
        <div class="filters" style="margin-bottom:24px; flex-wrap:wrap;">
            <label>
                Trạng thái
                <select id="statusFilter">
                    <option value="">Tất cả</option>
                    <option value="ChuaThanhToan">Chưa thanh toán</option>
                    <option value="DaThanhToan">Đã thanh toán</option>
                    <option value="DaHuy">Đã hủy</option>
                </select>
            </label>
            <label>
                Từ ngày
                <input type="date" id="dateFrom">
            </label>
            <label>
                Đến ngày
                <input type="date" id="dateTo">
            </label>
            <button id="clearFiltersBtn" class="button" style="background:transparent; color:#94a3b8;">Xóa lọc</button>
        </div>

        <!-- Bookings Table -->
        <article class="card">
            <div class="admin-table-wrap">
                <table id="bookingsTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th style="width:140px;">Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Phim</th>
                            <th style="width:180px;">Suất chiếu</th>
                            <th style="width:110px;">Ghế chọn</th>
                            <th style="width:120px;">Tổng tiền</th>
                            <th style="width:140px;">Trạng thái</th>
                            <th style="width:180px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsBody"></tbody>
                </table>
            </div>
            <div id="emptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có đơn hàng nào
            </div>
        </article>

        <!-- Booking Detail Modal -->
        <div id="bookingDetailModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:900px; width:95%; max-height:90vh; overflow-y:auto;">
                <div class="modal-header" style="position:sticky; top:0; background:#0f172a; z-index:10;">
                    <h2 id="detailModalTitle">Chi tiết đơn hàng</h2>
                    <button id="closeDetailModal" class="modal-close">×</button>
                </div>
                <div class="modal-body" style="padding:24px;" id="bookingDetailContent">
                    <div style="text-align:center; padding:40px; color:#94a3b8;">Đang tải...</div>
                </div>
            </div>
        </div>
    </main>
    <footer>© 2026 CineStar Admin</footer>
    <script src="../js/api.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js?v=2"></script>
</body>
</html>
