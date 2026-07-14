import fs from 'node:fs';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '..');
const siteUrl = 'https://damaga-pro.jp';

const readJson = (file) => JSON.parse(fs.readFileSync(path.join(root, file), 'utf8'));
const writeFile = (file, content) => {
  const target = path.join(root, file);
  fs.mkdirSync(path.dirname(target), { recursive: true });
  fs.writeFileSync(target, content.trimStart() + '\n');
};

const esc = (value = '') => String(value)
  .replaceAll('&', '&amp;')
  .replaceAll('<', '&lt;')
  .replaceAll('>', '&gt;')
  .replaceAll('"', '&quot;')
  .replaceAll("'", '&#039;');

const attr = esc;
const absUrl = (url = '') => /^https?:\/\//.test(url) ? url : `${siteUrl}/${String(url).replace(/^\/+/, '')}`;
const paragraphs = (text = '') => String(text).trim().split(/\n{2,}/).map((p) => p.trim()).filter(Boolean);
const publicItems = (items = []) => items.filter((item) => item.published);
const categoryMap = (categories = []) => Object.fromEntries(categories.map((category) => [category.id, category]));

const jsonLd = (data) => `<script type="application/ld+json">${JSON.stringify(data).replaceAll('<', '\\u003c')}</script>`;
const faqCalculator = (type) => type ? `<section class="faq-calculator" data-faq-calculator="${attr(type)}">
  <div class="faq-calculator-heading">
    <p class="eyebrow">QUICK CALCULATION</p>
    <h2>数値を入力して参考結果を見る</h2>
    <p>入力内容はこのブラウザ内で計算されます。結果は価格・削減額・効果を保証するものではありません。</p>
  </div>
  <form class="faq-calculator-form" data-calculator-form>
    <div class="faq-calculator-fields" data-calculator-fields></div>
    <button class="button button-primary" type="submit">参考結果を計算する</button>
  </form>
  <div class="faq-calculator-result" data-calculator-result aria-live="polite">
    <small>REFERENCE RESULT</small>
    <strong>数値を入力すると、ここに結果が表示されます。</strong>
  </div>
  <a class="button button-accent faq-calculator-contact" href="/#contact" data-use-calculation hidden>この結果を使って相談する <span>→</span></a>
</section>` : '';

const faqHeader = ({ title, description, canonical, structuredData }) => `<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${esc(title)}</title>
  <meta name="description" content="${attr(description)}">
  <meta name="theme-color" content="#063f2c">
  <link rel="canonical" href="${attr(canonical)}">
  <meta property="og:type" content="article">
  <meta property="og:title" content="${attr(title)}">
  <meta property="og:description" content="${attr(description)}">
  <meta property="og:url" content="${attr(canonical)}">
  <meta property="og:site_name" content="DAMAGA Pro">
  <meta property="og:image" content="${siteUrl}/assets/images/brand/damaga-pro-logo-og.png">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="${attr(title)}">
  <meta name="twitter:description" content="${attr(description)}">
  <meta name="twitter:image" content="${siteUrl}/assets/images/brand/damaga-pro-logo-og.png">
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="stylesheet" href="/assets/css/style.css?v=20260621-1">
  <link rel="stylesheet" href="/assets/css/faq.css?v=20260621-1">
  <script src="/assets/js/main.js?v=20260621-1" defer></script>
  <script src="/assets/js/faq-calculators.js?v=20260621-1" defer></script>
  ${structuredData ? jsonLd(structuredData) : ''}
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
        <a class="ai-nav-link" href="/ai" target="_blank" rel="noopener">AI営業担当</a>
      </nav>
      <div class="header-actions">
        <a class="dealer-login-link" href="/partner/">代理店ログイン</a>
        <a class="button button-small button-primary" href="/#contact">無料相談</a>
        <button class="menu-button" type="button" aria-label="メニューを開く" aria-expanded="false" data-menu-button><span></span><span></span><span></span></button>
      </div>
    </div>
    <nav class="mobile-nav" aria-label="モバイルナビゲーション" data-mobile-nav>
      <a href="/">トップ</a><a href="/#about">製品情報</a><a href="/#simulation">導入効果</a><a href="/case/">導入実績</a><a href="/#comparison">比較</a><a href="/#dealer">代理店募集</a><a href="/faq/">FAQ</a><a class="ai-nav-link" href="/ai" target="_blank" rel="noopener">AI営業担当</a><a href="/partner/">代理店ログイン</a><a href="/#contact">お問い合わせ</a>
    </nav>
  </header>`;

