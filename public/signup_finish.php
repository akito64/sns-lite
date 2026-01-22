<?php
require __DIR__.'/_common.php';
session_start_once();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location:/signup.php'); exit; }

$name  = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$pass  = (string)($_POST['password'] ?? '');
$pass2 = (string)($_POST['password_confirm'] ?? '');

$err = null;
if ($name === '' || $email === '' || $pass === '') {
  $err = '全ての必須項目を入力してください。';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $err = 'メールアドレスの形式が不正です。';
} elseif ($pass !== $pass2) {
  $err = 'パスワード確認が一致しません。';
}

if ($err) {
  echo "<p>".e($err)."</p><p><a href=\"/signup.php\">戻る</a></p>";
  exit;
}

// 既存チェック
$st = db()->prepare('SELECT id FROM users WHERE email = :email');
$st->execute([':email' => $email]);
if ($st->fetch()) {
  echo "<p>このメールは既に登録されています。</p><p><a href=\"/login.php\">ログイン</a></p>";
  exit;
}

// 登録
$hash = password_hash($pass, PASSWORD_DEFAULT);
$st = db()->prepare('INSERT INTO users(name, email, password_hash) VALUES(:n,:e,:p)');
$st->execute([':n'=>$name, ':e'=>$email, ':p'=>$hash]);

$_SESSION['login_user_id'] = db()->lastInsertId();
header('Location:/timeline.php');
exit;