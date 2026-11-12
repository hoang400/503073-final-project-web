<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

$root = dirname(__DIR__, 2);

require_once $root . '/app/core/bootstrap.php';

try {
    startAppSession();

    require_once $root . '/app/core/Database.php';
    require_once $root . '/app/models/UserModel.php';
    require_once $root . '/app/services/AuthService.php';
    require_once $root . '/app/controllers/AuthControllers.php';
    require_once $root . '/app/models/MovieModel.php';
    require_once $root . '/app/controllers/MovieController.php';    
    require_once $root . '/app/models/BookingModel.php';
    require_once $root . '/app/controllers/BookingController.php'; 
    require_once $root . '/app/models/OrderModel.php';
    require_once $root . '/app/controllers/OrderController.php';
    
    $routes = require $root . '/routes/api.php';

    $route = $_GET['route'] ?? '';

    if (
        !is_string($route)
        || $route === ''
        || $route[0] !== '/'
    ) {
        throw new ApiException(
            'Đường dẫn API không hợp lệ.',
            400
        );
    }

    $path = parse_url($route, PHP_URL_PATH);
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if (!is_string($path) || !isset($routes[$path])) {
        throw new ApiException('Không tìm thấy API.', 404);
    }

    if (!isset($routes[$path][$method])) {
        header(
            'Allow: ' . implode(', ', array_keys($routes[$path]))
        );

        throw new ApiException(
            'Phương thức không được hỗ trợ.',
            405
        );
    }

    if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        verifyCsrf();
    }

    $handler = $routes[$path][$method];
    $handler();
} catch (ApiException $exception) {
    jsonResponse([
        'ok' => false,
        'error' => [
            'message' => $exception->getMessage(),
        ],
    ], $exception->getCode());
} catch (Throwable $exception) {
    error_log((string) $exception);

    jsonResponse([
        'ok' => false,
        'error' => [
            'message' => 'Không thể xử lý yêu cầu.',
        ],
    ], 500);
}