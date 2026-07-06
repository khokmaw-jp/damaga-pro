<?php
$pageTitle = $pageTitle ?? '導入実績｜DAMAGA Pro';
$pageDescription = $pageDescription ?? 'DAMAGA Proの病院、介護施設、老人ホーム、オフィス、学校、商業施設への導入実績を紹介します。';
$canonicalUrl = $canonicalUrl ?? 'https://damaga-pro.jp/case/';
$ogImage = $ogImage ?? 'https://damaga-pro.jp/assets/images/brand/damaga-pro-logo-og.png';
$ogType = $ogType ?? 'website';
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= case_escape($pageTitle) ?></title>
  <meta name="description" content="<?= case_escape($pageDescription) ?>">
  <meta name="theme-color" content="#063f2c">
  <link rel="canonical" href="<?= case_escape($canonicalUrl) ?>">
  <meta property="og:type" content="<?= case_escape($ogType) ?>">
  <meta property="og:title" content="<?= case_escape($pageTitle) ?>">
  <meta property="og:description" content="<?= case_escape($pageDescription) ?>">
  <meta property="og:url" content="<?= case_escape($canonicalUrl) ?>">
  <meta property="og:site_name" content="DAMAGA Pro">
  <meta property="og:image" content="<?= case_escape($ogImage) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= case_escape($pageTitle) ?>">
  <meta name="twitter:description" content="<?= case_escape($pageDescription) ?>">
  <meta name="twitter:image" content="<?= case_escape($ogImage) ?>">
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="stylesheet" href="/assets/css/style.css?v=20260622-1">
  <link rel="stylesheet" href="/assets/css/case.css?v=20260625-1">
  <script src="/assets/js/main.js?v=20260622-1" defer></script>
  <script src="/assets/js/case.js?v=20260625-1" defer></script>
  <?php if (!empty($structuredData)): ?>
  <script type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
  <?php endif; ?>
</head>
<body class="case-site-body">
  <a class="skip-link" href="#main">本文へ移動</a>
  <header class="site-header is-scrolled" data-header>
    <div class="container header-inner">
      <a class="brand" href="/" aria-label="DAMAGA Pro トップへ"><img class="brand-logo-mark" src="/assets/images/brand/damaga-pro-logo-mark.png" alt="" aria-hidden="true"><span><b>Damaga Pro</b></span></a>
      <nav class="desktop-nav" aria-label="メインナビゲーション">
        <a href="/#about">製品情報</a><a href="/#simulation">導入効果</a><a href="/case/" aria-current="page">導入実績</a><a href="/#comparison">比較</a><a href="/#dealer">代理店募集</a><a href="/faq/">FAQ</a>
      </nav>
      <div class="header-actions"><a class="dealer-login-link" href="/?dealer-login=1">代理店ログイン</a><a class="button button-small button-primary" href="/#contact">無料相談</a><button class="menu-button" type="button" aria-label="メニューを開く" aria-expanded="false" data-menu-button><span></span><span></span><span></span></button></div>
    </div>
    <nav class="mobile-nav" aria-label="モバイルナビゲーション" data-mobile-nav><a href="/">トップ</a><a href="/#about">製品情報</a><a href="/#simulation">導入効果</a><a href="/case/">導入実績</a><a href="/#comparison">比較</a><a href="/#dealer">代理店募集</a><a href="/faq/">FAQ</a><a href="/?dealer-login=1">代理店ログイン</a><a href="/#contact">お問い合わせ</a></nav>
  </header>
