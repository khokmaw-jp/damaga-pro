# 導入実績管理CMS

## 管理画面

- 公開一覧: `https://damaga-pro.jp/case/`
- 管理画面: `https://damaga-pro.jp/case/admin/`

管理画面のパスワードは、サーバー環境変数 `CASE_ADMIN_PASSWORD` を使用します。
未設定の場合は、既存FAQ管理と同じ `FAQ_ADMIN_PASSWORD` を使用します。

## 書き込み権限

管理画面から追加・編集するには、PHPから以下へ書き込める必要があります。

- `case/data/cases.json`
- `case/uploads/`
- `case/`（公開URL用のフォルダ作成）
- `sitemap.xml`

## 画像

- JPEG、PNG、WebP
- 1枚8MBまで
- 1回につき最大20枚
- アップロード先ではPHP等の実行を禁止しています

公開前に、施設からの掲載許可と、患者・利用者の個人情報が写っていないことを確認してください。
