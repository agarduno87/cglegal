<?php require_once __DIR__.'/lib/layout.php'; require_once __DIR__.'/lib/totp.php'; require_login();
$uid=(int)current_user()['id']; $base='/portal/2fa.php';
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) post_redirect($base,'','Sesión expirada.');
  $a=$_POST['action']??'';
  if ($a==='enable') {
    $secret=$_SESSION['enroll_secret']??'';
    if ($secret && totp_verify($secret,$_POST['code']??'')) { user_set_2fa($uid,$secret); unset($_SESSION['enroll_secret']); audit('2fa_enable'); post_redirect($base,'Verificación en dos pasos activada.'); }
    post_redirect($base,'','Código incorrecto. Revisa la hora de tu teléfono e inténtalo de nuevo.');
  }
  if ($a==='disable') {
    $full=user_get($uid);
    if ($full && (int)$full['twofa'] && totp_verify((string)$full['totp_secret'],$_POST['code']??'')) { user_disable_2fa($uid); audit('2fa_disable'); post_redirect($base,'Verificación en dos pasos desactivada.'); }
    post_redirect($base,'','Código incorrecto.');
  }
}
$full=user_get($uid); $on=(int)($full['twofa']??0)===1; $t=csrf_token();
if (!$on && empty($_SESSION['enroll_secret'])) $_SESSION['enroll_secret']=totp_secret();
$secret=$_SESSION['enroll_secret']??'';
shell_top('Seguridad'); ?>
<h1>Verificación en dos pasos (2FA)</h1>
<?php if ($on): ?>
<section class="mcard" style="max-width:560px">
  <h2>✅ Activa</h2>
  <p class="dim">Tu cuenta pide un código de tu app autenticadora al iniciar sesión.</p>
  <form method="post" class="stack">
    <input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="disable">
    <label>Para desactivarla, ingresa un código actual<input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required></label>
    <div><button class="danger">Desactivar 2FA</button></div>
  </form>
</section>
<?php else: ?>
<section class="mcard" style="max-width:560px">
  <h2>Activar</h2>
  <p class="dim">Usa una app como Google Authenticator, Authy o 1Password.</p>
  <ol class="dim" style="line-height:1.9">
    <li>En tu app, elige <b>“Agregar cuenta”</b> → <b>“Ingresar clave de configuración”</b>.</li>
    <li>Cuenta: <b><?=h($full['email'])?></b> · Tipo: <b>Basada en tiempo</b>.</li>
    <li>Clave:</li>
  </ol>
  <p style="font-family:ui-monospace,Menlo,monospace;font-size:19px;letter-spacing:2px;background:var(--surface);border:1px solid var(--linea);border-radius:9px;padding:12px 14px;text-align:center"><?=h(trim(chunk_split($secret,4,' ')))?></p>
  <p class="dim" style="word-break:break-all">¿Tu app acepta enlace? <code><?=h(totp_uri($secret,$full['email']))?></code></p>
  <form method="post" class="stack">
    <input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="enable">
    <label>Confirma con el código de 6 dígitos que muestra tu app<input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required></label>
    <div><button>Activar 2FA</button></div>
  </form>
</section>
<?php endif; ?>
<?php shell_bottom();
