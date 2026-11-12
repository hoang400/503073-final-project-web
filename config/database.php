<?php
declare(strict_types=1);

$dbPassword = getenv('DB_PASSWORD');

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'cinestar',
    'username' => getenv('DB_USER') ?: '',
    'password' => $dbPassword === false ? '' : $dbPassword,
    'charset' => 'utf8mb4',
];