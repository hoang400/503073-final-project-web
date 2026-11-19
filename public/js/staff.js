// Staff Check-in Functions
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
    valid: '✅ Hợp lệ - Cho vào rạp',
    pending: '⏳ Chưa thanh toán',
    used: '❌ Đã sử dụng',
    cancelled: '🚫 Đã hủy',
    early: '⏰ Chưa đến giờ chiếu',
    invalid: '❌ Mã vé không tồn tại',
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
    
    // Clear result
    document.getElementById('clearResultBtn')?.addEventListener('click', clearResult);
    
    // Confirm checkin
    document.getElementById('confirmCheckinBtn')?.addEventListener('click', confirmCheckin);
    
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
        message('Vui lòng nhập mã vé / QR code!', true);
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
        
        // Update status badge
        const statusBadge = document.getElementById('resultStatus');
        statusBadge.textContent = statusLabels[result.status] || result.status;
        statusBadge.style.cssText = statusStyles[result.status] + ' padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:bold;';
        
        // Update title based on status
        const title = document.getElementById('resultTitle');
        if (result.status === 'valid') {
            title.textContent = '✅ Vé HỢP LỆ - Cho phép vào rạp';
        } else if (result.status === 'used') {
            title.textContent = '❌ Vé ĐÃ ĐƯỢC SỬ DỤNG';
        } else if (result.status === 'pending') {
            title.textContent = '⏳ Vé CHƯA THANH TOÁN';
        } else if (result.status === 'cancelled') {
            title.textContent = '🚫 Vé ĐÃ BỊ HỦY';
        } else if (result.status === 'early') {
            title.textContent = '⏰ CHƯA ĐẾN GIỜ CHIẾU';
        } else {
            title.textContent = '❌ MÃ VÉ KHÔNG TỒN TẠI';
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
        btn.textContent = '🔍 Kiểm tra';
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
        'DaThanhToan': '✅ Đã thanh toán',
        'ChuaThanhToan': '⏳ Chưa thanh toán',
        'DaHuy': '🚫 Đã hủy',
    }[data.trang_thai_thanh_toan] || data.trang_thai_thanh_toan;
}

function clearResult() {
    document.getElementById('resultSection').style.display = 'none';
    document.getElementById('qrInput').value = '';
    document.getElementById('qrInput').focus();
}

// Confirm check-in
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
        
        message('✅ Xác nhận vào rạp thành công!');
        
        // Update UI to show used status
        document.getElementById('resultStatus').textContent = statusLabels.used;
        document.getElementById('resultStatus').style.cssText = statusStyles.used + ' padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:bold;';
        document.getElementById('resultTitle').textContent = '❌ Vé ĐÃ ĐƯỢC SỬ DỤNG';
        document.getElementById('confirmCheckinBtn').style.display = 'none';
        document.getElementById('resPaymentStatus').textContent = '✅ Đã thanh toán (Đã check-in)';
        
        // Reload history
        loadCheckinHistory();
        
    } catch (e) {
        message(e.message, true);
    } finally {
        btn.disabled = false;
        btn.textContent = '✅ Xác nhận vào rạp';
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
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:10px;">${h.time}</td>
            <td style="padding:10px; font-family:monospace; font-size:12px;">${h.qr}</td>
            <td style="padding:10px;">${h.movie}</td>
            <td style="padding:10px;">${h.statusLabel}</td>
            <td style="padding:10px;">${h.staff}</td>
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
