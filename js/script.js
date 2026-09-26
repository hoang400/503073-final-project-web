const nowShowingMovies = [
  { title: 'Những Kẻ Sống Sót', year: 2024, rating: '8.6', genre: 'Chính kịch', image: 'https://images.unsplash.com/photo-1518709594023-6eab9bab7b23?auto=format&fit=crop&w=800&q=85' },
  { title: 'Đường Chân Trời', year: 2024, rating: '7.9', genre: 'Phiêu lưu', image: 'https://images.unsplash.com/photo-1498036882173-b41c28a8ba34?auto=format&fit=crop&w=800&q=85' },
  { title: 'Mật Mã Đỏ', year: 2024, rating: '8.1', genre: 'Hành động', image: 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=800&q=85' },
  { title: 'Lời Thì Thầm', year: 2023, rating: '7.6', genre: 'Tâm lý', image: 'https://images.unsplash.com/photo-1518173946687-a4c8892bbd9f?auto=format&fit=crop&w=800&q=85' },
  { title: 'Khúc Ca Mùa Hạ', year: 2024, rating: '8.0', genre: 'Lãng mạn', image: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=85' }
];

const trendingMovies = [
  { title: 'Bên Kia Bầu Trời', year: 2024, rating: '8.8', genre: 'Khoa học viễn tưởng', trend: '01', image: 'https://images.unsplash.com/photo-1444723121867-7a241cacace9?auto=format&fit=crop&w=800&q=85' },
  { title: 'Kẻ Đánh Cắp Giấc Mơ', year: 2023, rating: '8.4', genre: 'Bí ẩn', trend: '02', image: 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=800&q=85' },
  { title: 'Đèn Trong Sương', year: 2024, rating: '7.8', genre: 'Kinh dị', trend: '03', image: 'https://images.unsplash.com/photo-1519608487953-e999c86e7454?auto=format&fit=crop&w=800&q=85' },
  { title: 'Sóng Ngầm', year: 2022, rating: '8.2', genre: 'Tội phạm', trend: '04', image: 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=800&q=85' }
];


function createMovieCardHTML(movie) {
  const trendBadge = movie.trend 
    ? `<span class="movie-trend-number">${movie.trend}</span>` 
    : '';

  return `
    <article class="movie-card" tabindex="0" aria-label="${movie.title}, ${movie.year}">
      <div class="movie-poster-wrap">
        <img src="${movie.image}" alt="Poster phim ${movie.title}" class="movie-poster" loading="lazy" />
        <div class="movie-poster-overlay"></div>
        ${trendBadge}
        <span class="movie-rating-badge"><span>★</span>${movie.rating}</span>
        <button class="movie-add-btn" aria-label="Thêm ${movie.title} vào danh sách">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 5v14M5 12h14"/>
          </svg>
        </button>
      </div>
      <div class="movie-info">
        <h3 class="movie-title">${movie.title}</h3>
        <p class="movie-sub">${movie.year} <span class="separator">/</span> ${movie.genre}</p>
      </div>
    </article>
  `;
}

// Render dữ liệu vào DOM
document.addEventListener('DOMContentLoaded', () => {
  const nowShowingContainer = document.getElementById('now-showing-grid');
  const trendingContainer = document.getElementById('trending-grid');
  const catalogContainer = document.getElementById('catalog-movie-grid');
  const emptyState = document.getElementById('empty-state');

  const desktopSearch = document.getElementById('desktop-search-input');
  const mobileSearch = document.getElementById('mobile-search-input');
  const genreFilter = document.getElementById('genre-filter');
  const yearFilter = document.getElementById('year-filter');
  const sortFilter = document.getElementById('sort-filter');

  const menuBtn = document.getElementById('menu-toggle-btn');
  const mobileMenu = document.getElementById('mobile-menu');

  // Toggle Menu Mobile
  menuBtn.addEventListener('click', () => {
    const isOpen = mobileMenu.classList.toggle('open');
    menuBtn.setAttribute('aria-expanded', isOpen);
  });

  // Render mục Phim Đang Chiếu
  if (nowShowingContainer) {
    nowShowingContainer.innerHTML = nowShowingMovies.map(createMovieCardHTML).join('');
  }

  // Render mục Phim Thịnh Hành
  if (trendingContainer) {
    trendingContainer.innerHTML = trendingMovies.map(createMovieCardHTML).join('');
  }

  // Danh sách tổng để lọc
  const allMovies = [...nowShowingMovies, ...trendingMovies];

  // Hàm xử lý lọc phim theo bộ lọc và ô tìm kiếm
  function filterAndRenderCatalog() {
    const query = (desktopSearch?.value || mobileSearch?.value || '').toLowerCase().trim();
    const selectedGenre = genreFilter?.value || 'Tất cả thể loại';
    const selectedYear = yearFilter?.value || 'Tất cả năm';
    const selectedSort = sortFilter?.value || 'Phổ biến nhất';

    let result = allMovies.filter(movie => {
      const matchQuery = movie.title.toLowerCase().includes(query);
      const matchGenre = selectedGenre === 'Tất cả thể loại' || movie.genre === selectedGenre;
      const matchYear = selectedYear === 'Tất cả năm' || String(movie.year) === selectedYear;
      return matchQuery && matchGenre && matchYear;
    });

    if (selectedSort === 'Điểm đánh giá') {
      result.sort((a, b) => Number(b.rating) - Number(a.rating));
    }

    if (result.length === 0) {
      catalogContainer.innerHTML = '';
      emptyState.style.display = 'block';
    } else {
      emptyState.style.display = 'none';
      // Lấy tối đa 5 phim như logic trong App.tsx ban đầu
      catalogContainer.innerHTML = result.slice(0, 5).map(createMovieCardHTML).join('');
    }
  }

  // Đồng bộ ô search giữa Desktop và Mobile
  desktopSearch?.addEventListener('input', (e) => {
    if (mobileSearch) mobileSearch.value = e.target.value;
    filterAndRenderCatalog();
  });

  mobileSearch?.addEventListener('input', (e) => {
    if (desktopSearch) desktopSearch.value = e.target.value;
    filterAndRenderCatalog();
  });

  genreFilter?.addEventListener('change', filterAndRenderCatalog);
  yearFilter?.addEventListener('change', filterAndRenderCatalog);
  sortFilter?.addEventListener('change', filterAndRenderCatalog);

  // Render ban đầu cho mục tìm kiếm
  filterAndRenderCatalog();
});