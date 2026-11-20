// Admin Dashboard Functions
async function loadDashboard() {
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';
    
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    try {
        // /admin/reports trả cả 3 khối (summary, by_date, top_movies) trong 1 lần gọi
        const data = await api(`/admin/reports?${params.toString()}`);

        renderDashboardSummary(data.summary || {});
        renderChart(data.by_date || []);
        renderTopMovies(data.top_movies || []);
    } catch (e) {
        message(e.message, true);
    }
}

function renderDashboardSummary(data) {
    document.getElementById('statTotalRevenue').textContent = (data.total_revenue || 0).toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('statOrders').textContent = (data.order_count || 0).toLocaleString('vi-VN') + ' Đơn vé';
    document.getElementById('statMoviesShowing').textContent = (data.movies_showing || 0).toLocaleString('vi-VN') + ' Phim';
}

let revenueChart = null;
function renderChart(data) {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    
    const labels = data.map(d => new Date(d.date).toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit'}));
    const ticketRev = data.map(d => d.ticket_revenue || 0);
    const concessionRev = data.map(d => d.concession_revenue || 0);
    
    if (revenueChart) revenueChart.destroy();
    
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Doanh thu vé', data: ticketRev, backgroundColor: '#3b82f6' },
                { label: 'Doanh thu bắp', data: concessionRev, backgroundColor: '#f59e0b' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('vi-VN') + 'k' } }
            }
        }
    });
}

function renderTopMovies(data) {
    const tbody = document.getElementById('topMoviesBody');
    if (!tbody) return;
    
    tbody.innerHTML = (data || []).map(m => `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:12px;">${esc(m.ten_phim)}</td>
            <td style="padding:12px; text-align:center;">${m.orders}</td>
            <td style="padding:12px; text-align:center;">${m.tickets}</td>
            <td style="padding:12px; text-align:right;">${(m.revenue || 0).toLocaleString('vi-VN')} VNĐ</td>
        </tr>
    `).join('') || '<tr><td colspan="4" style="padding:24px; text-align:center; color:#94a3b8;">Chưa có dữ liệu</td></tr>';
}

// Dashboard (theo báo cáo 6.2): tải thống kê tổng, không lọc theo ngày
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('statTotalRevenue')) {
        loadDashboard();
    }
});

// Export for other pages
window.AdminDashboard = { loadDashboard, renderChart, renderTopMovies };

// ============================================
// MOVIES MANAGEMENT
// ============================================

let currentMoviePage = 'movies';

async function loadMoviesAdmin() {
    if (currentMoviePage !== 'movies') return;
    
    const search = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    
    try {
        const movies = await api(`/admin/movies?${params.toString()}`);
        renderMoviesTable(movies);
    } catch (e) {
        message(e.message, true);
    }
}

