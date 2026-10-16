<?php
/**
 * Shared helpers for the hero slider pages (admin/hero/*).
 *
 * The upload rules and the file removal rules live here so the add and edit
 * pages can never disagree about what is allowed, in the same way
 * team_helpers.php does for team members.
 */

declare(strict_types=1);

// Hero photos are uploaded from the admin, stored under the public assets
// folder so the home page can serve them directly, and referenced in the
// database by a site-relative path (the home page prints that into a CSS
// background-image).
const HERO_UPLOAD_DIR = __DIR__ . '/../../assets/uploads/hero/';
const HERO_UPLOAD_WEB_PATH = 'assets/uploads/hero/';

/**
 * Validate an uploaded hero photo and move it into the uploads folder.
 *
 * Returns ['success' => true, 'path' => ...] on success, or
 * ['success' => false, 'error' => message] on failure.
 */
function handle_hero_image_upload(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if (
            $file['error'] === UPLOAD_ERR_INI_SIZE ||
            $file['error'] === UPLOAD_ERR_FORM_SIZE
        ) {
            return ['success' => false, 'error' => 'The image is too large. Maximum size is 3 MB.'];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'Please choose an image to upload.'];
        }

        return ['success' => false, 'error' => 'The image could not be uploaded. Please try again.'];
    }

    if ((int) $file['size'] > 3 * 1024 * 1024) {
        return ['success' => false, 'error' => 'The image is too large. Maximum size is 3 MB.'];
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

    $filename = 'hero-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

    if (!is_dir(HERO_UPLOAD_DIR) && !mkdir(HERO_UPLOAD_DIR, 0755, true)) {
        return ['success' => false, 'error' => 'The uploads folder could not be created.'];
    }

    if (!move_uploaded_file($file['tmp_name'], HERO_UPLOAD_DIR . $filename)) {
        return ['success' => false, 'error' => 'The image could not be saved. Please try again.'];
    }

    return ['success' => true, 'path' => HERO_UPLOAD_WEB_PATH . $filename];
}

/**
 * Remove an uploaded hero image file (safe when called with any path).
 *
 * Only ever deletes files inside our own uploads folder, so a bad value in
 * the database can never delete something else on the server. The seeded
 * slides point at assets/images/hero_images/ and are therefore never deleted.
 */
function delete_hero_image(string $path): void
{
    $base = realpath(HERO_UPLOAD_DIR);

    if ($base === false) {
        return;
    }

    $fullPath = realpath(__DIR__ . '/../../' . $path);

    if ($fullPath !== false && str_starts_with($fullPath, $base . DIRECTORY_SEPARATOR)) {
        @unlink($fullPath);
    }
}

/**
 * Suggested display order for a brand new slide: one after the highest order
 * already in use, so new slides land at the end of the rotation.
 */
function hero_next_display_order(PDO $pdo): int
{
    $statement = $pdo->query(
        'SELECT COALESCE(MAX(display_order), 0) AS max_order FROM hero_slides'
    );

    return (int) $statement->fetch()['max_order'] + 1;
}

/**
 * Turn a failed hero_slides query into an instruction the admin can act on.
 *
 * The most common failure by far is the schema file not having been imported
 * on that environment yet, so the message says exactly that.
 */
function hero_error_message(Throwable $exception): string
{
    $message = $exception->getMessage();

    if (
        stripos($message, 'hero_slides') !== false &&
        (stripos($message, '42S02') !== false || stripos($message, "doesn't exist") !== false)
    ) {
        return 'The hero_slides table has not been created yet. Import admin/hero/schema.sql into the database, then reload this page.';
    }

    return 'The hero slides could not be loaded. Please try again.';
}
