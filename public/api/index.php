<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

$root = dirname(__DIR__, 2);

require_once $root . '/app/core/bootstrap.php';

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


require_once $root . '/app/middleware/CsrfMiddleware.php';
require_once $root . '/app/middleware/AuthMiddleware.php';
require_once $root . '/app/middleware/AdminMiddleware.php';
require_once $root . '/app/middleware/StaffMiddleware.php';


require_once $root . '/app/models/AdminModel.php';
require_once $root . '/app/controllers/AdminController.php';
require_once $root . '/app/models/StaffModel.php';
require_once $root . '/app/controllers/StaffController.php';

try {
    startAppSession();

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


    $matchedRoute = null;
    $routeParams = [];

    foreach ($routes as $routePattern => $handlers) {
        // Convert {id} to regex
        $regex = preg_replace('/\{[a-z_]+\}/', '([0-9]+)', $routePattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            $matchedRoute = $routePattern;
            // Extract params (skip first match which is full string)
            array_shift($matches);
            // Get param names from pattern
            preg_match_all('/\{([a-z_]+)\}/', $routePattern, $paramNames);
            $routeParams = array_combine($paramNames[1], $matches);
            break;
        }
    }

    if ($matchedRoute === null || !isset($routes[$matchedRoute])) {
        throw new ApiException('Không tìm thấy API.', 404);
    }

    if (!isset($routes[$matchedRoute][$method])) {
        header(
            'Allow: ' . implode(', ', array_keys($routes[$matchedRoute]))
        );

        throw new ApiException(
            'Phương thức không được hỗ trợ.',
            405
        );
    }

    // Store route params for controllers to use
    $_GET['route_params'] = $routeParams;

    if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        \App\Middleware\CsrfMiddleware::verify();
    }

    $handler = $routes[$matchedRoute][$method];
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
