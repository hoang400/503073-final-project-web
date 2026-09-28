
// 1. DỮ LIỆU CHUẨN 4 PHIM ĐANG CHIẾU
const MOVIES = [
    {
        id: 'godzilla-kong',
        title: 'Godzilla x Kong: Đế Chế Mới (Godzilla x Kong: The New Empire)',
        shortTitle: 'Godzilla x Kong',
        dropdownTitle: 'Godzilla x Kong: Đế Chế Mới (2D Vietsub)',
        ageTag: 'C13',
        ageDesc: 'C13 - Khán giả trên 13 tuổi',
        imdb: '7.4/10',
        duration: '115 phút',
        genres: ['Hành Động', 'Viễn Tưởng', 'Phiêu Lưu'],
        director: 'Adam Wingard',
        writers: 'Terry Rossio, Simon Barrett, Jeremy Slater',
        stars: 'Rebecca Hall, Brian Tyree Henry, Dan Stevens',
        overviewP1: 'Trận chiến hoành tráng giữa các Titan tiếp tục bùng nổ! Sau những sự kiện đối đầu nảy lửa trước đó, Godzilla và Kong đối mặt với một mối đe dọa sinh tồn hoàn toàn mới, khổng lồ và tàn bạo hơn bao giờ hết ẩn giấu sâu bên trong Trái Đất Rỗng (Hollow Earth). Sinh vật cổ đại Skar King cùng đội quân quái thú hung hãn đe dọa hủy diệt toàn bộ hệ sinh thái thế giới loài người.',
        overviewP2: 'Để bảo vệ sự sống còn của Trái Đất, hai huyền thoại Godzilla và Kong buộc phải gạt bỏ hiềm khích cũ, hợp sức chiến đấu bên cạnh những trang bị công nghệ đỉnh cao. Tác phẩm mang đến những pha giao tranh hoành tráng, mãn nhãn cùng kỹ xảo đỉnh cao của điện ảnh Hollywood.',
        poster: 'https://image.tmdb.org/t/p/w780/z1p34vh7dEOnLDmyCrlUVLuoDzd.jpg',
        youtubeId: 'lV1OOlGwExM'
    },
    {
        id: 'avengers-endgame',
        title: 'Avengers: Hồi Kết (Avengers: Endgame)',
        shortTitle: 'Avengers: Endgame',
        dropdownTitle: 'Avengers: Hồi Kết (2D Vietsub)',
        ageTag: 'C13',
        ageDesc: 'C13 - Khán giả trên 13 tuổi',
        imdb: '8.4/10',
        duration: '181 phút',
        genres: ['Hành Động', 'Sci-Fi', 'Siêu Anh Hùng'],
        director: 'Anthony Russo, Joe Russo',
        writers: 'Christopher Markus, Stephen McFeely',
        stars: 'Robert Downey Jr., Chris Evans, Mark Ruffalo, Scarlett Johansson',
        overviewP1: 'Sau cú búng tay tàn khốc của Thanos trong Cuộc Chiến Vô Cực làm biến mất một nửa sinh linh trong vũ trụ, biệt đội Avengers chìm trong đau thương và thất vọng. Những người còn sống sót gồm Iron Man, Captain America, Thor, Black Widow và Hulk phải tìm cách vượt qua nỗi đau mất mát để khôi phục lại trật tự thế giới.',
        overviewP2: 'Với sự xuất hiện bất ngờ của Ant-Man và phát kiến du hành thời gian qua Cõi Lượng Tử, các anh hùng quyết định dấn thân vào một sứ mệnh mạo hiểm chưa từng có: du hành về quá khứ để thu thập các Viên Đá Vô Cực trước Thanos. Đây là trận chiến cuối cùng đầy xúc động quyết định số phận vũ trụ.',
        poster: 'https://image.tmdb.org/t/p/w780/ulzhLuWrPK07P1YkdWQLZnQh1JL.jpg',
        youtubeId: 'TcMBFSGVi1c'
    },
    {
        id: 'dune-part-2',
        title: 'Dune: Hành Tinh Cát - Phần Hai (Dune: Part Two)',
        shortTitle: 'Dune: Part Two',
        dropdownTitle: 'Dune: Hành Tinh Cát - Phần Hai (2D Vietsub)',
        ageTag: 'C16',
        ageDesc: 'C16 - Khán giả trên 16 tuổi',
        imdb: '8.6/10',
        duration: '166 phút',
        genres: ['Hành Động', 'Viễn Tưởng', 'Phiêu Lưu'],
        director: 'Denis Villeneuve',
        writers: 'Denis Villeneuve, Jon Spaihts',
        stars: 'Timothée Chalamet, Zendaya, Rebecca Ferguson, Javier Bardem',
        overviewP1: 'Hành trình tiếp theo của Paul Atreides khi anh cùng mẹ là phu nhân Jessica hội ngộ với người Fremen trên hành tinh sa mạc khắc nghiệt Arrakis. Mối tình nảy nở giữa Paul và Chani cùng sự công nhận của các chiến binh sa mạc đã biến Paul trở thành vị cứu tinh Lisan al-Gaib theo lời tiên tri.',
        overviewP2: 'Đứng trước những âm mưu tàn bạo của gia tộc Harkonnen và Hoàng đế vũ trụ, Paul buộc phải đưa ra lựa chọn sinh tử giữa tình yêu cá nhân và định mệnh giải phóng Arrakis. Tác phẩm sở hữu quy mô hoành tráng, âm nhạc ma mị và góc quay choáng ngợp đạt giải Oscar.',
        poster: 'https://image.tmdb.org/t/p/w780/6izwz7rsy95ARzTR3poZ8H6c5pp.jpg',
        youtubeId: 'Way9Dexny3w'
    },
    {
        id: 'spiderman-no-way-home',
        title: 'Spider-Man: Không Còn Nhà (Spider-Man: No Way Home)',
        shortTitle: 'Spider-Man: No Way Home',
        dropdownTitle: 'Spider-Man: Không Còn Nhà (2D Lồng Tiếng)',
        ageTag: 'C13',
        ageDesc: 'C13 - Khán giả trên 13 tuổi',
        imdb: '8.2/10',
        duration: '148 phút',
        genres: ['Hành Động', 'Siêu Anh Hùng', 'Phiêu Lưu'],
        director: 'Jon Watts',
        writers: 'Chris McKenna, Erik Sommers',
        stars: 'Tom Holland, Zendaya, Benedict Cumberbatch, Andrew Garfield',
        overviewP1: 'Lần đầu tiên trong lịch sử điện ảnh của Người Nhện, danh tính thật Peter Parker bị Mysterio vạch trần trước toàn thế giới. Cuộc sống riêng tư cùng tương lai của Peter và các bạn thân MJ, Ned hoàn toàn bị xáo trộn.',
        overviewP2: 'Trong sự bế tắc, Peter đã nhờ đến Doctor Strange thực hiện một câu bùa phép xóa ký ký ức của mọi người. Tuy nhiên, sự cố ma thuật xảy ra đã vô tình xé rách ranh giới Đa Vũ Trụ, kéo theo hàng loạt ác nhân huyền thoại từ các vũ trụ song song trở lại đối đầu với Spider-Man.',
        poster: 'https://image.tmdb.org/t/p/w780/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg',
        youtubeId: 'JfVOs4VSpmA'
    }
];

