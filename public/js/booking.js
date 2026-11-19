document.addEventListener('DOMContentLoaded', async () => {
    const movieSelect = $('#movie');
    const showtimeSelect = $('#showtime');
    const seatsContainer = $('#seats');
    const fnbContainer = $('#fnb');
    const bill = $('#bill');
    const checkoutButton = $('#checkout');

    let selectedSeats = [];
    let showtimes = [];
    let concessions = [];
    let basePrice = 0;

    let user;

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

    function updateBill() {
        const ticketTotal = selectedSeats.length * basePrice;

        const foodTotal = concessions.reduce((sum, item) => {
            const quantity = Number(
                fnbContainer.querySelector(
                    `[data-concession="${item.id}"]`
                )?.value || 0
            );

            return sum + item.price * quantity;
        }, 0);

        bill.textContent =
            `${selectedSeats.length} ghế · ${money(ticketTotal + foodTotal)}`;

        checkoutButton.disabled =
            selectedSeats.length === 0 || !showtimeSelect.value;
    }

    function renderSeats(seats) {
        selectedSeats = [];

        if (seats.length === 0) {
            seatsContainer.textContent = 'Suất chiếu chưa có sơ đồ ghế.';
            updateBill();
            return;
        }

        seatsContainer.innerHTML = seats.map(seat => {
            const unavailable =
                seat.status !== 'AVAILABLE'
                && seat.status !== 'HELD_BY_ME';

            return `
                <button
                    type="button"
                    class="seat ${unavailable ? 'unavailable' : ''}"
                    data-seat-id="${seat.id}"
                    ${unavailable ? 'disabled' : ''}
                    aria-label="Ghế ${esc(seat.row)}${seat.number}, ${esc(seat.type)}"
                >
                    ${esc(seat.row)}${seat.number}
                </button>
            `;
        }).join('');

        seatsContainer.querySelectorAll('[data-seat-id]').forEach(button => {
            button.addEventListener('click', () => {
                const seatId = Number(button.dataset.seatId);
                const selected = selectedSeats.includes(seatId);

                if (!selected && selectedSeats.length >= 8) {
                    message('Bạn chỉ được chọn tối đa 8 ghế.', true);
                    return;
                }

                if (selected) {
                    selectedSeats = selectedSeats.filter(id => id !== seatId);
                    button.classList.remove('selected');
                } else {
                    selectedSeats.push(seatId);
                    button.classList.add('selected');
                }

                updateBill();
            });
        });

        updateBill();
    }

    async function loadShowtimes() {
        selectedSeats = [];
        seatsContainer.textContent = 'Chọn suất chiếu để xem ghế.';
        showtimeSelect.innerHTML = '<option value="">Đang tải suất chiếu…</option>';
        checkoutButton.disabled = true;

        if (!movieSelect.value) {
            showtimeSelect.innerHTML = '<option value="">Chọn phim trước</option>';
            return;
        }

        try {
            showtimes = await api(
                `/showtimes?movie_id=${encodeURIComponent(movieSelect.value)}`
            );

            showtimeSelect.innerHTML = showtimes.length
                ? '<option value="">Chọn suất chiếu</option>'
                    + showtimes.map(showtime => `
                        <option value="${showtime.id}">
                            ${new Date(showtime.startsAt).toLocaleString('vi-VN')}
                            · ${money(showtime.basePrice)}
                        </option>
                    `).join('')
                : '<option value="">Chưa có suất chiếu</option>';
        } catch (error) {
            showtimeSelect.innerHTML = '<option value="">Lỗi tải suất chiếu</option>';
            message(error.message, true);
        }
    }

    async function loadSeats() {
        selectedSeats = [];
        seatsContainer.textContent = 'Đang tải sơ đồ ghế…';
        checkoutButton.disabled = true;

        const showtime = showtimes.find(
            item => String(item.id) === showtimeSelect.value
        );

        if (!showtime) {
            seatsContainer.textContent = 'Chọn suất chiếu để xem ghế.';
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
            seatsContainer.textContent = 'Không tải được sơ đồ ghế.';
            message(error.message, true);
        }
    }

    function renderConcessions() {
        fnbContainer.innerHTML = concessions.length
            ? concessions.map(item => `
                <label class="fnb-item">
                    ${esc(item.name)} · ${money(item.price)}
                    <input
                        type="number"
                        min="0"
                        max="20"
                        value="0"
                        data-concession="${item.id}"
                    >
                </label>
            `).join('')
            : '<p>Hiện chưa có bắp nước.</p>';

        fnbContainer.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', updateBill);
        });
    }

    try {
        const movies = await api('/movies');
        const availableMovies = movies.filter(
            movie => movie.status === 'DANG_CHIEU'
        );

        movieSelect.innerHTML = availableMovies.length
            ? '<option value="">Chọn phim</option>'
                + availableMovies.map(movie => `
                    <option value="${esc(movie.id)}">
                        ${esc(movie.title)}
                    </option>
                `).join('')
            : '<option value="">Chưa có phim đang chiếu</option>';

        const requestedMovie = new URLSearchParams(location.search).get('movie');

        if (requestedMovie && availableMovies.some(
            movie => String(movie.id) === requestedMovie
        )) {
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
        if (!showtimeSelect.value || selectedSeats.length === 0) {
            return;
        }

        checkoutButton.disabled = true;

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

            location.href = `order-detail.php?id=${order.id}`;
        } catch (error) {
            message(error.message, true);
            checkoutButton.disabled = false;
        }
    });
});