function renderMoviesTable(movies) {
    const tbody = document.getElementById('moviesBody');
    const emptyState = document.getElementById('moviesEmptyState');
    const table = document.getElementById('moviesTable');

    if (!tbody) return;
    
    if (!movies || movies.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    table.style.display = 'table';
    emptyState.style.display = 'none';
    
    tbody.innerHTML = movies.map(m => `
        <tr>
            <td>
                ${m.poster_url ? `<img src="${esc(m.poster_url)}" alt="" class="poster-thumb">` : '<span class="movie-sub">Không có</span>'}
            </td>
            <td>
                <strong>${esc(m.title)}</strong>
                <br>
                <small style="color:#38bdf8; font-size:10px; font-weight:500; text-transform:uppercase; letter-spacing:.5px;">${esc(m.genre)}</small>
            </td>
            <td>${m.duration} phút</td>
            <td>${m.imdb !== null && m.imdb !== undefined ? `<span class="imdb-badge">★ ${esc(String(m.imdb))}/10</span>` : '<span class="movie-sub">—</span>'}</td>
            <td style="text-align:center;">
                <div style="display:flex; gap:8px; justify-content:center;">
                    <button class="button btn-sm btn-edit edit-btn" data-id="${m.id}">Sửa</button>
                    <button class="button btn-sm btn-delete delete-btn" data-id="${m.id}" data-title="${esc(m.title)}">Xóa</button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Bind events
    tbody.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => openMovieModal(btn.dataset.id));
    });
    tbody.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => confirmDeleteMovie(btn.dataset.id, btn.dataset.title));
    });
}

// Modal handling
let editingMovieId = null;

function openMovieModal(id = null) {
    editingMovieId = id;
    const modal = document.getElementById('movieModal');
    const form = document.getElementById('movieForm');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');
    const preview = document.getElementById('posterPreview');
    
    form.reset();
    preview.innerHTML = '';
    document.getElementById('movieId').value = '';
    
    // Nút lưu luôn là "LƯU THÔNG TIN PHIM" như file mẫu; chỉ tiêu đề đổi khi sửa
    submitBtn.textContent = 'LƯU THÔNG TIN PHIM';

    if (id) {
        title.textContent = 'Cập nhật thông tin phim';
        loadMovieForEdit(id);
    } else {
        title.textContent = 'Thêm phim mới';
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeMovieModal() {
    document.getElementById('movieModal').style.display = 'none';
    document.body.style.overflow = '';
    editingMovieId = null;
}
const isEditing = Boolean(editingMovieId);
async function loadMovieForEdit(id) {
    try {
        const movie = await api(`/admin/movies/${id}`);

        document.getElementById('movieId').value = movie.id;

        document.getElementById('ten_ngan').value =
            movie.shortTitle || '';

        document.getElementById('do_tuoi').value =
            movie.ageTag || '';

        document.getElementById('imdb').value =
            movie.imdb ?? '';

        document.getElementById('ten_phim').value =
            movie.title || '';

        document.getElementById('the_loai').value =
            movie.genre || '';

        document.getElementById('thoi_luong').value =
            movie.duration ?? '';

        document.getElementById('poster_url').value =
            movie.poster_url || '';

        document.getElementById('trailer_url').value =
            movie.trailer_url || '';

        const preview = document.getElementById('posterPreview');

        if (movie.poster_url) {
            preview.innerHTML = `
                <img
                    src="${esc(movie.poster_url)}"
                    alt="Preview"
                    style="max-height:150px; border-radius:4px;"
                >
            `;
        } else {
            preview.innerHTML = '';
        }

    } catch (e) {
        message(e.message, true);
        closeMovieModal();
    }
}
// Poster preview on URL change
document.addEventListener('DOMContentLoaded', () => {
    const posterInput = document.getElementById('poster_url');
    if (posterInput) {
        posterInput.addEventListener('input', (e) => {
            const preview = document.getElementById('posterPreview');
            if (e.target.value) {
                preview.innerHTML = `<img src="${esc(e.target.value)}" alt="Preview" style="max-height:150px; border-radius:4px;">`;
            } else {
                preview.innerHTML = '';
            }
        });
    }
});

// Form submit
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('movieForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('submitBtn');

            // Lưu trạng thái trước khi đóng modal
            const wasEditing = Boolean(editingMovieId);

            btn.disabled = true;
            btn.textContent = 'Đang lưu...';

            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Form chỉ có 1 ô "Tên Ngắn (hiển thị Dropdown)" nên dùng
            // luôn làm tên hiển thị trong danh sách chọn phim.
            if (data.ten_ngan) {
                data.ten_hien_thi_dropdown = data.ten_ngan;
            }

            // Ô trailer nhận ID YouTube (vd. dQw4w9WgXcQ) theo mẫu 6.2.2.
            // Trang đặt vé dùng thẳng trailer_url làm href nên nếu nhập
            // ID thì đổi thành link xem được.
            if (
                data.trailer_url
                && !/^https?:\/\//i.test(data.trailer_url.trim())
            ) {
                data.trailer_url =
                    'https://www.youtube.com/watch?v='
                    + data.trailer_url.trim();
            }

            // Chuyển thời lượng thành số nguyên
            if (data.thoi_luong) {
                data.thoi_luong = parseInt(data.thoi_luong, 10);
            }

            try {
                if (wasEditing) {
                    await api(
                        `/admin/movies/${editingMovieId}`,
                        'PATCH',
                        data
                    );

                    message('Cập nhật phim thành công!');
                } else {
                    await api('/admin/movies', 'POST', data);

                    message('Thêm phim thành công!');
                }

                closeMovieModal();
                await loadMoviesAdmin();

            } catch (err) {
                message(err.message, true);

            } finally {
                btn.disabled = false;
                btn.textContent = 'LƯU THÔNG TIN PHIM';
            }
        });
    }
    
    // Modal close buttons
    document.getElementById('closeModal')?.addEventListener('click', closeMovieModal);
    document.getElementById('cancelModal')?.addEventListener('click', closeMovieModal);
    document.getElementById('movieModal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeMovieModal();
    });
});

// Delete confirm
function confirmDeleteMovie(id, title) {
    if (!confirm(`Xóa phim "${title}"? Hành động này không thể hoàn tác (sẽ chuyển trạng thái Ngưng chiếu).`)) return;
    
    api(`/admin/movies/${id}`, 'DELETE')
        .then(() => {
            message('Xóa phim thành công!');
            loadMoviesAdmin();
        })
        .catch(err => message(err.message, true));
}

// Initialize movies page
function initMoviesPage() {
    currentMoviePage = 'movies';
    
    // Search with debounce
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    let debounceTimer;
    
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadMoviesAdmin, 300);
    });
    
    statusFilter?.addEventListener('change', loadMoviesAdmin);
    
    document.getElementById('addMovieBtn')?.addEventListener('click', () => openMovieModal());
    
    loadMoviesAdmin();
}

// Auto-init if on movies page
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('moviesTable')) {
        initMoviesPage();
    }
});

// ============================================
// CINEMAS MANAGEMENT
// ============================================

let currentCinemaPage = 'cinemas';

async function loadCinemasAdmin() {
    if (currentCinemaPage !== 'cinemas') return;
    
    try {
        const cinemas = await api('/admin/cinemas');
        renderCinemasTable(cinemas);
    } catch (e) {
        message(e.message, true);
    }
}

function renderCinemasTable(cinemas) {
    const tbody = document.getElementById('cinemasBody');
    const emptyState = document.getElementById('emptyState');
    const table = document.getElementById('cinemasTable');
    
    if (!cinemas || cinemas.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    table.style.display = 'table';
    emptyState.style.display = 'none';
    
    tbody.innerHTML = cinemas.map(c => `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:12px; font-family:monospace;">${c.id}</td>
            <td style="padding:12px;"><strong>${esc(c.name)}</strong></td>
            <td style="padding:12px; color:#94a3b8;">${esc(c.address)}</td>
            <td style="padding:12px;">${esc(c.city)}</td>
            <td style="padding:12px;">
                <div style="display:flex; gap:8px;">
                    <button class="button edit-cinema-btn" data-id="${c.id}" style="padding:6px 12px; font-size:12px;">Sửa</button>
                    <button class="button delete-cinema-btn" data-id="${c.id}" data-name="${esc(c.name)}" style="padding:6px 12px; font-size:12px; background:#ef4444;">Xóa</button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Bind events
    tbody.querySelectorAll('.edit-cinema-btn').forEach(btn => {
        btn.addEventListener('click', () => openCinemaModal(btn.dataset.id));
    });
    tbody.querySelectorAll('.delete-cinema-btn').forEach(btn => {
        btn.addEventListener('click', () => confirmDeleteCinema(btn.dataset.id, btn.dataset.name));
    });
}

// Modal handling
let editingCinemaId = null;

function openCinemaModal(id = null) {
    editingCinemaId = id;
    const modal = document.getElementById('cinemaModal');
    const form = document.getElementById('cinemaForm');
    const title = document.getElementById('cinemaModalTitle');
    const submitBtn = document.getElementById('cinemaSubmitBtn');
    
    form.reset();
    document.getElementById('cinemaId').value = '';
    
    if (id) {
        title.textContent = 'Sửa rạp';
        submitBtn.textContent = 'Cập nhật';
        loadCinemaForEdit(id);
    } else {
        title.textContent = 'Thêm rạp mới';
        submitBtn.textContent = 'Thêm';
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeCinemaModal() {
    document.getElementById('cinemaModal').style.display = 'none';
    document.body.style.overflow = '';
    editingCinemaId = null;
}

async function loadCinemaForEdit(id) {
    try {
        const cinema = await api(`/admin/cinemas/${id}`);
        document.getElementById('cinemaId').value = cinema.id;
        document.getElementById('ten_rap').value = cinema.name;
        document.getElementById('dia_chi').value = cinema.address;
        document.getElementById('thanh_pho').value = cinema.city;
    } catch (e) {
        message(e.message, true);
        closeCinemaModal();
    }
}

// Form submit
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('cinemaForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('cinemaSubmitBtn');
            btn.disabled = true;
            btn.textContent = 'Đang lưu...';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Clean empty optional fields
            Object.keys(data).forEach(k => {
                if (data[k] === '' && !['ten_rap', 'dia_chi', 'thanh_pho'].includes(k)) {
                    delete data[k];
                }
            });
            
            try {
                if (editingCinemaId) {
                    await api(`/admin/cinemas/${editingCinemaId}`, 'PATCH', data);
                    message('Cập nhật rạp thành công!');
                } else {
                    await api('/admin/cinemas', 'POST', data);
                    message('Thêm rạp thành công!');
                }
                
                closeCinemaModal();
                loadCinemasAdmin();
            } catch (err) {
                message(err.message, true);
            } finally {
                btn.disabled = false;
                btn.textContent = editingCinemaId ? 'Cập nhật' : 'Thêm';
            }
        });
    }
    
    // Modal close buttons
    document.getElementById('closeCinemaModal')?.addEventListener('click', closeCinemaModal);
    document.getElementById('cancelCinemaModal')?.addEventListener('click', closeCinemaModal);
    document.getElementById('cinemaModal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeCinemaModal();
    });
});

