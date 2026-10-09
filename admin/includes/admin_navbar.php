<?php
/**
 * Shared admin top bar and navigation.
 *
 * Included on every admin page except the login screen (admin/index.php)
 * and the logout script (admin/logout.php). The admin header is defined
 * in this ONE file so menu changes apply across the whole admin area.
 *
 * The including page must define two variables BEFORE including this file:
 *
 *   $adminPage  - subtitle shown next to the "EA Research Group" brand,
 *                 e.g. "Messages Inbox" or "Opportunities Manager".
 *
 *   $adminLinks - a list of [label, href] pairs rendered on the right.
 *                 Examples:
 *                   - Root admin pages: [['Dashboard', 'dashboard.php'],
 *                                        ['Log out', 'logout.php']]
 *                   - Pages inside admin/opportunities and admin/publications
 *                     must prefix the hrefs: [['Log out', '../logout.php']]
 */
?>
<header class="admin-topbar">
    <div>
        <strong>EA Research Group</strong>
        <span><?= $adminPage ?></span>
    </div>

    <nav class="admin-topnav">
        <?php foreach ($adminLinks as $adminLink): ?>
            <a href="<?= $adminLink[1] ?>"><?= $adminLink[0] ?></a>
        <?php endforeach; ?>
    </nav>
</header>