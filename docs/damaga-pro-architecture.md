# DAMAGA Pro システム設計書

作成日: 2026-07-06

## 1. 目的

DAMAGA Pro公式サイトを、商品紹介だけでなく、代理店営業、FAQ/AIO SEO、導入実績、シミュレーションを統合して運営できる事業基盤にする。

公開サイトは検索流入と問い合わせ獲得を担い、代理店システムは販売活動、シミュレーション、資料提供、案件管理を担う。

## 2. 全体構成

```txt
damaga-pro.jp
├─ 公開サイト
│  ├─ トップページ
│  ├─ 商品説明
│  ├─ 導入効果
│  ├─ 比較
│  ├─ FAQ
│  ├─ 導入実績
│  ├─ お問い合わせ
│  ├─ SEO/AIO用ファイル
│  └─ 管理画面CMS
│
└─ partner.damaga-pro.jp
   ├─ 代理店ログイン
   ├─ 代理店マイページ
   ├─ シミュレーション
   ├─ シミュレーション履歴
   ├─ 見積依頼
   ├─ 資料ダウンロード
   └─ 案件管理
```

## 3. ドメイン構成

| 用途 | URL | 管理方針 |
| --- | --- | --- |
| 公開サイト | https://damaga-pro.jp/ | Cloudflare Pagesでホスティング |
| www | https://www.damaga-pro.jp/ | damaga-pro.jpへ301リダイレクト |
| 代理店システム | https://partner.damaga-pro.jp/ | サブドメインで別アプリとして管理 |

ドメインの取得・更新はお名前.comで継続する。DNSはCloudflareへ移管し、Cloudflare Pagesへ向ける。

## 4. 技術構成

| 項目 | 方針 |
| --- | --- |
| ソース管理 | GitHub |
| 公開サイト | 静的HTML/PHP由来のサイトをCloudflare Pages配信。将来的にNext.jsへ移行 |
| 代理店システム | Next.js |
| ホスティング | Cloudflare Pages |
| DNS / セキュリティ | Cloudflare |
| フォーム保護 | Cloudflare Turnstile |
| データベース | Supabase または Neon |
| 認証 | Supabase Auth、または NextAuth/Auth.js |
| 画像管理 | CMS管理、またはCloudflare R2/Supabase Storage |

第一候補は Next.js + Supabase + Cloudflare Pages とする。

## 5. 公開サイト

公開サイトは、法人施設向けの窓用遮熱・断熱フィルムとしてDAMAGA Proを理解してもらうための入口とする。

主なページ:

- トップページ
- 製品情報
- 導入効果
- 比較
- FAQ
- 導入実績
- 代理店募集
- お問い合わせ
- プライバシーポリシー
- robots.txt
- sitemap.xml
- llms.txt
- ai-data.json

SEO/AIOでは以下の理解を狙う。

- DAMAGA Proは法人向け窓用遮熱・断熱フィルム
- 病院、介護施設、老人ホーム、店舗、オフィス、学校、工場などの窓対策
- 電気代削減、空調負荷軽減、暑さ対策、UVカット、結露抑制、飛散防止に貢献
- 株式会社ファンビータが販売元
- 代理店募集を行っている

## 6. FAQ CMS

FAQはSEO流入と営業支援の両方を目的にCMS化する。

管理項目:

- 質問
- 回答本文
- カテゴリー
- タグ
- スラッグ
- meta title
- meta description
- 公開/非公開
- おすすめ表示
- 関連FAQ
- 更新日

表示要件:

- FAQ一覧
- カテゴリー絞り込み
- タグ絞り込み
- 検索
- 個別FAQページ
- FAQPage JSON-LD
- 問い合わせ導線

注意:

- 断定表現を避ける
- 数値は参考値、条件により変動、保証値ではない旨を明記する
- 本文に表示していないQ&AをJSON-LDだけに入れない

## 7. 導入実績 CMS

導入実績は、SEOと信頼形成の中核コンテンツとしてCMS化する。病院、介護施設、老人ホーム、オフィス、学校、商業施設、個人宅などを登録できるようにする。

管理項目:

- 施設名
- 施設カテゴリ
- 所在地
- 施工日
- ヒアリング日
- 施工面積
- 施工箇所
- 使用製品
- メイン画像
- 写真ギャラリー
- 導入前の課題
- 施工内容
- 導入後の変化
- お客様の声
- 当社コメント
- 今後のフォロー
- meta title
- meta description
- OGP画像
- 公開/非公開

表示要件:

- 導入実績一覧
- カテゴリー絞り込み
- 個別事例ページ
- 写真ギャラリー
- Article JSON-LD
- 問い合わせ導線

## 8. 代理店システム

代理店システムは `partner.damaga-pro.jp` として公開サイトから分離する。

主な機能:

- 代理店ログイン
- 代理店マイページ
- 資料ダウンロード
- シミュレーション
- シミュレーション履歴保存
- 見積依頼
- 注文フォーム
- 案件管理
- お知らせ
- サポート窓口

権限:

- 管理者
- 代理店
- 必要に応じて営業担当者

## 9. シミュレーション保存

シミュレーションは代理店システム内に埋め込み、結果を保存する。以前作成したシミュレーションと同様に、案件ごとに再確認できる形を目指す。

保存する主なデータ:

- 代理店ID
- 案件名
- 顧客名または施設名
- 建物種別
- 窓面積
- 方角
- 電気料金単価
- 空調使用時間
- 想定削減額
- ROI
- 入力条件
- 計算結果
- メモ
- 作成日
- 更新日
- PDF出力用データ

注意:

- 削減額やROIは概算であることを明記する
- 効果保証と誤解される表現は避ける
- 現地調査や正式見積につなげる導線を入れる

## 10. DB設計案

初期案:

```txt
users
dealers
faq_categories
faq_tags
faqs
faq_tag_relations
case_categories
cases
case_images
simulation_projects
simulation_results
inquiries
downloads
announcements
```

詳細なカラム設計は、Next.js移行時に別紙 `cms-design.md` または `database-schema.md` として作成する。

## 11. GitHub / Cloudflare Pages 方針

GitHub:

- ソースコード管理
- 設計書管理
- 変更履歴管理

Cloudflare Pages:

- GitHub連携による自動デプロイ
- 公開サイトと代理店システムを別プロジェクトで管理
- 環境変数をCloudflare Pages側に設定
- Pages Functionsで問い合わせAPIを運用

Cloudflare:

- DNS管理
- Turnstile
- WAF
- CDN
- キャッシュ制御

お名前.com:

- jpドメインの取得・更新管理のみ継続

## 12. 移行手順

1. 現行サイトをバックアップ
2. GitHubリポジトリを作成
3. 現行HTML/PHPサイトをGitHubへ登録
4. Cloudflare Pagesプロジェクトを作成
5. FAQと導入実績のデータをCMS構造へ移行
6. 問い合わせフォームをPages Functions向けに移行
7. 代理店システムを `partner.damaga-pro.jp` として構築
8. シミュレーション保存機能を実装
9. Cloudflare Pagesに接続
10. Cloudflare DNSを設定
11. 本番切り替え
12. Google Search Consoleでsitemap.xmlを確認

## 13. 未決定事項

- DBを Supabase にするか Neon にするか
- 認証方式
- 画像保存先
- 管理画面を公開サイト側に含めるか、別管理アプリにするか
- 問い合わせメール送信サービス
- 代理店向けPDF出力の形式

## 14. 運用方針

この設計書は完成版ではなく、運用しながら更新する。大きな方針変更は `docs/decision-log.md` に記録する。
