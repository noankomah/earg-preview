<?php
/**
 * Legacy Founder's Story URL.
 *
 * The page moved into views/About/founder-story.php. This file is kept as
 * a permanent redirect so old links and bookmarks keep working.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/site.php';

header('Location: ' . url('views/About/founder-story.php'), true, 301);
exit;
