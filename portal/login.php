<?php require_once __DIR__.'/lib/auth.php'; require_once __DIR__.'/lib/models.php'; require_once __DIR__.'/lib/totp.php'; boot_session();
if (current_user()) { header('Location: '.role_home(current_user()['role'])); exit; }
if (isset($_GET['reset'])) { unset($_SESSION['pending_2fa']); header('Location: /portal/login.php'); exit; }
$err=''; $ip=$_SERVER['REMOTE_ADDR']??'';
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) {
    $err='Sesión expirada, vuelve a intentar.';
  } elseif (isset($_POST['code'])) {
    // --- Paso 2: código 2FA ---
    $u = !empty($_SESSION['pending_2fa']) ? user_get((int)$_SESSION['pending_2fa']) : null;
    if (!$u || !(int)$u['twofa']) { unset($_SESSION['pending_2fa']); $err='Sesión expirada, inicia de nuevo.'; }
    elseif (login_throttled($u['email'],$ip)) { audit('login_blocked:'.$u['email']); $err='Demasiados intentos fallidos. Espera unos minutos.'; }
    elseif (totp_verify((string)$u['totp_secret'], $_POST['code']??'')) { login_clear($u['email'],$ip); complete_login($u); header('Location: '.role_home($u['role'])); exit; }
    else { login_record_fail($u['email'],$ip); $err='Código incorrecto.'; }
  } else {
    // --- Paso 1: correo + contraseña ---
    $email=trim($_POST['email']??'');
    if (login_throttled($email,$ip)) { audit('login_blocked:'.$email); $err='Demasiados intentos fallidos. Espera unos minutos e inténtalo de nuevo.'; }
    else {
      $u = verify_credentials($email, $_POST['password']??'');
      if (!$u) { login_record_fail($email,$ip); $err='Credenciales incorrectas.'; }
      elseif ((int)$u['twofa']===1 && $u['totp_secret']) { $_SESSION['pending_2fa']=(int)$u['id']; }
      else { login_clear($email,$ip); complete_login($u); header('Location: '.role_home($u['role'])); exit; }
    }
  }
}
$stage = (!empty($_SESSION['pending_2fa']) && $err !== 'Sesión expirada, inicia de nuevo.') ? 'code' : 'password';
$t=csrf_token(); ?>
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso — Culebro Abogados</title>
<link rel="icon" type="image/png" href="/assets/icono-color.png">
<link rel="stylesheet" href="/assets/fonts.css"><link rel="stylesheet" href="assets/portal.css"></head>
<body class="login"><form method="post" class="card">
<img class="logo" src="/assets/icono-color.png" alt=""><h1>Portal Culebro</h1>
<?php if($err) echo '<p class="err">'.h($err).'</p>'; ?>
<input type="hidden" name="csrf" value="<?=h($t)?>">
<?php if ($stage==='code'): ?>
<p class="foot" style="text-align:left;color:var(--gris)">Ingresa el código de 6 dígitos de tu app de autenticación.</p>
<label>Código<input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]*" maxlength="6" required autofocus></label>
<button type="submit">Verificar</button>
<p class="foot"><a href="/portal/login.php?reset=1">← Cancelar</a></p>
<?php else: ?>
<label>Correo<input type="email" name="email" required autocomplete="username"></label>
<label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label>
<button type="submit">Entrar</button>
<p class="foot"><a href="/">← Volver al sitio</a></p>
<?php endif; ?>
</form></body></html>
