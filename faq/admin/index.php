<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/faq/admin/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();
require dirname(__DIR__) . '/lib.php';

header('X-Robots-Tag: noindex, nofollow, noarchive');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

$adminPassword = (string) (getenv('FAQ_ADMIN_PASSWORD') ?: '');
if ($adminPassword === '') {
    http_response_code(503);
    exit('FAQ管理画面は未設定です。サーバー環境変数 FAQ_ADMIN_PASSWORD を設定してください。');
}

if (empty($_SESSION['faq_csrf'])) {
    $_SESSION['faq_csrf'] = bin2hex(random_bytes(24));
}

function admin_password_matches(string $input, string $configured): bool
{
    $passwordInfo = password_get_info($configured);
    if (!empty($passwordInfo['algo'])) {
        return password_verify($input, $configured);
    }
    return hash_equals($configured, $input);
}

function admin_sections_to_text(array $sections): string
{
    $blocks = [];
    foreach ($sections as $section) {
        $blocks[] = trim((string) ($section['heading'] ?? '')) . "\n" . implode("\n", $section['paragraphs'] ?? []);
    }
    return implode("\n\n---\n\n", $blocks);
}

function admin_text_to_sections(string $text): array
{
    $sections = [];
    foreach (preg_split('/\R\s*---\s*\R/u', trim($text)) ?: [] as $block) {
        $lines = preg_split('/\R/u', trim($block)) ?: [];
        $heading = trim((string) array_shift($lines));
        $paragraphs = array_values(array_filter(array_map('trim', $lines), static fn(string $line): bool => $line !== ''));
        if ($heading !== '' && $paragraphs) {
            $sections[] = ['heading' => $heading, 'paragraphs' => $paragraphs];
        }
    }
    return $sections;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'login') {
        if (admin_password_matches((string) ($_POST['password'] ?? ''), $adminPassword)) {
            session_regenerate_id(true);
            $_SESSION['faq_admin_authenticated'] = true;
            $_SESSION['faq_csrf'] = bin2hex(random_bytes(24));
            header('Location: /faq/admin/');
            exit;
        }
        $error = 'パスワードが正しくありません。';
    } elseif ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: /faq/admin/');
        exit;
    }
}

$authenticated = !empty($_SESSION['faq_admin_authenticated']);
if (!$authenticated):
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>FAQ管理ログイン｜DAMAGA PRO</title><link rel="stylesheet" href="/assets/css/faq-admin.css?v=20260620-1"></head>
<body class="admin-login"><main><form method="post" class="admin-login-card"><img src="/assets/images/brand/damaga-pro-logo-mark.png" alt=""><p>DAMAGA PRO</p><h1>FAQ管理ログイン</h1><?php if ($error): ?><div class="admin-alert error"><?= faq_escape($error) ?></div><?php endif; ?><input type="hidden" name="action" value="login"><label>管理者パスワード<input type="password" name="password" required autocomplete="current-password"></label><button type="submit">ログイン</button><a href="/faq/">公開Q&amp;Aを見る</a></form></main></body></html>
<?php
exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals((string) $_SESSION['faq_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('不正なリクエストです。');
    }

    $data = faq_load_data();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save_item') {
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $question = trim((string) ($_POST['question'] ?? ''));
        $sections = admin_text_to_sections((string) ($_POST['sections'] ?? ''));
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $error = 'URL名は半角英小文字・数字・ハイフンのみで入力してください。';
        } elseif ($question === '' || !$sections) {
            $error = '質問と、少なくとも1つの本文セクションを入力してください。';
        } else {
            $item = [
                'slug' => $slug,
                'category' => (string) ($_POST['category'] ?? 'basics'),
                'tags' => array_values(array_filter(array_map('trim', preg_split('/[,、]/u', (string) ($_POST['tags'] ?? '')) ?: []))),
                'title' => trim((string) ($_POST['title'] ?? '')),
                'meta_description' => trim((string) ($_POST['meta_description'] ?? '')),
                'question' => $question,
                'short_answer' => trim((string) ($_POST['short_answer'] ?? '')),
                'calculator_type' => trim((string) ($_POST['calculator_type'] ?? '')),
                'sections' => $sections,
                'related' => array_values(array_filter(array_map('trim', explode(',', (string) ($_POST['related'] ?? ''))))),
                'published' => isset($_POST['published']),
                'updated' => date('Y-m-d'),
            ];
            $replaced = false;
            foreach ($data['items'] as $index => $existing) {
                if (($existing['slug'] ?? '') === (string) ($_POST['original_slug'] ?? $slug)) {
                    $data['items'][$index] = $item;
                    $replaced = true;
                    break;
                }
            }
            if (!$replaced) $data['items'][] = $item;

            if (!faq_create_route($slug)) {
                $error = '公開URL用フォルダを作成できませんでした。faqフォルダの書き込み権限を確認してください。';
            } elseif (!faq_save_data($data)) {
                $error = 'データを保存できませんでした。faq/dataフォルダの書き込み権限を確認してください。';
            } else {
                faq_sync_sitemap($data);
                $message = 'Q&Aを保存しました。';
            }
        }
    } elseif ($action === 'delete_item') {
        $slug = (string) ($_POST['slug'] ?? '');
        $data['items'] = array_values(array_filter($data['items'], static fn(array $item): bool => ($item['slug'] ?? '') !== $slug));
        if (faq_save_data($data)) { faq_sync_sitemap($data); $message = 'Q&Aを削除しました。'; } else $error = '削除内容を保存できませんでした。';
    } elseif ($action === 'save_category') {
        $id = strtolower(trim((string) ($_POST['category_id'] ?? '')));
        if (!preg_match('/^[a-z0-9-]+$/', $id)) {
            $error = 'カテゴリーIDは半角英小文字・数字・ハイフンで入力してください。';
        } else {
            $category = ['id' => $id, 'name' => trim((string) ($_POST['category_name'] ?? '')), 'description' => trim((string) ($_POST['category_description'] ?? ''))];
            $found = false;
            foreach ($data['categories'] as $index => $existing) {
                if (($existing['id'] ?? '') === $id) { $data['categories'][$index] = $category; $found = true; break; }
            }
            if (!$found) $data['categories'][] = $category;
            if (faq_save_data($data)) $message = 'カテゴリーを保存しました。'; else $error = 'カテゴリーを保存できませんでした。';
        }
    }
}

