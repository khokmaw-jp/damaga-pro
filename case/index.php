<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';

$data = case_load_data();
$items = case_public_items($data['items'] ?? []);
$categories = $data['categories'] ?? [];
$categoryMap = case_category_map($categories);
$pageTitle = '導入実績・施工事例｜病院・介護施設の窓対策｜DAMAGA Pro';
$pageDescription = 'DAMAGA Proの窓用遮熱フィルムを導入した病院、介護施設、老人ホーム、オフィス、学校、商業施設の施工事例を、写真と担当者の声で紹介します。';
$canonicalUrl = 'https://damaga-pro.jp/case/';
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'DAMAGA Pro 導入実績・施工事例',
    'url' => $canonicalUrl,
    'about' => ['@type' => 'Product', 'name' => 'DAMAGA Pro', 'category' => '法人向け窓用遮熱・断熱フィルム'],
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => array_map(static fn(array $item, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => 'https://damaga-pro.jp/case/' . $item['slug'] . '/',
            'name' => $item['facility_name'] . 'の施工事例',
        ], $items, array_keys($items)),
    ],
];
require __DIR__ . '/partials/header.php';
?>
<main id="main">
  <section class="case-hero"><div class="container case-hero-inner"><div><p class="eyebrow light">INSTALLATION RECORDS</p><h1>導入実績</h1></div><p>施設の窓にどのような課題があり、施工後に現場でどのような変化が語られたのか。写真と取材内容をもとに記録しています。</p></div></section>
  <section class="case-directory"><div class="container">
    <div class="case-filters" data-case-filters><button type="button" class="is-active" data-case-category="all">すべて</button><?php foreach ($categories as $category): ?><button type="button" data-case-category="<?= case_escape($category['id']) ?>"><?= case_escape($category['name']) ?></button><?php endforeach; ?></div>
    <?php if ($items): ?><div class="case-card-grid">
      <?php foreach ($items as $item): $category = $categoryMap[$item['category']] ?? ['name' => '施設']; ?>
      <article class="case-card" data-case-card data-category="<?= case_escape($item['category']) ?>">
        <a class="case-card-image" href="/case/<?= case_escape($item['slug']) ?>/"><img src="<?= case_escape($item['main_image']) ?>" alt="<?= case_escape($item['facility_name']) ?>のDAMAGA Pro施工事例" loading="lazy"><span><?= case_escape($category['name']) ?></span></a>
        <div class="case-card-body"><p class="case-card-date">施工 <?= case_escape($item['installation_date']) ?></p><h2><a href="/case/<?= case_escape($item['slug']) ?>/"><?= case_escape($item['facility_name']) ?></a></h2><dl><div><dt>所在地</dt><dd><?= case_escape($item['location']) ?></dd></div><div><dt>施工箇所</dt><dd><?= case_escape(implode('・', $item['installation_locations'] ?? [])) ?></dd></div></dl><p><?= case_escape($item['summary']) ?></p><a class="case-card-link" href="/case/<?= case_escape($item['slug']) ?>/">詳細を見る <span>→</span></a></div>
      </article>
      <?php endforeach; ?>
    </div><?php else: ?><p class="case-empty">公開中の導入実績はありません。</p><?php endif; ?>
  </div></section>
  <section class="case-bottom-cta"><div class="container case-bottom-cta-inner"><div><p class="eyebrow light">FREE CONSULTATION</p><h2>施設の窓について相談する</h2><p>窓の写真や寸法をもとに、施工可否と導入方法をご案内します。</p></div><a class="button button-accent" href="/#contact">無料相談へ進む <span>→</span></a></div></section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
