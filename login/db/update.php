<?php
session_start();



$pdo = new PDO(
  'mysql:host=localhost;dbname=webSite_db;charset=utf8',
  'webSite',
  'yuki',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$id        = $_POST['id'] ?? '';
$name      = $_POST['name'] ?? '';
$price     = $_POST['price'] ?? '';
$oldImage  = $_POST['old_image'] ?? '';
$newImage  = $_FILES['image'] ?? null;

if ($id === '' || $name === '' || $price === '') {
  exit('未入力があります');
}

$pdo->beginTransaction();

/* ===== 画像差し替え判定 ===== */
$imagePath = $oldImage;

if ($newImage && $newImage['error'] === UPLOAD_ERR_OK) {

  // 旧画像削除
  $oldPath = __DIR__ . '/../' . $oldImage;
  if (file_exists($oldPath)) unlink($oldPath);

  // 新画像保存
  $dir = __DIR__ . '/../images/';
  if (!is_dir($dir)) mkdir($dir, 0777, true);

  $filename = time() . '_' . basename($newImage['name']);
  move_uploaded_file($newImage['tmp_name'], $dir . $filename);

  $imagePath = 'images/' . $filename;
}

/* ===== DB更新 ===== */
$sql = "UPDATE products_login
        SET name = :name, price = :price, image_path = :image
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':name'  => $name,
  ':price'=> $price,
  ':image'=> $imagePath,
  ':id'   => $id
]);

$pdo->commit();

header("Location: ../index.php");
exit;
