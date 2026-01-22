<?php
require __DIR__.'/_common.php';
session_start_once();

// 既にログイン済みならタイムラインへ
if (current_user()) { header('Location:/timeline.php'); exit; }
?>
<h2>ログイン</h2>
<form action="/login_finish.php" method="post">
    <div>メール: <input type="email" name="email" required></div>
    <div>パスワード: <input type="password" name="password" required></div>
    <button>ログイン</button>
</form>
<p><a href="/signup.php">新規会員登録はこちら</a></p>