const faqFooter = `  <footer class="site-footer">
    <div class="container footer-main">
      <a class="brand footer-brand" href="/"><img class="brand-logo-mark" src="/assets/images/brand/damaga-pro-logo-mark.png" alt="" aria-hidden="true"><span><b>Damaga Pro</b></span></a>
      <div><small>総販売元</small><strong>株式会社ファンビータ</strong><p>本社は卸販売を基本とし、一般ユーザー様への直接販売は行いません。</p></div>
      <nav><a href="/#about">製品情報</a><a href="/#simulation">導入効果</a><a href="/case/">導入実績</a><a href="/#dealer">代理店募集</a><a href="/faq/">よくある質問</a><a href="/privacy.html">個人情報の取り扱い</a></nav>
    </div>
    <div class="container footer-notes"><p>※掲載数値は参考値であり、効果は建物条件、窓面積、方位、ガラス種類、空調設定、地域、季節などにより異なります。</p><small>© 株式会社ファンビータ All Rights Reserved.</small></div>
  </footer>
  <div class="mobile-cta" aria-label="お問い合わせ"><a href="/#contact">導入相談</a><a href="/#dealer">代理店相談</a></div>
</body>
</html>`;

const caseHeader = ({ title, description, canonical, structuredData, ogImage = `${siteUrl}/assets/images/brand/damaga-pro-logo-og.png`, ogType = 'website' }) => `<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>${esc(title)}</title>
  <meta name="description" content="${attr(description)}">
  <meta name="theme-color" content="#063f2c">
  <link rel="canonical" href="${attr(canonical)}">
  <meta property="og:type" content="${attr(ogType)}">
  <meta property="og:title" content="${attr(title)}">
  <meta property="og:description" content="${attr(description)}">
  <meta property="og:url" content="${attr(canonical)}">
  <meta property="og:site_name" content="DAMAGA Pro">
  <meta property="og:image" content="${attr(ogImage)}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="${attr(title)}">
  <meta name="twitter:description" content="${attr(description)}">
  <meta name="twitter:image" content="${attr(ogImage)}">
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="stylesheet" href="/assets/css/style.css?v=20260622-1">
  <link rel="stylesheet" href="/assets/css/case.css?v=20260625-1">
  <script src="/assets/js/main.js?v=20260622-1" defer></script>
  <script src="/assets/js/case.js?v=20260625-1" defer></script>
  ${structuredData ? jsonLd(structuredData) : ''}
</head>
<body class="case-site-body">
  <a class="skip-link" href="#main">本文へ移動</a>
  <header class="site-header is-scrolled" data-header>
    <div class="container header-inner">
      <a class="brand" href="/" aria-label="DAMAGA Pro トップへ"><img class="brand-logo-mark" src="/assets/images/brand/damaga-pro-logo-mark.png" alt="" aria-hidden="true"><span><b>Damaga Pro</b></span></a>
      <nav class="desktop-nav" aria-label="メインナビゲーション">
        <a href="/#about">製品情報</a><a href="/#simulation">導入効果</a><a href="/case/" aria-current="page">導入実績</a><a href="/#comparison">比較</a><a href="/#dealer">代理店募集</a><a href="/faq/">FAQ</a><a class="ai-nav-link" href="/ai" target="_blank" rel="noopener">AI営業担当</a>
      </nav>
      <div class="header-actions"><a class="dealer-login-link" href="/partner/">代理店ログイン</a><a class="button button-small button-primary" href="/#contact">無料相談</a><button class="menu-button" type="button" aria-label="メニューを開く" aria-expanded="false" data-menu-button><span></span><span></span><span></span></button></div>
    </div>
    <nav class="mobile-nav" aria-label="モバイルナビゲーション" data-mobile-nav><a href="/">トップ</a><a href="/#about">製品情報</a><a href="/#simulation">導入効果</a><a href="/case/">導入実績</a><a href="/#comparison">比較</a><a href="/#dealer">代理店募集</a><a href="/faq/">FAQ</a><a class="ai-nav-link" href="/ai" target="_blank" rel="noopener">AI営業担当</a><a href="/partner/">代理店ログイン</a><a href="/#contact">お問い合わせ</a></nav>
  </header>`;

