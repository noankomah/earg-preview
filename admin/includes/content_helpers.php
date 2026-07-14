<?php

declare(strict_types=1);

/**
 * Keeps only the limited formatting allowed in rich-text content.
 *
 * Allowed:
 * headings, paragraphs, line breaks, bold, italic,
 * bulleted lists, numbered lists, list items and links.
 */
function sanitize_rich_text(string $html): string
{
    $html = trim($html);

    if ($html === '') {
        return '';
    }

    $allowedTags = '<h2><h3><p><br><strong><b><em><i><ul><ol><li><a>';

    $html = strip_tags($html, $allowedTags);

    // Remove event handlers such as onclick, onerror and onload.
    $html = preg_replace(
        '/\s+on[a-z]+\s*=\s*(["\']).*?\1/iu',
        '',
        $html
    ) ?? '';

    $html = preg_replace(
        '/\s+on[a-z]+\s*=\s*[^\s>]+/iu',
        '',
        $html
    ) ?? '';

    // Remove style, class and id attributes.
    $html = preg_replace(
        '/\s+(style|class|id)\s*=\s*(["\']).*?\2/iu',
        '',
        $html
    ) ?? '';

    // Remove unsafe URL schemes from links.
    $html = preg_replace_callback(
        '/<a\b([^>]*)href\s*=\s*(["\'])(.*?)\2([^>]*)>/iu',
        static function (array $matches): string {
            $url = trim(html_entity_decode($matches[3], ENT_QUOTES, 'UTF-8'));

            if (
                $url === '' ||
                preg_match('/^(javascript|data|vbscript):/i', $url)
            ) {
                return '<a>';
            }

            $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

            return '<a href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">';
        },
        $html
    ) ?? '';

    return trim($html);
}
