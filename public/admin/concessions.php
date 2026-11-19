<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Bắp Nước | CineStar Admin</title>
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
            <a href="concessions.php" class="active">Bắp nước</a>
            <a href="bookings.php">Đơn hàng</a>
            <a href="reports.php">Báo cáo</a>
        </nav>
        <div id="account"></div>
    </header>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
            <h1>Quản Lý Bắp Nước</h1>
            <button id="addConcessionBtn" class="button">+ Thêm món</button>
        </div>

        <!-- Filter -->
        <div class="filters" style="margin-bottom:24px;">
            <label>
                Loại
                <select id="typeFilter">
                    <option value="">Tất cả loại</option>
                    <option value="Bap">Bắp</option>
                    <option value="Nuoc">Nước</option>
                    <option value="Combo">Combo</option>
                </select>
            </label>
            <label>
                Trạng thái
                <select id="statusFilter">
                    <option value="">Tất cả</option>
                    <option value="1">Còn bán</option>
                    <option value="0">Hết hàng</option>
                </select>
            </label>
        </div>

        <!-- Concessions Table -->
        <article class="card">
            <div style="overflow-x:auto;">
                <table id="concessionsTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #334155; text-align:left;">
                            <th style="padding:12px; width:60px;">ID</th>
                            <th style="padding:12px; width:80px;">Ảnh</th>
                            <th style="padding:12px;">Tên món</th>
                            <th style="padding:12px;">Loại</th>
                            <th style="padding:12px; width:120px;">Giá</th>
                            <th style="padding:12px; width:100px;">Trạng thái</th>
                            <th style="padding:12px; width:160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="concessionsBody"></tbody>
                </table>
            </div>
            <div id="emptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có món nào
            </div>
        </article>

        <!-- Concession Modal -->
        <div id="concessionModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:600px; width:90%;">
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid #334155;">
                    <h2 id="concessionModalTitle">Thêm món mới</h2>
                    <button id="closeConcessionModal" class="button" style="background:transparent; color:#94a3b8;">×</button>
                </div>
                <form id="concessionForm">
                    <div class="modal-body" style="padding:24px; display:grid; gap:16px;">
                        <input type="hidden" name="id" id="concessionId">
                        
                        <div style="grid-column:1/-1;">
                            <label>Tên món <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="ten_mon" id="ten_mon" required maxlength="255" style="width:100%;">
                        </div>
                        
                        <div>
                            <label>Loại <span style="color:#ef4444;">*</span></label>
                            <select name="loai" id="loai" required style="width:100%;">
                                <option value="Bap">Bắp</option>
                                <option value="Nuoc">Nước</option>
                                <option value="Combo">Combo</option>
                            </select>
                        </div>
                        
                        <div>
                            <label>Giá (VNĐ) <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="don_gia" id="don_gia" required min="0" step="1000" style="width:100%;">
                        </div>
                        
                        <div style="grid-column:1/-1;">
                            <label>URL Ảnh</label>
                            <input type="url" name="hinh_anh" id="hinh_anh" maxlength="255" placeholder="https://..." style="width:100%;">
                            <div id="concessionImagePreview" style="margin-top:8px;"></div>
                        </div>
                        
                        <div>
                            <label>Trạng thái</label>
                            <select name="trang_thai" id="trang_thai" style="width:100%;">
                                <option value="1">Còn bán</option>
                                <option value="0">Hết hàng</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:12px; padding:16px 24px; border-top:1px solid #334155;">
                        <button type="button" id="cancelConcessionModal" class="button" style="background:transparent; color:#94a3b8;">Hủy</button>
                        <button type="submit" class="button" id="concessionSubmitBtn">Lưu</button>
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
