-- Team members table that powers the public "Our Team" page.
--
-- Run this ONCE on each environment (local XAMPP and the cPanel database)
-- before opening admin/team/. Re-running is safe:
--   * the table is only created if it does not exist, and
--   * the three members below are only inserted while the table is empty,
--     so the people already shown on the website are not duplicated.
--
-- Design notes:
--   * name and role_title are printed under the photo on the public page.
--   * photo_url is a site-relative path (assets/images/... for seeded photos
--     or assets/uploads/team/... for uploaded ones). The public page runs it
--     through url() so it also works when the site is served from a sub-folder.
--   * display_order sorts the cards on the public page (ascending).
--   * is_visible = 0 keeps a member in the admin list but hides the card.

CREATE TABLE IF NOT EXISTS team_members (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    role_title VARCHAR(120) NULL,
    photo_url VARCHAR(255) NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_visible_order (is_visible, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the three members that were already hardcoded on the public page.
-- The NOT EXISTS guard keeps this to zero rows when the table has content.
INSERT INTO team_members (name, role_title, photo_url, display_order, is_visible)
SELECT
    seed.name,
    seed.role_title,
    seed.photo_url,
    seed.display_order,
    seed.is_visible
FROM (
    SELECT 'Emmanuel Adom' AS name,
           'Executive Director' AS role_title,
           'assets/images/Emma.jpg' AS photo_url,
           1 AS display_order,
           1 AS is_visible
    UNION ALL
    SELECT 'Abdul Kahar Abdul Rahman Gunu',
           'Operations Officer',
           'assets/images/kahar.jpg',
           2,
           1
    UNION ALL
    SELECT 'Nana Ankomah',
           'Digital and Communications Lead',
           'assets/images/nana2.png',
           3,
           1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM team_members);
