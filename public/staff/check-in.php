<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soát Vé - Nhân Viên | CineStar</title>
    <link rel="stylesheet" href="../../css/style.css">
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
                    <i class="fa-solid fa-house"></i> Trang Chủ
                </a>
                <a href="../movies.php">
                    <i class="fa-solid fa-film"></i> Phim Đang Chiếu
                </a>
                <a href="../booking.php">
                    <i class="fa-solid fa-ticket"></i> Đặt Vé
                </a>
                <a href="../history.php">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử Vé
                </a>
                <a href="../admin/index.php">
                    <i class="fa-solid fa-user-shield"></i> Trang Admin
                </a>
            </nav>

            <div class="account-area">
                <span class="staff-badge">Nhân viên</span>
                <div id="account"></div>
            </div>
        </div>
    </header>
    <main class="admin-main staff-checkin">
        <div id="notice" class="notice" role="status" aria-live="polite"></div>

        <div class="admin-page-head">
            <h1>Soát Vé &amp; Check-in</h1>
            <span id="staffInfo" class="staff-info"></span>
        </div>

        <!-- Nhập mã vé -->
        <article class="card checkin-card">
            <h2>Nhập mã vé</h2>
            <div class="checkin-form">
                <input type="text" id="qrInput" class="checkin-input" placeholder="Nhập mã đơn hàng, VD: CS474322" autocomplete="off">
                <button id="verifyBtn" class="btn-gradient">Kiểm tra</button>
            </div>
            <p class="checkin-hint">Nhập mã đơn hàng (VD: CS474322) để xem toàn bộ vé của đơn. Chỉ cần nhập mã, không cần quét QR.</p>
        </article>

        <!-- Result Section -->
        <article class="card" id="resultSection" style="display:none;">
            <div class="result-head">
                <h2 id="resultTitle">Kết Quả Kiểm Tra</h2>
                <span id="resultStatus" class="status-badge"></span>
            </div>

            <!-- Kết quả 1 vé -->
            <div id="singleResult">
                <div class="ticket-info">
                    <div class="info-item">
                        <label>Mã vé</label>
                        <strong id="resQrCode" class="checkin-code"></strong>
                    </div>
                    <div class="info-item">
                        <label>Mã đơn hàng</label>
                        <strong id="resOrderCode"></strong>
                    </div>
                    <div class="info-item">
                        <label>Phim</label>
                        <strong id="resMovie"></strong>
                    </div>
                    <div class="info-item">
                        <label>Rạp / Phòng</label>
                        <strong id="resCinemaRoom"></strong>
                    </div>
                    <div class="info-item">
                        <label>Ghế</label>
                        <strong id="resSeat"></strong>
                    </div>
                    <div class="info-item">
                        <label>Suất chiếu</label>
                        <strong id="resShowtime"></strong>
                    </div>
                    <div class="info-item">
                        <label>Khách hàng</label>
                        <strong id="resCustomer"></strong>
                    </div>
                    <div class="info-item">
                        <label>Trạng thái thanh toán</label>
                        <strong id="resPaymentStatus"></strong>
                    </div>
                </div>

                <div class="result-actions">
                    <button id="confirmCheckinBtn" class="button btn-confirm" style="display:none;">Xác nhận vào rạp</button>
                    <button type="button" class="button btn-ghost clear-result-btn">Kiểm tra vé khác</button>
                </div>
            </div>

            <!-- Kết quả mã đơn hàng: danh sách vé của đơn -->
            <div id="orderResult" style="display:none;">
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Ghế</th>
                                <th>Phim</th>
                                <th>Suất chiếu</th>
                                <th>Trạng thái</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="orderTicketsBody"></tbody>
                    </table>
                </div>

                <div class="result-actions">
                    <button type="button" class="button btn-ghost clear-result-btn">Kiểm tra vé khác</button>
                </div>
            </div>
        </article>

        <!-- History/Log Section -->
        <article class="card history-card">
            <h2>Lịch Sử Soát Vé Gần Đây</h2>
            <div class="admin-table-wrap">
                <table id="historyTable" class="admin-table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Mã vé</th>
                            <th>Phim</th>
                            <th>Kết quả</th>
                            <th>Nhân viên</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody"></tbody>
                </table>
            </div>
            <div id="historyEmpty" style="display:none; padding:32px; text-align:center; color:#94a3b8;">
                Chưa có lịch sử soát vé
            </div>
        </article>
    </main>
    <footer class="site-footer">
        <p>
            © 2026 CineStar Cinema Vietnam.
            Hệ thống đặt vé giá rẻ cho sinh viên & giới trẻ.
        </p>
    </footer>
    <script src="../../js/api.js"></script>
    <script src="../../js/main.js"></script>
    <script src="../../js/staff.js"></script>
</body>
</html>
