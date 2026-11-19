<?php
declare(strict_types=1);

final class AdminMiddleware
{
    public static function requireAdmin(): array
    {
        return AuthMiddleware::requireRole(['ADMIN']);
    }
}
