<?php
declare(strict_types=1);

const FAQ_DATA_FILE = __DIR__ . '/data/faqs.json';

function faq_load_data(): array
{
    $json = @file_get_contents(FAQ_DATA_FILE);
    $data = $json !== false ? json_decode($json, true) : null;
    return is_array($data) ? $data : ['categories' => [], 'items' => []];
}

function faq_save_data(array $data): bool
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    $temp = FAQ_DATA_FILE . '.tmp';
    if (file_put_contents($temp, $json . "\n", LOCK_EX) === false) {
        return false;
    }
    return rename($temp, FAQ_DATA_FILE);
}

function faq_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function faq_find(array $items, string $slug): ?array
{
    foreach ($items as $item) {
        if (($item['slug'] ?? '') === $slug && !empty($item['published'])) {
            return $item;
        }
    }
    return null;
}

function faq_category_map(array $categories): array
{
    $map = [];
    foreach ($categories as $category) {
        $map[$category['id']] = $category;
    }
    return $map;
}

function faq_public_items(array $items): array
{
    return array_values(array_filter($items, static fn(array $item): bool => !empty($item['published'])));
}

function faq_create_route(string $slug): bool
{
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        return false;
    }
    $directory = __DIR__ . '/' . $slug;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        return false;
    }
    $content = "<?php\n\$faqSlug = '" . $slug . "';\nrequire dirname(__DIR__) . '/article.php';\n";
    return file_put_contents($directory . '/index.php', $content, LOCK_EX) !== false;
}

function faq_sync_sitemap(array $data): bool
{
    $urls = [
        ['https://damaga-pro.jp/', 'weekly', '1.0'],
        ['https://damaga-pro.jp/privacy.html', 'monthly', '0.3'],
        ['https://damaga-pro.jp/faq/', 'weekly', '0.8'],
        ['https://damaga-pro.jp/case/', 'weekly', '0.9'],
    ];
    foreach (faq_public_items($data['items'] ?? []) as $item) {
        $urls[] = ['https://damaga-pro.jp/faq/' . $item['slug'] . '/', 'monthly', '0.7'];
    }
    $caseFile = dirname(__DIR__) . '/case/data/cases.json';
    $caseJson = @file_get_contents($caseFile);
    $caseData = $caseJson !== false ? json_decode($caseJson, true) : null;
    foreach (($caseData['items'] ?? []) as $item) {
        if (!empty($item['published'])) $urls[] = ['https://damaga-pro.jp/case/' . $item['slug'] . '/', 'monthly', '0.8'];
    }

    $date = date('Y-m-d');
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as [$location, $frequency, $priority]) {
        $xml .= "  <url>\n";
        $xml .= '    <loc>' . htmlspecialchars($location, ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "    <lastmod>{$date}</lastmod>\n";
        $xml .= "    <changefreq>{$frequency}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= "</urlset>\n";

    $file = dirname(__DIR__) . '/sitemap.xml';
    $temp = $file . '.tmp';
    if (file_put_contents($temp, $xml, LOCK_EX) === false) {
        return false;
    }
    return rename($temp, $file);
}
