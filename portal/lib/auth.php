<?php
require_once __DIR__ . '/db.php';
function boot_session(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;
  $https = (($_SERVER['HTTPS'] ?? '') === 'on') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
  session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>$https,'path'=>'/']);
  session_start();
}
function csrf_token(): string { boot_session(); if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_check($t): bool { boot_session(); return !empty($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t); }
function current_user(): ?array { boot_session(); return $_SESSION['user'] ?? null; }
function require_login(): void { if (!current_user()) { header('Location: /portal/login.php'); exit; } }
function require_role(string $role): void { require_login(); if ((current_user()['role'] ?? '') !== $role) { http_response_code(403); echo 'Acceso denegado.'; exit; } }
function audit(string $action): void { try { $u=current_user(); $st=db()->prepare("INSERT INTO audit_log(user_id,action,ip) VALUES(?,?,?)"); $st->execute([$u['id']??null,$action,$_SERVER['REMOTE_ADDR']??'']); } catch (Throwable $e) {} }
function attempt_login(string $email, string $pass): bool {
  $st=db()->prepare("SELECT * FROM users WHERE email=?"); $st->execute([$email]); $u=$st->fetch(PDO::FETCH_ASSOC);
  if ($u && password_verify($pass, $u['password_hash'])) {
    boot_session(); session_regenerate_id(true);
    $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
    audit('login'); return true;
  }
  return false;
}
function logout(): void { boot_session(); audit('logout'); $_SESSION=[]; session_destroy(); }
function role_home(string $r): string { return $r==='admin'?'/portal/admin/':($r==='abogado'?'/portal/abogado/':'/portal/cliente/'); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
