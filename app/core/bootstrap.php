<?php
declare(strict_types=1);

// Múi giờ VN: PHP (new DateTime) và MySQL NOW() phải cùng một đồng hồ,
// nếu không ghế vừa giữ đã bị coi là hết hạn ở checkout (lỗi 409).
date_default_timezone_set('Asia/Ho_Chi_Minh');

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

