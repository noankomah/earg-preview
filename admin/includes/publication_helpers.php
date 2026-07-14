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
