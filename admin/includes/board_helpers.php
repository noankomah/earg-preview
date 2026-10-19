<?php
/**
 * Shared helpers for the board member pages (admin/board/*).
 *
 * Mirrors team_helpers.php: the upload rules and the file removal rules live
 * here so the add and edit pages can never disagree about what is allowed.
 */

declare(strict_types=1);

// Board photos are uploaded from the admin, stored under the public assets
// folder so the website can serve them directly, and referenced in the
// database by a site-relative path (the public page runs it through url()).
const BOARD_UPLOAD_DIR = __DIR__ . '/../../assets/uploads/board/';
const BOARD_UPLOAD_WEB_PATH = 'assets/uploads/board/';

/**
 * Validate an uploaded board photo and move it into the uploads folder.
 *
 * Returns ['success' => true, 'path' => ...] on success, or
 * ['success' => false, 'error' => message] on failure.
 */
function handle_board_image_upload(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if (
            $file['error'] === UPLOAD_ERR_INI_SIZE ||
            $file['error'] === UPLOAD_ERR_FORM_SIZE
        ) {
            return ['success' => false, 'error' => 'The photo is too large. Maximum size is 3 MB.'];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'Please choose a photo to upload.'];
        }

        return ['success' => false, 'error' => 'The photo could not be uploaded. Please try again.'];
    }

    if ((int) $file['size'] > 3 * 1024 * 1024) {
        return ['success' => false, 'error' => 'The photo is too large. Maximum size is 3 MB.'];
    }

    $info = getimagesize($file['tmp_name']);

    if ($info === false) {
        return ['success' => false, 'error' => 'The uploaded file is not a valid image.'];
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

    $filename = 'board-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

    if (!is_dir(BOARD_UPLOAD_DIR) && !mkdir(BOARD_UPLOAD_DIR, 0755, true)) {
        return ['success' => false, 'error' => 'The uploads folder could not be created.'];
    }

    if (!move_uploaded_file($file['tmp_name'], BOARD_UPLOAD_DIR . $filename)) {
        return ['success' => false, 'error' => 'The photo could not be saved. Please try again.'];
    }

    return ['success' => true, 'path' => BOARD_UPLOAD_WEB_PATH . $filename];
}

/**
 * Remove an uploaded board photo file (safe when called with any path).
 *
 * Only ever deletes files inside our own uploads folder, so a bad value in
 * the database can never delete something else on the server.
 */
function delete_board_image(string $path): void
{
    $base = realpath(BOARD_UPLOAD_DIR);

    if ($base === false) {
        return;
    }

    $fullPath = realpath(__DIR__ . '/../../' . $path);

    if ($fullPath !== false && str_starts_with($fullPath, $base . DIRECTORY_SEPARATOR)) {
        @unlink($fullPath);
    }
}

/**
 * Suggested display order for a brand new member: one after the highest
 * order already in use, so new cards land at the end of the public grid.
 */
function board_next_display_order(PDO $pdo): int
{
    $statement = $pdo->query(
        'SELECT COALESCE(MAX(display_order), 0) AS max_order FROM board_members'
    );

    return (int) $statement->fetch()['max_order'] + 1;
}

/**
 * Turn a failed board_members query into an instruction the admin can act on.
 *
 * The most common failure by far is the schema file not having been
 * imported on that environment yet, so the message says exactly that.
 */
function board_error_message(Throwable $exception): string
{
    $message = $exception->getMessage();

    if (
        stripos($message, 'board_members') !== false &&
        (stripos($message, '42S02') !== false || stripos($message, "doesn't exist") !== false)
    ) {
        return 'The board_members table has not been created yet. Import admin/board/schema.sql into the database, then reload this page.';
    }

    return 'The board list could not be loaded. Please try again.';
}
