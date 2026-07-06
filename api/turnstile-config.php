<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$siteKey = getenv('NEXT_PUBLIC_TURNSTILE_SITE_KEY') ?: '';

echo json_encode([
    'siteKey' => $siteKey,
], JSON_UNESCAPED_UNICODE);
