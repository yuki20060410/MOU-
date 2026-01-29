<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');




// すでにログイン済みならサイトへ
if (isset($_SESSION['user'])) {
  header("Location: index.php");
  exit;
}
?>
<?php if (isset($_GET['error'])): ?>
  <p style="color:red;">メールアドレスまたはパスワードが違います</p>
<?php endif; ?>


<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/login.css">
  <title>ログインページ</title>
</head>
<body>
  
<div class="login-card">
    <h1>ログイン</h1>
    <div id="g_id_onload"
        data-client_id="579961042132-vpho1shu2mfcehdss1fn3ok0c3kcf1ej.apps.googleusercontent.com"
        data-login_uri="http://localhost/webSaito/origin/login/callback.php"
        data-auto_prompt="false">
    </div>

    <div class="g_id_signin"
        data-type="standard"
        data-size="large"
        data-theme="outline"
        data-text="signin_with"
        data-shape="rectangular"
        data-logo_alignment="left">
    </div>




    <p><a href="../index.php">ホームに戻る</a></p>
 </div>
<script src="https://accounts.google.com/gsi/client" async defer></script>



</body>
</html>

