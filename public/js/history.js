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
                            <h2 class="order-title">
                                <a class="order-title-link" href="order-detail.php?id=${encodeURIComponent(order.id)}">
                                    ${esc(order.movie)}
                                </a>
                            </h2>
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
                                <button type="button" class="button" data-qr-order="${order.id}" data-qr-code="${esc(order.code)}">
                                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i> Xem QR Vé
                                </button>
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

            container.querySelectorAll('[data-qr-order]').forEach(button => {
                button.addEventListener('click', () => {
                    openQrModal(button.dataset.qrOrder, button.dataset.qrCode);
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

// Modal mã QR vào rạp (Hình 6.1.3.6 trong báo cáo).
function openQrModal(orderId, orderCode) {
    const modal = $('#qr-modal');
    const body = $('#qr-modal-body');

    if (!modal || !body) {
        location.href = `order-detail.php?id=${encodeURIComponent(orderId)}`;
        return;
    }

    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    body.innerHTML = 'Đang tải mã vé…';

    api(`/order?order_id=${encodeURIComponent(orderId)}`).then(order => {
        const tickets = (order.tickets || []).filter(ticket => ticket.ma_qr_code);

        if (!tickets.length) {
            body.innerHTML = '<p>Đơn này chưa có mã vé.</p>';
            return;
        }

        const single = tickets.length === 1;

        body.innerHTML = `
            <div class="qr-modal-tickets">
                ${tickets.map(ticket => `
                    <figure class="qr-modal-ticket">
                        <span class="qr-modal-qr">
                            <img src="${qrImageUrl(ticket.ma_qr_code, single ? 200 : 140)}"
                                alt="Mã QR vé ghế ${esc(ticket.hang_ghe)}${esc(ticket.so_thu_tu)}"
                                width="${single ? 200 : 140}" height="${single ? 200 : 140}">
                        </span>
                        <figcaption>Ghế ${esc(ticket.hang_ghe)}${esc(ticket.so_thu_tu)}</figcaption>
                    </figure>
                `).join('')}
            </div>
            <p class="qr-modal-code">${esc(orderCode || order.ma_don_hang || '')}</p>
            <p class="qr-modal-note">Vui lòng xuất trình mã QR này tại quầy soát vé CineStar</p>
            <a class="qr-modal-detail" href="order-detail.php?id=${encodeURIComponent(orderId)}">
                Xem chi tiết đơn →
            </a>
        `;
    }).catch(error => {
        body.innerHTML = `<p>Không tải được mã vé: ${esc(error.message)}</p>`;
    });
}

function closeQrModal() {
    const modal = $('#qr-modal');

    if (!modal) {
        return;
    }

    modal.hidden = true;
    document.body.style.overflow = '';
}

document.addEventListener('click', event => {
    if (event.target.closest('[data-close-qr]')) {
        closeQrModal();
    }
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        closeQrModal();
    }
});

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
            <div class="detail-head">
                <div>
                    <p class="order-meta">
                        <span class="order-code">#${esc(order.ma_don_hang)}</span>
                        <span class="order-time">${esc(formatDateTime(order.thoi_gian_dat))}</span>
                        ${order.trang_thai_thanh_toan === 'DaHuy' ? '<span class="order-cancelled">Đã hủy</span>' : ''}
                    </p>
                    <h2 class="order-title">${esc(order.ten_phim)}</h2>
                    <p class="order-line">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        ${esc(order.ten_rap || '—')} | ${esc(formatShowtime(order.thoi_gian_bat_dau))}
                    </p>
                    ${order.dia_chi ? `<p class="order-line order-line-muted">Địa chỉ: ${esc(order.dia_chi)}</p>` : ''}
                    <p class="order-line order-line-muted">
                        ${order.ten_phong ? `${esc(order.ten_phong)} · ` : ''}Thanh toán: ${esc(order.phuong_thuc_thanh_toan)}
                        · ${esc(statusText(order.trang_thai_thanh_toan))}
                    </p>
                </div>
                <div class="order-side">
                    <p class="order-total-label">TỔNG TIỀN ĐÃ TRẢ</p>
                    <p class="order-total">${money(order.tong_tien)}</p>
                </div>
            </div>

            <h3 class="detail-section-title">Vé</h3>
            ${tickets.length
                ? `<ul class="ticket-list">${tickets.map(ticket => `
                    <li class="ticket-item">
                        <div class="ticket-info">
                            <span class="ticket-seat">
                                Ghế ${esc(ticket.hang_ghe)}${esc(ticket.so_thu_tu)}
                                <span class="ticket-type">${esc(ticket.loai_ghe)}</span>
                            </span>
                            <span class="ticket-price">${money(ticket.gia_ve)}</span>
                            <code class="ticket-code" title="${esc(ticket.ma_qr_code || '')}">
                                Mã vé: ${esc(ticket.ma_qr_code ? String(ticket.ma_qr_code).slice(0, 12) + '…' : 'Chưa có mã')}
                            </code>
                            ${ticket.thoi_gian_su_dung
                                ? `<span class="ticket-used">Đã sử dụng: ${esc(formatDate(ticket.thoi_gian_su_dung))}</span>`
                                : ''}
                        </div>
                        ${ticket.ma_qr_code ? `
                            <img class="ticket-qr" src="${qrImageUrl(ticket.ma_qr_code, 140)}"
                                alt="Mã QR vé ghế ${esc(ticket.hang_ghe)}${esc(ticket.so_thu_tu)}"
                                width="140" height="140" loading="lazy">
                        ` : ''}
                    </li>
                `).join('')}</ul>`
                : '<p>Đơn chưa có vé.</p>'}

            <h3 class="detail-section-title">Bắp nước</h3>
            ${concessions.length
                ? `<ul class="concession-list">${concessions.map(item => `
                    <li>
                        ${esc(item.ten_mon)} · SL ${esc(item.so_luong)}
                        · ${money(Number(item.gia_tien) * Number(item.so_luong))}
                    </li>
                `).join('')}</ul>`
                : '<p>Đơn không có bắp nước.</p>'}

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
