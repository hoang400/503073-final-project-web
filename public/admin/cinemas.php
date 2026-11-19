<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Rạp | CineStar Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <a class="brand" href="../index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Admin navigation">
            <a href="index.php">Dashboard</a>
            <a href="movies.php">Phim</a>
            <a href="cinemas.php" class="active">Rạp</a>
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
            <h1>Quản Lý Rạp Chiếu</h1>
            <button id="addCinemaBtn" class="button">+ Thêm rạp</button>
        </div>

        <!-- Cinemas Table -->
        <article class="card">
            <div style="overflow-x:auto;">
                <table id="cinemasTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #334155; text-align:left;">
                            <th style="padding:12px; width:60px;">ID</th>
                            <th style="padding:12px;">Tên rạp</th>
                            <th style="padding:12px;">Địa chỉ</th>
                            <th style="padding:12px;">Thành phố</th>
                            <th style="padding:12px; width:160px;">Thao tác</th>
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
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid #334155;">
                    <h2 id="cinemaModalTitle">Thêm rạp mới</h2>
                    <button id="closeCinemaModal" class="button" style="background:transparent; color:#94a3b8;">×</button>
                </div>
                <form id="cinemaForm">
                    <div class="modal-body" style="padding:24px; display:grid; gap:16px;">
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
                    <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:12px; padding:16px 24px; border-top:1px solid #334155;">
                        <button type="button" id="cancelCinemaModal" class="button" style="background:transparent; color:#94a3b8;">Hủy</button>
                        <button type="submit" class="button" id="cinemaSubmitBtn">Lưu</button>
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