const caseFooter = `  <footer class="site-footer">
    <div class="container footer-main">
      <a class="brand footer-brand" href="/"><img class="brand-logo-mark" src="/assets/images/brand/damaga-pro-logo-mark.png" alt="" aria-hidden="true"><span><b>Damaga Pro</b></span></a>
      <div><small>総販売元</small><strong>株式会社ファンビータ</strong><p>本社は卸販売を基本とし、一般ユーザー様への直接販売は行いません。</p></div>
      <nav><a href="/#about">製品情報</a><a href="/#simulation">導入効果</a><a href="/case/">導入実績</a><a href="/faq/">よくある質問</a><a href="/#dealer">代理店募集</a><a href="/#contact">お問い合わせ</a><a href="/privacy.html">個人情報の取り扱い</a></nav>
    </div>
    <div class="container footer-notes"><p>※掲載内容は取材時点の担当者の体感・運用状況です。効果は建物条件、窓面積、方位、ガラス種類、空調設定、地域、季節などにより異なり、個別の効果を保証するものではありません。</p><small>© 株式会社ファンビータ All Rights Reserved.</small></div>
  </footer>
  <div class="mobile-cta" aria-label="お問い合わせ"><a href="/#contact">導入相談</a><a href="/#contact">写真・寸法で相談</a></div>
</body>
</html>`;