// Delete confirm
function confirmDeleteCinema(id, name) {
    if (!confirm(`Xóa rạp "${name}"? Hành động này sẽ xóa cả các phòng thuộc rạp này.`)) return;
    
    api(`/admin/cinemas/${id}`, 'DELETE')
        .then(() => {
            message('Xóa rạp thành công!');
            loadCinemasAdmin();
        })
        .catch(err => message(err.message, true));
}

// Initialize cinemas page
function initCinemasPage() {
    currentCinemaPage = 'cinemas';
    
    document.getElementById('addCinemaBtn')?.addEventListener('click', () => openCinemaModal());
    
    loadCinemasAdmin();
}

// Auto-init if on cinemas page
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('cinemasTable')) {
        initCinemasPage();
    }
});

// ============================================
// ROOMS & SEATS MANAGEMENT
// ============================================

let currentRoomsPage = 'rooms';
let currentRoomId = null;
let currentRoomCinemaId = null;

async function loadRoomsAdmin() {
    if (currentRoomsPage !== 'rooms') return;
    
    try {
        const [rooms, cinemas] = await Promise.all([
            api('/admin/rooms'),
            api('/admin/cinemas')
        ]);
        
        // Populate cinema filter
        const filter = document.getElementById('cinemaFilter');
        if (filter) {
            const currentValue = filter.value;
            filter.innerHTML = '<option value="">Tất cả rạp</option>' + 
                cinemas.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
            filter.value = currentValue;
        }
        
        // Populate room modal cinema select
        const roomCinemaSelect = document.getElementById('roomCinemaId');
        if (roomCinemaSelect) {
            roomCinemaSelect.innerHTML = cinemas.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
        }
        
        renderRoomsTable(rooms);
    } catch (e) {
        message(e.message, true);
    }
}

