<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/../admin/includes/db.php';
require_once __DIR__ . '/../admin/includes/publication_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
        'publications' => [],
    ]);

    exit;
}

$type = trim((string) ($_GET['type'] ?? ''));

if ($type !== '' && !in_array($type, publication_types(), true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid publication type.',
        'publications' => [],
    ]);

    exit;
}

try {
    $pdo = db();

    $sql = "
        SELECT
            title,
            slug,
            publication_type,
            summary,
            author,
            publication_date,
            cover_image,
            document_link,
            is_featured,
            created_at
        FROM publications
        WHERE is_published = 1
    ";

    $params = [];

    if ($type !== '') {
        $sql .= " AND publication_type = :publication_type";
        $params[':publication_type'] = $type;
    }

    $sql .= "
        ORDER BY
            is_featured DESC,
            CASE
                WHEN publication_date IS NULL THEN 1
                ELSE 0
            END,
            publication_date DESC,
            created_at DESC
    ";

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    $publications = $statement->fetchAll();

    echo json_encode(
        [
            'success' => true,
            'count' => count($publications),
            'publications' => $publications,
        ],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
} catch (Throwable $exception) {
    error_log('Public publications API error: ' . $exception->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load publications at this time.',
        'publications' => [],
    ]);
}
