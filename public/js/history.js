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
                    <article class="card order">
                        <h2>${esc(order.movie)}</h2>
                        <p>Mã đơn: ${esc(order.code)}</p>
                        <p>Suất chiếu: ${esc(formatDate(order.showtime))}</p>
                        <p>Trạng thái: ${esc(statusText(order.status))}</p>
                        <p>Tổng tiền: ${money(order.total)}</p>
                        <a class="button" href="order-detail.php?id=${encodeURIComponent(order.id)}">
                            Xem chi tiết
                        </a>
                    </article>
                `).join('')
                : '<p>Không tìm thấy đơn vé phù hợp.</p>';
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
            <p>Suất chiếu: ${esc(formatDate(order.thoi_gian_bat_dau))}</p>
            <p>Trạng thái: ${esc(statusText(order.trang_thai_thanh_toan))}</p>
            <p>Phương thức: ${esc(order.phuong_thuc_thanh_toan)}</p>

            <h3>Vé</h3>
            ${tickets.length
                ? `<ul>${tickets.map(ticket => `
                    <li>
                        Ghế ${esc(ticket.hang_ghe)}${esc(ticket.so_thu_tu)}
                        (${esc(ticket.loai_ghe)}) · ${money(ticket.gia_ve)}
                        <br>
                        Mã vé: <code>${esc(ticket.ma_qr_code || 'Chưa có mã')}</code>
                        ${ticket.thoi_gian_su_dung
                            ? `<br>Đã sử dụng: ${esc(formatDate(ticket.thoi_gian_su_dung))}`
                            : ''}
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
        `;
    } catch (error) {
        container.textContent = 'Không tải được chi tiết đơn vé.';
        message(error.message, true);
    }
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

function statusText(status) {
    return ({
        DaThanhToan: 'Đã thanh toán',
        ChuaThanhToan: 'Chưa thanh toán',
        DaHuy: 'Đã hủy',
    })[status] || status;
}