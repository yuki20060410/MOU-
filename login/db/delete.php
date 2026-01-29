<?php
session_start();



$pdo = new PDO(
  'mysql:host=localhost;dbname=webSite_db;charset=utf8',
  'webSite',
  'yuki',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$id = $_POST['id'] ?? '';
if ($id === '') exit('不正なアクセス');

$pdo->beginTransaction();

/* ===== 画像パス取得 ===== */
$stmt = $pdo->prepare("SELECT image_path FROM products_login WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) exit('データが見つかりません');

/* ===== 画像削除 ===== */
$imagePath = __DIR__ . '/../' . $product['image_path'];
if (file_exists($imagePath)) {
  unlink($imagePath);
}

/* ===== DB削除 ===== */
$stmt = $pdo->prepare("DELETE FROM products_login WHERE id = ?");
$stmt->execute([$id]);

$pdo->commit();

header("Location: ../index.php");
exit;
