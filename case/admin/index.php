<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/case/admin/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();
require dirname(__DIR__) . '/lib.php';

header('X-Robots-Tag: noindex, nofollow, noarchive');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

$adminPassword = (string) (getenv('CASE_ADMIN_PASSWORD') ?: getenv('FAQ_ADMIN_PASSWORD') ?: '');
if ($adminPassword === '') {
    http_response_code(503);
    exit('導入実績管理画面は未設定です。CASE_ADMIN_PASSWORD または FAQ_ADMIN_PASSWORD を設定してください。');
}
if (empty($_SESSION['case_csrf'])) $_SESSION['case_csrf'] = bin2hex(random_bytes(24));

function case_admin_password_matches(string $input, string $configured): bool
{
    $info = password_get_info($configured);
    return !empty($info['algo']) ? password_verify($input, $configured) : hash_equals($configured, $input);
}

function case_admin_list(string $value): array
{
    return array_values(array_filter(array_map('trim', preg_split('/[,、\R]+/u', $value) ?: [])));
}

function case_admin_find_any(array $items, string $slug): ?array
{
    foreach ($items as $item) if (($item['slug'] ?? '') === $slug) return $item;
    return null;
}

function case_admin_upload_images(array $files, string $slug, string &$error): array
{
    if (empty($files['name']) || !is_array($files['name'])) return [];
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $directory = dirname(__DIR__) . '/uploads/' . $slug;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        $error = '画像保存フォルダを作成できませんでした。';
        return [];
    }
    $saved = [];
    $count = min(count($files['name']), 20);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    for ($index = 0; $index < $count; $index++) {
        $uploadError = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError === UPLOAD_ERR_NO_FILE) continue;
        if ($uploadError !== UPLOAD_ERR_OK) { $error = '画像アップロードに失敗しました。'; break; }
        $temp = (string) ($files['tmp_name'][$index] ?? '');
        $size = (int) ($files['size'][$index] ?? 0);
        if ($size < 1 || $size > 8 * 1024 * 1024 || !is_uploaded_file($temp)) { $error = '画像は1枚8MB以内で選択してください。'; break; }
        $mime = $finfo ? finfo_file($finfo, $temp) : '';
        if (!isset($allowed[$mime]) || @getimagesize($temp) === false) { $error = 'JPEG・PNG・WebP画像のみアップロードできます。'; break; }
        $filename = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($temp, $directory . '/' . $filename)) { $error = '画像を保存できませんでした。'; break; }
        $saved[] = [
            'src' => '/case/uploads/' . $slug . '/' . $filename,
            'alt' => '',
            'caption' => pathinfo((string) $files['name'][$index], PATHINFO_FILENAME),
        ];
    }
    if ($finfo) finfo_close($finfo);
    return $saved;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'login') {
        if (case_admin_password_matches((string) ($_POST['password'] ?? ''), $adminPassword)) {
            session_regenerate_id(true);
            $_SESSION['case_admin_authenticated'] = true;
            $_SESSION['case_csrf'] = bin2hex(random_bytes(24));
            header('Location: /case/admin/');
            exit;
        }
        $error = 'パスワードが正しくありません。';
    } elseif ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: /case/admin/');
        exit;
    }
}

