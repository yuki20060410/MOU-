<?php
session_start();

// すでにログイン済みならサイトへ
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}
?>
<?php if (isset($_GET['error'])): ?>
  <p style="color:red;">メールアドレスまたはパスワードが違います</p>
<?php endif; ?>


<h2>ログイン</h2>

<form action="login_check.php" method="post">
  <input type="email" name="email" placeholder="メール" required>
  <input type="password" name="password" placeholder="パスワード" required>
  <button type="submit">ログイン</button>
</form>

<p>
  <a href="register.php">新規登録はこちら</a>
</p>

<p><a href="../index.php">ホームに戻る</a></p>