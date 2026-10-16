-- Hero slider images that power the home page (index.php).
--
-- Run this ONCE on each environment (local XAMPP and the cPanel database)
-- before opening admin/hero/. Re-running is safe:
--   * the table is only created if it does not exist, and
--   * the slides below are only inserted while the table is empty, so the
--     photos already shown on the home page are not duplicated.
--
-- Design notes:
--   * image_url is a site-relative path (assets/images/hero_images/... for the
--     seeded photos or assets/uploads/hero/... for uploaded ones). The home
--     page prints it straight into a CSS background-image.
--   * position is the CSS background-position for that slide, e.g.
--     "center 45%". It decides which part of the photo stays in view.
--   * display_order is the slide order (ascending); the lowest shows first.
--   * is_visible = 0 keeps a slide in the admin list but hides it on the site.

CREATE TABLE IF NOT EXISTS hero_slides (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    image_url VARCHAR(255) NOT NULL,
    position VARCHAR(60) NOT NULL DEFAULT 'center center',
    display_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_visible_order (is_visible, display_order)
);

-- The nine slides that used to live in assets/images/hero_images/images.json,
-- copied in the same order (the EA logo is the opening slide).
INSERT INTO hero_slides (image_url, position, display_order, is_visible)
SELECT
    seed.image_url,
    seed.position,
    seed.display_order,
    1
FROM (
    SELECT 'assets/images/hero_images/ea-logo-green.jpg' AS image_url, 'center 45%' AS position, 1 AS display_order
    UNION ALL SELECT 'assets/images/hero_images/image1.jpg', 'center 45%', 2
    UNION ALL SELECT 'assets/images/hero_images/image2.jpg', 'center 20%', 3
    UNION ALL SELECT 'assets/images/hero_images/stake-holders6.jpg', 'center center', 4
    UNION ALL SELECT 'assets/images/hero_images/image3.jpg', 'center 20%', 5
    UNION ALL SELECT 'assets/images/hero_images/stake-holders2.jpg', 'center center', 6
    UNION ALL SELECT 'assets/images/hero_images/stake-holders3.jpg', 'center 30%', 7
    UNION ALL SELECT 'assets/images/hero_images/stake-holders5.jpg', 'center 28%', 8
    UNION ALL SELECT 'assets/images/hero_images/stake-holders4.jpg', 'center 30%', 9
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM hero_slides);
