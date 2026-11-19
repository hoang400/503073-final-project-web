<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Đơn Hàng | CineStar Admin</title>
    <link rel="stylesheet" href="../css/style.css">
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
            <a href="bookings.php" class="active">Đơn hàng</a>
            <a href="reports.php">Báo cáo</a>
        </nav>
        <div id="account"></div>
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
            <div style="overflow-x:auto;">
                <table id="bookingsTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #334155; text-align:left;">
                            <th style="padding:12px; width:60px;">ID</th>
                            <th style="padding:12px; width:140px;">Mã đơn</th>
                            <th style="padding:12px;">Khách hàng</th>
                            <th style="padding:12px;">Phim</th>
                            <th style="padding:12px; width:180px;">Suất chiếu</th>
                            <th style="padding:12px; width:120px;">Tổng tiền</th>
                            <th style="padding:12px; width:140px;">Trạng thái</th>
                            <th style="padding:12px; width:160px;">Thao tác</th>
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
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid #334155; position:sticky; top:0; background:#151b2b; z-index:10;">
                    <h2 id="detailModalTitle">Chi tiết đơn hàng</h2>
                    <button id="closeDetailModal" class="button" style="background:transparent; color:#94a3b8;">×</button>
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
    <script src="../js/admin.js"></script>
</body>
</html>
