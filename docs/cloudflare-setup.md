# DAMAGA Pro Cloudflare 運用手順

## 現在の構成

- GitHub: `khokmaw-jp/damaga-pro`
- Hosting: Vercel
- Production domain: `damaga-pro.jp` / `www.damaga-pro.jp`
- Site type: 静的HTMLサイト
- FAQ / 導入実績: JSONデータから `scripts/build-static-pages.mjs` で静的HTMLを生成

現時点では、お名前.com DNS から Vercel または既存サーバーへ向いています。Cloudflareを有効にするまでは、CloudflareのWAF、Bot対策、DDoS保護、CDN、キャッシュは適用されません。

## 目標構成

```text
利用者
  -> Cloudflare
  -> Vercel
  -> DAMAGA Pro 静的サイト
```

正規URLは次に統一します。

```text
https://damaga-pro.jp/
```

`www.damaga-pro.jp` は `https://damaga-pro.jp/` へ301リダイレクトします。

## 先に確認する重要事項

Cloudflareへ切り替える前に、Vercelの Domains で次を確認してください。

- `damaga-pro.jp` をProductionの正規ドメインにする
- `www.damaga-pro.jp` は `damaga-pro.jp` へリダイレクトする
- Vercel側で `damaga-pro.jp -> www.damaga-pro.jp` の逆向きリダイレクトが残っていないこと

この確認をせずにCloudflareやコードで `www -> apex` のリダイレクトだけを有効化すると、Vercel側の設定によってはリダイレクトループが起きます。

## Cloudflareへ移行するDNSレコード

お名前.comのDNS画面から、現在登録されているレコードを漏れなくCloudflareへ移行してください。値は推測せず、必ず現行画面で確認します。

最低限、確認が必要なレコード:

- A `@`
- CNAME `www`
- MX
- SPF用TXT
- DKIM
- DMARC
- Google認証TXT
- Vercel認証用TXT
- その他、現在登録されているDNSレコード

## Vercel向けDNS

Vercelの Domains 画面に表示される最新値を優先してください。2026年7月時点の画面例では、以下のような値が提示されています。

| Type | Name | Value | Cloudflare Proxy |
| --- | --- | --- | --- |
| A | `@` | `216.198.79.1` | Proxied |
| CNAME | `www` | Vercel Domains画面に表示される `*.vercel-dns-*.com` | Proxied |

古いVercel値として `76.76.21.21` や `cname.vercel-dns.com` が動作する場合もありますが、Vercel画面に新しい推奨値が出ている場合は新しい値へ更新します。

## メール・認証系DNSの注意

以下は削除しないでください。

- MX
- SPF用TXT
- DKIM
- DMARC
- Google認証TXT
- Vercel認証用TXT

メール関連レコードはCloudflareのプロキシ対象ではありません。DNS onlyで管理します。

| Record | Proxy |
| --- | --- |
| MX | DNS only |
| TXT / SPF | DNS only |
| TXT / DKIM | DNS only |
| TXT / DMARC | DNS only |
| mail用A/CNAME | DNS only |

## Cloudflare SSL/TLS推奨設定

- SSL/TLS encryption mode: `Full (strict)`
- Always Use HTTPS: 有効
- Automatic HTTPS Rewrites: 有効
- HTTP/3: 有効
- Brotli: 有効

`Flexible` は使用しません。CloudflareとVercel間がHTTP扱いになり、HTTPSリダイレクトのループ原因になります。

## セキュリティ

Cloudflare側で有効化する推奨項目:

- WAF managed rules
- Bot Fight Mode またはBot対策
- DDoS protection
- Rate limiting

問い合わせフォームやTurnstileがあるため、厳しすぎるBotブロックは送信テスト後に段階的に強めてください。

Vercel側では `vercel.json` で次のヘッダーを付与します。

- `Strict-Transport-Security`
- `X-Content-Type-Options`
- `Referrer-Policy`
- `Permissions-Policy`
- `X-Frame-Options`
- `Content-Security-Policy-Report-Only`