$authenticated = !empty($_SESSION['case_admin_authenticated']);
if (!$authenticated):
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>導入実績管理ログイン｜DAMAGA Pro</title><link rel="stylesheet" href="/assets/css/faq-admin.css?v=20260620-1"><link rel="stylesheet" href="/assets/css/case-admin.css?v=20260625-1"></head>
<body class="admin-login"><main><form method="post" class="admin-login-card"><img src="/assets/images/brand/damaga-pro-logo-mark.png" alt=""><p>DAMAGA Pro</p><h1>導入実績管理ログイン</h1><?php if ($error): ?><div class="admin-alert error"><?= case_escape($error) ?></div><?php endif; ?><input type="hidden" name="action" value="login"><label>管理者パスワード<input type="password" name="password" required autocomplete="current-password"></label><button type="submit">ログイン</button><a href="/case/">公開ページを見る</a></form></main></body></html>
<?php exit; endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals((string) $_SESSION['case_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('不正なリクエストです。');
    }
    $data = case_load_data();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save_case') {
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $facilityName = trim((string) ($_POST['facility_name'] ?? ''));
        $originalSlug = (string) ($_POST['original_slug'] ?? $slug);
        $existing = case_admin_find_any($data['items'] ?? [], $originalSlug);
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $error = 'URL名は半角英小文字・数字・ハイフンのみで入力してください。';
        } elseif ($facilityName === '' || trim((string) ($_POST['summary'] ?? '')) === '') {
            $error = '施設名と簡易コメントは必須です。';
        } else {
            $removeImages = array_map('strval', $_POST['remove_images'] ?? []);
            $images = array_values(array_filter($existing['images'] ?? [], static fn(array $image): bool => !in_array((string) ($image['src'] ?? ''), $removeImages, true)));
            $newImages = case_admin_upload_images($_FILES['photos'] ?? [], $slug, $error);
            foreach ($newImages as &$image) {
                $image['alt'] = $facilityName . 'のDAMAGA Pro施工事例';
                if ($image['caption'] === '') $image['caption'] = '施工箇所';
            }
            unset($image);
            $images = array_merge($images, $newImages);
            if ($error === '' && !$images) $error = '少なくとも1枚の写真を登録してください。';

            if ($error === '') {
                $mainImage = (string) ($_POST['main_image'] ?? '');
                if (!in_array($mainImage, array_column($images, 'src'), true)) $mainImage = (string) $images[0]['src'];
                $locations = case_admin_list((string) ($_POST['installation_locations'] ?? ''));
                $title = trim((string) ($_POST['title'] ?? '')) ?: $facilityName . 'の窓用遮熱フィルム施工事例｜DAMAGA Pro';
                $description = trim((string) ($_POST['meta_description'] ?? '')) ?: $facilityName . 'で実施したDAMAGA Proの窓用遮熱フィルム施工事例を、写真と担当者へのヒアリング内容で紹介します。';
                $item = [
                    'slug' => $slug,
                    'category' => (string) ($_POST['category'] ?? 'hospital'),
                    'facility_name' => $facilityName,
                    'location' => trim((string) ($_POST['location'] ?? '')),
                    'installation_date' => trim((string) ($_POST['installation_date'] ?? '')),
                    'installation_date_iso' => trim((string) ($_POST['installation_date_iso'] ?? '')),
                    'interview_date' => trim((string) ($_POST['interview_date'] ?? '')),
                    'area' => trim((string) ($_POST['area'] ?? '')),
                    'installation_locations' => $locations,
                    'product' => trim((string) ($_POST['product'] ?? '')) ?: 'DAMAGA Pro',
                    'scale' => trim((string) ($_POST['scale'] ?? '')),
                    'summary' => trim((string) ($_POST['summary'] ?? '')),
                    'lead_quote' => trim((string) ($_POST['lead_quote'] ?? '')),
                    'challenge' => trim((string) ($_POST['challenge'] ?? '')),
                    'installation' => trim((string) ($_POST['installation'] ?? '')),
                    'changes' => trim((string) ($_POST['changes'] ?? '')),
                    'customer_voice' => trim((string) ($_POST['customer_voice'] ?? '')),
                    'our_comment' => trim((string) ($_POST['our_comment'] ?? '')),
                    'follow_up' => trim((string) ($_POST['follow_up'] ?? '')),
                    'main_image' => $mainImage,
                    'images' => $images,
                    'title' => $title,
                    'meta_description' => $description,
                    'published' => isset($_POST['published']),
                    'updated' => date('Y-m-d'),
                ];
                $replaced = false;
                foreach ($data['items'] as $index => $current) {
                    if (($current['slug'] ?? '') === $originalSlug) { $data['items'][$index] = $item; $replaced = true; break; }
                }
                if (!$replaced) $data['items'][] = $item;

                if (!case_create_route($slug)) $error = '公開URLを作成できませんでした。caseフォルダの書き込み権限を確認してください。';
                elseif (!case_save_data($data)) $error = 'データを保存できませんでした。case/dataフォルダの書き込み権限を確認してください。';
                else { case_sync_sitemap($data); $message = '導入実績を保存しました。'; }
            }
        }
    } elseif ($action === 'delete_case') {
        $slug = (string) ($_POST['slug'] ?? '');
        $data['items'] = array_values(array_filter($data['items'], static fn(array $item): bool => ($item['slug'] ?? '') !== $slug));
        if (case_save_data($data)) { case_sync_sitemap($data); $message = '導入実績を削除しました。'; } else $error = '削除内容を保存できませんでした。';
    }
}

