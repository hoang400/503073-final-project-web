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
        <div class="admin-subnav-inner">
        <a href="index.php">Dashboard</a>
        <a href="cinemas.php">Rạp chiếu</a>
        <a href="rooms.php">Phòng & Ghế</a>
        <a href="showtimes.php" class="active">Suất chiếu</a>
        <a href="concessions.php">Bắp nước</a>
        </div>
    </nav>
    <main class="admin-main">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>

        <div class="admin-page-head">
            <h1>Quản Lý Suất Chiếu</h1>
            <button id="addShowtimeBtn" class="btn-sky">+ Thêm suất chiếu</button>
        </div>

        <!-- Filters -->
        <div class="admin-filters">
            <label>
                <span>Lọc theo phim</span>
                <select id="movieFilter">
                    <option value="">Tất cả phim</option>
                </select>
            </label>
            <label>
                <span>Lọc theo phòng</span>
                <select id="roomFilter">
                    <option value="">Tất cả phòng</option>
                </select>
            </label>
            <label>
                <span>Lọc theo ngày chiếu</span>
                <input type="date" id="dateFilter">
            </label>
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
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th style="width:160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="showtimesBody"></tbody>
                </table>
            </div>
            <div id="showtimesEmptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có suất chiếu nào
            </div>
        </article>

        <!-- Showtime Modal -->
        <div id="showtimeModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:600px; width:90%;">
                <div class="modal-header">
                    <h2 id="showtimeModalTitle">Thêm suất chiếu mới</h2>
                    <button id="closeShowtimeModal" class="modal-close">×</button>
                </div>
                <form id="showtimeForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="showtimeId">

                        <div id="showtimeFormError" style="display:none; grid-column:1/-1; padding:10px 12px; border-radius:6px; background:#450a0a; color:#fca5a5; font-size:13px;"></div>

                        <div style="grid-column:1/-1;">
                            <label>Phim <span style="color:#ef4444;">*</span></label>
                            <select name="phim_id" id="showtimeMovieId" required style="width:100%;"></select>
                        </div>

                        <div style="grid-column:1/-1;">
                            <label>Phòng <span style="color:#ef4444;">*</span></label>
                            <select name="phong_id" id="showtimeRoomId" required style="width:100%;"></select>
                        </div>

                        <div style="grid-column:1/-1;">
                            <label>Giờ bắt đầu <span style="color:#ef4444;">*</span></label>
                            <input type="datetime-local" name="thoi_gian_bat_dau" id="showtimeStart" required style="width:100%;">
                        </div>

                        <div style="grid-column:1/-1;">
                            <label>Giờ kết thúc <span style="color:#ef4444;">*</span></label>
                            <input type="datetime-local" name="thoi_gian_ket_thuc" id="showtimeEnd" required style="width:100%;">
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
    <footer class="site-footer">
        <p>
            © 2026 CineStar Cinema Vietnam.
            Hệ thống đặt vé giá rẻ cho sinh viên & giới trẻ.
        </p>
    </footer>
    <script src="../js/api.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js?v=4"></script>
</body>
</html>
