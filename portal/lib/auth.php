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
/* ---- Bloqueo por intentos de login (anti fuerza bruta) ---- */
const LOGIN_MAX_EMAIL = 5;   // fallos por cuenta en la ventana
const LOGIN_MAX_IP    = 12;  // fallos por IP en la ventana
function _login_window(): string { // ventana de 15 min, según el motor (usa el reloj de la BD)
  return db()->getAttribute(PDO::ATTR_DRIVER_NAME)==='sqlite'
    ? "datetime('now','-15 minutes')" : "(NOW() - INTERVAL 15 MINUTE)";
}
function login_throttled(string $email, string $ip): bool {
  $w=_login_window();
  $a=db()->prepare("SELECT COUNT(*) FROM login_attempts WHERE email=? AND at>=$w"); $a->execute([$email]);
  $b=db()->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip=? AND at>=$w"); $b->execute([$ip]);
  return ((int)$a->fetchColumn())>=LOGIN_MAX_EMAIL || ((int)$b->fetchColumn())>=LOGIN_MAX_IP;
}
function login_record_fail(string $email, string $ip): void {
  try { db()->prepare("INSERT INTO login_attempts(ip,email) VALUES(?,?)")->execute([$ip,$email]); } catch (Throwable $e) {}
}
function login_clear(string $email, string $ip): void {
  // limpia por cuenta (no por IP) para no permitir resetear el contador anti-spray
  try { db()->prepare("DELETE FROM login_attempts WHERE email=?")->execute([$email]); } catch (Throwable $e) {}
}

function verify_credentials(string $email, string $pass): ?array {
  $st=db()->prepare("SELECT * FROM users WHERE email=?"); $st->execute([$email]); $u=$st->fetch(PDO::FETCH_ASSOC);
  if ($u && (int)$u['active']===1 && password_verify($pass, $u['password_hash'])) return $u;
  return null;
}
function complete_login(array $u): void {
  boot_session(); session_regenerate_id(true); unset($_SESSION['pending_2fa']);
  $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
  audit('login');
}
function attempt_login(string $email, string $pass): bool { $u=verify_credentials($email,$pass); if($u){ complete_login($u); return true; } return false; }
function logout(): void { boot_session(); audit('logout'); $_SESSION=[]; session_destroy(); }
function role_home(string $r): string { return $r==='admin'?'/portal/admin/':($r==='abogado'?'/portal/abogado/':'/portal/cliente/'); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
