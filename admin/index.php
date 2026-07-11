<?php

declare(strict_types=1);

require_once '/home/earesearch/earg_private/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo 'Admin backend connected to database.';
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database connection failed.';
}