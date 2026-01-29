<?php
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

// POSTで送られる
if (empty($_POST['credential'])) {
    http_response_code(400);
    exit('不正なリクエストです');
}

$client = new Google_Client([
    'client_id' => '579961042132-vpho1shu2mfcehdss1fn3ok0c3kcf1ej.apps.googleusercontent.com',
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
