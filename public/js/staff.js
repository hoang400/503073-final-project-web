// Staff Check-in Functions
// Soát vé bằng mã nhập tay (mã đơn hàng hoặc mã vé) — không quét QR.
let checkinHistory = [];

// Status badge styles
const statusStyles = {
    valid: 'background:#22c55e; color:#052e16;',
    pending: 'background:#f59e0b; color:#451a03;',
    used: 'background:#ef4444; color:#450a0a;',
    cancelled: 'background:#6b7280; color:#1f2937;',
    early: 'background:#3b82f6; color:#1e3a5f;',
    invalid: 'background:#ef4444; color:#450a0a;',
};

const statusLabels = {
    valid: 'Hợp lệ',
    pending: 'Chưa thanh toán',
    used: 'Đã sử dụng',
    cancelled: 'Đã hủy',
    early: 'Chưa đến giờ chiếu',
    invalid: 'Mã vé không tồn tại',
    order: 'Đơn hàng',
};

document.addEventListener('DOMContentLoaded', () => {
    // Staff info
    updateStaffInfo();

    // Verify button
    document.getElementById('verifyBtn')?.addEventListener('click', verifyTicket);

    // Enter key on input
    document.getElementById('qrInput')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') verifyTicket();
    });

    // Clear result (nút này có ở cả 2 dạng kết quả: 1 vé và đơn hàng)
    document.querySelectorAll('.clear-result-btn').forEach(btn => {
        btn.addEventListener('click', clearResult);
    });

    // Confirm checkin (1 vé)
    document.getElementById('confirmCheckinBtn')?.addEventListener('click', confirmCheckin);

    // Confirm từng vé trong đơn hàng (ủy quyền click trên bảng vé)
    document.getElementById('orderTicketsBody')?.addEventListener('click', (e) => {
        const btn = e.target.closest('.order-confirm-btn');
        if (!btn) return;
        confirmOrderTicket(btn);
    });

    // Load history
    loadCheckinHistory();
});

// Update staff name in header
async function updateStaffInfo() {
    try {
        const user = await api('/auth/me');
        if (user) {
            document.getElementById('staffInfo').textContent = `Nhân viên: ${user.name} (${user.role})`;
        }
    } catch (e) {
        // Not logged in or error
    }
}

// Verify ticket
async function verifyTicket() {
    const qrCode = document.getElementById('qrInput')?.value?.trim();
    if (!qrCode) {
        message('Vui lòng nhập mã vé!', true);
        return;
    }

    const btn = document.getElementById('verifyBtn');
    btn.disabled = true;
    btn.textContent = 'Đang kiểm tra...';

    try {
        const result = await api('/staff/check-in', 'POST', { qr_code: qrCode });

        // Show result section
        document.getElementById('resultSection').style.display = 'block';
        document.getElementById('qrInput').value = '';

        // Nhập mã đơn hàng -> trả về danh sách vé của đơn để soát từng vé
        if (result.status === 'order') {
            renderOrderResult(result);
            document.getElementById('resultSection').scrollIntoView({ behavior: 'smooth' });
            return;
        }

        document.getElementById('singleResult').style.display = 'block';
        document.getElementById('orderResult').style.display = 'none';

        // Update status badge
        const statusBadge = document.getElementById('resultStatus');
        statusBadge.textContent = statusLabels[result.status] || result.status;
        statusBadge.style.cssText = statusStyles[result.status] + ' padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:bold;';

        // Update title based on status
        const title = document.getElementById('resultTitle');
        if (result.status === 'valid') {
            title.textContent = 'Vé HỢP LỆ';
        } else if (result.status === 'used') {
            title.textContent = 'Vé ĐÃ ĐƯỢC SỬ DỤNG';
        } else if (result.status === 'pending') {
            title.textContent = 'Vé CHƯA THANH TOÁN';
        } else if (result.status === 'cancelled') {
            title.textContent = 'Vé ĐÃ BỊ HỦY';
        } else if (result.status === 'early') {
            title.textContent = 'CHƯA ĐẾN GIỜ CHIẾU';
        } else {
            title.textContent = 'MÃ VÉ KHÔNG TỒN TẠI';
        }

        // Fill ticket info
        const data = result.data || {};
        fillTicketInfo(data);

        // Show/hide confirm button
        const confirmBtn = document.getElementById('confirmCheckinBtn');
        if (result.status === 'valid') {
            confirmBtn.style.display = 'inline-flex';
            confirmBtn.dataset.qr = qrCode;
        } else {
            confirmBtn.style.display = 'none';
        }

        // Scroll to result
        document.getElementById('resultSection').scrollIntoView({ behavior: 'smooth' });

        message(`Kết quả: ${statusLabels[result.status]}`);

    } catch (e) {
        message(e.message, true);
    } finally {
        const btn = document.getElementById('verifyBtn');
        btn.disabled = false;
        btn.textContent = 'Kiểm tra';
    }
}

