<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Phòng & Ghế | CineStar Admin</title>
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
        <a href="rooms.php" class="active">Phòng & Ghế</a>
        <a href="concessions.php">Bắp nước</a>
        </div>
    </nav>
    <main class="admin-main">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div class="admin-page-head">
            <h1>Quản Lý Phòng & Ghế</h1>
        </div>

        <!-- Cinema Filter -->
        <div class="admin-filters">
            <label>
                <span>Lọc theo rạp</span>
                <select id="cinemaFilter">
                    <option value="">Tất cả rạp</option>
                </select>
            </label>
        </div>

        <!-- Rooms Table -->
        <article class="card" style="margin-bottom:24px;">
            <h2>Danh sách phòng</h2>
            <div class="admin-table-wrap">
                <table id="roomsTable" class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">ID</th>
                            <th>Tên phòng</th>
                            <th>Loại phòng</th>
                            <th>Rạp</th>
                            <th style="width:100px;">Số ghế</th>
                            <th style="width:160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="roomsBody"></tbody>
                </table>
            </div>
            <div id="roomsEmptyState" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có phòng nào
            </div>
        </article>

        <!-- Seat Map Section -->
        <article class="card" id="seatMapSection" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
                <h2 id="seatMapTitle">Sơ đồ ghế - Phòng </h2>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button id="addSeatBtn" class="button">+ Thêm ghế</button>
                    <button id="autoGenSeatsBtn" class="button" style="background:#8b5cf6;">Tự động tạo ghế</button>
                    <button id="backToRoomsBtn" class="button" style="background:transparent; color:#94a3b8;">← Quay lại</button>
                </div>
            </div>
            
            <div style="margin-bottom:16px; display:flex; gap:16px; flex-wrap:wrap; align-items:center; font-size:13px; color:#94a3b8;">
                <label><input type="radio" name="seatType" value="Thuong" checked> Thường</label>
                <label><input type="radio" name="seatType" value="VIP"> VIP</label>
                <label><input type="radio" name="seatType" value="Couple"> Couple</label>
                <span id="selectedSeatInfo" style="margin-left:auto; font-weight:bold; color:#38bdf8;"></span>
            </div>

            <div class="screen" style="margin:20px auto; text-align:center;">MÀN HÌNH</div>
            
            <div class="seat-scroll" style="overflow-x:auto; padding:10px 0;">
                <div id="seatMap" style="min-width:600px;"></div>
            </div>
        </article>

        <!-- Room Modal -->
        <div id="roomModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:500px; width:90%;">
                <div class="modal-header">
                    <h2 id="roomModalTitle">Thêm phòng mới</h2>
                    <button id="closeRoomModal" class="modal-close">×</button>
                </div>
                <form id="roomForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="roomId">
                        
                        <div>
                            <label>Rạp <span style="color:#ef4444;">*</span></label>
                            <select name="rap_id" id="roomCinemaId" required style="width:100%;"></select>
                        </div>
                        
                        <div>
                            <label>Tên phòng <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="ten_phong" id="ten_phong" required maxlength="50" style="width:100%;">
                        </div>
                        
                        <div>
                            <label>Loại phòng <span style="color:#ef4444;">*</span></label>
                            <select name="loai_phong" id="loai_phong" required style="width:100%;">
                                <option value="2D">2D</option>
                                <option value="3D">3D</option>
                                <option value="IMAX">IMAX</option>
                                <option value="4DX">4DX</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="cancelRoomModal" class="button btn-ghost">Hủy</button>
                        <button type="submit" class="btn-gradient" id="roomSubmitBtn">Lưu</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Auto Generate Seats Modal -->
        <div id="autoGenModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:400px; width:90%;">
                <div class="modal-header">
                    <h2>Tự động tạo ghế</h2>
                    <button id="closeAutoGenModal" class="modal-close">×</button>
                </div>
                <form id="autoGenForm">
                    <div class="modal-body">
                        <div>
                            <label>Số hàng (A, B, C...)</label>
                            <input type="number" name="rows" id="genRows" value="8" min="1" max="20" style="width:100%;">
                        </div>
                        <div>
                            <label>Số ghế mỗi hàng</label>
                            <input type="number" name="cols" id="genCols" value="12" min="1" max="30" style="width:100%;">
                        </div>
                        <div style="grid-column:1/-1;">
                            <label>Loại ghế mặc định</label>
                            <select name="default_type" id="genDefaultType" style="width:100%;">
                                <option value="Thuong">Thường</option>
                                <option value="VIP">VIP</option>
                                <option value="Couple">Couple</option>
                            </select>
                        </div>
                        <div style="grid-column:1/-1; font-size:13px; color:#94a3b8;">
                            <strong>Lưu ý:</strong> Sẽ xóa tất cả ghế hiện tại của phòng này và tạo lại.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="cancelAutoGenModal" class="button btn-ghost">Hủy</button>
                        <button type="submit" class="btn-gradient">Tạo ghế</button>
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
    <script src="../js/admin.js?v=3"></script>
</body>
</html>
