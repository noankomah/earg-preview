<?php

declare(strict_types=1);

function publication_types(): array
{
    return [
        'Blog',
        'Newsletter',
        'Report',
    ];
}

function make_publication_slug(string $text): string
{
    $text = strtolower(trim($text));

    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

    $text = trim($text, '-');

    return $text !== '' ? $text : 'publication';
}

function unique_publication_slug(
    PDO $pdo,
    string $title,
    ?int $ignoreId = null
): string {
    $baseSlug = make_publication_slug($title);
    $slug = $baseSlug;
    $counter = 2;

    while (true) {
        $sql = "
            SELECT id
            FROM publications
            WHERE slug = :slug
        ";

        $params = [
            ':slug' => $slug,
        ];

        if ($ignoreId !== null) {
            $sql .= " AND id != :ignore_id";
            $params[':ignore_id'] = $ignoreId;
        }

        $sql .= " LIMIT 1";

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        if (!$statement->fetch()) {
            return $slug;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

function clean_publication_date_or_null(string $date): ?string
{
    $date = trim($date);

    if ($date === '') {
        return null;
    }

    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    $errors = DateTime::getLastErrors();

    if (
        !$parsed ||
        ($errors !== false &&
            ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
    ) {
        return null;
    }

    return $parsed->format('Y-m-d');
}

// Images for publications (including blogs) are uploaded from the admin,
// stored under the public assets folder so the website can serve them
// directly, and referenced by a site-relative path in the DB.
const PUBLICATIONS_UPLOAD_DIR = __DIR__ . '/../../assets/uploads/publications/';
const PUBLICATIONS_UPLOAD_WEB_PATH = 'assets/uploads/publications/';

/**
 * Validate an uploaded publication image and move it into the uploads folder.
 *
 * Returns ['success' => true, 'path' => ...] on success, or
 * ['success' => false, 'error' => message] on failure.
 */
function handle_publication_image_upload(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if (
            $file['error'] === UPLOAD_ERR_INI_SIZE ||
            $file['error'] === UPLOAD_ERR_FORM_SIZE
        ) {
            return ['success' => false, 'error' => 'An image is too large. Maximum size is 3 MB per image.'];
        }

        return ['success' => false, 'error' => 'One of the images could not be uploaded. Please try again.'];
    }

    if ((int) $file['size'] > 3 * 1024 * 1024) {
        return ['success' => false, 'error' => 'An image is too large. Maximum size is 3 MB per image.'];
    }

    $info = getimagesize($file['tmp_name']);

    if ($info === false) {
        return ['success' => false, 'error' => 'One of the uploaded files is not a valid image.'];
    }

    $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];

    if (!in_array($info[2], $allowedTypes, true)) {
        return ['success' => false, 'error' => 'Only PNG, JPG, WEBP and GIF images are allowed.'];
    }

    $extension = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF => 'gif',
    ][$info[2]];

    $filename = 'pub-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

    if (!is_dir(PUBLICATIONS_UPLOAD_DIR) && !mkdir(PUBLICATIONS_UPLOAD_DIR, 0755, true)) {
        return ['success' => false, 'error' => 'The uploads folder could not be created.'];
    }

    if (!move_uploaded_file($file['tmp_name'], PUBLICATIONS_UPLOAD_DIR . $filename)) {
        return ['success' => false, 'error' => 'An image could not be saved. Please try again.'];
    }

    return ['success' => true, 'path' => PUBLICATIONS_UPLOAD_WEB_PATH . $filename];
}

/**
 * Remove an uploaded publication image file (safe when called with any path).
 */
function delete_publication_image(string $path): void
{
    // Only ever delete files that live inside our own uploads folder.
    $base = realpath(PUBLICATIONS_UPLOAD_DIR);

    if ($base === false) {
        return;
    }

    $fullPath = realpath(__DIR__ . '/../../' . $path);

    if ($fullPath !== false && str_starts_with($fullPath, $base . DIRECTORY_SEPARATOR)) {
        @unlink($fullPath);
    }
}

/**
 * Read the stored gallery_images JSON back into an array of image paths.
 */
function decode_publication_gallery(?string $raw): array
{
    if ($raw === null || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        return [];
    }

    $gallery = [];

    foreach ($decoded as $path) {
        $path = (string) $path;

        if ($path !== '') {
            $gallery[] = $path;
        }
    }

    return $gallery;
}