function buildFaq() {
  const data = readJson('faq/data/faqs.json');
  const items = publicItems(data.items);
  const categories = data.categories || [];
  const categoryById = categoryMap(categories);
  const listStructuredData = {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'DAMAGA Pro（ダマガプロ）よくある質問',
    url: `${siteUrl}/faq/`,
    about: {
      '@type': 'Product',
      name: 'DAMAGA Pro',
      alternateName: ['ダマガプロ', 'ダマガ プロ', 'Damagapro'],
      category: '法人向け窓用遮熱・断熱フィルム',
    },
    mainEntity: {
      '@type': 'ItemList',
      itemListElement: items.map((item, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        url: `${siteUrl}/faq/${item.slug}/`,
        name: item.question,
      })),
    },
  };

  const categorySections = categories.map((category) => {
    const cards = items.filter((item) => item.category === category.id).map((item) => `
            <article class="faq-card" data-faq-card data-category="${attr(item.category)}" data-search="${attr(`${item.question} ${item.short_answer} ${(item.tags || []).join(' ')}`)}">
              <p class="faq-card-label">Q</p>
              <h3><a href="/faq/${attr(item.slug)}/">${esc(item.question)}</a></h3>
              <p>${esc(item.short_answer)}</p>
              <div class="faq-tags">${(item.tags || []).map((tag) => `<button type="button" data-tag="${attr(tag)}">#${esc(tag)}</button>`).join('')}</div>
              <a class="faq-card-link" href="/faq/${attr(item.slug)}/">詳しく読む <span>→</span></a>
            </article>`).join('');

    return `
        <section class="faq-category-section" data-category-section="${attr(category.id)}">
          <div class="faq-category-heading"><div><p class="eyebrow">${esc(String(category.id).toUpperCase())}</p><h2>${esc(category.name)}</h2></div><p>${esc(category.description)}</p></div>
          <div class="faq-card-grid">${cards}
          </div>
        </section>`;
  }).join('');

  writeFile('faq/index.html', `${faqHeader({
    title: '遮熱・断熱フィルムのよくある質問｜DAMAGA Pro',
    description: 'DAMAGA Pro（ダマガプロ）の遮熱・断熱効果、電気代、UVカット、結露対策、施工可否を解説。病院、老人ホーム、オフィス、店舗など法人施設の窓対策をQ&Aで確認できます。',
    canonical: `${siteUrl}/faq/`,
    structuredData: listStructuredData,
  })}
  <main id="main">
    <section class="faq-hero">
      <div class="container faq-hero-inner">
        <div><p class="eyebrow light">FAQ KNOWLEDGE BASE</p><h1>DAMAGA Proの<br>よくある質問</h1></div>
        <p>DAMAGA Pro（ダマガプロ）の窓用遮熱フィルムについて、暑さ対策、冬の断熱、UVカット、結露抑制、エアコン効率改善、費用、施工、施設別の活用をまとめています。</p>
      </div>
    </section>

    <section class="faq-directory">
      <div class="container">
        <div class="faq-tools" aria-label="Q&Aの絞り込み">
          <label class="faq-search"><span>キーワードで探す</span><input type="search" placeholder="例：電気代、病院、熱割れ" data-faq-search></label>
          <div class="faq-category-tabs" data-faq-tabs>
            <button type="button" class="is-active" data-category="all">すべて</button>
            ${categories.map((category) => `<button type="button" data-category="${attr(category.id)}">${esc(category.name)}</button>`).join('')}
          </div>
        </div>
        ${categorySections}
        <p class="faq-no-results" data-faq-empty hidden>該当するQ&amp;Aが見つかりませんでした。別のキーワードでお試しください。</p>
      </div>
    </section>

    <section class="faq-bottom-cta">
      <div class="container faq-bottom-cta-inner"><div><p class="eyebrow light">FREE CONSULTATION</p><h2>自施設の窓について相談したい方へ</h2><p>フォーム送信後、担当者からの返信メールへ窓の写真を添付いただけます。</p></div><a class="button button-accent" href="/#contact">窓の写真・寸法を送って相談する <span>→</span></a></div>
    </section>
  </main>
  <script src="/assets/js/faq.js?v=20260620-1" defer></script>
${faqFooter}`);

  for (const item of items) {
    const category = categoryById[item.category] || { name: 'Q&A' };
    const relatedItems = (item.related || []).map((slug) => items.find((related) => related.slug === slug)).filter(Boolean);
    const structuredData = {
      '@context': 'https://schema.org',
      '@type': 'FAQPage',
      about: {
        '@type': 'Product',
        name: 'DAMAGA Pro',
        alternateName: ['ダマガプロ', 'ダマガ プロ', 'Damagapro'],
        category: '法人向け窓用遮熱・断熱フィルム',
      },
      mainEntity: [{
        '@type': 'Question',
        name: item.question,
        acceptedAnswer: { '@type': 'Answer', text: item.short_answer },
      }],
    };
    const sections = (item.sections || []).map((section) => `
          <section class="faq-content-section">
            <h2>${esc(section.heading)}</h2>
            ${(section.paragraphs || []).map((p) => `<p>${esc(p)}</p>`).join('')}
          </section>`).join('');
    const related = relatedItems.length ? `
    <section class="faq-related">
      <div class="container"><div class="faq-related-heading"><p class="eyebrow">RELATED Q&amp;A</p><h2>関連する質問</h2></div><div class="faq-related-grid">
        ${relatedItems.map((relatedItem) => `<a href="/faq/${attr(relatedItem.slug)}/"><small>Q</small><strong>${esc(relatedItem.question)}</strong><span>詳しく読む →</span></a>`).join('')}
      </div></div>
    </section>` : '';

    writeFile(`faq/${item.slug}/index.html`, `${faqHeader({
      title: item.title,
      description: item.meta_description,
      canonical: `${siteUrl}/faq/${item.slug}/`,
      structuredData,
    })}
  <main id="main">
    <section class="faq-article-hero">
      <div class="container">
        <nav class="breadcrumbs" aria-label="パンくずリスト"><a href="/">トップ</a><span>›</span><a href="/faq/">よくある質問</a><span>›</span><span>${esc(category.name)}</span></nav>
        <p class="faq-article-category">${esc(category.name)}</p>
        <h1>${esc(item.question)}</h1>
        <div class="faq-tags">${(item.tags || []).map((tag) => `<a href="/faq/?tag=${encodeURIComponent(tag)}">#${esc(tag)}</a>`).join('')}</div>
      </div>
    </section>

    <section class="faq-article">
      <div class="container faq-article-layout">
        <article class="faq-article-main">
          <section class="faq-answer-box" aria-labelledby="answer-title"><p class="faq-answer-mark">A</p><h2 id="answer-title">結論</h2><p>${esc(item.short_answer)}</p></section>
          ${faqCalculator(item.calculator_type)}
          ${sections}
          <aside class="faq-caution"><strong>DAMAGA Pro導入前の確認について</strong><p>遮熱・断熱効果や施工可否は、ガラス種類、建物条件、方位、施工環境などによって異なります。正式な判断は現地調査後にご案内します。</p></aside>
        </article>

        <aside class="faq-article-side">
          <div class="faq-side-card"><p>窓の状況を確認します</p><h2>写真・寸法で<br>無料相談</h2><p>フォーム送信後、担当者からの返信メールへ窓全体やガラス刻印の写真を添付いただけます。</p><a class="button button-accent" href="/#contact">相談フォームへ <span>→</span></a></div>
          <a class="faq-back-link" href="/faq/">Q&amp;A一覧を見る <span>→</span></a>
        </aside>
      </div>
    </section>
    ${related}
    <section class="faq-bottom-cta"><div class="container faq-bottom-cta-inner"><div><p class="eyebrow light">FREE CONSULTATION</p><h2>窓の写真・寸法を送って相談する</h2><p>フォームに分かる範囲の寸法をご記入ください。写真は受付後の返信メールへ添付いただけます。</p></div><a class="button button-accent" href="/#contact">無料相談へ進む <span>→</span></a></div></section>
  </main>
${faqFooter}`);
  }
  return items.length;
}

function buildCase() {
  const data = readJson('case/data/cases.json');
  const items = publicItems(data.items).sort((a, b) => String(b.installation_date_iso || '').localeCompare(String(a.installation_date_iso || '')));
  const categories = data.categories || [];
  const categoryById = categoryMap(categories);
  const listStructuredData = {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'DAMAGA Pro 導入実績・施工事例',
    url: `${siteUrl}/case/`,
    about: { '@type': 'Product', name: 'DAMAGA Pro', category: '法人向け窓用遮熱・断熱フィルム' },
    mainEntity: {
      '@type': 'ItemList',
      itemListElement: items.map((item, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        url: `${siteUrl}/case/${item.slug}/`,
        name: `${item.facility_name}の施工事例`,
      })),
    },
  };

  const cards = items.map((item) => {
    const category = categoryById[item.category] || { name: '施設' };
    return `
      <article class="case-card" data-case-card data-category="${attr(item.category)}">
        <a class="case-card-image" href="/case/${attr(item.slug)}/"><img src="${attr(item.main_image)}" alt="${attr(`${item.facility_name}のDAMAGA Pro施工事例`)}" loading="lazy"><span>${esc(category.name)}</span></a>
        <div class="case-card-body"><p class="case-card-date">施工 ${esc(item.installation_date)}</p><h2><a href="/case/${attr(item.slug)}/">${esc(item.facility_name)}</a></h2><dl><div><dt>所在地</dt><dd>${esc(item.location)}</dd></div><div><dt>施工箇所</dt><dd>${esc((item.installation_locations || []).join('・'))}</dd></div></dl><p>${esc(item.summary)}</p><a class="case-card-link" href="/case/${attr(item.slug)}/">詳細を見る <span>→</span></a></div>
      </article>`;
  }).join('');

  writeFile('case/index.html', `${caseHeader({
    title: '導入実績・施工事例｜病院・介護施設の窓対策｜DAMAGA Pro',
    description: 'DAMAGA Proの窓用遮熱フィルムを導入した病院、介護施設、老人ホーム、オフィス、学校、商業施設の施工事例を、写真と担当者の声で紹介します。',
    canonical: `${siteUrl}/case/`,
    structuredData: listStructuredData,
  })}
<main id="main">
  <section class="case-hero"><div class="container case-hero-inner"><div><p class="eyebrow light">INSTALLATION RECORDS</p><h1>導入実績</h1></div><p>施設の窓にどのような課題があり、施工後に現場でどのような変化が語られたのか。写真と取材内容をもとに記録しています。</p></div></section>
  <section class="case-directory"><div class="container">
    <div class="case-filters" data-case-filters><button type="button" class="is-active" data-case-category="all">すべて</button>${categories.map((category) => `<button type="button" data-case-category="${attr(category.id)}">${esc(category.name)}</button>`).join('')}</div>
    ${items.length ? `<div class="case-card-grid">${cards}
    </div>` : '<p class="case-empty">公開中の導入実績はありません。</p>'}
  </div></section>
  <section class="case-bottom-cta"><div class="container case-bottom-cta-inner"><div><p class="eyebrow light">FREE CONSULTATION</p><h2>施設の窓について相談する</h2><p>窓の写真や寸法をもとに、施工可否と導入方法をご案内します。</p></div><a class="button button-accent" href="/#contact">無料相談へ進む <span>→</span></a></div></section>
</main>
${caseFooter}`);

  for (const item of items) {
    const category = categoryById[item.category] || { name: '施設' };
    const canonical = `${siteUrl}/case/${item.slug}/`;
    const images = item.images || [];
    const articleData = {
      '@type': 'Article',
      '@id': `${canonical}#article`,
      headline: `${item.facility_name}の窓用遮熱フィルム施工事例`,
      description: item.meta_description,
      image: images.map((image) => absUrl(image.src)),
      dateModified: item.updated,
      mainEntityOfPage: canonical,
      author: { '@type': 'Organization', name: '株式会社ファンビータ' },
      publisher: { '@type': 'Organization', name: '株式会社ファンビータ', url: `${siteUrl}/` },
      about: { '@type': 'Product', name: 'DAMAGA Pro', category: '法人向け窓用遮熱・断熱フィルム' },
      contentLocation: { '@type': 'Place', name: item.facility_name, address: item.location },
    };
    if (/^\d{4}-\d{2}$/.test(item.installation_date_iso || '')) articleData.datePublished = `${item.installation_date_iso}-01`;
    if (/^\d{4}-\d{2}-\d{2}$/.test(item.installation_date_iso || '')) articleData.datePublished = item.installation_date_iso;
    const structuredData = {
      '@context': 'https://schema.org',
      '@graph': [
        articleData,
        {
          '@type': 'BreadcrumbList',
          itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'トップ', item: `${siteUrl}/` },
            { '@type': 'ListItem', position: 2, name: '導入実績', item: `${siteUrl}/case/` },
            { '@type': 'ListItem', position: 3, name: item.facility_name, item: canonical },
          ],
        },
      ],
    };

    const contentSections = [
      ['導入前の課題', 'challenge'],
      ['施工内容', 'installation'],
      ['導入後の変化', 'changes'],
    ].map(([heading, key]) => `<section><h2>${heading}</h2>${paragraphs(item[key]).map((p) => `<p>${esc(p)}</p>`).join('')}</section>`).join('');

    const gallery = images.length ? `<section class="case-gallery-section"><div class="container"><div class="case-gallery-heading"><p class="eyebrow">PHOTO GALLERY</p><h2>施工箇所の写真</h2><p>写真を選択すると拡大表示できます。</p></div><div class="case-gallery">${images.map((image, index) => `<button type="button" data-gallery-open data-index="${index}" data-src="${attr(image.src)}" data-alt="${attr(image.alt)}" data-caption="${attr(image.caption)}"><img src="${attr(image.src)}" alt="${attr(image.alt)}" loading="lazy"><span>${esc(image.caption)}</span></button>`).join('')}</div></div></section>` : '';

    writeFile(`case/${item.slug}/index.html`, `${caseHeader({
      title: item.title,
      description: item.meta_description,
      canonical,
      structuredData,
      ogImage: absUrl(item.main_image),
      ogType: 'article',
    })}
<main id="main">
  <article>
    <header class="case-detail-hero"><div class="container"><nav class="case-breadcrumbs" aria-label="パンくずリスト"><a href="/">トップ</a><span>›</span><a href="/case/">導入実績</a><span>›</span><span>${esc(item.facility_name)}</span></nav><p class="case-category-label">${esc(category.name)}の導入実績</p><h1>${esc(item.facility_name)}<small>窓用遮熱フィルム施工事例</small></h1><p class="case-detail-summary">${esc(item.summary)}</p></div></header>
    <section class="case-facility-profile"><div class="container case-facility-profile-card"><figure><img src="${attr(item.facility_image || item.main_image)}" alt="${attr(item.facility_image_alt || `${item.facility_name}の外観`)}"></figure><div class="case-facility-profile-body"><p class="eyebrow">FACILITY PROFILE</p><h2>${esc(item.facility_name)}</h2><p>${esc(item.facility_profile_text || '病院・介護施設の窓環境改善を目的に、病室と共用部の窓へDAMAGA Proを施工しました。')}</p><dl><div><dt>所在地</dt><dd>${esc(item.location)}</dd></div><div><dt>施設カテゴリ</dt><dd>${esc(category.name)}</dd></div><div><dt>施工面積</dt><dd>${esc(item.area)}</dd></div><div><dt>施工箇所</dt><dd>${esc((item.installation_locations || []).join('・'))}</dd></div></dl></div></div></section>
    <section class="case-overview"><div class="container case-overview-grid"><div><p class="eyebrow">PROJECT OVERVIEW</p><h2>基本情報</h2></div><dl class="case-facts"><div><dt>施設名</dt><dd>${esc(item.facility_name)}</dd></div><div><dt>所在地</dt><dd>${esc(item.location)}</dd></div><div><dt>施工日</dt><dd>${esc(item.installation_date)}</dd></div><div><dt>ヒアリング日</dt><dd>${esc(item.interview_date)}</dd></div><div><dt>施工面積</dt><dd>${esc(item.area)}</dd></div><div><dt>施工箇所</dt><dd>${esc((item.installation_locations || []).join('・'))}</dd></div><div><dt>規模・概要</dt><dd>${esc(item.scale)}</dd></div><div><dt>使用製品</dt><dd>${esc(item.product)}</dd></div></dl></div></section>
    <section class="case-story"><div class="container case-story-grid"><div class="case-story-heading"><p class="eyebrow">VOICE FROM THE FACILITY</p><h2>現場で最初に<br>聞いた言葉。</h2></div><blockquote><p>「${esc(item.lead_quote)}」</p><cite>${esc(item.interview_date)} ヒアリング</cite></blockquote></div></section>
    <section class="case-content"><div class="container case-content-layout"><div class="case-content-main">
      ${contentSections}
      <section class="case-customer-voice"><p class="eyebrow">CUSTOMER VOICE</p><h2>お客様の声</h2>${paragraphs(item.customer_voice).map((p) => `<p>${esc(p)}</p>`).join('')}</section>
      <section><h2>当社コメント</h2>${paragraphs(item.our_comment).map((p) => `<p>${esc(p)}</p>`).join('')}</section>
      <section class="case-follow-up"><p class="eyebrow">FOLLOW UP</p><h2>今後のフォロー</h2><p>${esc(item.follow_up)}</p></section>
      <aside class="case-note"><strong>掲載内容について</strong><p>本記事はヒアリング時点の担当者の体感と施設の運用状況を記録したものです。効果は気象、方位、窓・ガラスの条件、空調運用などにより異なり、個別の効果を保証するものではありません。</p></aside>
    </div><aside class="case-detail-side"><div><p>同じような施設で<br>導入を検討している方へ</p><a class="button button-accent" href="/#contact">窓の写真・寸法で相談 <span>→</span></a></div><a href="/case/">導入実績一覧へ戻る <span>→</span></a></aside></div></section>
    ${gallery}
  </article>
  <section class="case-bottom-cta"><div class="container case-bottom-cta-inner"><div><p class="eyebrow light">FREE CONSULTATION</p><h2>施設の窓環境を相談する</h2><p>窓の写真や寸法をもとに、施工可否と導入方法をご案内します。</p></div><a class="button button-accent" href="/#contact">無料相談へ進む <span>→</span></a></div></section>
</main>
<dialog class="case-lightbox" data-gallery-dialog><button type="button" class="case-lightbox-close" data-gallery-close aria-label="拡大表示を閉じる">×</button><button type="button" class="case-lightbox-prev" data-gallery-prev aria-label="前の写真">‹</button><figure><img alt="" data-gallery-image><figcaption data-gallery-caption></figcaption></figure><button type="button" class="case-lightbox-next" data-gallery-next aria-label="次の写真">›</button></dialog>
${caseFooter}`);
  }
  return items.length;
}

