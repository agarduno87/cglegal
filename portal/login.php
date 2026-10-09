<?php require_once __DIR__.'/lib/auth.php'; boot_session();
if (current_user()) { header('Location: '.role_home(current_user()['role'])); exit; }
$err='';
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  $ip=$_SERVER['REMOTE_ADDR']??''; $email=trim($_POST['email']??'');
  if (!csrf_check($_POST['csrf']??'')) $err='Sesión expirada, vuelve a intentar.';
  elseif (login_throttled($email,$ip)) { audit('login_blocked:'.$email); $err='Demasiados intentos fallidos. Espera unos minutos e inténtalo de nuevo.'; }
  elseif (attempt_login($email, $_POST['password']??'')) { login_clear($email,$ip); header('Location: '.role_home(current_user()['role'])); exit; }
  else { login_record_fail($email,$ip); $err='Credenciales incorrectas.'; }
}
$t=csrf_token(); ?>
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso — Culebro Abogados</title>
<link rel="icon" type="image/png" href="/assets/icono-color.png">
<link rel="stylesheet" href="/assets/fonts.css"><link rel="stylesheet" href="assets/portal.css"></head>
<body class="login"><form method="post" class="card">
<img class="logo" src="/assets/icono-color.png" alt=""><h1>Portal Culebro</h1>
<?php if($err) echo '<p class="err">'.h($err).'</p>'; ?>
<input type="hidden" name="csrf" value="<?=h($t)?>">
<label>Correo<input type="email" name="email" required autocomplete="username"></label>
<label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label>
<button type="submit">Entrar</button>
<p class="foot"><a href="/">← Volver al sitio</a></p>
</form></body></html>