function renderRoomsTable(rooms) {
    const tbody = document.getElementById('roomsBody');
    const emptyState = document.getElementById('roomsEmptyState');
    const table = document.getElementById('roomsTable');
    const cinemaFilter = document.getElementById('cinemaFilter');
    
    if (!rooms || rooms.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    // Filter by cinema if selected
    const cinemaId = cinemaFilter?.value;
    const filtered = cinemaId ? rooms.filter(r => r.cinema_id == cinemaId) : rooms;
    
    if (filtered.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        emptyState.textContent = cinemaId ? 'Rạp này chưa có phòng' : 'Chưa có phòng nào';
        return;
    }
    
    table.style.display = 'table';
    emptyState.style.display = 'none';
    
    tbody.innerHTML = filtered.map(r => `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:12px; font-family:monospace;">${r.id}</td>
            <td style="padding:12px;"><strong>${esc(r.name)}</strong></td>
            <td style="padding:12px;">
                <span style="background:#3b82f6; color:#1e3a5f; padding:2px 8px; border-radius:4px; font-size:11px;">${esc(r.type)}</span>
            </td>
            <td style="padding:12px;">${esc(r.cinema_name)}</td>
            <td style="padding:12px; text-align:center;">${r.seat_count || 0}</td>
            <td style="padding:12px;">
                <div style="display:flex; gap:8px;">
                    <button class="button edit-room-btn" data-id="${r.id}" style="padding:6px 12px; font-size:12px;">Sửa</button>
                    <button class="button seat-map-btn" data-id="${r.id}" data-name="${esc(r.name)}" data-cinema="${r.cinema_id}" style="padding:6px 12px; font-size:12px; background:#8b5cf6;">Sơ đồ ghế</button>
                    <button class="button delete-room-btn" data-id="${r.id}" data-name="${esc(r.name)}" style="padding:6px 12px; font-size:12px; background:#ef4444;">Xóa</button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Bind events
    tbody.querySelectorAll('.edit-room-btn').forEach(btn => {
        btn.addEventListener('click', () => openRoomModal(btn.dataset.id));
    });
    tbody.querySelectorAll('.seat-map-btn').forEach(btn => {
        btn.addEventListener('click', () => openSeatMap(btn.dataset.id, btn.dataset.name, btn.dataset.cinema));
    });
    tbody.querySelectorAll('.delete-room-btn').forEach(btn => {
        btn.addEventListener('click', () => confirmDeleteRoom(btn.dataset.id, btn.dataset.name));
    });
}

// Room Modal
let editingRoomId = null;

function openRoomModal(id = null) {
    editingRoomId = id;
    const modal = document.getElementById('roomModal');
    const form = document.getElementById('roomForm');
    const title = document.getElementById('roomModalTitle');
    const submitBtn = document.getElementById('roomSubmitBtn');
    
    form.reset();
    document.getElementById('roomId').value = '';
    
    if (id) {
        title.textContent = 'Sửa phòng';
        submitBtn.textContent = 'Cập nhật';
        loadRoomForEdit(id);
    } else {
        title.textContent = 'Thêm phòng mới';
        submitBtn.textContent = 'Thêm';
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRoomModal() {
    document.getElementById('roomModal').style.display = 'none';
    document.body.style.overflow = '';
    editingRoomId = null;
}

async function loadRoomForEdit(id) {
    try {
        const room = await api(`/admin/rooms/${id}`);
        document.getElementById('roomId').value = room.id;
        document.getElementById('ten_phong').value = room.name;
        document.getElementById('roomCinemaId').value = room.cinema_id;
        document.getElementById('loai_phong').value = room.type;
    } catch (e) {
        message(e.message, true);
        closeRoomModal();
    }
}

// Room form submit
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('roomForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('roomSubmitBtn');
            btn.disabled = true;
            btn.textContent = 'Đang lưu...';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            data.rap_id = parseInt(data.rap_id);
            
            try {
                if (editingRoomId) {
                    await api(`/admin/rooms/${editingRoomId}`, 'PATCH', data);
                    message('Cập nhật phòng thành công!');
                } else {
                    await api('/admin/rooms', 'POST', data);
                    message('Thêm phòng thành công!');
                }
                
                closeRoomModal();
                loadRoomsAdmin();
            } catch (err) {
                message(err.message, true);
            } finally {
                btn.disabled = false;
                btn.textContent = editingRoomId ? 'Cập nhật' : 'Thêm';
            }
        });
    }
    
    // Room modal close
    document.getElementById('closeRoomModal')?.addEventListener('click', closeRoomModal);
    document.getElementById('cancelRoomModal')?.addEventListener('click', closeRoomModal);
    document.getElementById('roomModal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeRoomModal();
    });
});

function closeRoomModal() {
    document.getElementById('roomModal').style.display = 'none';
    document.body.style.overflow = '';
    editingRoomId = null;
}

function confirmDeleteRoom(id, name) {
    if (!confirm(`Xóa phòng "${name}"? Sẽ xóa cả sơ đồ ghế của phòng này.`)) return;
    
    api(`/admin/rooms/${id}`, 'DELETE')
        .then(() => {
            message('Xóa phòng thành công!');
            loadRoomsAdmin();
        })
        .catch(err => message(err.message, true));
}

// ============================================
// SEAT MAP
// ============================================

async function openSeatMap(roomId, roomName, cinemaId) {
    currentRoomId = roomId;
    currentRoomCinemaId = cinemaId;
    
    document.getElementById('roomsTable').closest('article').style.display = 'none';
    document.getElementById('roomsEmptyState').style.display = 'none';
    document.getElementById('cinemaFilter').closest('.admin-filters').style.display = 'none';
    document.getElementById('seatMapSection').style.display = 'block';
    
    document.getElementById('seatMapTitle').textContent = `Sơ đồ ghế - ${esc(roomName)}`;
    
    await loadSeatMap(roomId);
}

function backToRooms() {
    document.getElementById('roomsTable').closest('article').style.display = 'block';
    document.getElementById('roomsEmptyState').style.display = 'block';
    document.getElementById('cinemaFilter').closest('.admin-filters').style.display = 'block';
    document.getElementById('seatMapSection').style.display = 'none';
    currentRoomId = null;
    currentRoomCinemaId = null;
}

async function loadSeatMap(roomId) {
    selectedSeatId = null;
    const info = document.getElementById('selectedSeatInfo');
    if (info) info.textContent = '';
    try {
        const seats = await api(`/admin/rooms/${roomId}/seats`);
        renderSeatMap(seats);
    } catch (e) {
        message(e.message, true);
    }
}

function renderSeatMap(seats) {
    const container = document.getElementById('seatMap');
    if (!seats || seats.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:#94a3b8; padding:40px;">Chưa có ghế. Bấm "Tự động tạo ghế" hoặc thêm thủ công.</p>';
        return;
    }

    // Gom theo hàng, bố cục giống hệt trang đặt vé:
    // nhãn hàng 2 bên, lối đi sau ghế 2 / 7 / 10, ghế Couple rộng gấp đôi.
    const rows = {};
    seats.forEach(s => {
        if (!rows[s.row]) rows[s.row] = [];
        rows[s.row].push(s);
    });

    const rowLabels = Object.keys(rows).sort((a, b) =>
        a.localeCompare(b, 'vi', { numeric: true })
    );

    container.innerHTML = rowLabels.map(row => {
        const rowSeats = rows[row].sort((a, b) => a.number - b.number);

        const cells = [];
        rowSeats.forEach((s, index) => {
            const typeClass = s.type === 'VIP' ? 'vip' : s.type === 'Couple' ? 'couple' : 'standard';
            cells.push(`
                <button type="button" class="seat ${typeClass}"
                        data-seat-id="${s.id}" data-row="${esc(row)}" data-number="${s.number}" data-type="${s.type}"
                        title="Ghế ${esc(row)}${s.number} (${esc(s.type)})">
                    ${esc(row)}${s.number}
                </button>
            `);

            // Lối đi như trang đặt vé: hàng dài (>8 ghế) chừa khe sau ghế 2, 7, 10
            if (rowSeats.length > 8 && [1, 6, 9].includes(index)) {
                cells.push('<span class="seat-aisle" aria-hidden="true"></span>');
            }
        });

        return `
            <div class="seat-row">
                <span class="seat-row-label">${esc(row)}</span>
                <div class="seat-row-list">${cells.join('')}</div>
                <span class="seat-row-label">${esc(row)}</span>
            </div>
        `;
    }).join('');

    // Bind seat click events
    container.querySelectorAll('.seat').forEach(btn => {
        btn.addEventListener('click', () => handleSeatClick(btn));
    });
}

let selectedSeatType = 'Thuong';
let selectedSeatId = null;

function handleSeatClick(btn) {
    const seatId = btn.dataset.seatId;

    // Bấm lại ghế đang chọn -> hỏi xóa ghế
    if (selectedSeatId && String(selectedSeatId) === String(seatId)) {
        if (!confirm(`Xóa ghế ${btn.dataset.row}${btn.dataset.number}?`)) return;
        deleteSeat(seatId);
        return;
    }

    // Chọn ghế: highlight + hiện thông tin (đổi loại ghế ở radio phía trên)
    document.querySelectorAll('#seatMap .seat.selected').forEach(el => el.classList.remove('selected'));
    btn.classList.add('selected');
    selectedSeatId = seatId;
    document.getElementById('selectedSeatInfo').textContent =
        `Đã chọn: Ghế ${btn.dataset.row}${btn.dataset.number} (${btn.dataset.type}) — chọn loại ghế ở trên để đổi loại, bấm lại ghế để xóa`;
}

function deleteSeat(seatId) {
    api(`/admin/seats/${seatId}`, 'DELETE')
        .then(() => {
            message('Xóa ghế thành công!');
            selectedSeatId = null;
            loadSeatMap(currentRoomId);
        })
        .catch(err => message(err.message, true));
}

// Auto Generate Seats Modal
let autoGenRoomId = null;

function openAutoGenModal(roomId) {
    autoGenRoomId = roomId;
    document.getElementById('autoGenModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAutoGenModal() {
    document.getElementById('autoGenModal').style.display = 'none';
    document.body.style.overflow = '';
    autoGenRoomId = null;
}

document.addEventListener('DOMContentLoaded', () => {
    // Auto gen form
    const form = document.getElementById('autoGenForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Đang tạo...';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            data.rows = parseInt(data.rows);
            data.cols = parseInt(data.cols);
            
            try {
                if (!autoGenRoomId) throw new Error('Chưa chọn phòng.');
                const roomId = autoGenRoomId;
                await api(`/admin/rooms/${roomId}/seats/generate`, 'POST', data);
                message('Đã tạo lại sơ đồ ghế!');
                closeAutoGenModal();
                loadSeatMap(roomId);
            } catch (err) {
                message(err.message, true);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Tạo ghế';
            }
        });
    }
    
    // Auto gen modal close
    document.getElementById('closeAutoGenModal')?.addEventListener('click', closeAutoGenModal);
    document.getElementById('cancelAutoGenModal')?.addEventListener('click', closeAutoGenModal);
    document.getElementById('autoGenModal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeAutoGenModal();
    });
});

function closeAutoGenModal() {
    document.getElementById('autoGenModal').style.display = 'none';
    document.body.style.overflow = '';
    autoGenRoomId = null;
}

// Seat type radio handling
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[name="seatType"]').forEach(radio => {
        radio.addEventListener('change', async (e) => {
            selectedSeatType = e.target.value;
            // Đang chọn 1 ghế -> đổi loại ghế đó luôn
            if (selectedSeatId) {
                try {
                    await api(`/admin/seats/${selectedSeatId}`, 'PATCH', { loai_ghe: selectedSeatType });
                    message('Đã đổi loại ghế!');
                    selectedSeatId = null;
                    await loadSeatMap(currentRoomId);
                } catch (err) {
                    message(err.message, true);
                }
            }
        });
    });
    
    // Add seat button: thêm 1 ghế sau ghế cuối của hàng cuối, loại theo radio đang chọn
    document.getElementById('addSeatBtn')?.addEventListener('click', async () => {
        if (!currentRoomId) return;
        try {
            const seats = await api(`/admin/rooms/${currentRoomId}/seats`);
            let row = 'A';
            let number = 1;
            if (seats.length) {
                const sorted = [...seats].sort((a, b) => a.row.localeCompare(b.row) || a.number - b.number);
                const last = sorted[sorted.length - 1];
                row = last.row;
                number = last.number + 1;
            }
            await api(`/admin/rooms/${currentRoomId}/seats`, 'POST', {
                phong_id: currentRoomId,
                hang_ghe: row,
                so_thu_tu: number,
                loai_ghe: selectedSeatType,
            });
            message(`Đã thêm ghế ${row}${number} (${selectedSeatType})!`);
            loadSeatMap(currentRoomId);
        } catch (err) {
            message(err.message, true);
        }
    });
    
    // Auto gen seats button
    document.getElementById('autoGenSeatsBtn')?.addEventListener('click', () => {
        if (!currentRoomId) return;
        openAutoGenModal(currentRoomId);
    });
    
    // Back button
    document.getElementById('backToRoomsBtn')?.addEventListener('click', backToRooms);
});

function backToRooms() {
    document.getElementById('roomsTable').closest('article').style.display = 'block';
    document.getElementById('roomsEmptyState').style.display = 'block';
    document.getElementById('cinemaFilter').closest('.admin-filters').style.display = 'block';
    document.getElementById('seatMapSection').style.display = 'none';
    currentRoomId = null;
    currentRoomCinemaId = null;
    loadRoomsAdmin();
}

// Initialize rooms page
function initRoomsPage() {
    currentRoomsPage = 'rooms';
    
    document.getElementById('cinemaFilter')?.addEventListener('change', loadRoomsAdmin);
    
    loadRoomsAdmin();
}

// Auto-init if on rooms page
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('roomsTable')) {
        initRoomsPage();
    }
});

// ============================================
// CONCESSIONS MANAGEMENT
// ============================================

let currentConcessionsPage = 'concessions';

async function loadConcessionsAdmin() {
    if (currentConcessionsPage !== 'concessions') return;
    
    try {
        const concessions = await api('/admin/concessions');
        renderConcessionsTable(concessions);
    } catch (e) {
        message(e.message, true);
    }
}

function renderConcessionsTable(concessions) {
    const tbody = document.getElementById('concessionsBody');
    const emptyState = document.getElementById('emptyState');
    const table = document.getElementById('concessionsTable');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (!concessions || concessions.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    // Apply filters
    const typeFilterVal = typeFilter?.value;
    const statusFilterVal = statusFilter?.value;
    
    let filtered = concessions;
    
    if (typeFilterVal) filtered = filtered.filter(c => c.type === typeFilterVal);
    if (statusFilterVal !== '') filtered = filtered.filter(c => String(c.status) === statusFilterVal);
    
    if (filtered.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        emptyState.textContent = 'Không có món phù hợp với bộ lọc';
        return;
    }
    
    table.style.display = 'table';
    emptyState.style.display = 'none';
    
    const typeLabels = {
        'Bap': '<span style="background:#f59e0b; color:#451a03; padding:2px 8px; border-radius:4px; font-size:11px;">Bắp</span>',
        'Nuoc': '<span style="background:#3b82f6; color:#1e3a5f; padding:2px 8px; border-radius:4px; font-size:11px;">Nước</span>',
        'Combo': '<span style="background:#8b5cf6; color:#3b0764; padding:2px 8px; border-radius:4px; font-size:11px;">Combo</span>',
    };
    
    const statusLabels = {
        1: '<span style="background:#22c55e; color:#052e16; padding:2px 8px; border-radius:4px; font-size:11px;">Còn bán</span>',
        0: '<span style="background:#ef4444; color:#450a0a; padding:2px 8px; border-radius:4px; font-size:11px;">Hết hàng</span>',
    };
    
    tbody.innerHTML = filtered.map(c => `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:12px; font-family:monospace;">${c.id}</td>
            <td style="padding:12px;"><strong>${esc(c.name)}</strong></td>
            <td style="padding:12px;">${typeLabels[c.type] || c.type}</td>
            <td style="padding:12px; text-align:right;">${(c.price || 0).toLocaleString('vi-VN')} VNĐ</td>
            <td style="padding:12px;">${statusLabels[c.status] || c.status}</td>
            <td style="padding:12px;">
                <div style="display:flex; gap:8px;">
                    <button class="button edit-concession-btn" data-id="${c.id}" style="padding:6px 12px; font-size:12px;">Sửa</button>
                    <button class="button delete-concession-btn" data-id="${c.id}" data-name="${esc(c.name)}" style="padding:6px 12px; font-size:12px; background:#ef4444;">Xóa</button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Bind events
    tbody.querySelectorAll('.edit-concession-btn').forEach(btn => {
        btn.addEventListener('click', () => openConcessionModal(btn.dataset.id));
    });
    tbody.querySelectorAll('.delete-concession-btn').forEach(btn => {
        btn.addEventListener('click', () => confirmDeleteConcession(btn.dataset.id, btn.dataset.name));
    });
}

// Modal handling
let editingConcessionId = null;

function openConcessionModal(id = null) {
    editingConcessionId = id;
    const modal = document.getElementById('concessionModal');
    const form = document.getElementById('concessionForm');
    const title = document.getElementById('concessionModalTitle');
    const submitBtn = document.getElementById('concessionSubmitBtn');

    form.reset();
    document.getElementById('concessionId').value = '';
    
    if (id) {
        title.textContent = 'Sửa món';
        submitBtn.textContent = 'Cập nhật';
        loadConcessionForEdit(id);
    } else {
        title.textContent = 'Thêm món mới';
        submitBtn.textContent = 'Thêm';
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeConcessionModal() {
    document.getElementById('concessionModal').style.display = 'none';
    document.body.style.overflow = '';
    editingConcessionId = null;
}

async function loadConcessionForEdit(id) {
    try {
        const concession = await api(`/admin/concessions/${id}`);
        document.getElementById('concessionId').value = concession.id;
        document.getElementById('ten_mon').value = concession.name;
        document.getElementById('loai').value = concession.type;
        document.getElementById('don_gia').value = concession.price;
        document.getElementById('trang_thai').value = concession.status;
    } catch (e) {
        message(e.message, true);
        closeConcessionModal();
    }
}

// Form submit
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('concessionForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('concessionSubmitBtn');
            btn.disabled = true;
            btn.textContent = 'Đang lưu...';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Convert numeric fields
            data.don_gia = parseFloat(data.don_gia);
            data.trang_thai = parseInt(data.trang_thai);
            
            // Validate
            if (data.don_gia < 0) {
                message('Giá không được âm!', true);
                btn.disabled = false;
                btn.textContent = editingConcessionId ? 'Cập nhật' : 'Thêm';
                return;
            }
            
            try {
                if (editingConcessionId) {
                    await api(`/admin/concessions/${editingConcessionId}`, 'PATCH', data);
                    message('Cập nhật món thành công!');
                } else {
                    await api('/admin/concessions', 'POST', data);
                    message('Thêm món thành công!');
                }
                
                closeConcessionModal();
                loadConcessionsAdmin();
            } catch (err) {
                message(err.message, true);
            } finally {
                btn.disabled = false;
                btn.textContent = editingConcessionId ? 'Cập nhật' : 'Thêm';
            }
        });
    }
    
    // Modal close
    document.getElementById('closeConcessionModal')?.addEventListener('click', closeConcessionModal);
    document.getElementById('cancelConcessionModal')?.addEventListener('click', closeConcessionModal);
    document.getElementById('concessionModal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeConcessionModal();
    });
});

