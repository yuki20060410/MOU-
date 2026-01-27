<?php
session_start();

$pdo = new PDO(
  'mysql:host=localhost;dbname=webSite_db;charset=utf8',
  'webSite',
  'yuki',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$pdo->beginTransaction();

// 未定義・空対策
$username = $_POST['username'] ?? '';
$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
  exit('未入力の項目があります');
}

// パスワードをハッシュ化
$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, email, password)
        VALUES (:username, :email, :password)";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':password', $hash, PDO::PARAM_STR);

//  実行が必須
$stmt->execute();

// 登録後そのままログイン状態にする
$_SESSION['user_id'] = $pdo->lastInsertId();
$_SESSION['username'] = $username;
$pdo->commit();


header("Location: index.php");
exit;

