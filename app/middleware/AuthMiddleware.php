<?php
declare(strict_types=1);

use App\Middleware\Database;

final class AuthMiddleware
{
    /**
     * @return array{id: int, ho_ten: string, email: string, so_dien_thoai: string|null, vai_tro: string}
     */
    public static function requireAuth(): array
    {
        $userId = filter_var(
            $_SESSION['user_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($userId === false) {
            jsonResponse([
                'ok' => false,
                'error' => ['message' => 'Vui lòng đăng nhập.'],
            ], 401);
            exit;
        }

        $stmt = Database::connection()->prepare(
            'SELECT id, ho_ten, email, so_dien_thoai, vai_tro
             FROM nguoidung
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            unset($_SESSION['user_id']);
            jsonResponse([
                'ok' => false,
                'error' => ['message' => 'Vui lòng đăng nhập lại.'],
            ], 401);
            exit;
        }

        return $user;
    }

    /**
     * @param array<string> $allowedRoles
     * @return array{id: int, ho_ten: string, email: string, so_dien_thoai: string|null, vai_tro: string}
     */
    public static function requireRole(array $allowedRoles): array
    {
        $user = self::requireAuth();

        if (!in_array($user['vai_tro'], $allowedRoles, true)) {
            jsonResponse([
                'ok' => false,
                'error' => ['message' => 'Bạn không có quyền thực hiện thao tác này.'],
            ], 403);
            exit;
        }

        return $user;
    }
}
