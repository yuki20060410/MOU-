<?php
session_start();

$pdo = new PDO(
  'mysql:host=localhost;dbname=webSite_db;charset=utf8',
  'webSite',
  'yuki',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
  exit('メールアドレスとパスワードを入力してください');
}

$sql = "SELECT id, username, password FROM users WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
  // ログイン成功
  $_SESSION['user_id'] = $user['id'];
  $_SESSION['username'] = $user['username'];

  header("Location: index.php");
  exit;
} else {
  // 失敗時
  header("Location: login.php?error=1");
  exit;
}