// 2. BẢNG GIÁ VÉ & BỎNG NƯỚC (VNĐ)
const PRICING = {
    NORMAL: 90000,
    VIP: 110000,
    DOUBLE: 200000
};

const FNB_ITEMS = {
    comboSolo: { name: 'Combo Solo (1 Bỏng + 1 Nước)', price: 79000 },
    comboCouple: { name: 'Combo Couple (1 Bỏng + 2 Nước)', price: 109000 },
    popcorn: { name: 'Bỏng Ngô Phô Mai/Bơ', price: 45000 },
    drink: { name: 'Nước Ngọt Pepsi/Coca', price: 35000 }
};

// 3. QUẢN LÝ LOCALSTORAGE VÉ ĐÃ ĐẶT
function getStoredTickets() {
    try {
        const data = localStorage.getItem('userTickets');
        if (data) return JSON.parse(data);
        // Kiểm tra tương thích với key đơn 'userTicket' cũ từ Sv2
        const singleData = localStorage.getItem('userTicket');
        if (singleData) return [JSON.parse(singleData)];
    } catch (e) {
        console.error('Lỗi đọc localStorage:', e);
    }
    return [];
}

function saveTicketToStorage(ticket) {
    const tickets = getStoredTickets();
    tickets.unshift(ticket); // Đưa vé mới lên đầu
    localStorage.setItem('userTickets', JSON.stringify(tickets));
    localStorage.setItem('userTicket', JSON.stringify(ticket)); // Lưu cả bản sao 1 vé cho tương thích
}

function removeTicketFromStorage(ticketCode) {
    let tickets = getStoredTickets();
    tickets = tickets.filter(t => t.ticketCode !== ticketCode);
    localStorage.setItem('userTickets', JSON.stringify(tickets));
}

// 4. CÁC HÀM TIỆN ÍCH DÙNG CHUNG
function formatVND(amount) {
    return amount.toLocaleString('vi-VN') + ' VNĐ';
}

function showToast(msg) {
    const toast = document.getElementById('toast-notification');
    const messageEl = document.getElementById('toast-message');
    if (!toast || !messageEl) return;

    messageEl.innerText = msg;
    toast.classList.remove('translate-y-20', 'opacity-0');
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3200);
}

// SVG Thay thế khi ảnh poster lỗi đường dẫn
function getPosterFallbackSvg(title) {
    const cleanTitle = encodeURIComponent(title || 'CineStar');
    return `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="500" height="750" viewBox="0 0 500 750"><defs><linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="%231e1b4b"/><stop offset="50%" stop-color="%230f172a"/><stop offset="100%" stop-color="%23030712"/></linearGradient></defs><rect width="100%" height="100%" fill="url(%23bg)"/><rect x="20" y="20" width="460" height="710" rx="16" fill="none" stroke="%2338bdf8" stroke-width="2" stroke-dasharray="8,8" opacity="0.4"/><text x="50%" y="40%" fill="%236366f1" font-family="sans-serif" font-size="32" font-weight="900" text-anchor="middle">CINESTAR</text><text x="50%" y="52%" fill="%23ffffff" font-family="sans-serif" font-size="22" font-weight="bold" text-anchor="middle">${cleanTitle}</text><text x="50%" y="60%" fill="%2338bdf8" font-family="sans-serif" font-size="14" font-weight="bold" text-anchor="middle">OFFICIAL POSTER</text></svg>`;
}

function handlePosterError(imgEl) {
    imgEl.onerror = null;
    imgEl.src = getPosterFallbackSvg('CineStar');
}

// Tự động Highlight Menu đang chọn
document.addEventListener('DOMContentLoaded', () => {
    const currentPath = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('header nav a');
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath) {
            link.classList.remove('text-slate-300');
            link.classList.add('text-sky-400');
        }
    });
});