<?php
$pageTitle = $pageTitle ?? 'よくある質問｜DAMAGA Pro（ダマガプロ）';
$pageDescription = $pageDescription ?? 'DAMAGA Pro（ダマガプロ）の窓用遮熱・断熱フィルムに関するよくある質問をまとめています。';
$canonicalUrl = $canonicalUrl ?? 'https://damaga-pro.jp/faq/';
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= faq_escape($pageTitle) ?></title>
  <meta name="description" content="<?= faq_escape($pageDescription) ?>">
  <meta name="theme-color" content="#063f2c">
  <link rel="canonical" href="<?= faq_escape($canonicalUrl) ?>">
  <meta property="og:type" content="article">
  <meta property="og:title" content="<?= faq_escape($pageTitle) ?>">
  <meta property="og:description" content="<?= faq_escape($pageDescription) ?>">
  <meta property="og:url" content="<?= faq_escape($canonicalUrl) ?>">
  <meta property="og:site_name" content="DAMAGA Pro">
  <meta property="og:image" content="https://damaga-pro.jp/assets/images/brand/damaga-pro-logo-og.png">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= faq_escape($pageTitle) ?>">
  <meta name="twitter:description" content="<?= faq_escape($pageDescription) ?>">
  <meta name="twitter:image" content="https://damaga-pro.jp/assets/images/brand/damaga-pro-logo-og.png">
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="stylesheet" href="/assets/css/style.css?v=20260621-1">
  <link rel="stylesheet" href="/assets/css/faq.css?v=20260621-1">
  <script src="/assets/js/main.js?v=20260621-1" defer></script>
  <script src="/assets/js/faq-calculators.js?v=20260621-1" defer></script>
  <?php if (!empty($structuredData)): ?>
  <script type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
  <?php endif; ?>
</head>
<body class="faq-body">
  <a class="skip-link" href="#main">本文へ移動</a>
  <header class="site-header is-scrolled" data-header>
    <div class="container header-inner">
      <a class="brand" href="/" aria-label="DAMAGAシートPRO トップへ">
        <img class="brand-logo-mark" src="/assets/images/brand/damaga-pro-logo-mark.png" alt="" aria-hidden="true">
        <span><b>Damaga Pro</b></span>
      </a>
      <nav class="desktop-nav" aria-label="メインナビゲーション">
        <a href="/#about">製品情報</a>
        <a href="/#simulation">導入効果</a>
        <a href="/case/">導入実績</a>
        <a href="/#comparison">比較</a>
        <a href="/#dealer">代理店募集</a>
        <a href="/faq/" aria-current="page">FAQ</a>
      </nav>
      <div class="header-actions">
        <a class="dealer-login-link" href="/?dealer-login=1">代理店ログイン</a>
        <a class="button button-small button-primary" href="/#contact">無料相談</a>
        <button class="menu-button" type="button" aria-label="メニューを開く" aria-expanded="false" data-menu-button><span></span><span></span><span></span></button>
      </div>
    </div>
    <nav class="mobile-nav" aria-label="モバイルナビゲーション" data-mobile-nav>
      <a href="/">トップ</a><a href="/#about">製品情報</a><a href="/#simulation">導入効果</a><a href="/case/">導入実績</a><a href="/#comparison">比較</a><a href="/#dealer">代理店募集</a><a href="/faq/">FAQ</a><a href="/?dealer-login=1">代理店ログイン</a><a href="/#contact">お問い合わせ</a>
    </nav>
  </header>
