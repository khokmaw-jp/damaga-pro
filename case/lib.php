<?php
declare(strict_types=1);

const CASE_DATA_FILE = __DIR__ . '/data/cases.json';

function case_load_data(): array
{
    $json = @file_get_contents(CASE_DATA_FILE);
    $data = $json !== false ? json_decode($json, true) : null;
    return is_array($data) ? $data : ['categories' => [], 'items' => []];
}

function case_save_data(array $data): bool
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) return false;
    $temp = CASE_DATA_FILE . '.tmp';
    if (file_put_contents($temp, $json . "\n", LOCK_EX) === false) return false;
    return rename($temp, CASE_DATA_FILE);
}

function case_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function case_public_items(array $items): array
{
    $items = array_values(array_filter($items, static fn(array $item): bool => !empty($item['published'])));
    usort($items, static fn(array $a, array $b): int => strcmp((string) ($b['installation_date_iso'] ?? ''), (string) ($a['installation_date_iso'] ?? '')));
    return $items;
}

function case_find(array $items, string $slug): ?array
{
    foreach ($items as $item) {
        if (($item['slug'] ?? '') === $slug && !empty($item['published'])) return $item;
    }
    return null;
}

function case_category_map(array $categories): array
{
    $map = [];
    foreach ($categories as $category) $map[$category['id']] = $category;
    return $map;
}

function case_text_paragraphs(string $text): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/u', trim($text)) ?: [])));
}

function case_image_url(string $path): string
{
    if (preg_match('#^https?://#', $path)) return $path;
    return 'https://damaga-pro.jp/' . ltrim($path, '/');
}

function case_create_route(string $slug): bool
{
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) return false;
    $directory = __DIR__ . '/' . $slug;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) return false;
    $content = "<?php\n\$caseSlug = '" . $slug . "';\nrequire dirname(__DIR__) . '/article.php';\n";
    return file_put_contents($directory . '/index.php', $content, LOCK_EX) !== false;
}

function case_sync_sitemap(array $caseData): bool
{
    $urls = [
        ['https://damaga-pro.jp/', 'weekly', '1.0'],
        ['https://damaga-pro.jp/privacy.html', 'monthly', '0.3'],
        ['https://damaga-pro.jp/faq/', 'weekly', '0.8'],
        ['https://damaga-pro.jp/case/', 'weekly', '0.9'],
    ];

    $faqFile = dirname(__DIR__) . '/faq/data/faqs.json';
    $faqJson = @file_get_contents($faqFile);
    $faqData = $faqJson !== false ? json_decode($faqJson, true) : null;
    foreach (($faqData['items'] ?? []) as $item) {
        if (!empty($item['published'])) $urls[] = ['https://damaga-pro.jp/faq/' . $item['slug'] . '/', 'monthly', '0.7'];
    }
    foreach (case_public_items($caseData['items'] ?? []) as $item) {
        $urls[] = ['https://damaga-pro.jp/case/' . $item['slug'] . '/', 'monthly', '0.8'];
    }

    $date = date('Y-m-d');
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as [$location, $frequency, $priority]) {
        $xml .= "  <url>\n    <loc>" . htmlspecialchars($location, ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "    <lastmod>{$date}</lastmod>\n    <changefreq>{$frequency}</changefreq>\n    <priority>{$priority}</priority>\n  </url>\n";
    }
    $xml .= "</urlset>\n";
    $file = dirname(__DIR__) . '/sitemap.xml';
    $temp = $file . '.tmp';
    if (file_put_contents($temp, $xml, LOCK_EX) === false) return false;
    return rename($temp, $file);
}
