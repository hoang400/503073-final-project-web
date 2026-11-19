<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Suất Chiếu | CineStar Admin</title>
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
            <a href="showtimes.php" class="active">Suất chiếu</a>
            <a href="concessions.php">Bắp nước</a>
            <a href="bookings.php">Đơn hàng</a>
            <a href="reports.php">Báo cáo</a>
        </nav>
        <div id="account"></div>
    </header>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
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
            <div style="overflow-x:auto;">
                <table id="showtimesTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #334155; text-align:left;">
                            <th style="padding:12px; width:60px;">ID</th>
                            <th style="padding:12px;">Phim</th>
                            <th style="padding:12px;">Rạp / Phòng</th>
                            <th style="padding:12px; width:180px;">Bắt đầu</th>
                            <th style="padding:12px; width:180px;">Kết thúc</th>
                            <th style="padding:12px; width:120px;">Giá vé</th>
                            <th style="padding:12px; width:160px;">Thao tác</th>
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
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid #334155;">
                    <h2 id="showtimeModalTitle">Thêm suất chiếu</h2>
                    <button id="closeShowtimeModal" class="button" style="background:transparent; color:#94a3b8;">×</button>
                </div>
                <form id="showtimeForm">
                    <div class="modal-body" style="padding:24px; display:grid; gap:16px;">
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
                    <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:12px; padding:16px 24px; border-top:1px solid #334155;">
                        <button type="button" id="cancelShowtimeModal" class="button" style="background:transparent; color:#94a3b8;">Hủy</button>
                        <button type="submit" class="button" id="showtimeSubmitBtn">Lưu</button>
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
