<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Phim | CineStar Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <a class="brand" href="../index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Admin navigation">
            <a href="index.php">Dashboard</a>
            <a href="movies.php" class="active">Phim</a>
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
            <div style="overflow-x:auto;">
                <table id="moviesTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #334155; text-align:left;">
                            <th style="padding:12px; width:60px;">ID</th>
                            <th style="padding:12px; width:100px;">Poster</th>
                            <th style="padding:12px;">Tên phim</th>
                            <th style="padding:12px;">Thể loại</th>
                            <th style="padding:12px; width:100px;">Thời lượng</th>
                            <th style="padding:12px; width:130px;">Trạng thái</th>
                            <th style="padding:12px; width:160px;">Thao tác</th>
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
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid #334155;">
                    <h2 id="modalTitle">Thêm phim mới</h2>
                    <button id="closeModal" class="button" style="background:transparent; color:#94a3b8;">×</button>
                </div>
                <form id="movieForm">
                    <div class="modal-body" style="padding:24px; display:grid; gap:16px;">
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
                        
                        <div style="grid-column:1/-1;">
                            <label>URL Trailer (YouTube)</label>
                            <input type="url" name="trailer_url" id="trailer_url" maxlength="500" placeholder="https://youtube.com/watch?v=..." style="width:100%;">
                        </div>
                        
                        <div style="grid-column:1/-1;">
                            <label>Mô tả</label>
                            <textarea name="mo_ta" id="mo_ta" rows="4" maxlength="2000" placeholder="Mô tả phim..." style="width:100%;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:12px; padding:16px 24px; border-top:1px solid #334155;">
                        <button type="button" id="cancelModal" class="button" style="background:transparent; color:#94a3b8;">Hủy</button>
                        <button type="submit" class="button" id="submitBtn">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer>© 2026 CineStar Admin</footer>
    <script src="../js/api.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>
