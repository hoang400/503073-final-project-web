<?php
declare(strict_types=1);

final class ApiException extends RuntimeException
{
}

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off';

    session_name('cinestar_session');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (!session_start([
        'use_strict_mode' => 1,
        'use_only_cookies' => 1,
    ])) {
        throw new RuntimeException('Cannot start session.');
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function jsonResponse(array $payload, int $status = 200): void
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo $json;
    exit;
}

function readJsonBody(): array
{
    $contentType = strtolower(trim(
        explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]
    ));

    if ($contentType !== 'application/json') {
        throw new ApiException('Yêu cầu phải gửi dữ liệu JSON.', 415);
    }

    $raw = file_get_contents('php://input');

    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new ApiException('Dữ liệu JSON không hợp lệ.', 400);
    }

    if (!is_array($data) || !str_starts_with(ltrim($raw), '{')) {
        throw new ApiException('Dữ liệu gửi lên không hợp lệ.', 400);
    }

    return $data;
}

function verifyCsrf(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';

    if (
        $token === ''
        || $expected === ''
        || !hash_equals($expected, $token)
    ) {
        throw new ApiException(
            'Phiên xác nhận đã hết hạn. Vui lòng thử lại.',
            403
        );
    }
    
}
function requireRole(array $allowedRoles): array
{
    $userId = filter_var(
        $_SESSION['user_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($userId === false) {
        throw new ApiException('Vui lòng đăng nhập.', 401);
    }

    $statement = Database::connection()->prepare(
        'SELECT id, ho_ten, vai_tro
         FROM nguoidung
         WHERE id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch();

    if (!$user) {
        unset($_SESSION['user_id']);
        throw new ApiException('Vui lòng đăng nhập lại.', 401);
    }

    if (!in_array($user['vai_tro'], $allowedRoles, true)) {
        throw new ApiException('Bạn không có quyền thực hiện thao tác này.', 403);
    }

    return $user;
}