<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Suất Chiếu | CineStar Admin</title>
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
                <a href="showtimes.php" class="active">
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
            <h1>Quản Lý Suất Chiếu</h1>
            <button id="addShowtimeBtn" class="button">+ Thêm suất chiếu</button>
        </div>

        <!-- Filters -->
        <div class="filters" style="margin-bottom:24px; flex-wrap:wrap;">
            <label>
                Phim
                <select id="movieFilter">
                    <option value="">Tất cả phim</option>
                </select>
            </label>
            <label>
                Phòng
                <select id="roomFilter">
                    <option value="">Tất cả phòng</option>
                </select>
            </label>
            <label>
                Ngày
                <input type="date" id="dateFilter">
            </label>
            <button id="clearFiltersBtn" class="button" style="background:transparent; color:#94a3b8;">Xóa lọc</button>
        </div>

        <!-- Showtimes Table -->
        <article class="card">
            <div class="admin-table-wrap">
                <table id="showtimesTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Phim</th>
                            <th>Rạp / Phòng</th>
                            <th style="width:180px;">Bắt đầu</th>
                            <th style="width:180px;">Kết thúc</th>
                            <th style="width:120px;">Giá vé</th>
                            <th style="width:160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="showtimesBody"></tbody>
                </table>
            </div>
            <div id="emptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có suất chiếu nào
            </div>
        </article>

        <!-- Showtime Modal -->
        <div id="showtimeModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:600px; width:90%;">
                <div class="modal-header">
                    <h2 id="showtimeModalTitle">Thêm suất chiếu</h2>
                    <button id="closeShowtimeModal" class="modal-close">×</button>
                </div>
                <form id="showtimeForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="showtimeId">
                        
                        <div>
                            <label>Phim <span style="color:#ef4444;">*</span></label>
                            <select name="phim_id" id="showtimeMovieId" required style="width:100%;"></select>
                        </div>
                        
                        <div>
                            <label>Phòng <span style="color:#ef4444;">*</span></label>
                            <select name="phong_id" id="showtimeRoomId" required style="width:100%;"></select>
                        </div>
                        
                        <div style="grid-column:1/-1;">
                            <label>Thời gian bắt đầu <span style="color:#ef4444;">*</span></label>
                            <input type="datetime-local" name="thoi_gian_bat_dau" id="thoi_gian_bat_dau" required style="width:100%;">
                        </div>
                        
                        <div style="grid-column:1/-1;">
                            <label>Thời gian kết thúc <span style="color:#ef4444;">*</span></label>
                            <input type="datetime-local" name="thoi_gian_ket_thuc" id="thoi_gian_ket_thuc" required style="width:100%;">
                        </div>
                        
                        <div>
                            <label>Giá vé cơ bản (VNĐ) <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="gia_ve_co_ban" id="gia_ve_co_ban" required min="0" step="1000" style="width:100%;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="cancelShowtimeModal" class="button btn-ghost">Hủy</button>
                        <button type="submit" class="btn-gradient" id="showtimeSubmitBtn">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer>© 2026 CineStar Admin</footer>
    <script src="../js/api.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js?v=2"></script>
</body>
</html>