function buildSitemap() {
  const faqData = readJson('faq/data/faqs.json');
  const caseData = readJson('case/data/cases.json');
  const faqItems = publicItems(faqData.items);
  const caseItems = publicItems(caseData.items).sort((a, b) => String(b.installation_date_iso || '').localeCompare(String(a.installation_date_iso || '')));
  const date = new Date().toISOString().slice(0, 10);
  const urls = [
    [`${siteUrl}/`, 'weekly', '1.0'],
    [`${siteUrl}/privacy.html`, 'monthly', '0.3'],
    [`${siteUrl}/faq/`, 'weekly', '0.8'],
    [`${siteUrl}/case/`, 'weekly', '0.9'],
    [`${siteUrl}/partner/`, 'monthly', '0.6'],
    ...faqItems.map((item) => [`${siteUrl}/faq/${item.slug}/`, 'monthly', '0.7']),
    ...caseItems.map((item) => [`${siteUrl}/case/${item.slug}/`, 'monthly', '0.8']),
  ];
  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls.map(([loc, changefreq, priority]) => `  <url>
    <loc>${esc(loc)}</loc>
    <lastmod>${date}</lastmod>
    <changefreq>${changefreq}</changefreq>
    <priority>${priority}</priority>
  </url>`).join('\n')}
