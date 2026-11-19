<?php
declare(strict_types=1);

final class MovieController
{
    public function __construct(private MovieModel $movies)
    {
    }

    
    public function index(): void
    {
        $movies = array_map(static function (array $row): array {
            $genres = array_values(array_filter(
                array_map(
                    'trim',
                    explode(',', $row['the_loai'] ?? '')
                ),
                static fn(string $genre): bool => $genre !== ''
            ));

            return [
                'id' => (string) $row['id'],

                'title' => $row['ten_phim'],
                'shortTitle' => $row['ten_ngan'] ?: $row['ten_phim'],
                'dropdownTitle' => $row['ten_hien_thi_dropdown'] ?: $row['ten_phim'],

                'ageTag' => $row['do_tuoi'] ?? '',
                'ageDesc' => $row['mo_ta_do_tuoi'] ?? '',
                'imdb' => $row['diem_imdb'] !== null
                    ? number_format((float) $row['diem_imdb'], 1) . '/10'
                    : '',

                'duration' => (int) $row['thoi_luong'] . ' phút',
                'genres' => $genres,

                'director' => $row['dao_dien'] ?? '',
                'writers' => $row['bien_kich'] ?? '',
                'stars' => $row['dien_vien'] ?? '',

                'description' => $row['mo_ta'] ?? '',
                'poster' => $row['poster_url'] ?? '',
                'trailerUrl' => $row['trailer_url'] ?? '',
                'status' => $row['trang_thai'],
            ];
        }, $this->movies->findVisible());

        jsonResponse([
            'ok' => true,
            'data' => $movies,
        ]);
    }

}