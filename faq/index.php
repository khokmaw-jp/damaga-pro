<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';

$data = faq_load_data();
$items = faq_public_items($data['items'] ?? []);
$categories = $data['categories'] ?? [];
$pageTitle = '遮熱・断熱フィルムのよくある質問｜DAMAGA Pro';
$pageDescription = 'DAMAGA Pro（ダマガプロ）の遮熱・断熱効果、電気代、UVカット、結露対策、施工可否を解説。病院、老人ホーム、オフィス、店舗など法人施設の窓対策をQ&Aで確認できます。';
$canonicalUrl = 'https://damaga-pro.jp/faq/';
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'DAMAGA Pro（ダマガプロ）よくある質問',
    'url' => $canonicalUrl,
    'about' => [
        '@type' => 'Product',
        'name' => 'DAMAGA Pro',
        'alternateName' => ['ダマガプロ', 'ダマガ プロ', 'Damagapro'],
        'category' => '法人向け窓用遮熱・断熱フィルム',
    ],
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => array_map(static fn(array $item, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => 'https://damaga-pro.jp/faq/' . $item['slug'] . '/',
            'name' => $item['question'],
        ], $items, array_keys($items)),
    ],
];
require __DIR__ . '/partials/header.php';
?>
  <main id="main">
    <section class="faq-hero">
      <div class="container faq-hero-inner">
        <div><p class="eyebrow light">FAQ KNOWLEDGE BASE</p><h1>DAMAGA Proの<br>遮熱・断熱フィルムQ&amp;A</h1></div>
        <p>DAMAGA Pro（ダマガプロ）の窓用遮熱フィルムについて、暑さ対策、冬の断熱、UVカット、結露抑制、エアコン効率改善、費用、施工、施設別の活用をまとめています。</p>
      </div>
    </section>

    <section class="faq-directory">
      <div class="container">
        <div class="faq-tools" aria-label="Q&Aの絞り込み">
          <label class="faq-search"><span>キーワードで探す</span><input type="search" placeholder="例：電気代、病院、熱割れ" data-faq-search></label>
          <div class="faq-category-tabs" data-faq-tabs>
            <button type="button" class="is-active" data-category="all">すべて</button>
            <?php foreach ($categories as $category): ?><button type="button" data-category="<?= faq_escape($category['id']) ?>"><?= faq_escape($category['name']) ?></button><?php endforeach; ?>
          </div>
        </div>

        <?php foreach ($categories as $category): ?>
        <section class="faq-category-section" data-category-section="<?= faq_escape($category['id']) ?>">
          <div class="faq-category-heading"><div><p class="eyebrow"><?= faq_escape(strtoupper($category['id'])) ?></p><h2><?= faq_escape($category['name']) ?></h2></div><p><?= faq_escape($category['description']) ?></p></div>
          <div class="faq-card-grid">
            <?php foreach ($items as $item): if (($item['category'] ?? '') !== $category['id']) continue; ?>
            <article class="faq-card" data-faq-card data-category="<?= faq_escape($item['category']) ?>" data-search="<?= faq_escape($item['question'] . ' ' . $item['short_answer'] . ' ' . implode(' ', $item['tags'] ?? [])) ?>">
              <p class="faq-card-label">Q</p>
              <h3><a href="/faq/<?= faq_escape($item['slug']) ?>/"><?= faq_escape($item['question']) ?></a></h3>
              <p><?= faq_escape($item['short_answer']) ?></p>
              <div class="faq-tags"><?php foreach ($item['tags'] ?? [] as $tag): ?><button type="button" data-tag="<?= faq_escape($tag) ?>">#<?= faq_escape($tag) ?></button><?php endforeach; ?></div>
              <a class="faq-card-link" href="/faq/<?= faq_escape($item['slug']) ?>/">詳しく読む <span>→</span></a>
            </article>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endforeach; ?>
        <p class="faq-no-results" data-faq-empty hidden>該当するQ&amp;Aが見つかりませんでした。別のキーワードでお試しください。</p>
      </div>
    </section>

    <section class="faq-bottom-cta">
      <div class="container faq-bottom-cta-inner"><div><p class="eyebrow light">FREE CONSULTATION</p><h2>自施設の窓について相談したい方へ</h2><p>フォーム送信後、担当者からの返信メールへ窓の写真を添付いただけます。</p></div><a class="button button-accent" href="/#contact">窓の写真・寸法を送って相談する <span>→</span></a></div>
    </section>
  </main>
  <script src="/assets/js/faq.js?v=20260620-1" defer></script>
<?php require __DIR__ . '/partials/footer.php'; ?>
