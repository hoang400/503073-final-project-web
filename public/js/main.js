const $ = selector => document.querySelector(selector);
const SITE_BASE = new URL('../', document.currentScript.src).href;

function message(text, error = false) {
    const notice = $('#notice');

    if (!notice) {
        return;
    }

    notice.textContent = text;
    notice.className = error ? 'notice error' : 'notice';
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

function getGenres(movie) {
    return Array.isArray(movie.genres)
        ? movie.genres
        : [];
}

function isShowing(movie) {
    return movie.status === 'DANG_CHIEU';
}

function movieCard(movie) {
    const poster = posterUrl(movie.poster);
    const genres = getGenres(movie);
    const showing = isShowing(movie);

    return `
        <article class="movie-card">
            <div class="movie-poster">
                ${
                    poster
                        ? `
                            <img
                                src="${esc(poster)}"
                                alt="${esc(movie.title)}"
                                loading="lazy"
                            >
                        `
                        : `
                            <div class="poster-placeholder">
                                <i class="fa-solid fa-film"></i>
                                <span>Chưa có poster</span>
                            </div>
                        `
                }

                ${
                    movie.ageTag
                        ? `<span class="age-badge">${esc(movie.ageTag)}</span>`
                        : ''
                }

                ${
                    movie.imdb
                        ? `
                            <span class="imdb-badge">
                                <i class="fa-solid fa-star"></i>
                                ${esc(movie.imdb)}
                            </span>
                        `
                        : ''
                }
            </div>

            <div class="movie-card-content">
                <div class="movie-genres">
                    ${genres.map(genre => esc(genre)).join(' • ')}
                </div>

                <h3>${esc(movie.title)}</h3>

                <div class="movie-duration">
                    <i class="fa-regular fa-clock"></i>
                    ${esc(movie.duration)}
                </div>

                <div class="movie-status ${showing ? 'showing' : 'coming'}">
                    ${showing ? 'Đang chiếu' : 'Sắp chiếu'}
                </div>

                ${
                    showing
                        ? `
                            <a
                                class="movie-booking-button"
                                href="booking.php?movie=${encodeURIComponent(movie.id)}"
                            >
                                <i class="fa-solid fa-ticket"></i>
                                ĐẶT VÉ NGAY
                            </a>
                        `
                        : `
                            <button class="movie-disabled-button" disabled>
                                CHƯA MỞ ĐẶT VÉ
                            </button>
                        `
                }
            </div>
        </article>
    `;
}

function renderMovies(container, movies) {
    if (!container) {
        return;
    }

    container.innerHTML = movies.length
        ? movies.map(movieCard).join('')
        : `
            <div class="empty-movies">
                <i class="fa-solid fa-film"></i>
                <p>Không tìm thấy phim phù hợp.</p>
            </div>
        `;

    container.querySelectorAll('.movie-poster img').forEach(image => {
        image.addEventListener('error', () => {
            const placeholder = document.createElement('div');

            placeholder.className = 'poster-placeholder';
            placeholder.innerHTML = `
                <i class="fa-solid fa-film"></i>
                <span>Không thể tải poster</span>
            `;

            image.replaceWith(placeholder);
        });
    });
}

function renderHero(movies) {
    const hero = $('#hero');

    if (!hero) {
        return;
    }

    const featured = movies.find(movie =>
        movie.title.toLowerCase().includes('godzilla')
    ) || movies.find(isShowing) || movies[0];

    if (!featured) {
        $('#hero-title').textContent = 'Khám phá thế giới điện ảnh';
        $('#hero-description').textContent =
            'Hiện chưa có phim để giới thiệu.';
        $('#hero-booking').href = 'movies.php';
        return;
    }

    const poster = posterUrl(featured.poster);

    if (poster) {
        hero.style.backgroundImage = `
            linear-gradient(
                90deg,
                rgba(11, 14, 23, 0.98) 0%,
                rgba(11, 14, 23, 0.88) 45%,
                rgba(11, 14, 23, 0.25) 100%
            ),
            url("${poster}")
        `;
    }

    $('#hero-title').textContent = featured.title;

    $('#hero-description').textContent =
        featured.description || 'Khám phá bộ phim tại CineStar.';

    const meta = [];

    if (featured.ageTag) {
        meta.push(featured.ageTag);
    }

    if (featured.duration) {
        meta.push(featured.duration);
    }

    if (featured.imdb) {
        meta.push('IMDb ' + featured.imdb);
    }

    $('#hero-meta').textContent = meta.join('  •  ');

    $('#hero-booking').href = isShowing(featured)
        ? `booking.php?movie=${encodeURIComponent(featured.id)}`
        : 'movies.php';

    $('#hero-booking').style.display = isShowing(featured)
        ? 'inline-flex'
        : 'none';
}

function renderMovieFilters(movies) {
    const genreSelect = $('#genre');

    if (!genreSelect) {
        return;
    }

    const genres = [
        ...new Set(movies.flatMap(movie => getGenres(movie)))
    ].sort((a, b) => a.localeCompare(b, 'vi'));

    genreSelect.innerHTML = `
        <option value="">Tất cả thể loại</option>
        ${
            genres.map(genre => `
                <option value="${esc(genre)}">${esc(genre)}</option>
            `).join('')
        }
    `;
}

function setupMovieSearch(movies) {
    const search = $('#search');
    const genre = $('#genre');
    const status = $('#status');
    const container = $('#movies');
    const count = $('#movie-count');

    if (!search || !genre || !status || !container) {
        return;
    }

    const render = () => {
        const keyword = search.value
            .trim()
            .toLocaleLowerCase('vi-VN');

        const filtered = movies.filter(movie => {
            const title = (movie.title || '')
                .toLocaleLowerCase('vi-VN');

            const matchesKeyword = title.includes(keyword);

            const matchesGenre =
                !genre.value || getGenres(movie).includes(genre.value);

            const matchesStatus =
                !status.value || movie.status === status.value;

            return matchesKeyword && matchesGenre && matchesStatus;
        });

        renderMovies(container, filtered);

        if (count) {
            count.textContent = `${filtered.length} phim`;
        }
    };

    search.addEventListener('input', render);
    genre.addEventListener('change', render);
    status.addEventListener('change', render);

    render();
}
function returnPage() {
    const next = new URLSearchParams(location.search).get('next') || '';

    if (
        next
        && !next.startsWith('//')
        && !/^[a-zA-Z][a-zA-Z0-9+.-]*:/.test(next)
        && !next.includes('\\')
        && (next.startsWith('/') || next.endsWith('.php') || !next.includes(':'))
    ) {
        return next.replace(/^\/+/, '');
    }

    return 'index.php';
}

async function requireUser() {
    let user = null;

    try {
        user = await api('/auth/me');
    } catch {
        user = null;
    }

    if (user) {
        return true;
    }

    // Đường dẫn trang hiện tại tính từ thư mục gốc của web
    // (vd. "admin/index.php") để sau khi đăng nhập quay lại đúng trang.
    const basePath = new URL(SITE_BASE).pathname;
    const next = (location.pathname.startsWith(basePath)
        ? location.pathname.slice(basePath.length)
        : location.pathname.split('/').pop())
        + (location.search || '');

    location.href = SITE_BASE + 'login.php?next=' + encodeURIComponent(next);

    return false;
}

async function header() {
    const account = $('#account');

    if (!account) {
        return;
    }

    const user = await api('/auth/me');

    // Trang admin chỉ hiện tên người dùng (theo yêu cầu bỏ icon ở giao diện admin)
    const isAdminPage = window.location.pathname.includes('/admin/');

    account.innerHTML = user
        ? `
            <a href="${SITE_BASE}profile.php" class="account-user">
                ${isAdminPage ? '' : '<i class="fa-solid fa-circle-user"></i>'}
                <span class="account-user-name">${esc(user.name)}</span>
            </a>

            <button id="logout" type="button" class="account-button">
                Đăng xuất
            </button>
        `
        : `
            <a href="${SITE_BASE}login.php" class="account-login">
                Đăng nhập
            </a>
        `;

    // Trang soát vé đã có sẵn nhưng trước đây không có đường dẫn tới nó
    // từ giao diện — nhân viên đăng nhập xong không biết vào đâu để soát vé.
    if (
        user
        && (user.role === 'STAFF' || user.role === 'ADMIN')
        && !document.querySelector('[data-nav-checkin]')
    ) {
        const nav = document.querySelector('.main-nav');
        if (nav) {
            nav.insertAdjacentHTML('beforeend', `
                <a href="${SITE_BASE}staff/check-in.php" data-nav-checkin>
                    <i class="fa-solid fa-circle-check"></i> Soát vé
                </a>
            `);
        }
    }

    if (!user) {
        return;
    }

    $('#logout').addEventListener('click', async () => {
        const button = $('#logout');
        button.disabled = true;

        try {
            await api('/auth/logout', 'POST');
            location.href = SITE_BASE + 'login.php';
        } catch (error) {
            message(error.message, true);
            button.disabled = false;
        }
    });
}


async function loadMovies() {
    const container = $('#movies');
    const hero = $('#hero');

    if (!container && !hero) {
        return;
    }

    if (container) {
        container.innerHTML =
            '<p class="loading-text">Đang tải danh sách phim...</p>';
    }

    try {
  
        const movies = await api('/movies');

        if (!Array.isArray(movies)) {
            throw new Error('Dữ liệu phim không hợp lệ.');
        }
        if (hero) {
            renderHero(movies);
        }
        if (!container) {
            return;
        }

        if ($('#search')) {
            renderMovieFilters(movies);
            setupMovieSearch(movies);
        } else {
            const showingMovies = movies
                .filter(isShowing)
                .slice(0, 4);

            renderMovies(container, showingMovies);

            // Trang chủ thường chỉ có 3 phim đang chiếu trong lưới 4 cột
            // -> cột thứ 4 trống làm nút "XEM TẤT CẢ PHIM" (căn phải theo
            // mép section) trông lệch khỏi mép phải của poster cuối.
            // Co số cột theo số phim để mép phải lưới trùng mép phải poster.
            container.style.gridTemplateColumns =
                `repeat(${Math.max(1, Math.min(4, showingMovies.length))}, minmax(0, 1fr))`;
        }

    } catch (error) {
        if (container) {
            container.innerHTML = `
                <div class="empty-movies">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>Không tải được danh sách phim.</p>
                </div>
            `;
        }

        message(error.message, true);
    }
}
document.addEventListener('DOMContentLoaded', () => {
    header().catch(error => message(error.message, true));

    loadMovies();
});