// Mã đơn hàng -> hiện danh sách vé của đơn để soát từng vé
function renderOrderResult(result) {
    const tickets = result.tickets || [];

    document.getElementById('singleResult').style.display = 'none';
    document.getElementById('orderResult').style.display = 'block';
    document.getElementById('resultTitle').textContent =
        `Đơn hàng ${result.order_code} (${tickets.length} vé)`;
    document.getElementById('resultStatus').style.display = 'none';

    const tbody = document.getElementById('orderTicketsBody');
    tbody.innerHTML = tickets.map(t => `
        <tr data-qr="${esc(t.qr_code)}">
            <td><strong>${esc(t.seat)} (${esc(t.seat_type)})</strong></td>
            <td>${esc(t.movie)}</td>
            <td>${new Date(t.showtime).toLocaleString('vi-VN')}</td>
            <td><span class="order-ticket-status" style="${statusStyles[t.status] || ''} padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:bold;">${statusLabels[t.status] || t.status}</span></td>
            <td>${t.status === 'valid'
                ? '<button type="button" class="button btn-confirm order-confirm-btn">Xác nhận vào rạp</button>'
                : ''}</td>
        </tr>
    `).join('');

    message(`Tìm thấy đơn hàng ${result.order_code} (${tickets.length} vé).`);
}

