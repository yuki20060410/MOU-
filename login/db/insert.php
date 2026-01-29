<?php
$pdo = new PDO(
  'mysql:host=localhost;dbname=webSite_db;charset=utf8',
  'webSite',
  'yuki',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pdo->beginTransaction();

$name  = $_POST['name'] ?? '';
$price = $_POST['price'] ?? '';
$image = $_FILES['image'] ?? null;

if ($name === '' || $price === '' || !$image) {
  exit('未入力があります');
}

/* ===== 画像保存 ===== */
$dir = __DIR__ . '/../images/';   // ★ 重要
if (!is_dir($dir)) mkdir($dir, 0777, true);

$filename = time() . '_' . basename($image['name']);
$path = $dir . $filename;

move_uploaded_file($image['tmp_name'], $path);

/* ===== DB登録 ===== */
$sql = "INSERT INTO products_login (name, price, image_path)
        VALUES (:name, :price, :image_path)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':name' => $name,
  ':price' => $price,
  ':image_path' => 'images/' . $filename // ★ index.php からの相対パス
]);

$pdo->commit();

header("Location: ../index.php");
exit;