$data = case_load_data();
$editingSlug = (string) ($_GET['edit'] ?? '');
$editing = case_admin_find_any($data['items'] ?? [], $editingSlug);
$form = $editing ?? [
    'slug' => '', 'category' => $data['categories'][0]['id'] ?? 'hospital', 'facility_name' => '', 'location' => '',
    'installation_date' => '', 'installation_date_iso' => '', 'interview_date' => '', 'area' => '',
    'installation_locations' => [], 'product' => 'DAMAGA Pro', 'scale' => '', 'summary' => '', 'lead_quote' => '',
    'challenge' => '', 'installation' => '', 'changes' => '', 'customer_voice' => '', 'our_comment' => '',
    'follow_up' => '', 'main_image' => '', 'images' => [], 'title' => '', 'meta_description' => '', 'published' => true,
];
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>導入実績管理｜DAMAGA Pro</title><link rel="stylesheet" href="/assets/css/faq-admin.css?v=20260620-1"><link rel="stylesheet" href="/assets/css/case-admin.css?v=20260625-1"></head>
<body><header class="admin-header"><a href="/case/admin/"><img src="/assets/images/brand/damaga-pro-logo-mark.png" alt=""><strong>導入実績管理</strong></a><nav><a href="/case/" target="_blank" rel="noopener">公開ページを見る</a><a href="/faq/admin/">FAQ管理</a><form method="post"><input type="hidden" name="action" value="logout"><button type="submit">ログアウト</button></form></nav></header>
<main class="admin-shell"><section class="admin-heading"><div><p>DAMAGA Pro CASE MANAGEMENT</p><h1><?= $editing ? '導入実績を編集' : '導入実績を追加' ?></h1></div><a class="admin-new" href="/case/admin/">＋ 新規作成</a></section>
<?php if ($message): ?><div class="admin-alert success"><?= case_escape($message) ?></div><?php endif; ?><?php if ($error): ?><div class="admin-alert error"><?= case_escape($error) ?></div><?php endif; ?>
<div class="admin-grid"><section class="admin-panel admin-editor"><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save_case"><input type="hidden" name="csrf" value="<?= case_escape($_SESSION['case_csrf']) ?>"><input type="hidden" name="original_slug" value="<?= case_escape($form['slug']) ?>">
  <div class="admin-two"><label>URL名<input name="slug" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" value="<?= case_escape($form['slug']) ?>" placeholder="facility-name"></label><label>施設カテゴリ<select name="category"><?php foreach ($data['categories'] as $category): ?><option value="<?= case_escape($category['id']) ?>" <?= $category['id'] === $form['category'] ? 'selected' : '' ?>><?= case_escape($category['name']) ?></option><?php endforeach; ?></select></label></div>
  <label>施設名<input name="facility_name" required value="<?= case_escape($form['facility_name']) ?>"></label><label>所在地<input name="location" value="<?= case_escape($form['location']) ?>"></label>
  <div class="admin-two"><label>施工日（表示用）<input name="installation_date" value="<?= case_escape($form['installation_date']) ?>" placeholder="2026年5月"></label><label>施工年月（並び順用）<input type="month" name="installation_date_iso" value="<?= case_escape($form['installation_date_iso']) ?>"></label></div>
  <div class="admin-two"><label>ヒアリング日<input name="interview_date" value="<?= case_escape($form['interview_date']) ?>" placeholder="2026年6月"></label><label>施工面積<input name="area" value="<?= case_escape($form['area']) ?>" placeholder="約423㎡"></label></div>
  <label>施工箇所<input name="installation_locations" value="<?= case_escape(implode('、', $form['installation_locations'])) ?>" placeholder="病室、共用部、東側窓、南側窓"></label>
  <div class="admin-two"><label>使用製品<input name="product" value="<?= case_escape($form['product']) ?>"></label><label>施設・病棟規模<input name="scale" value="<?= case_escape($form['scale']) ?>"></label></div>
  <label>簡易コメント<textarea name="summary" required rows="3"><?= case_escape($form['summary']) ?></textarea></label><label>印象的な言葉<input name="lead_quote" value="<?= case_escape($form['lead_quote']) ?>" placeholder="すごく変わったよ"></label>
  <label>導入前の課題<textarea name="challenge" rows="6"><?= case_escape($form['challenge']) ?></textarea></label><label>施工内容<textarea name="installation" rows="6"><?= case_escape($form['installation']) ?></textarea></label><label>導入後の変化<textarea name="changes" rows="7"><?= case_escape($form['changes']) ?></textarea></label><label>お客様の声<textarea name="customer_voice" rows="9"><?= case_escape($form['customer_voice']) ?></textarea></label><label>当社コメント<textarea name="our_comment" rows="6"><?= case_escape($form['our_comment']) ?></textarea></label><label>今後のフォロー<textarea name="follow_up" rows="4"><?= case_escape($form['follow_up']) ?></textarea></label>
  <label>titleタグ<input name="title" maxlength="80" value="<?= case_escape($form['title']) ?>"><small>空欄の場合は施設名から自動生成します。</small></label><label>meta description<textarea name="meta_description" maxlength="180" rows="3"><?= case_escape($form['meta_description']) ?></textarea><small>空欄の場合は施設名から自動生成します。</small></label>
  <?php if ($form['images']): ?><fieldset class="case-admin-images"><legend>登録済み写真</legend><?php foreach ($form['images'] as $image): ?><label><img src="<?= case_escape($image['src']) ?>" alt=""><span><input type="radio" name="main_image" value="<?= case_escape($image['src']) ?>" <?= $image['src'] === $form['main_image'] ? 'checked' : '' ?>>メイン画像</span><span><input type="checkbox" name="remove_images[]" value="<?= case_escape($image['src']) ?>">削除する</span><small><?= case_escape($image['caption']) ?></small></label><?php endforeach; ?></fieldset><?php endif; ?>
  <label>写真を追加（複数選択可）<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple><small>JPEG・PNG・WebP、1枚8MBまで、最大20枚です。</small></label>
  <label class="admin-check"><input type="checkbox" name="published" <?= !empty($form['published']) ? 'checked' : '' ?>>公開する</label><button class="admin-submit" type="submit">導入実績を保存する</button>
</form></section>
<aside class="admin-sidebar"><section class="admin-panel"><h2>登録済み導入実績</h2><div class="admin-item-list"><?php foreach ($data['items'] as $item): ?><article><div><span><?= !empty($item['published']) ? '公開' : '下書き' ?></span><strong><?= case_escape($item['facility_name']) ?></strong><small>/case/<?= case_escape($item['slug']) ?>/</small></div><div class="admin-item-actions"><a href="?edit=<?= rawurlencode($item['slug']) ?>">編集</a><form method="post" onsubmit="return confirm('この導入実績を削除しますか？')"><input type="hidden" name="action" value="delete_case"><input type="hidden" name="csrf" value="<?= case_escape($_SESSION['case_csrf']) ?>"><input type="hidden" name="slug" value="<?= case_escape($item['slug']) ?>"><button type="submit">削除</button></form></div></article><?php endforeach; ?></div></section><section class="admin-help"><h2>公開前チェック</h2><ul><li>施設から掲載許可を得ている</li><li>患者・利用者の個人情報が写っていない</li><li>体感と測定値を区別している</li><li>効果を断定していない</li><li>今後のフォロー予定を確認した</li></ul></section></aside></div></main></body></html>
