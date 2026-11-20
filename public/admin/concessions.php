<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Bắp Nước | CineStar Admin</title>
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
                <a href="showtimes.php">
                    <i class="fa-solid fa-clock"></i> Suất chiếu
                </a>
                <a href="concessions.php" class="active">
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
            <div class="admin-table-wrap">
                <table id="concessionsTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th style="width:80px;">Ảnh</th>
                            <th>Tên món</th>
                            <th>Loại</th>
                            <th style="width:120px;">Giá</th>
                            <th style="width:100px;">Trạng thái</th>
                            <th style="width:160px;">Thao tác</th>
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
                <div class="modal-header">
                    <h2 id="concessionModalTitle">Thêm món mới</h2>
                    <button id="closeConcessionModal" class="modal-close">×</button>
                </div>
                <form id="concessionForm">
                    <div class="modal-body">
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
                    <div class="modal-footer">
                        <button type="button" id="cancelConcessionModal" class="button btn-ghost">Hủy</button>
                        <button type="submit" class="btn-gradient" id="concessionSubmitBtn">Lưu</button>
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
