# DAMAGA Pro SEO Step 1 実装メモ

## 追加・変更したファイル

- `index.html`
  - title / meta descriptionをSEO向けに調整
  - canonicalを追加
  - OGP / Twitter Cardを追加
  - Organization / WebSite / Product / FAQPage のJSON-LD構造化データを追加
- `privacy.html`
  - canonicalを追加
  - OGP / Twitter Cardを追加
- `sitemap.xml`
  - Google Search Console送信用サイトマップを追加
- `robots.txt`
  - クロール許可、`/api/`除外、sitemap URLを明記
- `llms.txt`
  - AI検索・LLM参照用の公式説明テキストを追加
- `ai-data.json`
  - AIや将来のMCP/API連携で参照しやすい公式データを追加
- `faq/`
  - 14カテゴリー、合計120件の個別Q&A、FAQPage構造化データを追加
  - 10件の数値シミュレーションと問い合わせフォームへの結果引き継ぎを追加
  - 管理画面からカテゴリー・ハッシュタグ・記事を追加可能

## Search Consoleに送信するサイトマップ

https://damaga-pro.jp/sitemap.xml

## 公開後に確認するURL

- https://damaga-pro.jp/robots.txt
- https://damaga-pro.jp/sitemap.xml
- https://damaga-pro.jp/llms.txt
- https://damaga-pro.jp/ai-data.json
- https://damaga-pro.jp/privacy.html
- https://damaga-pro.jp/faq/

## 次のSEO改善候補

- `/dealer/`
- `/price/`
- `/case/hospital/`
- `/case/care-facility/`
- `/installation/`

## 注意

- 掲載数値は参考値であり、保証値ではありません。
- 効果は建物条件、方位、窓面積、ガラス種別、施工環境、空調運用、地域、季節により変動します。
- `api/`配下には問い合わせフォーム処理があるため、robots.txtでクロール対象外にしています。
- Cloudflare Turnstileと問い合わせフォームの既存処理は変更していません。
