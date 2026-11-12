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
                'shortTitle' => $row['ten_phim'],
                'genres' => $genres,
                'duration' => (int) $row['thoi_luong'] . ' phút',
                'poster' => $row['poster_url'] ?? '',
                'description' => $row['mo_ta'] ?? '',
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