<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$username = $_SESSION['username'] ?? 'ゲスト';
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <main>







    </main>   
    




    <footer>
    <p><a href="logout.php">ログアウト</a></p>
    <p><a href="../index.php">ホームに戻る</a></p>
    </footer>
    
</body>
</html>