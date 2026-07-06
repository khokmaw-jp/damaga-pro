<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';

$data = case_load_data();
$items = case_public_items($data['items'] ?? []);
$item = case_find($items, (string) ($caseSlug ?? ''));
if ($item === null) {
    http_response_code(404);
    $pageTitle = '導入実績が見つかりません｜DAMAGA Pro';
    $pageDescription = 'お探しの導入実績ページは見つかりませんでした。';
    $canonicalUrl = 'https://damaga-pro.jp/case/';
    require __DIR__ . '/partials/header.php';
    echo '<main id="main"><section class="case-article"><div class="container"><h1>ページが見つかりません</h1><p><a class="button button-primary" href="/case/">導入実績一覧へ戻る</a></p></div></section></main>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$categoryMap = case_category_map($data['categories'] ?? []);
$category = $categoryMap[$item['category']] ?? ['name' => '施設'];
$pageTitle = $item['title'];
$pageDescription = $item['meta_description'];
$canonicalUrl = 'https://damaga-pro.jp/case/' . $item['slug'] . '/';
$ogImage = case_image_url($item['main_image']);
$ogType = 'article';
$articleData = [
    '@type' => 'Article',
    '@id' => $canonicalUrl . '#article',
    'headline' => $item['facility_name'] . 'の窓用遮熱フィルム施工事例',
    'description' => $item['meta_description'],
    'image' => array_map('case_image_url', array_column($item['images'] ?? [], 'src')),
    'dateModified' => $item['updated'],
    'mainEntityOfPage' => $canonicalUrl,
    'author' => ['@type' => 'Organization', 'name' => '株式会社ファンビータ'],
    'publisher' => ['@type' => 'Organization', 'name' => '株式会社ファンビータ', 'url' => 'https://damaga-pro.jp/'],
    'about' => ['@type' => 'Product', 'name' => 'DAMAGA Pro', 'category' => '法人向け窓用遮熱・断熱フィルム'],
    'contentLocation' => ['@type' => 'Place', 'name' => $item['facility_name'], 'address' => $item['location']],
];
$installationDateIso = (string) ($item['installation_date_iso'] ?? '');
if (preg_match('/^\d{4}-\d{2}$/', $installationDateIso)) {
    $articleData['datePublished'] = $installationDateIso . '-01';
} elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $installationDateIso)) {
    $articleData['datePublished'] = $installationDateIso;
}
$structuredData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        $articleData,
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'トップ', 'item' => 'https://damaga-pro.jp/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => '導入実績', 'item' => 'https://damaga-pro.jp/case/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $item['facility_name'], 'item' => $canonicalUrl],
            ],
        ],
    ],
];
require __DIR__ . '/partials/header.php';
?>
<main id="main">
  <article>
    <header class="case-detail-hero"><div class="container"><nav class="case-breadcrumbs" aria-label="パンくずリスト"><a href="/">トップ</a><span>›</span><a href="/case/">導入実績</a><span>›</span><span><?= case_escape($item['facility_name']) ?></span></nav><p class="case-category-label"><?= case_escape($category['name']) ?>の導入実績</p><h1><?= case_escape($item['facility_name']) ?><small>窓用遮熱フィルム施工事例</small></h1><p class="case-detail-summary"><?= case_escape($item['summary']) ?></p></div></header>
    <section class="case-facility-profile"><div class="container case-facility-profile-card"><figure><img src="<?= case_escape($item['facility_image'] ?? $item['main_image']) ?>" alt="<?= case_escape($item['facility_image_alt'] ?? $item['facility_name'] . 'の外観') ?>"></figure><div class="case-facility-profile-body"><p class="eyebrow">FACILITY PROFILE</p><h2><?= case_escape($item['facility_name']) ?></h2><p><?= case_escape($item['facility_profile_text'] ?? '病院・介護施設の窓環境改善を目的に、病室と共用部の窓へDAMAGA Proを施工しました。') ?></p><dl><div><dt>所在地</dt><dd><?= case_escape($item['location']) ?></dd></div><div><dt>施設カテゴリ</dt><dd><?= case_escape($category['name']) ?></dd></div><div><dt>施工面積</dt><dd><?= case_escape($item['area']) ?></dd></div><div><dt>施工箇所</dt><dd><?= case_escape(implode('・', $item['installation_locations'] ?? [])) ?></dd></div></dl></div></div></section>

    <section class="case-overview"><div class="container case-overview-grid"><div><p class="eyebrow">PROJECT OVERVIEW</p><h2>基本情報</h2></div><dl class="case-facts"><div><dt>施設名</dt><dd><?= case_escape($item['facility_name']) ?></dd></div><div><dt>所在地</dt><dd><?= case_escape($item['location']) ?></dd></div><div><dt>施工日</dt><dd><?= case_escape($item['installation_date']) ?></dd></div><div><dt>ヒアリング日</dt><dd><?= case_escape($item['interview_date']) ?></dd></div><div><dt>施工面積</dt><dd><?= case_escape($item['area']) ?></dd></div><div><dt>施工箇所</dt><dd><?= case_escape(implode('・', $item['installation_locations'] ?? [])) ?></dd></div><div><dt>規模・概要</dt><dd><?= case_escape($item['scale']) ?></dd></div><div><dt>使用製品</dt><dd><?= case_escape($item['product']) ?></dd></div></dl></div></section>

    <section class="case-story"><div class="container case-story-grid"><div class="case-story-heading"><p class="eyebrow">VOICE FROM THE FACILITY</p><h2>現場で最初に<br>聞いた言葉。</h2></div><blockquote><p>「<?= case_escape($item['lead_quote']) ?>」</p><cite><?= case_escape($item['interview_date']) ?> ヒアリング</cite></blockquote></div></section>

    <section class="case-content"><div class="container case-content-layout"><div class="case-content-main">
      <?php foreach ([['導入前の課題', 'challenge'], ['施工内容', 'installation'], ['導入後の変化', 'changes']] as [$heading, $key]): ?><section><h2><?= $heading ?></h2><?php foreach (case_text_paragraphs((string) $item[$key]) as $paragraph): ?><p><?= case_escape($paragraph) ?></p><?php endforeach; ?></section><?php endforeach; ?>
      <section class="case-customer-voice"><p class="eyebrow">CUSTOMER VOICE</p><h2>お客様の声</h2><?php foreach (case_text_paragraphs((string) $item['customer_voice']) as $paragraph): ?><p><?= case_escape($paragraph) ?></p><?php endforeach; ?></section>
      <section><h2>当社コメント</h2><?php foreach (case_text_paragraphs((string) $item['our_comment']) as $paragraph): ?><p><?= case_escape($paragraph) ?></p><?php endforeach; ?></section>
      <section class="case-follow-up"><p class="eyebrow">FOLLOW UP</p><h2>今後のフォロー</h2><p><?= case_escape($item['follow_up']) ?></p></section>
      <aside class="case-note"><strong>掲載内容について</strong><p>本記事はヒアリング時点の担当者の体感と施設の運用状況を記録したものです。効果は気象、方位、窓・ガラスの条件、空調運用などにより異なり、個別の効果を保証するものではありません。</p></aside>
    </div><aside class="case-detail-side"><div><p>同じような施設で<br>導入を検討している方へ</p><a class="button button-accent" href="/#contact">窓の写真・寸法で相談 <span>→</span></a></div><a href="/case/">導入実績一覧へ戻る <span>→</span></a></aside></div></section>

    <?php if (!empty($item['images'])): ?><section class="case-gallery-section"><div class="container"><div class="case-gallery-heading"><p class="eyebrow">PHOTO GALLERY</p><h2>施工箇所の写真</h2><p>写真を選択すると拡大表示できます。</p></div><div class="case-gallery"><?php foreach ($item['images'] as $index => $image): ?><button type="button" data-gallery-open data-index="<?= $index ?>" data-src="<?= case_escape($image['src']) ?>" data-alt="<?= case_escape($image['alt']) ?>" data-caption="<?= case_escape($image['caption']) ?>"><img src="<?= case_escape($image['src']) ?>" alt="<?= case_escape($image['alt']) ?>" loading="lazy"><span><?= case_escape($image['caption']) ?></span></button><?php endforeach; ?></div></div></section><?php endif; ?>
  </article>
  <section class="case-bottom-cta"><div class="container case-bottom-cta-inner"><div><p class="eyebrow light">FREE CONSULTATION</p><h2>施設の窓環境を相談する</h2><p>窓の写真や寸法をもとに、施工可否と導入方法をご案内します。</p></div><a class="button button-accent" href="/#contact">無料相談へ進む <span>→</span></a></div></section>
</main>
<dialog class="case-lightbox" data-gallery-dialog><button type="button" class="case-lightbox-close" data-gallery-close aria-label="拡大表示を閉じる">×</button><button type="button" class="case-lightbox-prev" data-gallery-prev aria-label="前の写真">‹</button><figure><img alt="" data-gallery-image><figcaption data-gallery-caption></figcaption></figure><button type="button" class="case-lightbox-next" data-gallery-next aria-label="次の写真">›</button></dialog>
<?php require __DIR__ . '/partials/footer.php'; ?>