function closeConcessionModal() {
    document.getElementById('concessionModal').style.display = 'none';
    document.body.style.overflow = '';
    editingConcessionId = null;
}

// Filters
document.addEventListener('DOMContentLoaded', () => {
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    [typeFilter, statusFilter].forEach(el => {
        el?.addEventListener('change', loadConcessionsAdmin);
    });
});

// Delete confirm
function confirmDeleteConcession(id, name) {
    if (!confirm(`Xóa món "${name}"?`)) return;
    
    api(`/admin/concessions/${id}`, 'DELETE')
        .then(() => {
            message('Xóa món thành công!');
            loadConcessionsAdmin();
        })
        .catch(err => message(err.message, true));
}

// Initialize concessions page
function initConcessionsPage() {
    currentConcessionsPage = 'concessions';
    
    document.getElementById('addConcessionBtn')?.addEventListener('click', () => openConcessionModal());
    
    loadConcessionsAdmin();
}

// Auto-init if on concessions page
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('concessionsTable')) {
        initConcessionsPage();
    }
});

// ============================================
// BOOKINGS MANAGEMENT (ADMIN)
// ============================================

let currentBookingsPage = 'bookings';

async function loadBookingsAdmin() {
    if (currentBookingsPage !== 'bookings') return;
    
    const status = document.getElementById('statusFilter')?.value || '';
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    try {
        const bookings = await api(`/admin/bookings?${params.toString()}`);
        renderBookingsTable(bookings);
    } catch (e) {
        message(e.message, true);
    }
}

