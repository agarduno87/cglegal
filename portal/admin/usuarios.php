<?php require_once __DIR__.'/../lib/layout.php'; require_role('admin');
$self=current_user();
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) post_redirect('/portal/admin/usuarios.php','','Sesión expirada.');
  $a=$_POST['action']??'';
  try {
    if ($a==='create') {
      $n=trim($_POST['name']??''); $e=trim($_POST['email']??''); $p=$_POST['password']??''; $r=$_POST['role']??'';
      if ($n===''||mb_strlen($n)>120) post_redirect('/portal/admin/usuarios.php','','Nombre inválido.');
      if (!filter_var($e,FILTER_VALIDATE_EMAIL)) post_redirect('/portal/admin/usuarios.php','','Correo inválido.');
      if (strlen($p)<8) post_redirect('/portal/admin/usuarios.php','','La contraseña debe tener 8+ caracteres.');
      if (!in_array($r,ROLES,true)) post_redirect('/portal/admin/usuarios.php','','Rol inválido.');
      if (user_email_exists($e)) post_redirect('/portal/admin/usuarios.php','','Ese correo ya existe.');
      user_create($n,$e,$p,$r); audit('user_create:'.$e); post_redirect('/portal/admin/usuarios.php','Usuario creado.');
    }
    $id=(int)($_POST['id']??0); $target=$id?user_get($id):null;
    if (!$target) post_redirect('/portal/admin/usuarios.php','','Usuario no encontrado.');
    if ($a==='update') {
      $n=trim($_POST['name']??''); $r=$_POST['role']??''; $active=isset($_POST['active'])?1:0;
      if ($n===''||!in_array($r,ROLES,true)) post_redirect('/portal/admin/usuarios.php','','Datos inválidos.');
      // Guarda: no dejar el sistema sin admins activos
      if ($target['role']==='admin' && ($r!=='admin' || !$active) && admins_active_count($id)===0)
        post_redirect('/portal/admin/usuarios.php','','Debe quedar al menos un administrador activo.');
      if ($id===$self['id'] && $r!=='admin') post_redirect('/portal/admin/usuarios.php','','No puedes quitarte tu propio rol de admin.');
      user_update($id,$n,$r,$active); audit('user_update:'.$id); post_redirect('/portal/admin/usuarios.php','Usuario actualizado.');
    }
    if ($a==='resetpw') {
      $p=$_POST['password']??''; if (strlen($p)<8) post_redirect('/portal/admin/usuarios.php','','La contraseña debe tener 8+ caracteres.');
      user_reset_pw($id,$p); audit('user_resetpw:'.$id); post_redirect('/portal/admin/usuarios.php','Contraseña restablecida.');
    }
    if ($a==='delete') {
      if ($id===$self['id']) post_redirect('/portal/admin/usuarios.php','','No puedes eliminarte a ti mismo.');
      if ($target['role']==='admin' && admins_active_count($id)===0) post_redirect('/portal/admin/usuarios.php','','No puedes eliminar al último administrador.');
      user_delete($id); audit('user_delete:'.$id); post_redirect('/portal/admin/usuarios.php','Usuario eliminado.');
    }
  } catch (Throwable $ex) { post_redirect('/portal/admin/usuarios.php','','Error: '.$ex->getMessage()); }
  post_redirect('/portal/admin/usuarios.php');
}
$t=csrf_token(); $users=users_all(); shell_top('Usuarios'); ?>
<h1>Usuarios y roles</h1>
<details class="new"><summary>+ Nuevo usuario</summary>
<form method="post" class="row-form">
  <input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="create">
  <input name="name" placeholder="Nombre" required>
  <input name="email" type="email" placeholder="Correo" required>
  <input name="password" type="password" placeholder="Contraseña (8+)" required>
  <select name="role"><option value="cliente">cliente</option><option value="abogado">abogado</option><option value="admin">admin</option></select>
  <button>Crear</button>
</form></details>
<table class="tbl"><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Activo</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($users as $u): ?>
<tr>
  <form method="post"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?=$u['id']?>">
  <td><input name="name" value="<?=h($u['name'])?>"></td>
  <td><?=h($u['email'])?></td>
  <td><select name="role"><?php foreach(ROLES as $r) echo '<option'.($u['role']===$r?' selected':'').'>'.$r.'</option>'; ?></select></td>
  <td style="text-align:center"><input type="checkbox" name="active" <?=$u['active']?'checked':''?>></td>
  <td class="acts"><button title="Guardar">Guardar</button></form>
    <form method="post" onsubmit="return confirm('¿Eliminar a <?=h($u['email'])?>?')"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$u['id']?>"><button class="danger">Eliminar</button></form>
    <form method="post" onsubmit="var p=prompt('Nueva contraseña (8+):');if(!p)return false;this.password.value=p;return true"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="resetpw"><input type="hidden" name="id" value="<?=$u['id']?>"><input type="hidden" name="password"><button>Reset pass</button></form>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php shell_bottom();
