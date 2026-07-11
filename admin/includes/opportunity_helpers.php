<?php

declare(strict_types=1);

function opportunity_categories(): array
{
    return [
        'Scholarship',
        'Fellowship',
        'Internship',
        'Grant',
        'Conference',
        'Training',
        'Job',
        'Research Opportunity',
        'Other',
    ];
}

function opportunity_statuses(): array
{
    return [
        'Open',
        'Closing Soon',
        'Closed',
    ];
}

function make_slug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'opportunity';
}

function unique_opportunity_slug(PDO $pdo, string $title, ?int $ignoreId = null): string
{
    $base = make_slug($title);
    $slug = $base;
    $counter = 2;

    while (true) {
        if ($ignoreId) {
            $stmt = $pdo->prepare("
                SELECT id
                FROM opportunities
                WHERE slug = :slug
                  AND id != :id
                LIMIT 1
            ");

            $stmt->execute([
                ':slug' => $slug,
                ':id' => $ignoreId,
            ]);
        } else {
            $stmt = $pdo->prepare("
                SELECT id
                FROM opportunities
                WHERE slug = :slug
                LIMIT 1
            ");

            $stmt->execute([
                ':slug' => $slug,
            ]);
        }

        if (!$stmt->fetch()) {
            return $slug;
        }

        $slug = $base . '-' . $counter;
        $counter++;
    }
}

function clean_date_or_null(string $date): ?string
{
    $date = trim($date);

    if ($date === '') {
        return null;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return null;
    }

    return $date;
}