function renderBookingsTable(bookings) {
    const tbody = document.getElementById('bookingsBody');
    const emptyState = document.getElementById('bookingsEmptyState');
    const table = document.getElementById('bookingsTable');

    if (!tbody) return;

    if (!bookings || bookings.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    table.style.display = 'table';
    emptyState.style.display = 'none';

    tbody.innerHTML = bookings.map(b => {
        const showtime = new Date(b.showtime);
        const timeText = showtime.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})
            + ' - ' + showtime.toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit', year: 'numeric'});

        return `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:12px; color:#38bdf8; font-family:monospace; font-weight:700;">#${esc(b.code)}</td>
            <td style="padding:12px;"><strong>${esc(b.movie)}</strong></td>
            <td style="padding:12px; color:#94a3b8;">
                ${esc(b.cinema)}
                <br><span style="color:#7dd3fc; font-weight:700;">${timeText}</span>
            </td>
            <td><span class="seats-badge">${esc(b.seats || '—')}</span></td>
            <td style="padding:12px; color:#34d399; font-weight:900;">${(b.total || 0).toLocaleString('vi-VN')} VNĐ</td>
            <td style="padding:12px; text-align:center;">
                <button class="button btn-sm btn-delete delete-booking-btn" data-id="${b.id}" data-code="${esc(b.code)}">Xóa Đơn</button>
            </td>
        </tr>`;
    }).join('');

    // Bind events
    tbody.querySelectorAll('.delete-booking-btn').forEach(btn => {
        btn.addEventListener('click', () => confirmDeleteBooking(btn.dataset.id, btn.dataset.code));
    });
}

