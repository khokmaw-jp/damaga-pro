<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';

$data = faq_load_data();
$items = faq_public_items($data['items'] ?? []);
$item = faq_find($items, (string) ($faqSlug ?? ''));
if ($item === null) {
    http_response_code(404);
    $pageTitle = 'ページが見つかりません｜DAMAGA Pro';
    $pageDescription = 'お探しのQ&Aページは見つかりませんでした。';
    $canonicalUrl = 'https://damaga-pro.jp/faq/';
    require __DIR__ . '/partials/header.php';
    echo '<main id="main"><section class="faq-article"><div class="container faq-article-layout"><article class="faq-article-main"><h1>ページが見つかりません</h1><p><a class="button button-primary" href="/faq/">Q&A一覧へ戻る</a></p></article></div></section></main>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$categoryMap = faq_category_map($data['categories'] ?? []);
$category = $categoryMap[$item['category']] ?? ['name' => 'Q&A'];
$relatedItems = [];
foreach ($item['related'] ?? [] as $relatedSlug) {
    $related = faq_find($items, $relatedSlug);
    if ($related !== null) $relatedItems[] = $related;
}
$pageTitle = $item['title'];
$pageDescription = $item['meta_description'];
$canonicalUrl = 'https://damaga-pro.jp/faq/' . $item['slug'] . '/';
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'about' => [
        '@type' => 'Product',
        'name' => 'DAMAGA Pro',
        'alternateName' => ['ダマガプロ', 'ダマガ プロ', 'Damagapro'],
        'category' => '法人向け窓用遮熱・断熱フィルム',
    ],
    'mainEntity' => [[
        '@type' => 'Question',
        'name' => $item['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['short_answer']],
    ]],
];
require __DIR__ . '/partials/header.php';
?>
  <main id="main">
    <section class="faq-article-hero">
      <div class="container">
        <nav class="breadcrumbs" aria-label="パンくずリスト"><a href="/">トップ</a><span>›</span><a href="/faq/">よくある質問</a><span>›</span><span><?= faq_escape($category['name']) ?></span></nav>
        <p class="faq-article-category"><?= faq_escape($category['name']) ?></p>
        <h1><?= faq_escape($item['question']) ?></h1>
        <div class="faq-tags"><?php foreach ($item['tags'] ?? [] as $tag): ?><a href="/faq/?tag=<?= rawurlencode($tag) ?>">#<?= faq_escape($tag) ?></a><?php endforeach; ?></div>
      </div>
    </section>

    <section class="faq-article">
      <div class="container faq-article-layout">
        <article class="faq-article-main">
          <section class="faq-answer-box" aria-labelledby="answer-title"><p class="faq-answer-mark">A</p><h2 id="answer-title">結論</h2><p><?= faq_escape($item['short_answer']) ?></p></section>
          <?php if (!empty($item['calculator_type'])) require __DIR__ . '/partials/calculator.php'; ?>
          <?php foreach ($item['sections'] ?? [] as $section): ?>
          <section class="faq-content-section">
            <h2><?= faq_escape($section['heading']) ?></h2>
            <?php foreach ($section['paragraphs'] ?? [] as $paragraph): ?><p><?= faq_escape($paragraph) ?></p><?php endforeach; ?>
          </section>
          <?php endforeach; ?>
          <aside class="faq-caution"><strong>DAMAGA Pro導入前の確認について</strong><p>遮熱・断熱効果や施工可否は、ガラス種類、建物条件、方位、施工環境などによって異なります。正式な判断は現地調査後にご案内します。</p></aside>
        </article>

        <aside class="faq-article-side">
          <div class="faq-side-card"><p>窓の状況を確認します</p><h2>写真・寸法で<br>無料相談</h2><p>フォーム送信後、担当者からの返信メールへ窓全体やガラス刻印の写真を添付いただけます。</p><a class="button button-accent" href="/#contact">相談フォームへ <span>→</span></a></div>
          <a class="faq-back-link" href="/faq/">Q&amp;A一覧を見る <span>→</span></a>
        </aside>
      </div>
    </section>

    <?php if ($relatedItems): ?>
    <section class="faq-related">
      <div class="container"><div class="faq-related-heading"><p class="eyebrow">RELATED Q&amp;A</p><h2>関連する質問</h2></div><div class="faq-related-grid">
        <?php foreach ($relatedItems as $related): ?><a href="/faq/<?= faq_escape($related['slug']) ?>/"><small>Q</small><strong><?= faq_escape($related['question']) ?></strong><span>詳しく読む →</span></a><?php endforeach; ?>
      </div></div>
    </section>
    <?php endif; ?>

    <section class="faq-bottom-cta"><div class="container faq-bottom-cta-inner"><div><p class="eyebrow light">FREE CONSULTATION</p><h2>窓の写真・寸法を送って相談する</h2><p>フォームに分かる範囲の寸法をご記入ください。写真は受付後の返信メールへ添付いただけます。</p></div><a class="button button-accent" href="/#contact">無料相談へ進む <span>→</span></a></div></section>
  </main>
<?php require __DIR__ . '/partials/footer.php'; ?>
