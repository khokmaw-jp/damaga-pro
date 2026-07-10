# Cloudflare Pages 運用メモ

DAMAGA Pro公式サイトは、Cloudflare Pages / pages.dev を本番運用の前提にする。

## 構成

```text
利用者
  -> Cloudflare DNS / WAF / CDN
  -> Cloudflare Pages
  -> Static HTML + Pages Functions
```

問い合わせフォームのAPIは Cloudflare Pages Functions で動かす。

- `/api/turnstile-config`
- `/api/contact`

Vercel Functionsは使用しない。

## Cloudflare Pages の設定

Pages のプロジェクト設定は以下を推奨する。

```text
Production branch: main
Build command: npm run pages:build
Build output directory: public
Functions directory: functions
```

`npm run pages:build` は、FAQ・導入実績の静的HTMLを生成し、公開に必要なファイルだけを `public/` にコピーする。

`faq/data/`、`case/data/`、管理画面、PHPファイルは `public/` には含めない。

## 環境変数

Cloudflare Pages の Settings > Environment variables に以下を設定する。

```env
NEXT_PUBLIC_TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
CONTACT_TO_EMAIL=info@damaga-pro.jp
CONTACT_FROM_EMAIL=no-reply@damaga-pro.jp
RESEND_API_KEY=
```

`TURNSTILE_SECRET_KEY` と `RESEND_API_KEY` はGitHubに保存しない。

## Turnstile

Cloudflare Turnstile でウィジェットを作成し、許可ホスト名に以下を設定する。

```text
damaga-pro.jp
www.damaga-pro.jp
localhost
```

本番のサーバー側検証では、Turnstileの `hostname` が `damaga-pro.jp` または `www.damaga-pro.jp` の場合のみ問い合わせ送信を許可する。

ローカル開発では `localhost`、`127.0.0.1`、`::1` を許可する。

## メール送信

Cloudflare Pages Functions ではPHPの `mail()` は使用しない。

問い合わせメールと自動返信は Resend API で送信する。

`CONTACT_FROM_EMAIL` には、Resend側で認証済みの送信元を指定する。

## DNS

Cloudflare DNSで以下を設定する。

```text
damaga-pro.jp       CNAME  <Cloudflare Pagesの指定値>
www.damaga-pro.jp   CNAME  <Cloudflare Pagesの指定値>
```

Cloudflare Pages の Custom domains 画面に表示される値を優先する。

メール関連レコードは削除しない。

- MX
- SPF TXT
- DKIM
- DMARC
- Google認証TXT
- その他既存の認証TXT

メール関連レコードはプロキシ対象外にする。

## リダイレクト

`_redirects` で `www` から `https://damaga-pro.jp` へ301リダイレクトする。

正規URLは以下。

```text
https://damaga-pro.jp/
```

## セキュリティヘッダー

Cloudflare Pagesでは `_headers` を使って以下を付与する。

- Strict-Transport-Security
- X-Content-Type-Options
- Referrer-Policy
- Permissions-Policy
- X-Frame-Options
- Content-Security-Policy-Report-Only

CSPは既存機能を壊さないよう、まずReport-Onlyで運用する。

## 動作確認

公開後に以下を確認する。

```text
https://damaga-pro.jp/
https://damaga-pro.jp/faq/
https://damaga-pro.jp/case/
https://damaga-pro.jp/api/turnstile-config
https://damaga-pro.jp/api/contact
```

確認ポイント:

- トップページが表示される
- FAQと導入実績が表示される
- 問い合わせフォームにTurnstileが表示される
- Turnstile未設定時は「セキュリティ確認の設定が未完了です。」と表示され、送信できない
- Turnstile検証失敗時は送信されない
- 送信成功後にTurnstileがリセットされる

## ロールバック

問題が起きた場合は、Cloudflare Pages の Deployments から直前の正常デプロイへ戻す。

DNS切り替え直後の問題であれば、Cloudflare DNSまたはCustom domains設定を直前の状態に戻す。
