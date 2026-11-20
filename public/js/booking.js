// Booking page — layout giống ảnh chốt của nhóm.
// API giữ nguyên như bản cũ: /movies, /showtimes, /seats,
// /concessions, /holds, /checkout. Chỉ thay đổi cách hiển thị.

document.addEventListener('DOMContentLoaded', async () => {
    const movieSelect = $('#movie');
    const showtimeSelect = $('#showtime'); // select ẩn, giữ để đồng bộ id suất chiếu
    const timeSelect = $('#showtimeTime');
    const formatSelect = $('#formatSelect');
    const cinemaSelect = $('#cinemaSelect');
    const dateStrip = $('#dateStrip');
    const seatsContainer = $('#seats');
    const fnbContainer = $('#fnb');
    const fnbPanel = $('#fnbPanel');
    const fnbToggle = $('#fnbToggle');
    const bill = $('#bill');
    const checkoutButton = $('#checkout');

    let selectedSeats = [];
    let selectedSeatDetails = [];
    let showtimes = []; // tất cả suất chiếu của phim đang chọn
    let movies = [];
    let concessions = [];
    let basePrice = 0;
    let user;

    // Ngày đang chọn (YYYY-MM-DD, giờ địa phương) + cửa sổ 7 ngày
    let selectedDate = '';
    let dateOffset = 0;

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

    function getFoodItems() {
        return concessions
            .map(item => ({
                id: item.id,
                quantity: Number(
                    fnbContainer.querySelector(
                        `[data-concession="${item.id}"]`
                    )?.value || 0
                ),
            }))
            .filter(item => item.quantity > 0);
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

    function getFoodCount() {
        return getFoodItems().reduce((n, item) => n + item.quantity, 0);
    }

    function shortMoney(value) {
        // 90000 -> "90k", 110000 -> "110k"
        const v = Math.round(Number(value) || 0);
        return v >= 1000 ? `${Math.round(v / 1000)}k` : `${v}`;
    }

    function updateLegendPrices() {
        const std = document.getElementById('legendStandardPrice');
        const vip = document.getElementById('legendVipPrice');
        const cpl = document.getElementById('legendCouplePrice');
        if (std) std.textContent = shortMoney(basePrice * 1);
        if (vip) vip.textContent = shortMoney(basePrice * 1.2);
        if (cpl) cpl.textContent = shortMoney(basePrice * 1.5);
    }

    function selectedMovie() {
        return movies.find(m => String(m.id) === String(movieSelect.value)) || null;
    }

    function selectedShowtime() {
        return showtimes.find(item => String(item.id) === String(showtimeSelect.value)) || null;
    }

    function updateTicketInfo() {
        const movie = selectedMovie();
        const showtime = selectedShowtime();

        const ticketMovie = document.getElementById('ticketMovie');
        const ticketShowtime = document.getElementById('ticketShowtime');
        const ticketCinema = document.getElementById('ticketCinema');
        const ticketSeats = document.getElementById('ticketSeats');
        const ticketCount = document.getElementById('ticketCount');
        const ticketFormat = document.getElementById('ticketFormat');

        if (ticketMovie) {
            ticketMovie.textContent = movie
                ? (movie.shortTitle || movie.title)
                : '—';
        }

        if (ticketShowtime) {
            ticketShowtime.textContent = showtime
                ? new Date(showtime.startsAt).toLocaleString('vi-VN', {
                    hour: '2-digit',
                    minute: '2-digit',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                })
                : '—';
        }

        if (ticketCinema) {
            ticketCinema.textContent = showtime
                ? (showtime.cinemaName || showtime.roomName || '—')
                : '—';
        }

        if (ticketSeats) {
            ticketSeats.textContent = selectedSeatDetails.length
                ? selectedSeatDetails
                    .map(s => `${s.row}${s.number}`)
                    .join(', ')
                : 'Chưa chọn ghế';
            ticketSeats.classList.toggle('empty', selectedSeatDetails.length === 0);
        }

        if (ticketCount) {
            ticketCount.textContent =
                `(${selectedSeats.length} ghế, ${getFoodCount()} dịch vụ)`;
        }

        if (ticketFormat) {
            const fmt = movieFormat(movie);
            ticketFormat.hidden = !fmt;
            ticketFormat.textContent = fmt || '';
        }
    }

    function updateBill() {
        const total = getSelectedSeatTotal() + getFoodTotal();

        bill.textContent = money(total);
        checkoutButton.disabled =
            selectedSeats.length === 0 || !showtimeSelect.value;

        updateTicketInfo();
        updateLegendPrices();
    }

    function resetSelectedSeats() {
        selectedSeats = [];
        selectedSeatDetails = [];
    }

    function getSeatClass(type) {
        if (type === 'VIP') return 'vip';
        if (type === 'Couple') return 'couple';
        return 'standard';
    }

    // ---- Hero phim ----
    function renderHero(movie) {
        const hero = document.getElementById('movieHero');
        if (!hero) return;

        if (!movie) {
            hero.hidden = true;
            return;
        }

        hero.hidden = false;

        const poster = document.getElementById('moviePoster');
        if (poster) {
            poster.src = posterUrl(movie.poster);
            poster.alt = movie.title || '';
        }

        const ageBadge = document.getElementById('movieAgeBadge');
        if (ageBadge) {
            ageBadge.hidden = !movie.ageTag;
            ageBadge.textContent = movie.ageTag || '';
        }

        const genresEl = document.getElementById('movieGenres');
        if (genresEl) {
            genresEl.innerHTML = getGenres(movie)
                .map(g => `<span class="booking-genre-pill">${esc(g)}</span>`)
                .join('');
        }

        const titleEl = document.getElementById('movieTitle');
        if (titleEl) titleEl.textContent = movie.title || '';

        const imdbEl = document.getElementById('movieImdb');
        if (imdbEl) {
            imdbEl.hidden = !movie.imdb;
            imdbEl.querySelector('span').textContent = movie.imdb || '';
        }

        const durEl = document.getElementById('movieDuration');
        if (durEl) {
            durEl.hidden = !movie.duration;
            durEl.querySelector('span').textContent = movie.duration || '';
        }

        const ageEl = document.getElementById('movieAge');
        if (ageEl) {
            ageEl.hidden = !movie.ageDesc;
            ageEl.textContent = movie.ageDesc || '';
        }

        const descEl = document.getElementById('movieDesc');
        const toggle = document.getElementById('movieDescToggle');
        if (descEl) {
            descEl.textContent = movie.description || '';
            descEl.classList.remove('expanded');
        }
        if (toggle) {
            toggle.hidden = !(movie.description && movie.description.length > 220);
            toggle.innerHTML = 'XEM THÊM <i class="fa-solid fa-chevron-down"></i>';
        }

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value || '—';
        };
        setText('movieDirector', movie.director);
        setText('movieWriters', movie.writers);
        setText('movieStars', movie.stars);

        const trailer = document.getElementById('movieTrailer');
        if (trailer) {
            trailer.hidden = !movie.trailerUrl;
            trailer.href = movie.trailerUrl || '#';
        }
    }

    // ---- Ngày / giờ / định dạng / rạp ----
    function localDateString(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function dateOfShowtime(showtime) {
        return localDateString(new Date(showtime.startsAt));
    }

    function movieFormat(movie) {
        if (!movie) return '';
        // dropdownTitle dạng "Godzilla x Kong: ... (2D Vietsub)"
        const fromDropdown = (movie.dropdownTitle || '').match(/\(([^)]+)\)/);
        if (fromDropdown) return fromDropdown[1];
        return '2D Vietsub';
    }

    function buildDateStrip() {
        if (!dateStrip) return;

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const days = [];
        for (let i = 0; i < 7; i++) {
            const d = new Date(today);
            d.setDate(d.getDate() + dateOffset + i);
            days.push(d);
        }

        const weekday = ['CN', 'TH 2', 'TH 3', 'TH 4', 'TH 5', 'TH 6', 'TH 7'];

        dateStrip.innerHTML = days.map(d => {
            const key = localDateString(d);
            const isToday = key === localDateString(today);
            const isSelected = key === selectedDate;
            const hasShowtime = showtimes.some(s => dateOfShowtime(s) === key);

            return `
                <button type="button"
                    class="booking-date-day${isSelected ? ' selected' : ''}${hasShowtime ? '' : ' no-showtime'}"
                    data-date="${key}"
                    role="tab"
                    aria-selected="${isSelected}">
                    <span class="booking-date-weekday">${isToday ? 'HÔM NAY' : weekday[d.getDay()]}</span>
                    <strong>${d.getDate()}</strong>
                    <small>Thg ${d.getMonth() + 1}</small>
                </button>
            `;
        }).join('');

        dateStrip.querySelectorAll('[data-date]').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedDate = btn.dataset.date;
                buildDateStrip();
                populateFilters();
            });
        });

        const prev = document.getElementById('datePrev');
        const next = document.getElementById('dateNext');
        if (prev) prev.disabled = dateOffset <= 0;
        if (next) next.disabled = dateOffset >= 21;
    }

    function filteredShowtimes() {
        return showtimes.filter(s => {
            if (selectedDate && dateOfShowtime(s) !== selectedDate) return false;
            if (cinemaSelect.value && (s.cinemaName || '') !== cinemaSelect.value) return false;
            return true;
        });
    }

    function populateFilters() {
        // Định dạng: lấy từ phim đang chọn (backend hiện không có trường format riêng)
        const movie = selectedMovie();
        const fmt = movieFormat(movie);
        if (formatSelect) {
            formatSelect.innerHTML = fmt
                ? `<option value="${esc(fmt)}">${esc(fmt)}</option>`
                : '<option value="">—</option>';
            formatSelect.disabled = !fmt;
        }

        // Cụm rạp: distinct cinemaName từ suất chiếu (cần backend patch, xem README)
        const cinemas = [...new Set(
            showtimes
                .filter(s => !selectedDate || dateOfShowtime(s) === selectedDate)
                .map(s => s.cinemaName)
                .filter(Boolean)
        )];
        if (cinemaSelect) {
            const prevValue = cinemaSelect.value;
            cinemaSelect.innerHTML =
                '<option value="">Tất cả rạp</option>' +
                cinemas.map(c => `<option value="${esc(c)}">${esc(c)}</option>`).join('');
            cinemaSelect.disabled = cinemas.length === 0;
            if (prevValue && cinemas.includes(prevValue)) {
                cinemaSelect.value = prevValue;
            }
        }

        // Giờ chiếu: các suất trong ngày + rạp đang chọn
        const list = filteredShowtimes()
            .slice()
            .sort((a, b) => new Date(a.startsAt) - new Date(b.startsAt));

        if (timeSelect) {
            timeSelect.innerHTML = list.length
                ? '<option value="">Chọn giờ chiếu</option>' +
                    list.map(s => `
                        <option value="${s.id}">
                            ${new Date(s.startsAt).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}
                        </option>
                    `).join('')
                : '<option value="">Chưa có suất chiếu</option>';
            timeSelect.disabled = list.length === 0;
            timeSelect.value = '';
        }

        // Đồng bộ select ẩn + sơ đồ ghế
        showtimeSelect.innerHTML = '<option value=""></option>';
        showtimeSelect.value = '';
        seatsContainer.innerHTML = `
            <p class="booking-placeholder">Chọn suất chiếu để xem sơ đồ ghế.</p>
        `;
        basePrice = 0;
        resetSelectedSeats();
        updateBill();
    }

    // ---- Sơ đồ ghế ----
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
            if (!groupedSeats[rowName]) groupedSeats[rowName] = [];
            groupedSeats[rowName].push(seat);
        });

        const rowNames = Object.keys(groupedSeats).sort((a, b) =>
            a.localeCompare(b, 'vi', { numeric: true })
        );

        seatsContainer.innerHTML = rowNames.map(rowName => {
            const rowSeats = groupedSeats[rowName].sort(
                (a, b) => Number(a.number) - Number(b.number)
            );

            // Lối đi như ảnh chốt: hàng dài (>8 ghế) chừa khe sau ghế 2, 7, 10
            const cells = [];
            rowSeats.forEach((seat, index) => {
                const status = seat.status;
                const unavailable = ['BOOKED', 'HELD'].includes(status);
                const seatType = getSeatClass(seat.type);
                const isHeldByMe = status === 'HELD_BY_ME';

                cells.push(`
                    <button
                        type="button"
                        class="seat ${seatType} ${unavailable ? 'taken' : ''}${isHeldByMe ? ' selected' : ''}"
                        data-seat-id="${seat.id}"
                        ${unavailable ? 'disabled' : ''}
                        title="Ghế ${esc(rowName)}${seat.number} · ${esc(seat.type)}"
                        aria-label="Ghế ${esc(rowName)}${seat.number}, ${esc(seat.type)}"
                        ${isHeldByMe ? 'data-held-by-me="true"' : ''}
                    >
                        ${esc(rowName)}${seat.number}
                    </button>
                `);

                if (rowSeats.length > 8 && [1, 6, 9].includes(index)) {
                    cells.push('<span class="seat-aisle" aria-hidden="true"></span>');
                }
            });

            return `
                <div class="seat-row">
                    <span class="seat-row-label">${esc(rowName)}</span>
                    <div class="seat-row-list">${cells.join('')}</div>
                    <span class="seat-row-label">${esc(rowName)}</span>
                </div>
            `;
        }).join('');

        seatsContainer.querySelectorAll('[data-seat-id]').forEach(button => {
            button.addEventListener('click', () => {
                const seatId = Number(button.dataset.seatId);
                const seat = seats.find(item => Number(item.id) === seatId);
                if (!seat) return;

                const selected = selectedSeats.includes(seatId);

                if (!selected && selectedSeats.length >= 8) {
                    message('Bạn chỉ được chọn tối đa 8 ghế.', true);
                    return;
                }

                if (selected) {
                    selectedSeats = selectedSeats.filter(id => id !== seatId);
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
        showtimes = [];
        selectedDate = '';

        seatsContainer.innerHTML = `
            <p class="booking-placeholder">
                Chọn suất chiếu để xem sơ đồ ghế.
            </p>
        `;
        checkoutButton.disabled = true;

        const movie = selectedMovie();
        renderHero(movie);

        if (!movieSelect.value || !movie) {
            if (timeSelect) {
                timeSelect.innerHTML = '<option value="">Chọn phim trước</option>';
                timeSelect.disabled = true;
            }
            buildDateStrip();
            populateFilters();
            updateBill();
            return;
        }

        try {
            showtimes = await api(
                `/showtimes?movie_id=${encodeURIComponent(movieSelect.value)}`
            );

            // Mặc định chọn hôm nay nếu có suất, không thì ngày có suất đầu tiên
            const todayKey = localDateString(new Date());
            const availableDates = [...new Set(showtimes.map(dateOfShowtime))].sort();
            selectedDate = availableDates.includes(todayKey)
                ? todayKey
                : (availableDates[0] || todayKey);

            // Đưa cửa sổ ngày về tuần chứa ngày đang chọn
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const diffDays = Math.round(
                (new Date(selectedDate + 'T00:00:00') - today) / 86400000
            );
            dateOffset = Math.max(0, Math.floor(diffDays / 7) * 7);

            buildDateStrip();
            populateFilters();

            // Tự chọn giờ chiếu đầu tiên trong ngày để giống ảnh chốt
            const first = filteredShowtimes()
                .slice()
                .sort((a, b) => new Date(a.startsAt) - new Date(b.startsAt))[0];
            if (first && timeSelect) {
                timeSelect.value = String(first.id);
                timeSelect.dispatchEvent(new Event('change'));
            }
        } catch (error) {
            showtimes = [];
            if (timeSelect) {
                timeSelect.innerHTML = '<option value="">Lỗi tải suất chiếu</option>';
                timeSelect.disabled = true;
            }
            buildDateStrip();
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

        const showtime = selectedShowtime();

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
                if (!Number.isFinite(quantity) || quantity < 0) quantity = 0;
                if (quantity > 20) quantity = 20;
                input.value = String(Math.floor(quantity));
                updateBill();
            });
        });
    }

    // ---- Khởi tạo ----
    try {
        user = await api('/auth/me');
    } catch (error) {
        message(error.message, true);
        checkoutButton.disabled = true;
        return;
    }

    if (!user) {
        const next = location.pathname.split('/').pop() + location.search;
        location.href = 'login.php?next=' + encodeURIComponent(next);
        return;
    }

    try {
        movies = await api('/movies');

        const availableMovies = movies.filter(
            movie => movie.status === 'DANG_CHIEU'
        );

        movieSelect.innerHTML = availableMovies.length
            ? availableMovies.map(movie => `
                    <option value="${esc(movie.id)}">
                        ${esc(movie.dropdownTitle || movie.title)}
                    </option>
                `).join('')
            : '<option value="">Chưa có phim đang chiếu</option>';

        const requestedMovie =
            new URLSearchParams(location.search).get('movie');

        if (
            requestedMovie
            && availableMovies.some(movie => String(movie.id) === requestedMovie)
        ) {
            movieSelect.value = requestedMovie;
        } else if (availableMovies.length) {
            movieSelect.value = String(availableMovies[0].id);
        }

        concessions = await api('/concessions');
        renderConcessions();

        await loadShowtimes();
    } catch (error) {
        message(error.message, true);
    }

    // ---- Events ----
    movieSelect.addEventListener('change', loadShowtimes);

    timeSelect.addEventListener('change', () => {
        // Đồng bộ sang select ẩn rồi tải ghế
        showtimeSelect.innerHTML = timeSelect.value
            ? `<option value="${esc(timeSelect.value)}" selected></option>`
            : '<option value=""></option>';
        showtimeSelect.value = timeSelect.value || '';
        loadSeats();
    });

    cinemaSelect.addEventListener('change', populateFilters);

    document.getElementById('datePrev')?.addEventListener('click', () => {
        if (dateOffset <= 0) return;
        dateOffset = Math.max(0, dateOffset - 7);
        buildDateStrip();
    });

    document.getElementById('dateNext')?.addEventListener('click', () => {
        if (dateOffset >= 21) return;
        dateOffset += 7;
        buildDateStrip();
    });

    document.getElementById('movieDescToggle')?.addEventListener('click', () => {
        const desc = document.getElementById('movieDesc');
        const toggle = document.getElementById('movieDescToggle');
        if (!desc || !toggle) return;
        const expanded = desc.classList.toggle('expanded');
        toggle.innerHTML = expanded
            ? 'THU GỌN <i class="fa-solid fa-chevron-up"></i>'
            : 'XEM THÊM <i class="fa-solid fa-chevron-down"></i>';
    });

    fnbToggle?.addEventListener('click', () => {
        const isHidden = fnbPanel.hidden;
        fnbPanel.hidden = !isHidden;
        fnbToggle.classList.toggle('open', isHidden);
    });

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

        const selectedConcessions = getFoodItems();

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
            checkoutButton.textContent = 'THANH TOÁN ĐẶT VÉ';
        }
    });
});
