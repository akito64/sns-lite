<?php
if (!function_exists('db')) {
  function db(): PDO {
    static $pdo=null; if ($pdo) return $pdo;
    $host=getenv('DB_HOST') ?: 'mysql';
    $name=getenv('DB_NAME') ?: 'example_db';
    $user=getenv('DB_USER') ?: 'root';
    $pass=getenv('DB_PASS') ?: '';
    $pdo=new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4",$user,$pass,[
      PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    ]);
    return $pdo;
  }
}
if (!function_exists('e')) {
  function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('session_start_once')) {
  function session_start_once(): void { if (session_status()!==PHP_SESSION_ACTIVE) session_start(); }
}
if (!function_exists('login_required')) {
  function login_required(): void {
    session_start_once();
    if (empty($_SESSION['login_user_id'])) { header('Location:/login.php'); exit; }
  }
}
if (!function_exists('current_user')) {
  function current_user(): ?array {
    session_start_once();
    if (empty($_SESSION['login_user_id'])) return null;
    $st=db()->prepare('SELECT * FROM users WHERE id=:id');
    $st->execute([':id'=>$_SESSION['login_user_id']]);
    $u=$st->fetch(); return $u ?: null;
  }
}
if (!function_exists('save_base64_image')) {
  function save_base64_image(string $dataUrl): ?string {
    if (!preg_match('#^data:image/(png|jpe?g|gif);base64,#i',$dataUrl)) return null;
    $bin=base64_decode(preg_replace('#^data:[^;]+;base64,#','',$dataUrl),true);
    if ($bin===false) return null;
    $dir='/var/www/upload/image'; if (!is_dir($dir)) @mkdir($dir,0775,true);
    $fn=sprintf('%d%s.png',time(),bin2hex(random_bytes(12)));
    return file_put_contents("$dir/$fn",$bin)!==false ? $fn : null;
  }
}