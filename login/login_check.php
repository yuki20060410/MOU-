<?php

session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');


// すでにログインしているならホームへ
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$client = new Google_Client();

/**
 * Google OAuth 認証情報（JSON）
 * ※ Web公開ディレクトリ外を推奨
 */
$client->setAuthConfig(
    __DIR__ . '/../../config/google_oauth.json'
);

/**
 * Google Cloud Console に登録したものと
 * 完全一致させる
 */
$client->setRedirectUri(
    'http://localhost/webSaito/origin/login/callback.php'
);

/**
 * 取得するスコープ
 */
$client->setScopes([
    'openid',
    'email',
    'profile'
]);

/**
 * CSRF 対策用 state
 */
$state = bin2hex(random_bytes(16));
$_SESSION['oauth2state'] = $state;
$client->setState($state);

/**
 * Google ログイン画面へリダイレクト
 */
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
