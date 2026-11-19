
document.addEventListener('DOMContentLoaded', async () => {
    const movieSelect = $('#movie');
    const showtimeSelect = $('#showtime');
    const seatsContainer = $('#seats');
    const fnbContainer = $('#fnb');
    const bill = $('#bill');
    const checkoutButton = $('#checkout');

    let selectedSeats = [];
    let selectedSeatDetails = [];
    let showtimes = [];
    let concessions = [];
    let basePrice = 0;
    let user;

    const seatMultipliers = {
        Thuong: 1,
        VIP: 1.2,
        Couple: 1.5,
    };

    function seatPrice(seat) {
        const multiplier = seatMultipliers[seat.type] || 1;
        return basePrice * multiplier;
    }

    function getSelectedSeatTotal() {
        return selectedSeatDetails.reduce(
            (total, seat) => total + seatPrice(seat),
            0
        );
    }

    function getFoodTotal() {
        return concessions.reduce((total, item) => {
            const quantity = Number(
                fnbContainer.querySelector(
                    `[data-concession="${item.id}"]`
                )?.value || 0
            );

            return total + item.price * quantity;
        }, 0);
    }

    function updateBill() {
        const total = getSelectedSeatTotal() + getFoodTotal();

        bill.textContent = money(total);

        checkoutButton.disabled =
            selectedSeats.length === 0 || !showtimeSelect.value;
    }

    function resetSelectedSeats() {
        selectedSeats = [];
        selectedSeatDetails = [];
    }

    function getSeatClass(type) {
        if (type === 'VIP') {
            return 'vip';
        }

        if (type === 'Couple') {
            return 'couple';
        }

        return 'standard';
    }

    function renderSeats(seats) {
        resetSelectedSeats();

        if (!Array.isArray(seats) || seats.length === 0) {
            seatsContainer.innerHTML = `
                <p class="booking-placeholder">
                    Suất chiếu chưa có sơ đồ ghế.
                </p>
            `;
            updateBill();
            return;
        }

        const groupedSeats = {};

        seats.forEach(seat => {
            const rowName = String(seat.row || '');

            if (!groupedSeats[rowName]) {
                groupedSeats[rowName] = [];
            }

            groupedSeats[rowName].push(seat);
        });

        const rowNames = Object.keys(groupedSeats).sort((a, b) =>
            a.localeCompare(b, 'vi', { numeric: true })
        );

        seatsContainer.innerHTML = rowNames.map(rowName => {
            const rowSeats = groupedSeats[rowName].sort(
                (a, b) => Number(a.number) - Number(b.number)
            );

            return `
                <div class="seat-row">
                    <span class="seat-row-label">${esc(rowName)}</span>

                    <div class="seat-row-list">
                        ${rowSeats.map(seat => {
                            const status = seat.status;
                            const unavailable = [
                                'BOOKED',
                                'HELD',
                            ].includes(status);

                            const seatType = getSeatClass(seat.type);
                            const isHeldByMe = status === 'HELD_BY_ME';

                            return `
                                <button
                                    type="button"
                                    class="seat ${seatType} ${unavailable ? 'taken' : ''}"
                                    data-seat-id="${seat.id}"
                                    ${unavailable ? 'disabled' : ''}
                                    title="Ghế ${esc(rowName)}${seat.number} · ${esc(seat.type)}"
                                    aria-label="Ghế ${esc(rowName)}${seat.number}, ${esc(seat.type)}"
                                    ${isHeldByMe ? 'data-held-by-me="true"' : ''}
                                >
                                    ${esc(rowName)}${seat.number}
                                </button>
                            `;
                        }).join('')}
                    </div>

                    <span class="seat-row-label">${esc(rowName)}</span>
                </div>
            `;
        }).join('');

        seatsContainer.querySelectorAll('[data-seat-id]').forEach(button => {
            button.addEventListener('click', () => {
                const seatId = Number(button.dataset.seatId);
                const seat = seats.find(
                    item => Number(item.id) === seatId
                );

                if (!seat) {
                    return;
                }

                const selected = selectedSeats.includes(seatId);

                if (!selected && selectedSeats.length >= 8) {
                    message('Bạn chỉ được chọn tối đa 8 ghế.', true);
                    return;
                }

                if (selected) {
                    selectedSeats = selectedSeats.filter(
                        id => id !== seatId
                    );

                    selectedSeatDetails = selectedSeatDetails.filter(
                        item => Number(item.id) !== seatId
                    );

                    button.classList.remove('selected');
                } else {
                    selectedSeats.push(seatId);
                    selectedSeatDetails.push(seat);
                    button.classList.add('selected');
                }

                updateBill();
            });
        });

        updateBill();
    }

    async function loadShowtimes() {
        resetSelectedSeats();

        seatsContainer.innerHTML = `
            <p class="booking-placeholder">
                Chọn suất chiếu để xem sơ đồ ghế.
            </p>
        `;

        showtimeSelect.disabled = true;
        showtimeSelect.innerHTML =
            '<option value="">Đang tải suất chiếu...</option>';

        checkoutButton.disabled = true;

        if (!movieSelect.value) {
            showtimes = [];
            showtimeSelect.innerHTML =
                '<option value="">Chọn phim trước</option>';
            updateBill();
            return;
        }

        try {
            showtimes = await api(
                `/showtimes?movie_id=${encodeURIComponent(movieSelect.value)}`
            );

            showtimeSelect.innerHTML = showtimes.length
                ? `
                    <option value="">Chọn suất chiếu</option>
                    ${showtimes.map(showtime => `
                        <option value="${showtime.id}">
                            ${new Date(showtime.startsAt).toLocaleString('vi-VN')}
                            · ${money(showtime.basePrice)}
                        </option>
                    `).join('')}
                `
                : '<option value="">Chưa có suất chiếu</option>';

            showtimeSelect.disabled = showtimes.length === 0;
        } catch (error) {
            showtimes = [];
            showtimeSelect.innerHTML =
                '<option value="">Lỗi tải suất chiếu</option>';

            message(error.message, true);
        }

        updateBill();
    }

    async function loadSeats() {
        resetSelectedSeats();

        seatsContainer.innerHTML = `
            <p class="booking-placeholder">
                Đang tải sơ đồ ghế...
            </p>
        `;

        checkoutButton.disabled = true;

        const showtime = showtimes.find(
            item => String(item.id) === showtimeSelect.value
        );

        if (!showtime) {
            seatsContainer.innerHTML = `
                <p class="booking-placeholder">
                    Chọn suất chiếu để xem sơ đồ ghế.
                </p>
            `;

            updateBill();
            return;
        }

        basePrice = Number(showtime.basePrice);

        try {
            const seats = await api(
                `/seats?showtime_id=${encodeURIComponent(showtime.id)}`
            );

            renderSeats(seats);
        } catch (error) {
            seatsContainer.innerHTML = `
                <p class="booking-placeholder">
                    Không tải được sơ đồ ghế.
                </p>
            `;

            message(error.message, true);
            updateBill();
        }
    }

    function renderConcessions() {
        fnbContainer.innerHTML = concessions.length
            ? concessions.map(item => `
                <label class="fnb-item">
                    <span class="fnb-item-info">
                        <strong>${esc(item.name)}</strong>
                        <small>${money(item.price)}</small>
                    </span>

                    <input
                        type="number"
                        min="0"
                        max="20"
                        step="1"
                        value="0"
                        data-concession="${item.id}"
                        aria-label="Số lượng ${esc(item.name)}"
                    >
                </label>
            `).join('')
            : '<p class="booking-placeholder">Hiện chưa có bắp nước.</p>';

        fnbContainer.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => {
                let quantity = Number(input.value);

                if (!Number.isFinite(quantity) || quantity < 0) {
                    quantity = 0;
                }

                if (quantity > 20) {
                    quantity = 20;
                }

                input.value = String(Math.floor(quantity));
                updateBill();
            });
        });
    }

    try {
        user = await api('/auth/me');
    } catch (error) {
        message(error.message, true);
        checkoutButton.disabled = true;
        return;
    }

    if (!user) {
        const next = location.pathname.split('/').pop()
            + location.search;

        location.href = 'login.php?next=' + encodeURIComponent(next);
        return;
    }

    try {
        const movies = await api('/movies');

        const availableMovies = movies.filter(
            movie => movie.status === 'DANG_CHIEU'
        );

        movieSelect.innerHTML = availableMovies.length
            ? `
                <option value="">Chọn phim</option>
                ${availableMovies.map(movie => `
                    <option value="${esc(movie.id)}">
                        ${esc(movie.title)}
                    </option>
                `).join('')}
            `
            : '<option value="">Chưa có phim đang chiếu</option>';

        const requestedMovie =
            new URLSearchParams(location.search).get('movie');

        if (
            requestedMovie
            && availableMovies.some(
                movie => String(movie.id) === requestedMovie
            )
        ) {
            movieSelect.value = requestedMovie;
        }

        concessions = await api('/concessions');
        renderConcessions();

        await loadShowtimes();
    } catch (error) {
        message(error.message, true);
    }

    movieSelect.addEventListener('change', loadShowtimes);
    showtimeSelect.addEventListener('change', loadSeats);

    checkoutButton.addEventListener('click', async () => {
        if (
            !showtimeSelect.value
            || selectedSeats.length === 0
            || selectedSeats.length > 8
        ) {
            return;
        }

        checkoutButton.disabled = true;
        checkoutButton.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin"></i>
            Đang xử lý...
        `;

        const selectedConcessions = concessions
            .map(item => ({
                id: item.id,
                quantity: Number(
                    fnbContainer.querySelector(
                        `[data-concession="${item.id}"]`
                    )?.value || 0
                ),
            }))
            .filter(item => item.quantity > 0);

        try {
            await api('/holds', 'POST', {
                showtimeId: Number(showtimeSelect.value),
                seatIds: selectedSeats,
            });

            const order = await api('/checkout', 'POST', {
                showtimeId: Number(showtimeSelect.value),
                concessions: selectedConcessions,
            });

            location.href = `order-detail.php?id=${encodeURIComponent(order.id)}`;
        } catch (error) {
            message(error.message, true);
            checkoutButton.disabled = false;
            checkoutButton.innerHTML = `
                <i class="fa-solid fa-ticket"></i>
                Đặt vé ngay
            `;
        }
    });
});
