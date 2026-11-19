// Admin Dashboard Functions
async function loadDashboard() {
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';
    
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    try {
        const [summary, byDate, topMovies] = await Promise.all([
            api(`/admin/reports?${params.toString()}`),
            api(`/admin/reports?${params.toString()}&by_date=1`),
            api(`/admin/reports?${params.toString()}&top_movies=1`)
        ]);
        
        renderSummary(summary);
        renderChart(byDate);
        renderTopMovies(topMovies);
    } catch (e) {
        message(e.message, true);
    }
}

function renderSummary(data) {
    document.getElementById('statOrders').textContent = data.order_count?.toLocaleString('vi-VN') || '0';
    document.getElementById('statTicketRev').textContent = (data.ticket_revenue || 0).toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('statConcessionRev').textContent = (data.concession_revenue || 0).toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('statTickets').textContent = data.ticket_count?.toLocaleString('vi-VN') || '0';
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

// Filter handlers
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('filterBtn')) {
        document.getElementById('filterBtn').addEventListener('click', loadDashboard);
        
        // Set default dates (last 7 days)
        const today = new Date();
        const weekAgo = new Date(today.getTime() - 7*24*60*60*1000);
        document.getElementById('dateTo').value = today.toISOString().split('T')[0];
        document.getElementById('dateFrom').value = weekAgo.toISOString().split('T')[0];
        
        loadDashboard();
    }
});

