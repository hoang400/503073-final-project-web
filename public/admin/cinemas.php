<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Rạp | CineStar Admin</title>
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
        <a href="index.php">Dashboard</a>
        <a href="cinemas.php" class="active">Rạp chiếu</a>
        <a href="rooms.php">Phòng & Ghế</a>
        <a href="showtimes.php">Suất chiếu</a>
        <a href="concessions.php">Bắp nước</a>
        <a href="reports.php">Báo cáo</a>
    </nav>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div class="admin-page-head">
            <h1>Quản Lý Rạp Chiếu</h1>
            <button id="addCinemaBtn" class="btn-sky">+ Thêm rạp</button>
        </div>

        <!-- Cinemas Table -->
        <article class="card">
            <div class="admin-table-wrap">
                <table id="cinemasTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Tên rạp</th>
                            <th>Địa chỉ</th>
                            <th>Thành phố</th>
                            <th style="width:160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="cinemasBody"></tbody>
                </table>
            </div>
            <div id="emptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có rạp nào
            </div>
        </article>

        <!-- Cinema Modal -->
        <div id="cinemaModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:600px; width:90%;">
                <div class="modal-header">
                    <h2 id="cinemaModalTitle">Thêm rạp mới</h2>
                    <button id="closeCinemaModal" class="modal-close">×</button>
                </div>
                <form id="cinemaForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="cinemaId">
                        
                        <div style="grid-column:1/-1;">
                            <label>Tên rạp <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="ten_rap" id="ten_rap" required maxlength="255" style="width:100%;">
                        </div>
                        
                        <div style="grid-column:1/-1;">
                            <label>Địa chỉ <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="dia_chi" id="dia_chi" required maxlength="255" style="width:100%;">
                        </div>
                        
                        <div>
                            <label>Thành phố <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="thanh_pho" id="thanh_pho" required maxlength="100" style="width:100%;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="cancelCinemaModal" class="button btn-ghost">Hủy</button>
                        <button type="submit" class="btn-gradient" id="cinemaSubmitBtn">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer>© 2026 CineStar Admin</footer>
    <script src="../js/api.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js?v=3"></script>
</body>
</html>