// Xác nhận 1 vé trong danh sách vé của đơn hàng
async function confirmOrderTicket(btn) {
    const row = btn.closest('tr');
    const qrCode = row?.dataset?.qr;
    if (!qrCode) return;

    if (!confirm('Xác nhận khách đã vào rạp? Vé sẽ được đánh dấu "Đã sử dụng" và không thể dùng lại.')) {
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Đang xác nhận...';

    try {
        await api('/staff/check-in/confirm', 'POST', { qr_code: qrCode });

        const statusCell = row.querySelector('.order-ticket-status');
        statusCell.textContent = statusLabels.used;
        statusCell.style.cssText = statusStyles.used + ' padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:bold;';
        btn.remove();

        const movie = row.children[1]?.textContent || '';
        const user = await api('/auth/me');
        saveToHistory(qrCode, movie, 'used', user?.name || 'Staff');
        loadCheckinHistory();

        message('Xác nhận vào rạp thành công!');
    } catch (e) {
        message(e.message, true);
        btn.disabled = false;
        btn.textContent = 'Xác nhận vào rạp';
    }
}

function fillTicketInfo(data) {
    const safe = (val) => val || '—';
    const formatDate = (str) => str ? new Date(str).toLocaleString('vi-VN') : '—';

    document.getElementById('resQrCode').textContent = safe(data.ma_qr_code);
    document.getElementById('resOrderCode').textContent = safe(data.ma_don_hang);
    document.getElementById('resMovie').textContent = safe(data.ten_phim);
    document.getElementById('resCinemaRoom').textContent = `${safe(data.ten_rap)} / ${safe(data.ten_phong)}`;
    document.getElementById('resSeat').textContent = safe(data.hang_ghe) + safe(data.so_thu_tu) + ` (${safe(data.loai_ghe)})`;
    document.getElementById('resShowtime').textContent = formatDate(data.thoi_gian_bat_dau) + ' - ' + formatDate(data.thoi_gian_ket_thuc);
    document.getElementById('resCustomer').textContent = safe(data.khach_hang);
    document.getElementById('resPaymentStatus').textContent = {
        'DaThanhToan': 'Đã thanh toán',
        'ChuaThanhToan': 'Chưa thanh toán',
        'DaHuy': 'Đã hủy',
    }[data.trang_thai_thanh_toan] || data.trang_thai_thanh_toan;
}

function clearResult() {
    document.getElementById('resultSection').style.display = 'none';
    document.getElementById('singleResult').style.display = 'block';
    document.getElementById('orderResult').style.display = 'none';
    document.getElementById('qrInput').value = '';
    document.getElementById('qrInput').focus();
}

// Confirm checkin
async function confirmCheckin() {
    const qrCode = document.getElementById('confirmCheckinBtn')?.dataset?.qr;
    if (!qrCode) return;

    if (!confirm('Xác nhận khách đã vào rạp? Vé sẽ được đánh dấu "Đã sử dụng" và không thể dùng lại.')) {
        return;
    }

    const btn = document.getElementById('confirmCheckinBtn');
    btn.disabled = true;
    btn.textContent = 'Đang xác nhận...';

    try {
        const result = await api('/staff/check-in/confirm', 'POST', { qr_code: qrCode });

        message('Xác nhận vào rạp thành công!');

        // Update UI to show used status
        document.getElementById('resultStatus').textContent = statusLabels.used;
        document.getElementById('resultStatus').style.cssText = statusStyles.used + ' padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:bold;';
        document.getElementById('resultTitle').textContent = 'Vé ĐÃ ĐƯỢC SỬ DỤNG';
        document.getElementById('confirmCheckinBtn').style.display = 'none';
        document.getElementById('resPaymentStatus').textContent = 'Đã thanh toán (Đã check-in)';

        // Reload history
        loadCheckinHistory();

    } catch (e) {
        message(e.message, true);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Xác nhận vào rạp';
    }
}

// Load check-in history
async function loadCheckinHistory() {
    try {
        const history = JSON.parse(localStorage.getItem('checkin_history') || '[]');
        renderHistory(history);
    } catch (e) {
        console.error(e);
    }
}

function renderHistory(history) {
    const tbody = document.getElementById('historyBody');
    const empty = document.getElementById('historyEmpty');
    const table = document.getElementById('historyTable');

    if (!history || history.length === 0) {
        table.style.display = 'none';
        empty.style.display = 'block';
        return;
    }

    table.style.display = 'table';
    empty.style.display = 'none';

    tbody.innerHTML = history.slice(0, 50).map(h => `
        <tr>
            <td>${h.time}</td>
            <td class="checkin-history-code">${h.qr}</td>
            <td>${h.movie}</td>
            <td>${h.statusLabel}</td>
            <td>${h.staff}</td>
        </tr>
    `).join('');
}

// Save to history (called after confirm)
function saveToHistory(qr, movie, status, staffName) {
    const history = JSON.parse(localStorage.getItem('checkin_history') || '[]');
    history.unshift({
        time: new Date().toLocaleString('vi-VN'),
        qr: qr.substring(0, 20) + '...',
        movie,
        status,
        statusLabel: statusLabels[status] || status,
        staff: staffName
    });
    // Keep only last 100
    if (history.length > 100) history.pop();
    localStorage.setItem('checkin_history', JSON.stringify(history));
}

// Patch confirmCheckin to save history
const originalConfirmCheckin = confirmCheckin;
confirmCheckin = async function() {
    const qrCode = document.getElementById('confirmCheckinBtn')?.dataset?.qr;
    const movie = document.getElementById('resMovie')?.textContent || '';

    try {
        await originalConfirmCheckin();
        // Save to history after success
        const user = await api('/auth/me');
        saveToHistory(qrCode, movie, 'used', user?.name || 'Staff');
        loadCheckinHistory();
    } catch (e) {
        // Error handled in original
    }
};
