<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/../admin/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
    ]);

    exit;
}

try {
    $pdo = db();

    $statement = $pdo->prepare(
        "
        SELECT
            title,
            slug,
            category,
            summary,
            deadline,
            status,
            country,
            host_organization,
            application_link,
            is_featured,
            created_at
        FROM opportunities
        WHERE is_published = 1
        ORDER BY
            is_featured DESC,
            CASE
                WHEN deadline IS NULL THEN 1
                ELSE 0
            END,
            deadline ASC,
            created_at DESC
        "
    );

    $statement->execute();

    $opportunities = $statement->fetchAll();

    echo json_encode(
        [
            'success' => true,
            'count' => count($opportunities),
            'opportunities' => $opportunities,
        ],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (Throwable $exception) {
    error_log('Public opportunities API error: ' . $exception->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load opportunities at this time.',
        'opportunities' => [],
    ]);
}