// Export for other pages
window.AdminDashboard = { loadDashboard, renderSummary, renderChart, renderTopMovies };

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
    const emptyState = document.getElementById('emptyState');
    const table = document.getElementById('moviesTable');
    
    if (!movies || movies.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    table.style.display = 'table';
    emptyState.style.display = 'none';
    
    const statusLabels = {
        'DANG_CHIEU': '<span style="background:#22c55e; color:#052e16; padding:4px 8px; border-radius:4px; font-size:11px;">Đang chiếu</span>',
        'SAP_CHIEU': '<span style="background:#3b82f6; color:#1e3a5f; padding:4px 8px; border-radius:4px; font-size:11px;">Sắp chiếu</span>',
        'NGUNG_CHIEU': '<span style="background:#ef4444; color:#450a0a; padding:4px 8px; border-radius:4px; font-size:11px;">Ngưng chiếu</span>',
    };
    
    tbody.innerHTML = movies.map(m => `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:12px; font-family:monospace;">${m.id}</td>
            <td style="padding:12px;">
                ${m.poster_url ? `<img src="${esc(m.poster_url)}" alt="" style="width:60px; height:90px; object-fit:cover; border-radius:4px;">` : '<span style="color:#94a3b8;">Không có</span>'}
            </td>
            <td style="padding:12px; max-width:200px;">
                <strong>${esc(m.title)}</strong>
            </td>
            <td style="padding:12px; color:#94a3b8; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${esc(m.genre)}</td>
            <td style="padding:12px;">${m.duration} phút</td>
            <td style="padding:12px;">${statusLabels[m.status] || m.status}</td>
            <td style="padding:12px;">
                <div style="display:flex; gap:8px;">
                    <button class="button edit-btn" data-id="${m.id}" style="padding:6px 12px; font-size:12px;">Sửa</button>
                    <button class="button delete-btn" data-id="${m.id}" data-title="${esc(m.title)}" style="padding:6px 12px; font-size:12px; background:#ef4444;">Xóa</button>
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
    
    if (id) {
        title.textContent = 'Sửa phim';
        submitBtn.textContent = 'Cập nhật';
        loadMovieForEdit(id);
    } else {
        title.textContent = 'Thêm phim mới';
        submitBtn.textContent = 'Thêm';
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeMovieModal() {
    document.getElementById('movieModal').style.display = 'none';
    document.body.style.overflow = '';
    editingMovieId = null;
}

async function loadMovieForEdit(id) {
    try {
        const movie = await api(`/admin/movies/${id}`);
        document.getElementById('movieId').value = movie.id;
        document.getElementById('ten_phim').value = movie.title;
        document.getElementById('the_loai').value = movie.genre;
        document.getElementById('thoi_luong').value = movie.duration;
        document.getElementById('trang_thai').value = movie.status;
        document.getElementById('poster_url').value = movie.poster_url || '';
        document.getElementById('trailer_url').value = movie.trailer_url || '';
        document.getElementById('mo_ta').value = movie.description || '';
        
        // Preview poster
        const preview = document.getElementById('posterPreview');
        if (movie.poster_url) {
            preview.innerHTML = `<img src="${esc(movie.poster_url)}" alt="Preview" style="max-height:150px; border-radius:4px;">`;
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
            btn.disabled = true;
            btn.textContent = 'Đang lưu...';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Convert numeric fields
            if (data.thoi_luong) data.thoi_luong = parseInt(data.thoi_luong);
            
            // Remove empty optional fields
            Object.keys(data).forEach(k => {
                if (data[k] === '' && !['ten_phim', 'the_loai', 'thoi_luong', 'trang_thai'].includes(k)) {
                    delete data[k];
                }
            });
            
            try {
                if (editingMovieId) {
                    await api(`/admin/movies/${editingMovieId}`, 'PATCH', data);
                    message('Cập nhật phim thành công!');
                } else {
                    await api('/admin/movies', 'POST', data);
                    message('Thêm phim thành công!');
                }
                
                closeMovieModal();
                loadMoviesAdmin();
            } catch (err) {
                message(err.message, true);
            } finally {
                btn.disabled = false;
                btn.textContent = editingMovieId ? 'Cập nhật' : 'Thêm';
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
    document.getElementById('cinemaFilter').closest('.filters').style.display = 'none';
    document.getElementById('addRoomBtn').style.display = 'none';
    document.getElementById('seatMapSection').style.display = 'block';
    
    document.getElementById('seatMapTitle').textContent = `Sơ đồ ghế - ${esc(roomName)}`;
    
    await loadSeatMap(roomId);
}

function backToRooms() {
    document.getElementById('roomsTable').closest('article').style.display = 'block';
    document.getElementById('roomsEmptyState').style.display = 'block';
    document.getElementById('cinemaFilter').closest('.filters').style.display = 'block';
    document.getElementById('addRoomBtn').style.display = 'inline-block';
    document.getElementById('seatMapSection').style.display = 'none';
    currentRoomId = null;
    currentRoomCinemaId = null;
}

async function loadSeatMap(roomId) {
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
    
    // Group by row
    const rows = {};
    seats.forEach(s => {
        if (!rows[s.row]) rows[s.row] = [];
        rows[s.row].push(s);
    });
    
    const rowLabels = Object.keys(rows).sort();
    
    let html = '';
    rowLabels.forEach(row => {
        const rowSeats = rows[row].sort((a, b) => a.number - b.number);
        html += `
            <div class="seat-row" style="display:flex; align-items:center; gap:6px; margin:8px 0; min-width:540px;">
                <b style="width:20px; text-align:right; color:#94a3b8;">${esc(row)}</b>
                ${rowSeats.map(s => `
                    <button type="button" class="seat ${s.type === 'VIP' ? 'vip' : ''} ${s.type === 'Couple' ? 'double' : ''} ${s.selected ? 'selected' : ''}" 
                            data-seat-id="${s.id}" data-row="${esc(row)}" data-number="${s.number}" data-type="${s.type}"
                            style="padding:0; width:${s.type === 'Couple' ? '76px' : '35px'}; height:32px; border:1px solid #475569; background:${s.type === 'VIP' ? '#312e81' : s.type === 'Couple' ? '#581c87' : '#1e293b'}; font-size:10px; flex-shrink:0;"
                            title="Ghế ${esc(row)}${s.number} (${esc(s.type)})">
                        ${esc(row)}${s.number}
                    </button>
                `).join('')}
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Bind seat click events
    container.querySelectorAll('.seat').forEach(btn => {
        btn.addEventListener('click', () => handleSeatClick(btn));
    });
}

let selectedSeatType = 'Thuong';

function handleSeatClick(btn) {
    const seatId = btn.dataset.seatId;
    const isSelected = btn.classList.contains('selected');
    
    if (isSelected) {
        // Deselect - delete seat
        if (!confirm('Xóa ghế này?')) return;
        deleteSeat(seatId);
    } else {
        // Select - change type or add new
        // For existing seats, allow type change
        // For empty spots, we don't have UI yet - just show info
        document.getElementById('selectedSeatInfo').textContent = 
            `Đã chọn: Ghế ${btn.dataset.row}${btn.dataset.number} (${btn.dataset.type})`;
    }
}

function deleteSeat(seatId) {
    api(`/admin/seats/${seatId}`, 'DELETE')
        .then(() => {
            message('Xóa ghế thành công!');
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
                // Call admin model method to auto generate seats
                // We'll need to add this endpoint or use a direct approach
                // For now, show message
                message('Chức năng đang phát triển. Hãy thêm ghế thủ công hoặc implement API auto-gen.');
                closeAutoGenModal();
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
        radio.addEventListener('change', (e) => {
            selectedSeatType = e.target.value;
        });
    });
    
    // Add seat button (placeholder)
    document.getElementById('addSeatBtn')?.addEventListener('click', () => {
        message('Chức năng thêm ghế thủ công đang phát triển. Hãy dùng "Tự động tạo ghế" hoặc implement thêm.');
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
    document.getElementById('cinemaFilter').closest('.filters').style.display = 'block';
    document.getElementById('addRoomBtn').style.display = 'inline-block';
    document.getElementById('seatMapSection').style.display = 'none';
    currentRoomId = null;
    currentRoomCinemaId = null;
    loadRoomsAdmin();
}

// Initialize rooms page
function initRoomsPage() {
    currentRoomsPage = 'rooms';
    
    document.getElementById('addRoomBtn')?.addEventListener('click', () => openRoomModal());
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
// SHOWTIMES MANAGEMENT
// ============================================

let currentShowtimesPage = 'showtimes';

async function loadShowtimesAdmin() {
    if (currentShowtimesPage !== 'showtimes') return;
    
    try {
        const [showtimes, movies, rooms] = await Promise.all([
            api('/admin/showtimes'),
            api('/admin/movies'),
            api('/admin/rooms')
        ]);
        
        // Populate filters
        const movieFilter = document.getElementById('movieFilter');
        const roomFilter = document.getElementById('roomFilter');
        
        if (movieFilter) {
            movieFilter.innerHTML = '<option value="">Tất cả phim</option>' + 
                movies.map(m => `<option value="${m.id}">${esc(m.title)}</option>`).join('');
        }
        
        if (roomFilter) {
            roomFilter.innerHTML = '<option value="">Tất cả phòng</option>' + 
                rooms.map(r => `<option value="${r.id}">${esc(r.cinema_name)} - ${esc(r.name)} (${esc(r.type)})</option>`).join('');
        }
        
        // Populate modal selects
        const modalMovieSelect = document.getElementById('showtimeMovieId');
        const modalRoomSelect = document.getElementById('showtimeRoomId');
        
        if (modalMovieSelect) {
            modalMovieSelect.innerHTML = movies.map(m => `<option value="${m.id}">${esc(m.title)} (${m.duration} phút)</option>`).join('');
        }
        
        if (modalRoomSelect) {
            modalRoomSelect.innerHTML = rooms.map(r => `<option value="${r.id}">${esc(r.cinema_name)} - ${esc(r.name)} (${esc(r.type)})</option>`).join('');
        }
        
        renderShowtimesTable(showtimes);
    } catch (e) {
        message(e.message, true);
    }
}

function renderShowtimesTable(showtimes) {
    const tbody = document.getElementById('showtimesBody');
    const emptyState = document.getElementById('emptyState');
    const table = document.getElementById('showtimesTable');
    const movieFilter = document.getElementById('movieFilter');
    const roomFilter = document.getElementById('roomFilter');
    const dateFilter = document.getElementById('dateFilter');
    
    if (!showtimes || showtimes.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    // Apply filters
    const movieId = movieFilter?.value;
    const roomId = roomFilter?.value;
    const date = dateFilter?.value;
    
    let filtered = showtimes;
    
    if (movieId) filtered = filtered.filter(s => s.movie_id == movieId);
    if (roomId) filtered = filtered.filter(s => s.room_id == roomId);
    if (date) filtered = filtered.filter(s => s.start_time.startsWith(date));
    
    if (filtered.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        emptyState.textContent = 'Không có suất chiếu phù hợp với bộ lọc';
        return;
    }
    
    table.style.display = 'table';
    emptyState.style.display = 'none';
    
    tbody.innerHTML = filtered.map(s => {
        const start = new Date(s.start_time);
        const end = new Date(s.end_time);
        
        return `
            <tr style="border-bottom:1px solid #334155;">
                <td style="padding:12px; font-family:monospace;">${s.id}</td>
                <td style="padding:12px;"><strong>${esc(s.movie_title)}</strong></td>
                <td style="padding:12px;">
                    ${esc(s.cinema_name)} / ${esc(s.room_name)}
                    <br><span style="color:#94a3b8; font-size:12px;">${esc(s.type || '')}</span>
                </td>
                <td style="padding:12px;">
                    ${start.toLocaleDateString('vi-VN')} ${start.toLocaleTimeString('vi-VN', {hour:'2-digit',minute:'2-digit'})}
                </td>
                <td style="padding:12px;">
                    ${end.toLocaleDateString('vi-VN')} ${end.toLocaleTimeString('vi-VN', {hour:'2-digit',minute:'2-digit'})}
                </td>
                <td style="padding:12px; text-align:right;">
                    ${(s.base_price || 0).toLocaleString('vi-VN')} VNĐ
                </td>
                <td style="padding:12px;">
                    <div style="display:flex; gap:8px;">
                        <button class="button edit-showtime-btn" data-id="${s.id}" style="padding:6px 12px; font-size:12px;">Sửa</button>
                        <button class="button delete-showtime-btn" data-id="${s.id}" data-movie="${esc(s.movie_title)}" style="padding:6px 12px; font-size:12px; background:#ef4444;">Xóa</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    
    // Bind events
    tbody.querySelectorAll('.edit-showtime-btn').forEach(btn => {
        btn.addEventListener('click', () => openShowtimeModal(btn.dataset.id));
    });
    tbody.querySelectorAll('.delete-showtime-btn').forEach(btn => {
        btn.addEventListener('click', () => confirmDeleteShowtime(btn.dataset.id, btn.dataset.movie));
    });
}

// Filters
document.addEventListener('DOMContentLoaded', () => {
    const movieFilter = document.getElementById('movieFilter');
    const roomFilter = document.getElementById('roomFilter');
    const dateFilter = document.getElementById('dateFilter');
    const clearBtn = document.getElementById('clearFiltersBtn');
    
    [movieFilter, roomFilter, dateFilter].forEach(el => {
        el?.addEventListener('change', () => {
            // Reload with filters via API params
            loadShowtimesAdminWithFilters();
        });
    });
    
    clearBtn?.addEventListener('click', () => {
        document.getElementById('movieFilter').value = '';
        document.getElementById('roomFilter').value = '';
        document.getElementById('dateFilter').value = '';
        loadShowtimesAdmin();
    });
});

async function loadShowtimesAdminWithFilters() {
    if (currentShowtimesPage !== 'showtimes') return;
    
    const movieId = document.getElementById('movieFilter')?.value;
    const roomId = document.getElementById('roomFilter')?.value;
    const date = document.getElementById('dateFilter')?.value;
    
    const params = new URLSearchParams();
    if (movieId) params.append('movie_id', movieId);
    if (roomId) params.append('room_id', roomId);
    if (date) params.append('date', date);
    
    try {
        const showtimes = await api(`/admin/showtimes?${params.toString()}`);
        renderShowtimesTable(showtimes);
    } catch (e) {
        message(e.message, true);
    }
}

// Modal handling
let editingShowtimeId = null;

function openShowtimeModal(id = null) {
    editingShowtimeId = id;
    const modal = document.getElementById('showtimeModal');
    const form = document.getElementById('showtimeForm');
    const title = document.getElementById('showtimeModalTitle');
    const submitBtn = document.getElementById('showtimeSubmitBtn');
    
    form.reset();
    document.getElementById('showtimeId').value = '';
    
    // Set default datetime to next hour
    const now = new Date();
    now.setMinutes(0, 0, 0);
    now.setHours(now.getHours() + 1);
    const defaultStart = now.toISOString().slice(0, 16);
    now.setHours(now.getHours() + 2);
    const defaultEnd = now.toISOString().slice(0, 16);
    
    document.getElementById('thoi_gian_bat_dau').value = defaultStart;
    document.getElementById('thoi_gian_ket_thuc').value = defaultEnd;
    
    if (id) {
        title.textContent = 'Sửa suất chiếu';
        submitBtn.textContent = 'Cập nhật';
        loadShowtimeForEdit(id);
    } else {
        title.textContent = 'Thêm suất chiếu';
        submitBtn.textContent = 'Thêm';
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeShowtimeModal() {
    document.getElementById('showtimeModal').style.display = 'none';
    document.body.style.overflow = '';
    editingShowtimeId = null;
}

async function loadShowtimeForEdit(id) {
    try {
        const showtimes = await api('/admin/showtimes');
        const showtime = showtimes.find(s => s.id == id);
        
        if (!showtime) throw new Error('Không tìm thấy suất chiếu');
        
        document.getElementById('showtimeId').value = showtime.id;
        document.getElementById('showtimeMovieId').value = showtime.movie_id;
        document.getElementById('showtimeRoomId').value = showtime.room_id;
        document.getElementById('thoi_gian_bat_dau').value = showtime.start_time.slice(0, 16);
        document.getElementById('thoi_gian_ket_thuc').value = showtime.end_time.slice(0, 16);
        document.getElementById('gia_ve_co_ban').value = showtime.base_price;
    } catch (e) {
        message(e.message, true);
        closeShowtimeModal();
    }
}

// Form submit
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('showtimeForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('showtimeSubmitBtn');
            btn.disabled = true;
            btn.textContent = 'Đang lưu...';
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Convert numeric fields
            data.phim_id = parseInt(data.phim_id);
            data.phong_id = parseInt(data.phong_id);
            data.gia_ve_co_ban = parseFloat(data.gia_ve_co_ban);
            
            // Validate times
            const start = new Date(data.thoi_gian_bat_dau);
            const end = new Date(data.thoi_gian_ket_thuc);
            
            if (end <= start) {
                message('Thời gian kết thúc phải sau thời gian bắt đầu!', true);
                btn.disabled = false;
                btn.textContent = editingShowtimeId ? 'Cập nhật' : 'Thêm';
                return;
            }
            
            if (data.gia_ve_co_ban < 0) {
                message('Giá vé không được âm!', true);
                btn.disabled = false;
                btn.textContent = editingShowtimeId ? 'Cập nhật' : 'Thêm';
                return;
            }
            
            try {
                if (editingShowtimeId) {
                    await api(`/admin/showtimes/${editingShowtimeId}`, 'PATCH', data);
                    message('Cập nhật suất chiếu thành công!');
                } else {
                    await api('/admin/showtimes', 'POST', data);
                    message('Thêm suất chiếu thành công!');
                }
                
                closeShowtimeModal();
                loadShowtimesAdmin();
            } catch (err) {
                // Handle conflict error specifically
                if (err.message.includes('trùng lịch')) {
                    message('Suất chiếu bị trùng lịch với suất khác trong cùng phòng!', true);
                } else {
                    message(err.message, true);
                }
            } finally {
                btn.disabled = false;
                btn.textContent = editingShowtimeId ? 'Cập nhật' : 'Thêm';
            }
        });
    }
    
    // Modal close
    document.getElementById('closeShowtimeModal')?.addEventListener('click', closeShowtimeModal);
    document.getElementById('cancelShowtimeModal')?.addEventListener('click', closeShowtimeModal);
    document.getElementById('showtimeModal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) closeShowtimeModal();
    });
});

function closeShowtimeModal() {
    document.getElementById('showtimeModal').style.display = 'none';
    document.body.style.overflow = '';
    editingShowtimeId = null;
}

function confirmDeleteShowtime(id, movieTitle) {
    if (!confirm(`Xóa suất chiếu của phim "${movieTitle}"?`)) return;
    
    api(`/admin/showtimes/${id}`, 'DELETE')
        .then(() => {
            message('Xóa suất chiếu thành công!');
            loadShowtimesAdmin();
        })
        .catch(err => message(err.message, true));
}

// Initialize showtimes page
function initShowtimesPage() {
    currentShowtimesPage = 'showtimes';
    
    document.getElementById('addShowtimeBtn')?.addEventListener('click', () => openShowtimeModal());
    document.getElementById('clearFiltersBtn')?.addEventListener('click', () => {
        document.getElementById('movieFilter').value = '';
        document.getElementById('roomFilter').value = '';
        document.getElementById('dateFilter').value = '';
        loadShowtimesAdmin();
    });
    
    loadShowtimesAdmin();
}

// Auto-init if on showtimes page
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('showtimesTable')) {
        initShowtimesPage();
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
            <td style="padding:12px; text-align:center;">
                ${c.image ? `<img src="${esc(c.image)}" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">` : '<span style="color:#94a3b8;">Không có</span>'}
            </td>
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
    const preview = document.getElementById('concessionImagePreview');
    
    form.reset();
    preview.innerHTML = '';
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
        document.getElementById('hinh_anh').value = concession.image || '';
        document.getElementById('trang_thai').value = concession.status;
        
        // Preview image
        const preview = document.getElementById('concessionImagePreview');
        if (concession.image) {
            preview.innerHTML = `<img src="${esc(concession.image)}" alt="Preview" style="max-height:150px; border-radius:4px;">`;
        }
    } catch (e) {
        message(e.message, true);
        closeConcessionModal();
    }
}

// Image preview on URL change
document.addEventListener('DOMContentLoaded', () => {
    const imageInput = document.getElementById('hinh_anh');
    if (imageInput) {
        imageInput.addEventListener('input', (e) => {
            const preview = document.getElementById('concessionImagePreview');
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
            
            // Clean empty optional fields
            if (!data.hinh_anh) delete data.hinh_anh;
            
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

// Image preview on URL change
document.addEventListener('DOMContentLoaded', () => {
    const imageInput = document.getElementById('hinh_anh');
    if (imageInput) {
        imageInput.addEventListener('input', (e) => {
            const preview = document.getElementById('concessionImagePreview');
            if (e.target.value) {
                preview.innerHTML = `<img src="${esc(e.target.value)}" alt="Preview" style="max-height:150px; border-radius:4px;">`;
            } else {
                preview.innerHTML = '';
            }
        });
    }
});

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
    const emptyState = document.getElementById('emptyState');
    const table = document.getElementById('bookingsTable');
    
    if (!bookings || bookings.length === 0) {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    table.style.display = 'table';
    emptyState.style.display = 'none';
    
    const statusLabels = {
        'ChuaThanhToan': '<span style="background:#f59e0b; color:#451a03; padding:4px 8px; border-radius:4px; font-size:11px;">Chưa TT</span>',
        'DaThanhToan': '<span style="background:#22c55e; color:#052e16; padding:4px 8px; border-radius:4px; font-size:11px;">Đã TT</span>',
        'DaHuy': '<span style="background:#ef4444; color:#450a0a; padding:4px 8px; border-radius:4px; font-size:11px;">Đã hủy</span>',
    };
    
    tbody.innerHTML = bookings.map(b => `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:12px; font-family:monospace;">${b.id}</td>
            <td style="padding:12px; font-family:monospace;">${esc(b.code)}</td>
            <td style="padding:12px;">
                <strong>${esc(b.customer_name)}</strong>
                <br><span style="color:#94a3b8; font-size:12px;">${esc(b.customer_email)}</span>
            </td>
            <td style="padding:12px;">${esc(b.movie)}</td>
            <td style="padding:12px;">
                ${new Date(b.showtime).toLocaleString('vi-VN', {day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'})}
                <br><span style="color:#94a3b8; font-size:12px;">${esc(b.room)} - ${esc(b.cinema)}</span>
            </td>
            <td style="padding:12px; text-align:right;">${(b.total || 0).toLocaleString('vi-VN')} VNĐ</td>
            <td style="padding:12px;">${statusLabels[b.payment_status] || b.payment_status}</td>
            <td style="padding:12px;">
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button class="button view-detail-btn" data-id="${b.id}" style="padding:6px 12px; font-size:12px;">Xem chi tiết</button>
                    ${b.payment_status === 'ChuaThanhToan' ? `
                        <button class="button confirm-booking-btn" data-id="${b.id}" style="padding:6px 12px; font-size:12px; background:#22c55e;">Xác nhận TT</button>
                        <button class="button cancel-booking-btn" data-id="${b.id}" style="padding:6px 12px; font-size:12px; background:#ef4444;">Hủy</button>
                    ` : b.payment_status === 'DaThanhToan' ? `
                        <button class="button cancel-booking-btn" data-id="${b.id}" style="padding:6px 12px; font-size:12px; background:#ef4444;">Hủy</button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
    
    // Bind events
    tbody.querySelectorAll('.view-detail-btn').forEach(btn => {
        btn.addEventListener('click', () => openBookingDetail(btn.dataset.id));
    });
    tbody.querySelectorAll('.confirm-booking-btn').forEach(btn => {
        btn.addEventListener('click', () => confirmBooking(btn.dataset.id));
    });
    tbody.querySelectorAll('.cancel-booking-btn').forEach(btn => {
        btn.addEventListener('click', () => cancelBooking(btn.dataset.id));
    });
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
    
    // Set default dates (last 30 days)
    const today = new Date();
    const monthAgo = new Date(today.getTime() - 30*24*60*60*1000);
    document.getElementById('dateTo').value = today.toISOString().split('T')[0];
    document.getElementById('dateFrom').value = monthAgo.toISOString().split('T')[0];
    
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

let reportsDateFrom = '';
let reportsDateTo = '';

let dailyRevenueChart = null;
let topMoviesChart = null;

async function loadReports() {
    const dateFrom = document.getElementById('reportDateFrom')?.value || '';
    const dateTo = document.getElementById('reportDateTo')?.value || '';
    
    reportsDateFrom = dateFrom;
    reportsDateTo = dateTo;
    
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    try {
        const [summary, byDate, topMovies] = await Promise.all([
            api(`/admin/reports?${params.toString()}`),
            api(`/admin/reports?${params.toString()}&by_date=1`),
            api(`/admin/reports?${params.toString()}&top_movies=1`)
        ]);
        
        renderSummary(summary);
        renderDailyRevenueChart(summary.by_date || []);
        renderTopMoviesChart(summary.top_movies || []);
        renderDailyRevenueTable(summary.by_date || []);
        renderTopMoviesTable(summary.top_movies || []);
    } catch (e) {
        message(e.message, true);
    }
}

function renderSummary(data) {
    document.getElementById('totalRevenue').textContent = (data.total_revenue || 0).toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('ticketRevenue').textContent = (data.ticket_revenue || 0).toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('concessionRevenue').textContent = (data.concession_revenue || 0).toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('orderCount').textContent = (data.order_count || 0).toLocaleString('vi-VN');
    document.getElementById('ticketCount').textContent = (data.ticket_count || 0).toLocaleString('vi-VN');
}

// Daily Revenue Chart
function renderDailyRevenueChart(data) {
    const ctx = document.getElementById('dailyRevenueChart');
    if (!ctx) return;
    
    const labels = data.map(d => new Date(d.date).toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit'}));
    const ticketRev = data.map(d => (d.ticket_revenue || 0) / 1000);
    const concessionRev = data.map(d => (d.concession_revenue || 0) / 1000);
    const totalRev = data.map(d => (d.total_revenue || 0) / 1000);
    
    if (dailyRevenueChart) dailyRevenueChart.destroy();
    
    dailyRevenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                { 
                    label: 'Doanh thu vé (nghìn VNĐ)', 
                    data: ticketRev, 
                    borderColor: '#3b82f6', 
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.3
                },
                { 
                    label: 'Doanh thu bắp (nghìn VNĐ)', 
                    data: concessionRev, 
                    borderColor: '#f59e0b', 
                    backgroundColor: 'rgba(245,158,11,0.1)',
                    fill: true,
                    tension: 0.3
                },
                { 
                    label: 'Tổng doanh thu (nghìn VNĐ)', 
                    data: totalRev, 
                    borderColor: '#22c55e', 
                    backgroundColor: 'rgba(34,197,94,0.1)',
                    fill: true,
                    tension: 0.3,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { 
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${ctx.raw.toLocaleString('vi-VN')}k VNĐ`
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { callback: v => v + 'k' }
                }
            }
        }
    });
}

// Top Movies Chart
function renderTopMoviesChart(data) {
    const ctx = document.getElementById('topMoviesChart');
    if (!ctx) return;
    
    const labels = data.map(m => m.ten_phim.length > 20 ? m.ten_phim.substring(0, 20) + '...' : m.ten_phim);
    const revenue = data.map(m => (m.revenue || 0) / 1000);
    const tickets = data.map(m => m.tickets);
    
    if (topMoviesChart) topMoviesChart.destroy();
    
    topMoviesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { 
                    label: 'Doanh thu (nghìn VNĐ)', 
                    data: revenue, 
                    backgroundColor: 'rgba(59,130,246,0.8)',
                    borderRadius: 4
                },
                { 
                    label: 'Số vé', 
                    data: tickets, 
                    backgroundColor: 'rgba(34,197,94,0.8)',
                    borderRadius: 4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v + 'k' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
            }
        }
    });
}

function renderDailyRevenueTable(data) {
    const tbody = document.getElementById('dailyRevenueBody');
    if (!tbody) return;
    
    tbody.innerHTML = data.map(d => `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:10px;">${new Date(d.date).toLocaleDateString('vi-VN')}</td>
            <td style="padding:10px; text-align:right;">${d.orders || 0}</td>
            <td style="padding:10px; text-align:right;">${d.tickets || 0}</td>
            <td style="padding:10px; text-align:right;">${(d.ticket_revenue || 0).toLocaleString('vi-VN')}</td>
            <td style="padding:10px; text-align:right;">${(d.concession_revenue || 0).toLocaleString('vi-VN')}</td>
            <td style="padding:10px; text-align:right; font-weight:bold;">${(d.total_revenue || 0).toLocaleString('vi-VN')}</td>
        </tr>
    `).join('') || '<tr><td colspan="6" style="padding:20px; text-align:center; color:#94a3b8;">Chưa có dữ liệu</td></tr>';
}

function renderTopMoviesTable(data) {
    const tbody = document.getElementById('topMoviesBody');
    if (!tbody) return;
    
    tbody.innerHTML = (data || []).map((m, i) => `
        <tr style="border-bottom:1px solid #334155;">
            <td style="padding:10px;">${i+1}. ${esc(m.ten_phim)}</td>
            <td style="padding:10px; text-align:center;">${m.orders}</td>
            <td style="padding:10px; text-align:center;">${m.tickets}</td>
            <td style="padding:10px; text-align:right;">${(m.revenue || 0).toLocaleString('vi-VN')}</td>
        </tr>
    `).join('') || '<tr><td colspan="4" style="padding:20px; text-align:center; color:#94a3b8;">Chưa có dữ liệu</td></tr>';
}

// CSV Export
function exportReportCSV() {
    const dateFrom = document.getElementById('reportDateFrom')?.value || '';
    const dateTo = document.getElementById('reportDateTo')?.value || '';
    
    // Fetch data again for export
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    params.append('by_date', '1');
    params.append('top_movies', '1');
    
    fetch(`/api/index.php?route=/admin/reports&${params.toString()}`)
        .then(r => r.json())
        .then(res => {
            if (!res.ok) throw new Error(res.error?.message || 'Lỗi tải dữ liệu');
            
            const { by_date, top_movies } = res.data;
            
            // Create CSV
            let csv = 'BÁO CÁO DOANH THU CINESTAR\n\n';
            csv += `Từ ngày: ${document.getElementById('reportDateFrom').value || 'Đầu'}\n`;
            csv += `Đến ngày: ${document.getElementById('reportDateTo').value || 'Hiện tại'}\n\n`;
            
            csv += 'DOANH THU THEO NGÀY\n';
            csv += 'Ngày,Đơn,Vé,Doanh thu vé (VNĐ),Doanh thu bắp (VNĐ),Tổng doanh thu (VNĐ)\n';
            (by_date || []).forEach(d => {
                csv += `${d.date},${d.orders || 0},${d.tickets || 0},${d.ticket_revenue || 0},${d.concession_revenue || 0},${d.total_revenue || 0}\n`;
            });
            
            csv += '\nTOP PHIM BÁN CHẠY\n';
            csv += 'Thứ tự,Phim,Đơn,Vé,Doanh thu (VNĐ)\n';
            (top_movies || []).forEach((m, i) => {
                csv += `${i+1},"${m.ten_phim}",${m.orders},${m.tickets},${m.revenue || 0}\n`;
            });
            
            // Download
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `cinestar_report_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
        })
        .catch(e => message(e.message, true));
}

// Initialize reports page
function initReportsPage() {
    // Set default dates (last 30 days)
    const today = new Date();
    const monthAgo = new Date(today.getTime() - 30*24*60*60*1000);
    document.getElementById('reportDateTo').value = today.toISOString().split('T')[0];
    document.getElementById('reportDateFrom').value = monthAgo.toISOString().split('T')[0];
    
    document.getElementById('filterReportBtn')?.addEventListener('click', loadReports);
    document.getElementById('exportCsvBtn')?.addEventListener('click', exportReportCSV);
    
    loadReports();
}

// Auto-init if on reports page
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('dailyRevenueChart')) {
        initReportsPage();
    }
});
