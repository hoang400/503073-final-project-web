<?php
declare(strict_types=1);

use function App\Middleware\AdminMiddleware;

final class AdminController
{
    public function __construct(private AdminModel $admin)
    {
    }

    // ============================================
    // MOVIES
    // ============================================

    
    public function getMovies(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str(
            (string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY),
            $query
        );

        $search = $query['search'] ?? null;
        $status = $query['status'] ?? null;

        $movies = $this->admin->findMovies($search, $status);

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],

            'title' => $row['ten_phim'],
            'shortTitle' => $row['ten_ngan'] ?? '',
            'dropdownTitle' => $row['ten_hien_thi_dropdown'] ?? '',

            'genre' => $row['the_loai'],
            'duration' => (int) $row['thoi_luong'],

            'poster_url' => $row['poster_url'] ?? '',
            'description' => $row['mo_ta'] ?? '',
            'trailer_url' => $row['trailer_url'] ?? '',

            'ageTag' => $row['do_tuoi'] ?? '',
            'ageDesc' => $row['mo_ta_do_tuoi'] ?? '',
            'imdb' => $row['diem_imdb'] !== null
                ? (float) $row['diem_imdb']
                : null,

            'director' => $row['dao_dien'] ?? '',
            'writers' => $row['bien_kich'] ?? '',
            'stars' => $row['dien_vien'] ?? '',

