<?php
require __DIR__.'/_common.php';
session_start_once();

// 既にログイン済みならタイムラインへ
if (current_user()) { header('Location:/timeline.php'); exit; }
?>
<h2>新規会員登録</h2>
<form action="/signup_finish.php" method="post">
    <div>名前: <input type="text" name="name" required></div>
    <div>メール: <input type="email" name="email" required></div>
    <div>パスワード: <input type="password" name="password" minlength="6" required></div>
    <div>パスワード(確認): <input type="password" name="password_confirm" minlength="6" required></div>
    <button>登録</button>
</form>
<p><a href="/login.php">ログインへ戻る</a></p>