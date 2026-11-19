<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản Lý Phòng & Ghế | CineStar Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <a class="brand" href="../index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Admin navigation">
            <a href="index.php">Dashboard</a>
            <a href="movies.php">Phim</a>
            <a href="cinemas.php">Rạp</a>
            <a href="rooms.php" class="active">Phòng & Ghế</a>
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
            <h1>Quản Lý Phòng & Ghế</h1>
            <button id="addRoomBtn" class="button">+ Thêm phòng</button>
        </div>

        <!-- Cinema Filter -->
        <div class="filters" style="margin-bottom:24px;">
            <label>
                Lọc theo rạp
                <select id="cinemaFilter">
                    <option value="">Tất cả rạp</option>
                </select>
            </label>
        </div>

        <!-- Rooms Table -->
        <article class="card" style="margin-bottom:24px;">
            <h2>Danh sách phòng</h2>
            <div style="overflow-x:auto;">
                <table id="roomsTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #334155; text-align:left;">
                            <th style="padding:12px; width:60px;">ID</th>
                            <th style="padding:12px;">Tên phòng</th>
                            <th style="padding:12px;">Loại phòng</th>
                            <th style="padding:12px;">Rạp</th>
                            <th style="padding:12px; width:100px;">Số ghế</th>
                            <th style="padding:12px; width:160px;">Thao tác</th>
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
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid #334155;">
                    <h2 id="roomModalTitle">Thêm phòng mới</h2>
                    <button id="closeRoomModal" class="button" style="background:transparent; color:#94a3b8;">×</button>
                </div>
                <form id="roomForm">
                    <div class="modal-body" style="padding:24px; display:grid; gap:16px;">
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
                    <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:12px; padding:16px 24px; border-top:1px solid #334155;">
                        <button type="button" id="cancelRoomModal" class="button" style="background:transparent; color:#94a3b8;">Hủy</button>
                        <button type="submit" class="button" id="roomSubmitBtn">Lưu</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Auto Generate Seats Modal -->
        <div id="autoGenModal" class="modal-overlay" style="display:none;">
            <div class="modal" style="max-width:400px; width:90%;">
                <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid #334155;">
                    <h2>Tự động tạo ghế</h2>
                    <button id="closeAutoGenModal" class="button" style="background:transparent; color:#94a3b8;">×</button>
                </div>
                <form id="autoGenForm">
                    <div class="modal-body" style="padding:24px; display:grid; gap:16px;">
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
                    <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:12px; padding:16px 24px; border-top:1px solid #334155;">
                        <button type="button" id="cancelAutoGenModal" class="button" style="background:transparent; color:#94a3b8;">Hủy</button>
                        <button type="submit" class="button" style="background:#8b5cf6;">Tạo ghế</button>
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