CSPは既存のフォーム、Cloudflare Turnstile、画像、CSS、JavaScriptを壊さないよう、最初はReport-Onlyで導入します。問題がないことを確認してから強制CSPへ移行します。

## キャッシュ方針

Cloudflareでキャッシュしない対象:

- `/api/*`
- `/faq/admin/*`
- `/case/admin/*`
- 代理店ログイン関連
- 問い合わせフォーム送信
- 個人情報を含むページ
- 動的に生成される結果ページ

長期キャッシュ可能な対象:

- `/assets/*`
- `/image-library/*`
- CSS
- JavaScript
- フォント
- 画像

Vercel側では `vercel.json` で静的ファイルへ長期キャッシュを設定します。HTMLページはVercel標準の再検証設定を使い、Cloudflare側ではHTMLの過剰キャッシュを避けます。

## IPアドレスの扱い

Cloudflare経由では、アプリケーション側で利用者IPが必要な場合に以下を優先します。

1. `CF-Connecting-IP`
2. `X-Forwarded-For`

現在の公開サイトは静的HTML中心のため、サーバー側でIPを扱う箇所は限定的です。今後、問い合わせAPIや代理店システムをVercel Functions / Next.js APIへ移す場合は、Cloudflareヘッダーを考慮して実装します。

## 切り替え後の確認手順

DNS変更後、以下を確認します。

```bash
curl -I https://damaga-pro.jp/
curl -I https://www.damaga-pro.jp/
curl -I https://damaga-pro.jp/faq/
curl -I https://damaga-pro.jp/case/
curl -I https://damaga-pro.jp/robots.txt
curl -I https://damaga-pro.jp/sitemap.xml
```

確認ポイント:

- `https://damaga-pro.jp/` が200で表示される
- `https://www.damaga-pro.jp/` が `https://damaga-pro.jp/` へ301リダイレクトされる
- `http://damaga-pro.jp/` がHTTPSへリダイレクトされる
- `http://www.damaga-pro.jp/` がHTTPSの正規URLへリダイレクトされる
- `/faq/` と `/case/` が200で表示される
- `robots.txt` と `sitemap.xml` が200で取得できる
- レスポンスヘッダーに `cf-ray` が含まれる
- CloudflareのSSL/TLSがFull (strict)でエラーにならない
- 問い合わせフォームとTurnstileが動作する

## SEO確認

Cloudflare切り替え後も、SEO上のURLは分散させません。

- canonical: `https://damaga-pro.jp/`
- sitemap: `https://damaga-pro.jp/sitemap.xml`
- robots: `https://damaga-pro.jp/robots.txt`
- OGP URL: `https://damaga-pro.jp/`

FAQと導入実績の静的ページは、生成スクリプト内の `siteUrl = 'https://damaga-pro.jp'` により正規URLへ統一されています。

## ロールバック手順

問題が発生した場合は、次の順で戻します。

1. Cloudflareの該当DNSレコードをDNS onlyに変更し、プロキシを停止する
2. 改善しない場合は、お名前.comのネームサーバーを `01.dnsv.jp`〜`04.dnsv.jp` に戻す
3. VercelのDomains設定で、直近の正常なドメイン設定へ戻す
4. GitHub / Vercelで直近の正常なデプロイへRollbackする

DNSの反映には時間差があります。切り替え作業前にTTLを短めにしておくと戻しやすくなります。

## 人が行う作業

コードでは実行できない作業:

- Cloudflareアカウントに `damaga-pro.jp` を追加
- お名前.comからCloudflareへDNSレコードを漏れなく移行
- お名前.com側でネームサーバーをCloudflare指定へ変更
- CloudflareでSSL/TLSをFull (strict)に設定
- CloudflareでWAF、Bot対策、キャッシュ除外ルールを設定
- Vercel Domainsで `damaga-pro.jp` を正規ドメインに設定
- `www.damaga-pro.jp` から `damaga-pro.jp` へのリダイレクトを設定

