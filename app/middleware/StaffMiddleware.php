<?php
declare(strict_types=1);

final class StaffMiddleware
{
    public static function requireStaff(): array
    {
        return AuthMiddleware::requireRole(['STAFF']);
    }
}
