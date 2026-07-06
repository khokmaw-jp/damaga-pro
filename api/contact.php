<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

const TURNSTILE_ERROR_MESSAGE = 'セキュリティ確認に失敗しました。時間をおいて再度お試しください。';
const ALLOWED_TURNSTILE_HOSTNAMES = ['damaga-pro.jp', 'www.damaga-pro.jp'];

function json_response(int $statusCode, array $payload): never
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function post_siteverify(string $secret, string $token, string $remoteIp): array
{
    $payload = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $remoteIp,
    ]);

    if (function_exists('curl_init')) {
        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $body === '') {
            throw new RuntimeException($error ?: 'Cloudflare Siteverify request failed.');
        }

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 10,
        ],
    ]);
    $body = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);

    if ($body === false || $body === '') {
        throw new RuntimeException('Cloudflare Siteverify request failed.');
    }

    return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
}

function clean_field(string $key, int $maxLength = 1000): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    $value = str_replace(["\r", "\n"], ' ', $value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }
    return substr($value, 0, $maxLength);
}

function truncate_text(string $value, int $maxLength): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }
    return substr($value, 0, $maxLength);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => '許可されていない送信方法です。']);
}

$secret = getenv('TURNSTILE_SECRET_KEY') ?: '';
$token = (string)($_POST['cf-turnstile-response'] ?? '');

if ($secret === '' || $token === '') {
    json_response(400, ['success' => false, 'message' => TURNSTILE_ERROR_MESSAGE]);
}

try {
    $verification = post_siteverify($secret, $token, (string)($_SERVER['REMOTE_ADDR'] ?? ''));
} catch (Throwable $error) {
    json_response(400, ['success' => false, 'message' => TURNSTILE_ERROR_MESSAGE]);
}

$hostname = (string)($verification['hostname'] ?? '');
$hostnameIsAllowed = in_array($hostname, ALLOWED_TURNSTILE_HOSTNAMES, true);

if (($verification['success'] ?? false) !== true || !$hostnameIsAllowed) {
    json_response(400, ['success' => false, 'message' => TURNSTILE_ERROR_MESSAGE]);
}

$type = clean_field('type', 80);
$company = clean_field('company', 120);
$name = clean_field('name', 80);
$email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$tel = clean_field('tel', 60);
$message = trim((string)($_POST['message'] ?? ''));
$message = truncate_text($message, 3000);

if ($type === '' || $company === '' || $name === '' || $email === false) {
    json_response(422, ['success' => false, 'message' => '必須項目を確認してください。']);
}

$to = getenv('CONTACT_TO_EMAIL') ?: 'info@damaga-pro.jp';
$subject = '【DAMAGAシートPRO】お問い合わせ';
$from = 'DAMAGAシートPRO <no-reply@damaga-pro.jp>';
$body = implode("\n", [
    'DAMAGAシートPROサイトからお問い合わせがありました。',
    '',
    'お問い合わせ内容: ' . $type,
    '会社・施設名: ' . $company,
    'ご担当者名: ' . $name,
    'メールアドレス: ' . $email,
    '電話番号: ' . ($tel !== '' ? $tel : '未入力'),
    '',
    '本文:',
    $message !== '' ? $message : '未入力',
]);
$headers = [
    'From: ' . $from,
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
];

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$sent = mail($to, $encodedSubject, $body, implode("\r\n", $headers));

if (!$sent) {
    json_response(500, ['success' => false, 'message' => '送信に失敗しました。時間をおいて再度お試しください。']);
}

$autoReplySubject = '【DAMAGAシートPRO】お問い合わせありがとうございます';
$autoReplyBody = implode("\n", [
    $name . ' 様',
    '',
    'この度は、DAMAGA シート PROへお問い合わせいただきありがとうございます。',
    '以下の内容でお問い合わせを受け付けました。',
    '担当者より内容を確認のうえ、あらためてご連絡いたします。',
    '',
    'お問い合わせ内容: ' . $type,
    '会社・施設名: ' . $company,
    'ご担当者名: ' . $name,
    'メールアドレス: ' . $email,
    '電話番号: ' . ($tel !== '' ? $tel : '未入力'),
    '',
    '本文:',
    $message !== '' ? $message : '未入力',
    '',
    '----------------------------------------',
    'DAMAGA シート PRO',
    '株式会社ファンビータ',
    'https://damaga-pro.jp/',
    '----------------------------------------',
    '',
    '※このメールは自動送信です。お心当たりがない場合は破棄してください。',
]);
$autoReplyHeaders = [
    'From: ' . $from,
    'Reply-To: ' . $to,
    'Content-Type: text/plain; charset=UTF-8',
];
$encodedAutoReplySubject = '=?UTF-8?B?' . base64_encode($autoReplySubject) . '?=';
$autoReplySent = mail((string)$email, $encodedAutoReplySubject, $autoReplyBody, implode("\r\n", $autoReplyHeaders));

if (!$autoReplySent) {
    json_response(500, ['success' => false, 'message' => '自動返信メールの送信に失敗しました。時間をおいて再度お試しください。']);
}

json_response(200, ['success' => true]);
