<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hệ Thống Quản Trị | CineStar</title>
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
        <a href="index.php" class="active">Dashboard</a>
        <a href="cinemas.php">Rạp chiếu</a>
        <a href="rooms.php">Phòng & Ghế</a>
        <a href="showtimes.php">Suất chiếu</a>
        <a href="concessions.php">Bắp nước</a>
        </div>
    </nav>
    <main class="admin-main">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>

        <div class="admin-page-head">
            <div>
                <h1>HỆ THỐNG QUẢN TRỊ BACK-END ADMIN</h1>
                <p class="admin-page-sub">Quản lý doanh thu, danh sách đơn vé và nghiệp vụ phim</p>
            </div>
        </div>

        <!-- 3 Cards -->
        <div class="admin-stat-grid">
            <article class="card admin-stat-card">
                <div>
                    <p class="admin-stat-label">Tổng doanh thu đặt vé</p>
                    <h2 class="admin-stat-value" id="statTotalRevenue" style="color:#34d399;">0 VNĐ</h2>
                    <p class="admin-stat-sub">Tính từ dữ liệu hệ thống</p>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div>
                    <p class="admin-stat-label">Tổng số vé đã bán</p>
                    <h2 class="admin-stat-value" id="statOrders" style="color:#38bdf8;">0 Đơn vé</h2>
                    <p class="admin-stat-sub">Cập nhật thời gian thực</p>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div>
                    <p class="admin-stat-label">Số phim đang chiếu</p>
                    <h2 class="admin-stat-value" id="statMoviesShowing" style="color:#818cf8;">0 Phim</h2>
                    <p class="admin-stat-sub">Danh mục phim active</p>
                </div>
            </article>
        </div>

        <!-- Quản lý danh sách phim -->
        <article class="card admin-section">
            <div class="admin-section-head">
                <h2>Quản lý danh sách phim</h2>
                <button id="addMovieBtn" class="btn-sky" type="button">+ Thêm Phim Mới</button>
            </div>
            <div class="admin-filters">
                <button type="button" class="button btn-sky movie-status-btn" data-status="">Tất cả</button>
                <button type="button" class="button btn-ghost movie-status-btn" data-status="DANG_CHIEU">Đang chiếu</button>
                <button type="button" class="button btn-ghost movie-status-btn" data-status="SAP_CHIEU">Sắp chiếu</button>
                <button type="button" class="button btn-ghost movie-status-btn" data-status="NGUNG_CHIEU">Ngưng chiếu</button>
            </div>
            <div class="admin-table-wrap">
                <table id="moviesTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:100px;">Poster</th>
                            <th>Tên phim</th>
                            <th style="width:100px;">Thời lượng</th>
                            <th style="width:100px;">IMDb</th>
                            <th style="width:110px;">Trạng thái</th>
                            <th style="width:160px; text-align:center;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="moviesBody"></tbody>
                </table>
            </div>
            <div id="moviesEmptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có phim nào trong hệ thống!
            </div>
        </article>

        <!-- Danh sách đơn vé hệ thống -->
        <article class="card admin-section">
            <div class="admin-section-head">
                <h2>Danh sách đơn vé hệ thống</h2>
            </div>
            <div class="admin-table-wrap">
                <table id="bookingsTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:140px;">Mã vé</th>
                            <th>Tên phim</th>
                            <th style="width:240px;">Rạp / Phòng / Suất chiếu</th>
                            <th style="width:110px;">Ghế chọn</th>
                            <th style="width:130px;">Doanh thu</th>
                            <th style="width:120px; text-align:center;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsBody"></tbody>
                </table>
            </div>
            <div id="bookingsEmptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có đơn vé nào được ghi nhận!
            </div>
        </article>

        <!-- Thêm / Sửa phim -->
        <div id="movieModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:768px; width:90%;">
                <div class="modal-header">
                    <h2 id="modalTitle">Thêm phim mới</h2>
                    <button id="closeModal" class="modal-close">×</button>
                </div>
                <form id="movieForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="movieId">
                        <div>
                            <label>Tên Phim</label>
                            <input type="text" name="ten_phim" id="ten_phim" required maxlength="255" style="width:100%;">
                        </div>
                        <div>
                            <label>Tên Ngắn (hiển thị Dropdown)</label>
                            <input type="text" name="ten_ngan" id="ten_ngan" required maxlength="255" placeholder="Ví dụ: Godzilla x Kong" style="width:100%;">
                        </div>
                        <div style="grid-column:1/-1;">
                            <label>URL Ảnh Poster</label>
                            <input type="url" name="poster_url" id="poster_url" required maxlength="255" placeholder="https://..." style="width:100%;">
                            <div id="posterPreview" style="margin-top:8px; max-height:200px;"></div>
                        </div>
                        <div>
                            <label>Thời Lượng</label>
                            <input type="number" name="thoi_luong" id="thoi_luong" required min="1" max="600" placeholder="VD: 115 phút" style="width:100%;">
                        </div>
                        <div>
                            <label>Điểm IMDb</label>
                            <input type="number" name="imdb" id="imdb" required min="0" max="10" step="0.1" placeholder="VD: 7.5/10" style="width:100%;">
                        </div>
                        <div>
                            <label>Giới Hạn Tuổi</label>
                            <input type="text" name="do_tuoi" id="do_tuoi" required maxlength="10" placeholder="VD: C13, T18..." style="width:100%;">
                        </div>
                        <div>
                            <label>ID Trailer YouTube</label>
                            <input type="text" name="trailer_url" id="trailer_url" required maxlength="500" placeholder="VD: dQw4w9WgXcQ" style="width:100%;">
                        </div>
                        <div style="grid-column:1/-1;">
                            <label>Thể Loại (Cách nhau bằng dấu phẩy)</label>
                            <input type="text" name="the_loai" id="the_loai" required maxlength="100" placeholder="Hành Động, Khoa Học Viễn Tưởng..." style="width:100%;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn-gradient btn-block" id="submitBtn">LƯU THÔNG TIN PHIM</button>
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
