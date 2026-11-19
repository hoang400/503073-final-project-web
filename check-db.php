<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/app/core/Database.php';

try {
    $pdo = Database::connection();

    $database = $pdo
        ->query('SELECT DATABASE()')
        ->fetchColumn();

    $tables = $pdo
        ->query('SHOW TABLES')
        ->fetchAll(PDO::FETCH_COLUMN);

    echo 'Connection OK', PHP_EOL;
    echo 'Database: ', $database, PHP_EOL;
    echo 'Tables: ', count($tables), PHP_EOL;
} catch (Throwable $exception) {
    fwrite(
        STDERR,
        'Connection failed: ' . $exception->getMessage() . PHP_EOL
    );

    exit(1);
}