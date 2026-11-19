<?php
declare(strict_types=1);

namespace App\Middleware;

final class CsrfMiddleware
{
    public static function verify(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $expected = $_SESSION['csrf_token'] ?? '';

        if (
            $token === ''
            || $expected === ''
            || !hash_equals($expected, $token)
        ) {
            jsonResponse([
                'ok' => false,
                'error' => [
                    'message' => 'Phiên xác nhận đã hết hạn. Vui lòng thử lại.',
                ],
            ], 403);
            exit;
        }
    }
}
