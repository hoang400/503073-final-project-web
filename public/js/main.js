const $ = selector => document.querySelector(selector);

function message(text, error = false) {
    const notice = $('#notice');

    if (!notice) {
        return;
    }

    notice.textContent = text;
    notice.className = error ? 'notice error' : 'notice';
}

function returnPage() {
    const next = new URLSearchParams(location.search).get('next')
        || 'index.php';

    const allowed =
        /^(index|movies|booking|history|order-detail|profile)\.php(?:\?[^#]*)?$/;

    return allowed.test(next) && !next.includes('\\')
        ? next
        : 'index.php';
}

async function requireUser() {
    const user = await api('/auth/me');

    if (!user) {
        const page = location.pathname.split('/').pop()
            + location.search;

        location.href =
            'login.php?next=' + encodeURIComponent(page);

        return null;
    }

    return user;
}

async function header() {
    const account = $('#account');

    if (!account) {
        return;
    }

    const user = await api('/auth/me');

    account.innerHTML = user
        ? `
            <a href="profile.php">${esc(user.name)}</a>
            <button id="logout" type="button">
                Đăng xuất
            </button>
        `
        : `
            <a href="login.php">Đăng nhập</a>
            <a href="register.php">Đăng ký</a>
        `;

    if (!user) {
        return;
    }

    $('#logout').onclick = async () => {
        const button = $('#logout');
        button.disabled = true;

        try {
            await api('/auth/logout', 'POST');
            location.href = 'login.php';
        } catch (error) {
            message(error.message, true);
        } finally {
            button.disabled = false;
        }
    };
}

function posterUrl(value) {
    if (!value) {
        return '';
    }

    try {
        const url = new URL(value, location.href);

        return ['http:', 'https:'].includes(url.protocol)
            ? url.href
            : '';
    } catch {
        return '';
    }
}

function movieCard(movie) {
    const poster = posterUrl(movie.poster);
    const showing = movie.status === 'DANG_CHIEU';

    return `
        <article class="card movie">
            ${poster ? `
                <img
                    src="${esc(poster)}"
                    alt="${esc(movie.title)}"
                    loading="lazy"
                >
            ` : '<p>Chưa có poster.</p>'}

            <div>
                <span class="tag">
                    ${showing ? 'Đang chiếu' : 'Sắp chiếu'}
                    · ${esc(movie.duration)}
                </span>

                <h2>${esc(movie.title)}</h2>
                <p>${esc(movie.genres.join(' · '))}</p>

                ${showing ? `
                    <a
                        class="button"
                        href="booking.php?movie=${encodeURIComponent(movie.id)}"
                    >
                        Đặt vé
                    </a>
                ` : '<p>Chưa mở đặt vé.</p>'}
            </div>
        </article>
    `;
}

function renderMovies(container, movies) {
    container.innerHTML = movies.length
        ? movies.map(movieCard).join('')
        : '<p>Chưa có phim phù hợp.</p>';

    container.querySelectorAll('img').forEach(image => {
        image.addEventListener('error', () => {
            image.hidden = true;
        });
    });
}

async function loadMovies() {
    const container = $('#movies');
    container.textContent = 'Đang tải danh sách phim…';

    try {
        const movies = await api('/movies');
        const search = $('#search');

        // Trang chủ: hiển thị tối đa 4 phim đang chiếu.
        if (!search) {
            const showingMovies = movies
                .filter(movie => movie.status === 'DANG_CHIEU')
                .slice(0, 4);

            renderMovies(container, showingMovies);
            return;
        }

        // Trang danh sách: tìm kiếm và lọc.
        const genre = $('#genre');
        const status = $('#status');

        const genres = [
            ...new Set(movies.flatMap(movie => movie.genres)),
        ];

        genre.innerHTML =
            '<option value="">Tất cả thể loại</option>'
            + genres.map(value => `
                <option value="${esc(value)}">
                    ${esc(value)}
                </option>
            `).join('');

        const render = () => {
            const keyword = search.value
                .trim()
                .toLocaleLowerCase('vi-VN');

            const filtered = movies.filter(movie =>
                movie.title
                    .toLocaleLowerCase('vi-VN')
                    .includes(keyword)
                && (
                    !genre.value
                    || movie.genres.includes(genre.value)
                )
                && (
                    !status.value
                    || movie.status === status.value
                )
            );

            renderMovies(container, filtered);
        };

        search.oninput = render;
        genre.onchange = render;
        status.onchange = render;

        render();
    } catch (error) {
        container.textContent = 'Không tải được danh sách phim.';
        message(error.message, true);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    header().catch(error => message(error.message, true));

    if ($('#movies')) {
        loadMovies();
    }
});