            'status' => $row['trang_thai'],
        ], $movies);

        jsonResponse([
            'ok' => true,
            'data' => $data,
        ]);
    }


    public function getMovie(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã phim không hợp lệ.', 422);
        }

        $movie = $this->admin->findMovieById($id);

        if (!$movie) {
            throw new ApiException('Không tìm thấy phim.', 404);
        }

        $data = [
            'id' => (int) $movie['id'],

            'title' => $movie['ten_phim'],
            'shortTitle' => $movie['ten_ngan'] ?? '',
            'dropdownTitle' => $movie['ten_hien_thi_dropdown'] ?? '',

            'genre' => $movie['the_loai'],
            'duration' => (int) $movie['thoi_luong'],

            'poster_url' => $movie['poster_url'] ?? '',
            'description' => $movie['mo_ta'] ?? '',
            'trailer_url' => $movie['trailer_url'] ?? '',

            'ageTag' => $movie['do_tuoi'] ?? '',
            'ageDesc' => $movie['mo_ta_do_tuoi'] ?? '',
            'imdb' => $movie['diem_imdb'] !== null
                ? (float) $movie['diem_imdb']
                : null,

            'director' => $movie['dao_dien'] ?? '',
            'writers' => $movie['bien_kich'] ?? '',
            'stars' => $movie['dien_vien'] ?? '',

            'status' => $movie['trang_thai'],
        ];

        jsonResponse([
            'ok' => true,
            'data' => $data,
        ]);
    }

    
    public function createMovie(): void
    {
        AdminMiddleware::requireAdmin();

        $body = readJsonBody();

        $required = [
            'ten_phim',
            'the_loai',
            'thoi_luong',
        ];

        foreach ($required as $field) {
            if (
                !isset($body[$field])
                || trim((string) $body[$field]) === ''
            ) {
                throw new ApiException("Thiếu trường: {$field}", 422);
            }
        }

        $duration = filter_var(
            $body['thoi_luong'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 600]]
        );

        if ($duration === false) {
            throw new ApiException('Thời lượng phim không hợp lệ.', 422);
        }

        $status = $body['trang_thai'] ?? 'SAP_CHIEU';

        $allowedStatus = [
            'DANG_CHIEU',
            'SAP_CHIEU',
            'NGUNG_CHIEU',
        ];

        if (!in_array($status, $allowedStatus, true)) {
            throw new ApiException('Trạng thái không hợp lệ.', 422);
        }

        $imdb = null;

        if (
            isset($body['imdb'])
            && $body['imdb'] !== ''
        ) {
            if (!is_numeric($body['imdb'])) {
                throw new ApiException('Điểm IMDb phải là số.', 422);
            }

            $imdb = (float) $body['imdb'];

            if ($imdb < 0 || $imdb > 10) {
                throw new ApiException('Điểm IMDb phải từ 0 đến 10.', 422);
            }
        }

        $data = [
            'ten_phim' => trim($body['ten_phim']),
            'ten_ngan' => trim($body['ten_ngan'] ?? ''),
            // Form 6.2.2 chỉ có 1 ô "Tên Ngắn (hiển thị Dropdown)" nên
            // khi không gửi riêng thì dùng tên ngắn làm tên hiển thị.
            'ten_hien_thi_dropdown' => trim(
                $body['ten_hien_thi_dropdown'] ?? ''
            ) ?: trim($body['ten_ngan'] ?? ''),

            'the_loai' => trim($body['the_loai']),
            'thoi_luong' => $duration,

            'poster_url' => trim($body['poster_url'] ?? '') ?: null,
            'mo_ta' => trim($body['mo_ta'] ?? '') ?: null,
            'trailer_url' => trim($body['trailer_url'] ?? '') ?: null,

            'do_tuoi' => trim($body['do_tuoi'] ?? '') ?: null,
            'mo_ta_do_tuoi' => trim($body['mo_ta_do_tuoi'] ?? '') ?: null,
            'diem_imdb' => $imdb,

            'dao_dien' => trim($body['dao_dien'] ?? '') ?: null,
            'bien_kich' => trim($body['bien_kich'] ?? '') ?: null,
            'dien_vien' => trim($body['dien_vien'] ?? '') ?: null,

            'trang_thai' => $status,
        ];

        $id = $this->admin->createMovie($data);

        jsonResponse([
            'ok' => true,
            'data' => ['id' => $id],
        ], 201);
    }

    public function updateMovie(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);
        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã phim không hợp lệ.', 422);
        }

        $body = readJsonBody();

        $allowed = [
            'ten_phim',
            'ten_ngan',
            'ten_hien_thi_dropdown',
            'the_loai',
            'thoi_luong',
            'poster_url',
            'mo_ta',
            'trailer_url',
            'do_tuoi',
            'mo_ta_do_tuoi',
            'diem_imdb',
            'dao_dien',
            'bien_kich',
            'dien_vien',
            'trang_thai',
        ];

        $data = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $data[$field] = $body[$field];
            }
        }

        if (isset($data['thoi_luong'])) {
            $duration = filter_var(
                $data['thoi_luong'],
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 600]]
            );

            if ($duration === false) {
                throw new ApiException('Thời lượng phim không hợp lệ.', 422);
            }

            $data['thoi_luong'] = $duration;
        }
        if (array_key_exists('imdb', $body)) {
            $data['diem_imdb'] = $body['imdb'];
        }

        if (isset($data['diem_imdb']) && $data['diem_imdb'] !== '') {
            if (
                !is_numeric($data['diem_imdb'])
                || (float) $data['diem_imdb'] < 0
                || (float) $data['diem_imdb'] > 10
            ) {
                throw new ApiException('Điểm IMDb phải từ 0 đến 10.', 422);
            }

            $data['diem_imdb'] = (float) $data['diem_imdb'];
        } elseif (array_key_exists('diem_imdb', $data)) {
            $data['diem_imdb'] = null;
        }

        if (isset($data['trang_thai'])) {
            $allowedStatus = [
                'DANG_CHIEU',
                'SAP_CHIEU',
                'NGUNG_CHIEU',
            ];

            if (!in_array($data['trang_thai'], $allowedStatus, true)) {
                throw new ApiException('Trạng thái không hợp lệ.', 422);
            }
        }

        foreach ([
            'ten_phim',
            'ten_ngan',
            'ten_hien_thi_dropdown',
            'the_loai',
            'poster_url',
            'mo_ta',
            'trailer_url',
            'do_tuoi',
            'mo_ta_do_tuoi',
            'dao_dien',
            'bien_kich',
            'dien_vien',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = trim((string) $data[$field]);
                if ($data[$field] === '') {
                    $data[$field] = null;
                }
            }
        }

        $this->admin->updateMovie($id, $data);

        jsonResponse([
            'ok' => true,
            'data' => ['id' => $id],
        ]);

    }

    public function deleteMovie(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã phim không hợp lệ.', 422);
        }

        if ($this->admin->findShowtimes($id, null)) {
            throw new ApiException('Phim còn suất chiếu nên không thể xóa.', 422);
        }

        $this->admin->deleteMovie($id);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    public function deleteBooking(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã đơn hàng không hợp lệ.', 422);
        }

        $this->admin->deleteBooking($id);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    // ============================================
    // CINEMAS
    // ============================================

    public function getCinemas(): void
    {
        AdminMiddleware::requireAdmin();

        $cinemas = $this->admin->findCinemas();

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['ten_rap'],
            'address' => $row['dia_chi'],
            'city' => $row['thanh_pho'],
        ], $cinemas);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function getCinema(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã rạp không hợp lệ.', 422);
        }

        $cinema = $this->admin->findCinemaById($id);

        if (!$cinema) {
            throw new ApiException('Không tìm thấy rạp.', 404);
        }

        jsonResponse(['ok' => true, 'data' => [
            'id' => (int) $cinema['id'],
            'name' => $cinema['ten_rap'],
            'address' => $cinema['dia_chi'],
            'city' => $cinema['thanh_pho'],
        ]]);
    }

    public function createCinema(): void
    {
        AdminMiddleware::requireAdmin();

        $body = readJsonBody();

        $required = ['ten_rap', 'dia_chi', 'thanh_pho'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                throw new ApiException("Thiếu trường: {$field}", 422);
            }
        }

        $data = [
            'ten_rap' => trim($body['ten_rap']),
            'dia_chi' => trim($body['dia_chi']),
            'thanh_pho' => trim($body['thanh_pho']),
        ];

        $id = $this->admin->createCinema($data);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]], 201);
    }

    public function updateCinema(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã rạp không hợp lệ.', 422);
        }

        $body = readJsonBody();

        $data = [];
        $allowed = ['ten_rap', 'dia_chi', 'thanh_pho'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $data[$field] = trim($body[$field]);
            }
        }

        $this->admin->updateCinema($id, $data);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    public function deleteCinema(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã rạp không hợp lệ.', 422);
        }

        $this->admin->deleteCinema($id);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    // ============================================
    // ROOMS & SEATS
    // ============================================

    public function getRooms(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $cinemaId = $query['cinema_id'] ?? null;

        if ($cinemaId !== null) {
            $cinemaId = filter_var($cinemaId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($cinemaId === false) {
                throw new ApiException('Mã rạp không hợp lệ.', 422);
            }
            $rooms = $this->admin->findRoomsByCinema($cinemaId);
        } else {
            $rooms = $this->admin->findAllRooms();
        }

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'cinema_id' => (int) $row['rap_id'],
            'cinema_name' => $row['ten_rap'] ?? '',
            'name' => $row['ten_phong'],
            'type' => $row['loai_phong'],
            'seat_count' => (int) ($row['seat_count'] ?? 0),
        ], $rooms);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function getRoom(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã phòng không hợp lệ.', 422);
        }

        $room = $this->admin->findRoomById($id);

        if (!$room) {
            throw new ApiException('Không tìm thấy phòng.', 404);
        }

        jsonResponse(['ok' => true, 'data' => [
            'id' => (int) $room['id'],
            'cinema_id' => (int) $room['rap_id'],
            'name' => $room['ten_phong'],
            'type' => $room['loai_phong'],
        ]]);
    }

    public function getRoomSeats(): void
    {
        AdminMiddleware::requireAdmin();

        $roomId = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($roomId === false) {
            throw new ApiException('Mã phòng không hợp lệ.', 422);
        }

        $seats = $this->admin->findSeatsByRoom($roomId);

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'room_id' => (int) $row['phong_id'],
            'row' => $row['hang_ghe'],
            'number' => (int) $row['so_thu_tu'],
            'type' => $row['loai_ghe'],
        ], $seats);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function createRoom(): void
    {
        AdminMiddleware::requireAdmin();

        $body = readJsonBody();

        $required = ['rap_id', 'ten_phong', 'loai_phong'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                throw new ApiException("Thiếu trường: {$field}", 422);
            }
        }

        $data = [
            'rap_id' => (int) $body['rap_id'],
            'ten_phong' => trim($body['ten_phong']),
            'loai_phong' => trim($body['loai_phong']),
        ];

        $id = $this->admin->createRoom($data);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]], 201);
    }

    public function updateRoom(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã phòng không hợp lệ.', 422);
        }

        $body = readJsonBody();

        $data = [];
        $allowed = ['rap_id', 'ten_phong', 'loai_phong'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $data[$field] = $field === 'rap_id' ? (int) $body[$field] : trim($body[$field]);
            }
        }

        $this->admin->updateRoom($id, $data);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    public function deleteRoom(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã phòng không hợp lệ.', 422);
        }

        // Phòng còn suất chiếu thì không xóa được (FK suatchieu.phong_id RESTRICT)
        if ($this->admin->findShowtimes(null, $id)) {
            throw new ApiException('Phòng còn suất chiếu nên không thể xóa.', 422);
        }

        $this->admin->deleteRoom($id);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    public function createSeat(): void
    {
        AdminMiddleware::requireAdmin();

        $body = readJsonBody();

        $required = ['phong_id', 'hang_ghe', 'so_thu_tu', 'loai_ghe'];
        foreach ($required as $field) {
            if (!isset($body[$field]) || $body[$field] === '') {
                throw new ApiException("Thiếu trường: {$field}", 422);
            }
        }

        $data = [
            'phong_id' => (int) $body['phong_id'],
            'hang_ghe' => trim($body['hang_ghe']),
            'so_thu_tu' => (int) $body['so_thu_tu'],
            'loai_ghe' => trim($body['loai_ghe']),
        ];

        $allowedTypes = ['Thuong', 'VIP', 'Couple'];
        if (!in_array($data['loai_ghe'], $allowedTypes, true)) {
            throw new ApiException('Loại ghế không hợp lệ.', 422);
        }

        if (!preg_match('/^[A-Z]$/', $data['hang_ghe'])) {
            throw new ApiException('Hàng ghế không hợp lệ (chỉ 1 ký tự A–Z).', 422);
        }
        if ($data['so_thu_tu'] < 1 || $data['so_thu_tu'] > 30) {
            throw new ApiException('Số thứ tự ghế phải từ 1 đến 30.', 422);
        }
        if ($this->admin->seatExists($data['phong_id'], $data['hang_ghe'], $data['so_thu_tu'])) {
            throw new ApiException("Ghế {$data['hang_ghe']}{$data['so_thu_tu']} đã tồn tại trong phòng này.", 422);
        }

        $id = $this->admin->createSeat($data);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]], 201);
    }

    public function deleteSeat(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã ghế không hợp lệ.', 422);
        }

        $this->admin->deleteSeat($id);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }



    public function updateSeat(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã ghế không hợp lệ.', 422);
        }

        $body = readJsonBody();

        $type = trim((string) ($body['loai_ghe'] ?? ''));
        if (!in_array($type, ['Thuong', 'VIP', 'Couple'], true)) {
            throw new ApiException('Loại ghế không hợp lệ.', 422);
        }

        $this->admin->updateSeat($id, $type);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    // ============================================
    // SHOWTIMES
    // ============================================

    public function getShowtimes(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $movieId = $query['movie_id'] ?? null;
        $roomId = $query['room_id'] ?? null;
        $date = $query['date'] ?? null;

        if ($movieId !== null) {
            $movieId = filter_var($movieId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        }
        if ($roomId !== null) {
            $roomId = filter_var($roomId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        }

        $showtimes = $this->admin->findShowtimes($movieId, $roomId, $date);

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'movie_id' => (int) $row['phim_id'],
            'movie_title' => $row['ten_phim'],
            'room_id' => (int) $row['phong_id'],
            'room_name' => $row['ten_phong'],
            'cinema_name' => $row['ten_rap'],
            'start_time' => $row['thoi_gian_bat_dau'],
            'end_time' => $row['thoi_gian_ket_thuc'],
            'base_price' => (float) $row['gia_ve_co_ban'],
        ], $showtimes);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function createShowtime(): void
    {
        AdminMiddleware::requireAdmin();

        $body = readJsonBody();

        $required = ['phim_id', 'phong_id', 'thoi_gian_bat_dau', 'thoi_gian_ket_thuc', 'gia_ve_co_ban'];
        foreach ($required as $field) {
            if (!isset($body[$field]) || $body[$field] === '') {
                throw new ApiException("Thiếu trường: {$field}", 422);
            }
        }

        $data = [
            'phim_id' => (int) $body['phim_id'],
            'phong_id' => (int) $body['phong_id'],
            'thoi_gian_bat_dau' => $body['thoi_gian_bat_dau'],
            'thoi_gian_ket_thuc' => $body['thoi_gian_ket_thuc'],
            'gia_ve_co_ban' => (float) $body['gia_ve_co_ban'],
        ];

        if ($data['gia_ve_co_ban'] < 0) {
            throw new ApiException('Giá vé không được âm.', 422);
        }

        try {
            $id = $this->admin->createShowtime($data);
        } catch (ApiException $e) {
            if ($e->getCode() === 409) {
                throw $e;
            }
            throw new ApiException('Dữ liệu suất chiếu không hợp lệ.', 422);
        }

        jsonResponse(['ok' => true, 'data' => ['id' => $id]], 201);
    }

    public function updateShowtime(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $id = filter_var($query['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new ApiException('Mã suất chiếu không hợp lệ.', 422);
        }

        $body = readJsonBody();

        $data = [];
        $allowed = ['phim_id', 'phong_id', 'thoi_gian_bat_dau', 'thoi_gian_ket_thuc', 'gia_ve_co_ban'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $data[$field] = $field === 'gia_ve_co_ban' ? (float) $body[$field] : $body[$field];
            }
        }

        if (isset($data['gia_ve_co_ban']) && $data['gia_ve_co_ban'] < 0) {
            throw new ApiException('Giá vé không được âm.', 422);
        }

        try {
            $this->admin->updateShowtime($id, $data);
        } catch (ApiException $e) {
            if ($e->getCode() === 409) {
                throw $e;
            }
            throw new ApiException('Dữ liệu suất chiếu không hợp lệ.', 422);
        }

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    public function deleteShowtime(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $id = filter_var($query['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new ApiException('Mã suất chiếu không hợp lệ.', 422);
        }

        $this->admin->deleteShowtime($id);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    // ============================================
    // CONCESSIONS
    // ============================================

    public function getConcessions(): void
    {
        AdminMiddleware::requireAdmin();

        $concessions = $this->admin->findConcessions();

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'name' => $row['ten_mon'],
            'type' => $row['loai'],
            'price' => (float) $row['don_gia'],
            'image' => $row['hinh_anh'] ?? '',
            'status' => (int) $row['trang_thai'],
        ], $concessions);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function getConcession(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã món không hợp lệ.', 422);
        }

        $concession = $this->admin->findConcessionById($id);

        if (!$concession) {
            throw new ApiException('Không tìm thấy món.', 404);
        }

        jsonResponse(['ok' => true, 'data' => [
            'id' => (int) $concession['id'],
            'name' => $concession['ten_mon'],
            'type' => $concession['loai'],
            'price' => (float) $concession['don_gia'],
            'image' => $concession['hinh_anh'] ?? '',
            'status' => (int) $concession['trang_thai'],
        ]]);
    }

    public function createConcession(): void
    {
        AdminMiddleware::requireAdmin();

        $body = readJsonBody();

        $required = ['ten_mon', 'loai', 'don_gia'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                throw new ApiException("Thiếu trường: {$field}", 422);
            }
        }

        $allowedTypes = ['Bap', 'Nuoc', 'Combo'];
        if (!in_array($body['loai'], $allowedTypes, true)) {
            throw new ApiException('Loại không hợp lệ.', 422);
        }

        $data = [
            'ten_mon' => trim($body['ten_mon']),
            'loai' => $body['loai'],
            'don_gia' => (float) $body['don_gia'],
            'hinh_anh' => $body['hinh_anh'] ?? null,
            'trang_thai' => isset($body['trang_thai']) ? (int) $body['trang_thai'] : 1,
        ];

        if ($data['don_gia'] < 0) {
            throw new ApiException('Giá không được âm.', 422);
        }

        $id = $this->admin->createConcession($data);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]], 201);
    }

    public function updateConcession(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã món không hợp lệ.', 422);
        }

        $body = readJsonBody();

        $data = [];
        $allowed = ['ten_mon', 'loai', 'don_gia', 'hinh_anh', 'trang_thai'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $data[$field] = $field === 'don_gia' ? (float) $body[$field] : ($field === 'trang_thai' ? (int) $body[$field] : trim($body[$field]));
            }
        }

        if (isset($data['loai'])) {
            $allowedTypes = ['Bap', 'Nuoc', 'Combo'];
            if (!in_array($data['loai'], $allowedTypes, true)) {
                throw new ApiException('Loại không hợp lệ.', 422);
            }
        }

        if (isset($data['don_gia']) && $data['don_gia'] < 0) {
            throw new ApiException('Giá không được âm.', 422);
        }

        $this->admin->updateConcession($id, $data);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    public function deleteConcession(): void
    {
        AdminMiddleware::requireAdmin();

        $id = filter_var(
            $_GET['route_params']['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false) {
            throw new ApiException('Mã món không hợp lệ.', 422);
        }

        $this->admin->deleteConcession($id);

        jsonResponse(['ok' => true, 'data' => ['id' => $id]]);
    }

    // ============================================
    // BOOKINGS
    // ============================================

    public function getBookings(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $status = $query['status'] ?? null;
        $dateFrom = $query['date_from'] ?? null;
        $dateTo = $query['date_to'] ?? null;

        $bookings = $this->admin->findBookings($status, $dateFrom, $dateTo);

        $data = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'code' => $row['ma_don_hang'],
            'total' => (float) $row['tong_tien'],
            'payment_method' => $row['phuong_thuc_thanh_toan'],
            'payment_status' => $row['trang_thai_thanh_toan'],
            'created_at' => $row['thoi_gian_dat'],
            'customer_name' => $row['khach_hang'],
            'customer_email' => $row['email'],
            'movie' => $row['ten_phim'],
            'shortTitle' => $row['ten_ngan'] ?? '',
            'showtime' => $row['thoi_gian_bat_dau'],
            'room' => $row['ten_phong'],
            'cinema' => $row['ten_rap'],
            'seats' => $row['ghe_da_chon'] ?? '',
        ], $bookings);

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function getBookingDetail(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $id = filter_var($query['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new ApiException('Mã đơn hàng không hợp lệ.', 422);
        }

        $order = $this->admin->findBookingDetail($id);

        if (!$order) {
            throw new ApiException('Không tìm thấy đơn hàng.', 404);
        }

        $data = [
            'id' => (int) $order['id'],
            'code' => $order['ma_don_hang'],
            'total' => (float) $order['tong_tien'],
            'payment_method' => $order['phuong_thuc_thanh_toan'],
            'payment_status' => $order['trang_thai_thanh_toan'],
            'created_at' => $order['thoi_gian_dat'],
            'customer' => [
                'name' => $order['khach_hang'],
                'email' => $order['email'],
                'phone' => $order['so_dien_thoai'] ?? '',
            ],
            'movie' => $order['ten_phim'],
            'showtime' => [
                'start' => $order['thoi_gian_bat_dau'],
                'end' => $order['thoi_gian_ket_thuc'],
                'room' => $order['ten_phong'],
                'cinema' => $order['ten_rap'],
            ],
            'tickets' => array_map(static fn(array $t): array => [
                'id' => (int) $t['id'],
                'price' => (float) $t['gia_ve'],
                'qr_code' => $t['ma_qr_code'],
                'used_at' => $t['thoi_gian_su_dung'],
                'seat' => [
                    'row' => $t['hang_ghe'],
                    'number' => (int) $t['so_thu_tu'],
                    'type' => $t['loai_ghe'],
                ],
            ], $order['tickets'] ?? []),
            'concessions' => array_map(static fn(array $c): array => [
                'name' => $c['ten_mon'],
                'type' => $c['loai'],
                'quantity' => (int) $c['so_luong'],
                'price' => (float) $c['gia_tien'],
            ], $order['concessions'] ?? []),
        ];

        jsonResponse(['ok' => true, 'data' => $data]);
    }

    public function confirmBooking(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $id = filter_var($query['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new ApiException('Mã đơn hàng không hợp lệ.', 422);
        }

        try {
            $this->admin->confirmPayment($id);
        } catch (ApiException $e) {
            throw $e;
        }

        jsonResponse(['ok' => true, 'data' => ['id' => $id, 'message' => 'Xác nhận thanh toán thành công.']]);
    }

    public function cancelBooking(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $id = filter_var($query['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new ApiException('Mã đơn hàng không hợp lệ.', 422);
        }

        try {
            $this->admin->cancelBooking($id);
        } catch (ApiException $e) {
            throw $e;
        }

        jsonResponse(['ok' => true, 'data' => ['id' => $id, 'message' => 'Hủy đơn hàng thành công.']]);
    }

    // ============================================
    // REPORTS
    // ============================================

    public function getReport(): void
    {
        AdminMiddleware::requireAdmin();

        $query = [];
        parse_str((string) parse_url($_GET['route'] ?? '', PHP_URL_QUERY), $query);

        $dateFrom = $query['date_from'] ?? null;
        $dateTo = $query['date_to'] ?? null;

        $summary = $this->admin->getRevenueReport($dateFrom, $dateTo);
        $byDate = $this->admin->getRevenueByDate($dateFrom, $dateTo);
        $topMovies = $this->admin->getTopMovies($dateFrom, $dateTo, 10);

        jsonResponse(['ok' => true, 'data' => [
            'summary' => $summary,
            'by_date' => $byDate,
            'top_movies' => $topMovies,
        ]]);
    }
}
