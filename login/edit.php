<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

require_once __DIR__ . '/../../lib/db.php';

$id = $_GET['id'] ?? '';
if ($id === '') exit('不正なアクセス');

$stmt = $pdo->prepare("SELECT * FROM products_login WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) exit('商品が見つかりません');
?>
<h2>商品編集</h2>
<form action="db/update.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= $product['id'] ?>">
  <input type="hidden" name="old_image" value="<?= $product['image_path'] ?>">

  <p>
    商品名<br>
    <input type="text" name="name" value="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>" required>
  </p>

  <p>
    値段<br>
    <input type="number" name="price" value="<?= $product['price'] ?>" required>
  </p>

  <p>
    画像プレビュー<br>
    <img id="preview" src="<?= htmlspecialchars($product['image_path'], ENT_QUOTES) ?>" width="180">
  </p>

  <p id="imageStatus" style="display:none; color:red; font-size:14px;">
    画像変更中
  </p>

  <input type="file" name="image" id="imageInput" accept="image/*">
  <button type="button" id="resetImageBtn">画像変更を取り消す</button>

  <p><button type="submit">更新</button></p>
</form>

<p><a href="index.php">戻る</a></p>


<script>
const input = document.getElementById("imageInput");
const preview = document.getElementById("preview");
const resetBtn = document.getElementById("resetImageBtn");
const status = document.getElementById("imageStatus");

// 元画像
const originalImage = preview.src;

// 画像選択 → プレビュー + ラベル表示
input.addEventListener("change", () => {
  const file = input.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = () => {
    preview.src = reader.result;
    status.style.display = "block"; // ← 変更中表示
  };
  reader.readAsDataURL(file);
});

// 取り消し
resetBtn.addEventListener("click", () => {
  input.value = "";
  preview.src = originalImage;
  status.style.display = "none";   // ← 非表示
});
</script>