</urlset>`;
  writeFile('sitemap.xml', xml);
}

function copyForStaticHostOutput() {
  const outputDir = path.join(root, 'public');
  const entries = [
    'index.html',
    'privacy.html',
    'robots.txt',
    'sitemap.xml',
    'llms.txt',
    'ai-data.json',
    'favicon.ico',
    'site.webmanifest',
    '_headers',
    '_redirects',
    'assets',
    'image-library',
    'faq',
    'case',
    'partner',
  ];

  fs.rmSync(outputDir, { recursive: true, force: true });
  fs.mkdirSync(outputDir, { recursive: true });

  for (const entry of entries) {
    const source = path.join(root, entry);
    if (!fs.existsSync(source)) continue;
    fs.cpSync(source, path.join(outputDir, entry), {
      recursive: true,
      filter: (sourcePath) => {
        const segments = path.relative(root, sourcePath).split(path.sep);
        return !segments.includes('admin') && !segments.includes('data');
      },
    });
  }
}

const requiredDataFiles = ['faq/data/faqs.json', 'case/data/cases.json'];
const hasRequiredData = requiredDataFiles.every((file) => fs.existsSync(path.join(root, file)));

if (hasRequiredData) {
  const faqCount = buildFaq();
  const caseCount = buildCase();
  buildSitemap();
  console.log(`Generated ${faqCount} FAQ pages and ${caseCount} case pages.`);
} else {
  console.log('FAQ/case source data is not included in this deployment. Using prebuilt static pages.');
}

if (process.env.CLOUDFLARE_PAGES === '1' || process.env.VERCEL === '1') {
  copyForStaticHostOutput();
  console.log('Copied static site files to public/ for static host output.');
}
