document.addEventListener('DOMContentLoaded', async () => {
    if (!await requireUser()) {
        return;
    }

    const ordersContainer = $('#orders');
    const detailContainer = $('#detail');

    if (ordersContainer) {
        await loadHistory(ordersContainer);
    } else if (detailContainer) {
        await loadOrderDetail(detailContainer);
    }
});

function qrImageUrl(code, size = 160) {
    return 'https://api.qrserver.com/v1/create-qr-code/?size='
        + size + 'x' + size + '&data=' + encodeURIComponent(code);
}

async function cancelOrder(orderId, button) {
    if (!confirm('Bạn có chắc muốn hủy đơn này?')) {
        return;
    }

    if (button) button.disabled = true;

    try {
        await api('/order/cancel', 'POST', { order_id: Number(orderId) });
        message('Đã hủy đơn vé.');

        const ordersContainer = $('#orders');
        if (ordersContainer) {
            await loadHistory(ordersContainer);
            return;
        }

        const detailContainer = $('#detail');
        if (detailContainer) {
            await loadOrderDetail(detailContainer);
        }
    } catch (error) {
        message(error.message, true);
        if (button) button.disabled = false;
    }
}

async function loadHistory(container) {
    const search = $('#order-search');
    container.textContent = 'Đang tải lịch sử đơn vé…';

    try {
        const orders = await api('/orders');

        function render() {
            const keyword = search.value.trim().toLocaleLowerCase('vi-VN');

            const filtered = orders.filter(order =>
                String(order.code).toLocaleLowerCase('vi-VN').includes(keyword)
                || String(order.movie).toLocaleLowerCase('vi-VN').includes(keyword)
            );

            container.innerHTML = filtered.length
                ? filtered.map(order => `
                    <article class="order-card">
                        <div class="order-main">
                            <p class="order-meta">
                                <span class="order-code">#${esc(order.code)}</span>
                                <span class="order-time">${esc(formatDateTime(order.createdAt))}</span>
                                ${order.status === 'DaHuy' ? '<span class="order-cancelled">Đã hủy</span>' : ''}
                            </p>
                            <h2 class="order-title">${esc(order.movie)}</h2>
                            <p class="order-line">
                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                ${esc(order.cinema || '—')} | ${esc(formatShowtime(order.showtime))}
                            </p>
                            ${order.seats ? `
                                <p class="order-line">
                                    <i class="fa-solid fa-ticket" aria-hidden="true"></i>
                                    Ghế: ${esc(order.seats)}
                                </p>
                            ` : ''}
                        </div>
                        <div class="order-side">
                            <p class="order-total-label">TỔNG TIỀN ĐÃ TRẢ</p>
                            <p class="order-total">${money(order.total)}</p>
                            <div class="order-actions">
                                <a class="button" href="order-detail.php?id=${encodeURIComponent(order.id)}">
                                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i> Xem QR Vé
                                </a>
                                ${order.status !== 'DaHuy' ? `
                                    <button type="button" class="button danger-button" data-cancel-order="${order.id}">
                                        Hủy Vé
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </article>
                `).join('')
                : '<p>Không tìm thấy đơn vé phù hợp.</p>';

            container.querySelectorAll('[data-cancel-order]').forEach(button => {
                button.addEventListener('click', () => {
                    cancelOrder(button.dataset.cancelOrder, button);
                });
            });
        }

        search.addEventListener('input', render);
        render();
    } catch (error) {
        container.textContent = 'Không tải được lịch sử đơn vé.';
        message(error.message, true);
    }
}

async function loadOrderDetail(container) {
    const orderId = new URLSearchParams(location.search).get('id');

    if (!orderId || !/^\d+$/.test(orderId)) {
        container.textContent = 'Mã đơn hàng không hợp lệ.';
        return;
    }

    container.textContent = 'Đang tải thông tin đơn…';

    try {
        const order = await api(
            `/order?order_id=${encodeURIComponent(orderId)}`
        );

        const tickets = order.tickets || [];
        const concessions = order.concessions || [];

        container.innerHTML = `
            <h2>${esc(order.ten_phim)}</h2>
            <p>Mã đơn: ${esc(order.ma_don_hang)}</p>
            <p>Rạp: ${esc(order.ten_rap || '—')}${order.ten_phong ? ' · ' + esc(order.ten_phong) : ''}</p>
            ${order.dia_chi ? `<p>Địa chỉ: ${esc(order.dia_chi)}</p>` : ''}
            <p>Suất chiếu: ${esc(formatDate(order.thoi_gian_bat_dau))}</p>
            <p>Ngày đặt: ${esc(formatDate(order.thoi_gian_dat))}</p>
            <p>Trạng thái: ${esc(statusText(order.trang_thai_thanh_toan))}</p>
            <p>Phương thức: ${esc(order.phuong_thuc_thanh_toan)}</p>

            <h3>Vé</h3>
            ${tickets.length
                ? `<ul class="ticket-list">${tickets.map(ticket => `
                    <li class="ticket-item">
                        <div>
                            Ghế ${esc(ticket.hang_ghe)}${esc(ticket.so_thu_tu)}
                            (${esc(ticket.loai_ghe)}) · ${money(ticket.gia_ve)}
                            <br>
                            Mã vé: <code>${esc(ticket.ma_qr_code || 'Chưa có mã')}</code>
                            ${ticket.thoi_gian_su_dung
                                ? `<br>Đã sử dụng: ${esc(formatDate(ticket.thoi_gian_su_dung))}`
                                : ''}
                        </div>
                        ${ticket.ma_qr_code ? `
                            <img class="ticket-qr" src="${qrImageUrl(ticket.ma_qr_code)}"
                                alt="Mã QR vé ghế ${esc(ticket.hang_ghe)}${esc(ticket.so_thu_tu)}"
                                width="160" height="160" loading="lazy">
                        ` : ''}
                    </li>
                `).join('')}</ul>`
                : '<p>Đơn chưa có vé.</p>'}

            <h3>Bắp nước</h3>
            ${concessions.length
                ? `<ul>${concessions.map(item => `
                    <li>
                        ${esc(item.ten_mon)} · SL ${esc(item.so_luong)}
                        · ${money(Number(item.gia_tien) * Number(item.so_luong))}
                    </li>
                `).join('')}</ul>`
                : '<p>Đơn không có bắp nước.</p>'}

            <p><strong>Tổng tiền: ${money(order.tong_tien)}</strong></p>

            ${order.trang_thai_thanh_toan !== 'DaHuy' ? `
                <button type="button" class="button danger-button" data-cancel-order="${order.id}">
                    Hủy vé
                </button>
            ` : ''}
        `;

        container.querySelectorAll('[data-cancel-order]').forEach(button => {
            button.addEventListener('click', () => {
                cancelOrder(button.dataset.cancelOrder, button);
            });
        });
    } catch (error) {
        container.textContent = 'Không tải được chi tiết đơn vé.';
        message(error.message, true);
    }
}

function parseDate(value) {
    if (!value) {
        return null;
    }

    const date = new Date(String(value).replace(' ', 'T'));

    return Number.isNaN(date.getTime()) ? null : date;
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    const date = new Date(String(value).replace(' ', 'T'));

    return Number.isNaN(date.getTime())
        ? String(value)
        : date.toLocaleString('vi-VN');
}

// 12:37:13 18/11/2026 — dùng cho mã đơn ở danh sách lịch sử.
function formatDateTime(value) {
    const date = parseDate(value);

    if (!date) {
        return String(value || '');
    }

    const pad = number => String(number).padStart(2, '0');

    return `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`
        + ` ${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`;
}

// 19:30 - 18/11/2026 — dùng cho dòng rạp + suất chiếu ở danh sách lịch sử.
function formatShowtime(value) {
    const date = parseDate(value);

    if (!date) {
        return String(value || '');
    }

    const pad = number => String(number).padStart(2, '0');

    return `${pad(date.getHours())}:${pad(date.getMinutes())}`
        + ` - ${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`;
}

function statusText(status) {
    return ({
        DaThanhToan: 'Đã thanh toán',
        ChuaThanhToan: 'Chưa thanh toán',
        DaHuy: 'Đã hủy',
    })[status] || status;
}
