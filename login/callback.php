<?php
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

// POSTで送られる
if (empty($_POST['credential'])) {
    http_response_code(400);
    exit('不正なリクエストです');
}



// 修正後
$env = parse_ini_file('/var/www/.env');
$client = new Google_Client([
    'client_id' => $env['GOOGLE_CLIENT_ID'],
]);

$payload = $client->verifyIdToken($_POST['credential']);

if (!$payload) {
    http_response_code(401);
    exit('認証失敗');
}

// ログイン成功
$_SESSION['user'] = [
    'google_id' => $payload['sub'],
    'email'     => $payload['email'],
    'name'      => $payload['name'],
    'picture'   => $payload['picture'],
];

header('Location: index.php');
exit;
