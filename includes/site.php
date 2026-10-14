<?php
/**
 * Shared site helpers for the public pages.
 *
 * Defines EARG_BASE (the URL path the site is served from) and the url()
 * helper, which builds links that keep working no matter how deep a page
 * sits in the folder structure.
 *
 * Local XAMPP serves the site from /earg, while cPanel serves it from the
 * domain root, so links can never be hard-coded. EARG_BASE is worked out by
 * comparing the site root (the folder that contains /includes) with the web
 * server's document root:
 *
 *   Local : C:/xampp/htdocs/earg vs C:/xampp/htdocs    -> "/earg"
 *   cPanel: /home/earesearch/public_html vs same      -> ""
 */

declare(strict_types=1);

if (!defined('EARG_BASE')) {
    // Physical path of the site root (the folder that contains /includes).
    $siteRoot = str_replace('\\', '/', dirname(__DIR__));

    // Physical path of the web server document root, when available.
    $documentRoot = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));

    $base = '';

    // stripos() keeps this working even if the drive letter casing differs.
    if ($documentRoot !== '' && $documentRoot !== '/' && stripos($siteRoot, $documentRoot) === 0) {
        $base = substr($siteRoot, strlen($documentRoot));
    }

    define('EARG_BASE', rtrim($base, '/'));
}

if (!function_exists('url')) {
    /**
     * Build a site-relative URL, e.g. url('assets/css/about.css?v=2')
     * or url('views/About/who-we-are.php').
     */
    function url(string $path = ''): string
    {
        return EARG_BASE . '/' . ltrim($path, '/');
    }
}
