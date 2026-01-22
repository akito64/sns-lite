<?php
require __DIR__.'/_common.php';
session_start_once();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location:/login.php'); exit; }

$email = trim((string)($_POST['email'] ?? ''));
$pass  = (string)($_POST['password'] ?? '');

$err = null;
if ($email === '' || $pass === '') {
  $err = 'メールとパスワードを入力してください。';
} else {
  $st = db()->prepare('SELECT * FROM users WHERE email = :email');
  $st->execute([':email' => $email]);
  $u = $st->fetch();
  if (!$u || !password_verify($pass, $u['password_hash'])) {
    $err = 'メールまたはパスワードが違います。';
  }
}

if ($err) {
  echo "<p>".e($err)."</p><p><a href=\"/login.php\">戻る</a></p>";
  exit;
}

$_SESSION['login_user_id'] = $u['id'];
header('Location:/timeline.php');
exit;