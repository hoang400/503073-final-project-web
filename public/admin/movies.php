<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Phim | CineStar Admin</title>
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
                <a href="movies.php" class="active">
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
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
            <h1>Quản Lý Phim</h1>
            <button id="addMovieBtn" class="button">+ Thêm phim</button>
        </div>

        <!-- Search/Filter -->
        <div class="filters" style="margin-bottom:24px;">
            <label>
                Tìm kiếm
                <input type="search" id="searchInput" placeholder="Tên phim..." style="width:280px;">
            </label>
            <label>
                Trạng thái
                <select id="statusFilter">
                    <option value="">Tất cả</option>
                    <option value="DANG_CHIEU">Đang chiếu</option>
                    <option value="SAP_CHIEU">Sắp chiếu</option>
                    <option value="NGUNG_CHIEU">Ngưng chiếu</option>
                </select>
            </label>
        </div>

        <!-- Movies Table -->
        <article class="card">
            <div class="admin-table-wrap">
                <table id="moviesTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:100px;">Poster</th>
                            <th>Tên phim</th>
                            <th style="width:100px;">Thời lượng</th>
                            <th style="width:100px;">IMDb</th>
                            <th style="width:130px;">Trạng thái</th>
                            <th style="width:160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="moviesBody"></tbody>
                </table>
            </div>
            <div id="emptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có phim nào
            </div>
        </article>

        <!-- Movie Modal -->
        <div id="movieModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:700px; width:90%;">
                <div class="modal-header">
                    <h2 id="modalTitle">Thêm phim mới</h2>
                    <button id="closeModal" class="modal-close">×</button>
                </div>
                <form id="movieForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="movieId">
                        
                        <div style="grid-column:1/-1;">
                            <label>Tên phim <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="ten_phim" id="ten_phim" required maxlength="255" style="width:100%;">
                        </div>
                        
                        <div style="grid-column:1/-1;">
                            <label>Thể loại <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="the_loai" id="the_loai" required maxlength="100" placeholder="Ví dụ: Hành động, Phiêu lưu" style="width:100%;">
                        </div>
                        
                        <div>
                            <label>Thời lượng (phút) <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="thoi_luong" id="thoi_luong" required min="1" max="600" style="width:100%;">
                        </div>
                        
                        <div>
                            <label>Trạng thái <span style="color:#ef4444;">*</span></label>
                            <select name="trang_thai" id="trang_thai" required style="width:100%;">
                                <option value="SAP_CHIEU">Sắp chiếu</option>
                                <option value="DANG_CHIEU">Đang chiếu</option>
                                <option value="NGUNG_CHIEU">Ngưng chiếu</option>
                            </select>
                        </div>
                        
                        <div style="grid-column:1/-1;">
                            <label>URL Poster</label>
                            <input type="url" name="poster_url" id="poster_url" maxlength="255" placeholder="https://..." style="width:100%;">
                            <div id="posterPreview" style="margin-top:8px; max-height:200px;"></div>
                        </div>
                        
                        <div>
                            <label>Tên ngắn</label>
                            <input
                                type="text"
                                name="ten_ngan"
                                id="ten_ngan"
                                maxlength="255"
                                placeholder="Ví dụ: Godzilla x Kong"
                                style="width:100%;"
                            >
                        </div>

                        <div>
                            <label>Tên hiển thị trong danh sách chọn</label>
                            <input
                                type="text"
                                name="ten_hien_thi_dropdown"
                                id="ten_hien_thi_dropdown"
                                maxlength="255"
                                placeholder="Tên phim (2D Vietsub)"
                                style="width:100%;"
                            >
                        </div>

                        <div>
                            <label>Phân loại độ tuổi</label>
                            <select name="do_tuoi" id="do_tuoi" style="width:100%;">
                                <option value="">Chưa xác định</option>
                                <option value="P">P</option>
                                <option value="K">K</option>
                                <option value="T13">T13</option>
                                <option value="T16">T16</option>
                                <option value="T18">T18</option>
                                <option value="C13">C13</option>
                                <option value="C16">C16</option>
                                <option value="C18">C18</option>
                            </select>
                        </div>

                        <div>
                            <label>Mô tả độ tuổi</label>
                            <input
                                type="text"
                                name="mo_ta_do_tuoi"
                                id="mo_ta_do_tuoi"
                                maxlength="255"
                                placeholder="Ví dụ: C13 - Khán giả trên 13 tuổi"
                                style="width:100%;"
                            >
                        </div>

                        <div>
                            <label>Điểm IMDb (0–10)</label>
                            <input
                                type="number"
                                name="imdb"
                                id="imdb"
                                min="0"
                                max="10"
                                step="0.1"
                                placeholder="8.5"
                                style="width:100%;"
                            >
                        </div>

                        <div>
                            <label>Đạo diễn</label>
                            <input
                                type="text"
                                name="dao_dien"
                                id="dao_dien"
                                maxlength="255"
                                style="width:100%;"
                            >
                        </div>

                        <div style="grid-column:1/-1;">
                            <label>Biên kịch</label>
                            <textarea
                                name="bien_kich"
                                id="bien_kich"
                                rows="2"
                                style="width:100%;"
                            ></textarea>
                        </div>

                        <div style="grid-column:1/-1;">
                            <label>Diễn viên</label>
                            <textarea
                                name="dien_vien"
                                id="dien_vien"
                                rows="2"
                                style="width:100%;"
                            ></textarea>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label>URL Trailer YouTube</label>
                            <input
                                type="url"
                                name="trailer_url"
                                id="trailer_url"
                                maxlength="500"
                                placeholder="https://www.youtube.com/watch?v=..."
                                style="width:100%;"
                            >
                        </div>

                        <div style="grid-column:1/-1;">
                            <label>Mô tả phim</label>
                            <textarea
                                name="mo_ta"
                                id="mo_ta"
                                rows="5"
                                style="width:100%;"
                                placeholder="Nhập nội dung giới thiệu phim..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn-gradient btn-block" id="submitBtn">LƯU THÔNG TIN PHIM</button>
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