$data = faq_load_data();
$editingSlug = (string) ($_GET['edit'] ?? '');
$editing = null;
foreach ($data['items'] ?? [] as $item) if (($item['slug'] ?? '') === $editingSlug) $editing = $item;
$form = $editing ?? ['slug' => '', 'category' => $data['categories'][0]['id'] ?? 'basics', 'tags' => [], 'title' => '', 'meta_description' => '', 'question' => '', 'short_answer' => '', 'calculator_type' => '', 'sections' => [], 'related' => [], 'published' => true];
$calculatorTypes = [
    '' => 'なし（通常のQ&A）',
    'window-area' => '窓面積',
    'installation-area' => '概算施工面積',
    'aircon-cost' => '年間電気代・空調費',
    'savings' => '想定削減額',
    'roi' => '投資回収年数（ROI）',
    'solar-exposure' => '方角・日射時間',
    'facility-operation' => '施設面積・営業時間',
    'schedule' => '施工希望時期',
    'performance' => '性能数値',
    'before-after' => '施工前後比較',
];
?>
<!doctype html>
<html lang="ja">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>FAQ管理｜DAMAGA PRO</title><link rel="stylesheet" href="/assets/css/faq-admin.css?v=20260620-1"></head>
<body>
  <header class="admin-header"><a href="/faq/admin/"><img src="/assets/images/brand/damaga-pro-logo-mark.png" alt=""><strong>FAQ管理</strong></a><nav><a href="/faq/" target="_blank" rel="noopener">公開ページを見る</a><form method="post"><input type="hidden" name="action" value="logout"><button type="submit">ログアウト</button></form></nav></header>
  <main class="admin-shell">
    <section class="admin-heading"><div><p>DAMAGA PRO KNOWLEDGE BASE</p><h1><?= $editing ? 'Q&Aを編集' : 'Q&Aを追加' ?></h1></div><a class="admin-new" href="/faq/admin/">＋ 新規作成</a></section>
    <?php if ($message): ?><div class="admin-alert success"><?= faq_escape($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-alert error"><?= faq_escape($error) ?></div><?php endif; ?>

    <div class="admin-grid">
      <section class="admin-panel admin-editor">
        <form method="post">
          <input type="hidden" name="action" value="save_item"><input type="hidden" name="csrf" value="<?= faq_escape($_SESSION['faq_csrf']) ?>"><input type="hidden" name="original_slug" value="<?= faq_escape($form['slug']) ?>">
          <div class="admin-two"><label>URL名（英数字・ハイフン）<input name="slug" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" value="<?= faq_escape($form['slug']) ?>" placeholder="window-film-example"></label><label>カテゴリー<select name="category"><?php foreach ($data['categories'] as $category): ?><option value="<?= faq_escape($category['id']) ?>" <?= $category['id'] === $form['category'] ? 'selected' : '' ?>><?= faq_escape($category['name']) ?></option><?php endforeach; ?></select></label></div>
          <label>ページの質問（h1・構造化データ）<input name="question" required value="<?= faq_escape($form['question']) ?>" placeholder="窓フィルムについての質問を入力"></label>
          <label>回答（ページ表示・FAQPage JSON-LD共通）<textarea name="short_answer" required rows="4"><?= faq_escape($form['short_answer']) ?></textarea><small>この文章がページ上の回答と構造化データの両方に使われます。</small></label>
          <label>数値ウィジェット<select name="calculator_type"><?php foreach ($calculatorTypes as $value => $label): ?><option value="<?= faq_escape($value) ?>" <?= ($form['calculator_type'] ?? '') === $value ? 'selected' : '' ?>><?= faq_escape($label) ?></option><?php endforeach; ?></select><small>数値入力や計算を表示する記事だけ選択します。通常の記事は「なし」のままにします。</small></label>
          <label>titleタグ <input name="title" required maxlength="80" value="<?= faq_escape($form['title']) ?>"><small>30〜35文字程度を目安に、末尾へ「｜DAMAGA PRO」を付けます。</small></label>
          <label>meta description<textarea name="meta_description" required maxlength="180" rows="3"><?= faq_escape($form['meta_description']) ?></textarea><small>80〜120文字程度を目安にします。</small></label>
          <label>本文セクション<textarea name="sections" required rows="15" placeholder="見出し&#10;本文を入力&#10;&#10;---&#10;&#10;次の見出し&#10;次の本文"><?= faq_escape(admin_sections_to_text($form['sections'])) ?></textarea><small>1行目をh2見出し、2行目以降を本文にします。セクション間は「---」だけの行で区切ります。</small></label>
          <div class="admin-two"><label>ハッシュタグ<input name="tags" value="<?= faq_escape(implode('、', $form['tags'])) ?>" placeholder="暑さ対策、病院、電気代"></label><label>関連Q&AのURL名<input name="related" value="<?= faq_escape(implode(',', $form['related'])) ?>" placeholder="slug-a,slug-b"></label></div>
          <label class="admin-check"><input type="checkbox" name="published" <?= !empty($form['published']) ? 'checked' : '' ?>>公開する</label>
          <button class="admin-submit" type="submit">Q&amp;Aを保存する</button>
        </form>
      </section>

      <aside class="admin-sidebar">
        <section class="admin-panel"><h2>登録済みQ&amp;A</h2><div class="admin-item-list"><?php foreach ($data['items'] as $item): ?><article><div><span><?= !empty($item['published']) ? '公開' : '下書き' ?></span><strong><?= faq_escape($item['question']) ?></strong><small>/faq/<?= faq_escape($item['slug']) ?>/</small></div><div class="admin-item-actions"><a href="?edit=<?= rawurlencode($item['slug']) ?>">編集</a><form method="post" onsubmit="return confirm('このQ&Aを削除しますか？')"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="csrf" value="<?= faq_escape($_SESSION['faq_csrf']) ?>"><input type="hidden" name="slug" value="<?= faq_escape($item['slug']) ?>"><button type="submit">削除</button></form></div></article><?php endforeach; ?></div></section>
        <section class="admin-panel"><h2>カテゴリー追加・更新</h2><form method="post"><input type="hidden" name="action" value="save_category"><input type="hidden" name="csrf" value="<?= faq_escape($_SESSION['faq_csrf']) ?>"><label>ID<input name="category_id" required pattern="[a-z0-9-]+" placeholder="installation"></label><label>表示名<input name="category_name" required placeholder="施工について"></label><label>説明<input name="category_description" required></label><button class="admin-secondary" type="submit">カテゴリーを保存</button></form></section>
        <section class="admin-help"><h2>公開前チェック</h2><ul><li>断定や誇大な表現を避ける</li><li>未確認の性能数値を入れない</li><li>回答と本文に矛盾がないか確認</li><li>関連Q&Aを2〜3件設定する</li></ul></section>
      </aside>
    </div>
  </main>
</body></html>