// Xóa đơn (nút "Xóa Đơn" ở ảnh 6.2.1): xóa hẳn đơn + vé + bắp nước
async function confirmDeleteBooking(id, code) {
    if (!confirm(`Xóa đơn #${code}? Hành động này không thể hoàn tác.`)) {
        return;
    }

    try {
        await api(`/admin/bookings/${id}`, 'DELETE');

        message('Đã xóa đơn hàng.');

        await loadBookingsAdmin();
        await loadDashboard();
    } catch (e) {
        message(e.message, true);
    }
}

// Filters
document.addEventListener('DOMContentLoaded', () => {
    const statusFilter = document.getElementById('statusFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const clearBtn = document.getElementById('clearFiltersBtn');
    
    [statusFilter, dateFrom, dateTo].forEach(el => {
        el?.addEventListener('change', loadBookingsAdmin);
    });
    
    clearBtn?.addEventListener('click', () => {
        document.getElementById('statusFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        loadBookingsAdmin();
    });
});

// Booking Detail Modal
function openBookingDetail(id) {
    const modal = document.getElementById('bookingDetailModal');
    const content = document.getElementById('bookingDetailContent');
    const title = document.getElementById('detailModalTitle');
    
    title.textContent = `Chi tiết đơn hàng #${id}`;
    content.innerHTML = '<div style="text-align:center; padding:40px; color:#94a3b8;">Đang tải...</div>';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    loadBookingDetail(id);
}

function closeDetailModal() {
    document.getElementById('bookingDetailModal').style.display = 'none';
    document.body.style.overflow = '';
}

async function loadBookingDetail(id) {
    try {
        const order = await api(`/admin/bookings/${id}`);
        renderBookingDetail(order);
    } catch (e) {
        document.getElementById('bookingDetailContent').innerHTML = 
            `<div style="text-align:center; padding:40px; color:#ef4444;">Lỗi: ${esc(e.message)}</div>`;
    }
}

function renderBookingDetail(order) {
    const content = document.getElementById('bookingDetailContent');
    const statusLabels = {
        'ChuaThanhToan': '<span style="background:#f59e0b; color:#451a03; padding:4px 8px; border-radius:4px;">Chưa thanh toán</span>',
        'DaThanhToan': '<span style="background:#22c55e; color:#052e16; padding:4px 8px; border-radius:4px;">Đã thanh toán</span>',
        'DaHuy': '<span style="background:#ef4444; color:#450a0a; padding:4px 8px; border-radius:4px;">Đã hủy</span>',
    };
    
    const paymentMethods = {
        'DEMO': 'Thanh toán Demo',
        'CASH': 'Tiền mặt',
        'CARD': 'Thẻ/Ví điện tử',
    };
    
    const formatDate = (dateStr) => new Date(dateStr).toLocaleString('vi-VN');
    
    content.innerHTML = `
        <div style="display:grid; gap:24px;">
            <!-- Order Info -->
            <article class="card" style="padding:20px;">
                <h3 style="margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #334155;">Thông tin đơn hàng</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px;">
                    <div><strong>Mã đơn:</strong> ${esc(order.code)}</div>
                    <div><strong>Trạng thái:</strong> ${statusLabels[order.payment_status] || order.payment_status}</div>
                    <div><strong>Phương thức:</strong> ${paymentMethods[order.payment_method] || order.payment_method}</div>
                    <div><strong>Ngày đặt:</strong> ${formatDate(order.created_at)}</div>
                    <div><strong>Khách hàng:</strong> ${esc(order.customer.name)}</div>
                    <div><strong>Email:</strong> ${esc(order.customer.email)}</div>
                    <div><strong>SĐT:</strong> ${esc(order.customer.phone || 'Chưa cung cấp')}</div>
                    <div><strong>Tổng tiền:</strong> <span style="color:#38bdf8; font-size:18px;">${(order.total || 0).toLocaleString('vi-VN')} VNĐ</span></div>
                </div>
            </article>
            
            <!-- Movie/Showtime Info -->
            <article class="card" style="padding:20px;">
                <h3 style="margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #334155;">Thông tin suất chiếu</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px;">
                    <div><strong>Phim:</strong> ${esc(order.movie)}</div>
                    <div><strong>Rạp:</strong> ${esc(order.cinema)}</div>
                    <div><strong>Phòng:</strong> ${esc(order.room)} (${esc(order.room_type || '')})</div>
                    <div><strong>Suất chiếu:</strong> ${formatDate(order.showtime.start)} - ${formatDate(order.showtime.end)}</div>
                </div>
            </article>
            
            <!-- Tickets -->
            <article class="card" style="padding:20px;">
                <h3 style="margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #334155;">Vé (${order.tickets?.length || 0})</h3>
                ${order.tickets && order.tickets.length > 0 ? `
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:1px solid #334155; text-align:left;">
                                    <th style="padding:12px;">Ghế</th>
                                    <th style="padding:12px;">Loại</th>
                                    <th style="padding:12px;">Giá vé</th>
                                    <th style="padding:12px;">Mã QR</th>
                                    <th style="padding:12px;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.tickets.map(t => `
                                    <tr style="border-bottom:1px solid #334155;">
                                        <td style="padding:12px;">${esc(t.seat.row)}${t.seat.number}</td>
                                        <td style="padding:12px;">${esc(t.seat.type)}</td>
                                        <td style="padding:12px; text-align:right;">${(t.price || 0).toLocaleString('vi-VN')} VNĐ</td>
                                        <td style="padding:12px; font-family:monospace; font-size:12px;">${esc(t.qr_code)}</td>
                                        <td style="padding:12px;">
                                            ${t.used_at ? '<span style="color:#22c55e;">Đã dùng</span>' : '<span style="color:#f59e0b;">Chưa dùng</span>'}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                ` : '<p style="color:#94a3b8;">Không có vé</p>'}
            </article>
            
            <!-- Concessions -->
            <article class="card" style="padding:20px;">
                <h3 style="margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #334155;">Bắp nước (${order.concessions?.length || 0})</h3>
                ${order.concessions && order.concessions.length > 0 ? `
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:1px solid #334155; text-align:left;">
                                    <th style="padding:12px;">Món</th>
                                    <th style="padding:12px;">Loại</th>
                                    <th style="padding:12px; text-align:center;">SL</th>
                                    <th style="padding:12px; text-align:right;">Đơn giá</th>
                                    <th style="padding:12px; text-align:right;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.concessions.map(c => `
                                    <tr style="border-bottom:1px solid #334155;">
                                        <td style="padding:12px;">${esc(c.ten_mon)}</td>
                                        <td style="padding:12px;">${esc(c.loai)}</td>
                                        <td style="padding:12px; text-align:center;">${c.so_luong}</td>
                                        <td style="padding:12px; text-align:right;">${(c.gia_tien || 0).toLocaleString('vi-VN')} VNĐ</td>
                                        <td style="padding:12px; text-align:right;">${((c.gia_tien || 0) * c.so_luong).toLocaleString('vi-VN')} VNĐ</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                ` : '<p style="color:#94a3b8;">Không có bắp nước</p>'}
            </article>
            
            <!-- Actions -->
            <div style="display:flex; gap:12px; justify-content:flex-end; padding-top:16px; border-top:1px solid #334155;">
                ${order.payment_status === 'ChuaThanhToan' ? `
                    <button class="button" onclick="adminConfirmBooking('${order.id}')" style="background:#22c55e;">Xác nhận thanh toán</button>
                    <button class="button" onclick="adminCancelBooking('${order.id}')" style="background:#ef4444;">Hủy đơn</button>
                ` : order.payment_status === 'DaThanhToan' ? `
                    <button class="button" onclick="adminCancelBooking('${order.id}')" style="background:#ef4444;">Hủy đơn</button>
                ` : ''}
                <button class="button" onclick="closeDetailModal()" style="background:transparent; color:#94a3b8;">Đóng</button>
            </div>
        </div>
    `;
}

function closeDetailModal() {
    document.getElementById('bookingDetailModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Admin actions
async function adminConfirmBooking(id) {
    if (!confirm('Xác nhận thanh toán cho đơn hàng này?')) return;
    
    try {
        await api(`/admin/bookings/${id}`, 'POST');
        message('Xác nhận thanh toán thành công!');
        closeDetailModal();
        loadBookingsAdmin();
    } catch (e) {
        message(e.message, true);
    }
}

async function adminCancelBooking(id) {
    if (!confirm('Hủy đơn hàng này? Sẽ giải phóng ghế và hoàn tiền (demo).')) return;
    
    try {
        await api(`/admin/bookings/${id}/cancel`, 'POST');
        message('Hủy đơn hàng thành công!');
        closeDetailModal();
        loadBookingsAdmin();
    } catch (e) {
        message(e.message, true);
    }
}

// Initialize bookings page
function initBookingsPage() {
    currentBookingsPage = 'bookings';
    
    // Đặt ngày mặc định khi còn ô lọc (trang bookings cũ); dashboard
    // theo 6.2.1 không có bộ lọc nên bỏ qua.
    const dateToEl = document.getElementById('dateTo');
    const dateFromEl = document.getElementById('dateFrom');

    if (dateToEl && dateFromEl) {
        const today = new Date();
        const monthAgo = new Date(today.getTime() - 30*24*60*60*1000);
        dateToEl.value = today.toISOString().split('T')[0];
        dateFromEl.value = monthAgo.toISOString().split('T')[0];
    }
    
    const statusFilter = document.getElementById('statusFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const clearBtn = document.getElementById('clearFiltersBtn');
    
    [statusFilter, dateFrom, dateTo].forEach(el => {
        el?.addEventListener('change', loadBookingsAdmin);
    });
    
    clearBtn?.addEventListener('click', () => {
        document.getElementById('statusFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        loadBookingsAdmin();
    });
    
    loadBookingsAdmin();
}

// Auto-init if on bookings page
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('bookingsTable')) {
        initBookingsPage();
    }
});

// ============================================
// REPORTS (ADMIN)
// ============================================
