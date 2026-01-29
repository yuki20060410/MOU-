<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['user']['name'] ?? 'ゲスト';

/* ===== DB接続 ===== */
$pdo = new PDO(
  'mysql:host=localhost;dbname=webSite_db;charset=utf8',
  'webSite',
  'yuki',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

/* ===== 商品取得 ===== */
$stmt = $pdo->query("SELECT * FROM products_login ORDER BY created_at DESC");
$products_login = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/edit.css">
    <title>hokann</title>
</head>
<body>
    <header>
        <h2>
        ようこそ
        <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
        さん
        </h2>    
    </header>

    <main class="edit-page">
    <h2 class="edit-title">商品編集</h2>

    <form action="db/insert.php" method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="商品名" required>
        <input type="number" name="price" placeholder="値段" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">登録</button>
    </form>

    <div class="products">
        <?php foreach ($products_login as $p): ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($p['image_path']) ?>" class="product-img" alt="">
                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="product-price">¥<?= number_format($p['price']) ?></div>

                <div class="product-actions">
                <a href="edit.php?id=<?= $p['id'] ?>" class="edit-btn">編集</a>
                <a href="delete.php?id=<?= $p['id'] ?>" class="delete-btn"
                    onclick="return confirm('削除しますか？')">削除</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>






    </main>   
    

    <footer>
    <p><a href="logout.php">ログアウト</a></p>
    <p><a href="../index.php">ホームに戻る</a></p>
    </footer>
    
    <script src="../js/edit.js"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    
</body>
</html>