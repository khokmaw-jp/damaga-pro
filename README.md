# DAMAGA Pro 公式サイト

DAMAGA Pro公式サイトの現行HTML/PHP版です。公開サイト、FAQ、導入実績、お問い合わせフォーム、SEO/AIO参照用ファイルを含みます。

トップページは静的HTML、問い合わせフォーム、FAQ管理、導入実績管理はPHPで構成されています。

将来的にはNext.jsへ移行し、代理店システムは `partner.damaga-pro.jp` のサブドメインで分離する方針です。

## 設計書

Next.js移行、代理店システム分離、FAQ/導入実績CMS、シミュレーション保存の方針は以下に記録しています。

- `docs/damaga-pro-architecture.md`
- `docs/decision-log.md`

## 主な構成

- `index.html`: トップページ
- `faq/`: FAQページ、FAQ管理、FAQデータ
- `case/`: 導入実績ページ、導入実績管理、事例データ
- `functions/api/`: Cloudflare Pages Functions用の問い合わせフォーム、Turnstile設定
- `api/`: 旧PHPサーバー用の問い合わせフォーム実装
- `assets/`: CSS、JavaScript、画像
- `docs/`: システム設計書、意思決定ログ
- `llms.txt`: AI参照用サイト説明
- `ai-data.json`: AI参照用構造化データ
- `sitemap.xml`: サイトマップ
- `robots.txt`: クロール設定

## ローカル確認

```bash
python3 -m http.server 3000
```

ブラウザで `http://localhost:3000` を開きます。

PHP部分を含めて確認する場合は、PHPが使えるローカルサーバーまたはレンタルサーバー上で確認してください。

## 公開

このディレクトリの中身をWebサーバーの公開フォルダへアップロードしてください。

GitHub/Cloudflare Pagesへの移行方針は `docs/damaga-pro-architecture.md` と `docs/cloudflare-pages-setup.md` を参照してください。
