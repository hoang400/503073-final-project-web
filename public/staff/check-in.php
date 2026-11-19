<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soát Vé - Nhân Viên | CineStar</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <header>
        <a class="brand" href="../index.php">CINESTAR<span>.</span></a>
        <nav aria-label="Staff navigation">
            <span style="color:#f59e0b; font-weight:bold;">👮 NHÂN VIÊN</span>
            <a href="../index.php">Trang chủ</a>
        </nav>
        <div id="account"></div>
    </header>
    <main>
        <div id="notice" class="notice" role="status" aria-live="polite"></div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
            <h1>Soát Vé & Check-in</h1>
            <span id="staffInfo" style="color:#94a3b8; font-size:13px;"></span>
        </div>

        <!-- QR Input Section -->
        <article class="card" style="max-width:600px; margin-bottom:24px;">
            <h2>Nhập Mã Vé / Quét QR</h2>
            <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                <input type="text" id="qrInput" placeholder="Nhập mã QR hoặc mã vé..." style="flex:1; min-width:280px; padding:14px; font-size:16px; font-family:monospace;">
                <button id="verifyBtn" class="button" style="padding:14px 24px; font-size:16px;">🔍 Kiểm tra</button>
            </div>
            <p style="font-size:12px; color:#94a3b8;">Hỗ trợ nhập mã QR code hoặc mã đơn hàng (VD: CS...)</p>
        </article>

        <!-- Result Section -->
        <article class="card" id="resultSection" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
                <h2 id="resultTitle">Kết Quả Kiểm Tra</h2>
                <span id="resultStatus" class="status-badge"></span>
            </div>
            
            <div id="resultContent" style="display:grid; gap:16px;">
                <!-- Ticket Info Grid -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px;">
                    <div class="info-item">
                        <label>Mã vé / QR</label>
                        <strong id="resQrCode" style="font-family:monospace; font-size:13px; word-break:break-all;"></strong>
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
                
                <!-- Action Buttons -->
                <div id="resultActions" style="display:flex; gap:12px; flex-wrap:wrap; padding-top:16px; border-top:1px solid #334155;">
                    <button id="confirmCheckinBtn" class="button" style="background:#22c55e; display:none;">✅ Xác nhận vào rạp</button>
                    <button id="clearResultBtn" class="button" style="background:transparent; color:#94a3b8;">🔄 Kiểm tra vé khác</button>
                </div>
            </div>
        </article>

        <!-- History/Log Section (Optional) -->
        <article class="card" style="margin-top:24px;">
            <h2>Lịch Sử Soát Vé Gần Đây</h2>
            <div style="overflow-x:auto;">
                <table id="historyTable" style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="border-bottom:1px solid #334155; text-align:left;">
                            <th style="padding:10px;">Thời gian</th>
                            <th style="padding:10px;">Mã vé</th>
                            <th style="padding:10px;">Phim</th>
                            <th style="padding:10px;">Kết quả</th>
                            <th style="padding:10px;">Nhân viên</th>
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
    <footer>© 2026 CineStar Staff</footer>
    <script src="../../js/api.js"></script>
    <script src="../../js/main.js"></script>
    <script src="../../js/staff.js"></script>
</body>
</html>
