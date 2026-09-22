<?php require_once __DIR__.'/../lib/layout.php'; require_role('admin');
$byRole=[]; foreach(db()->query("SELECT role,COUNT(*) c FROM users GROUP BY role") as $r) $byRole[$r['role']]=$r['c'];
$byStatus=[]; foreach(db()->query("SELECT status,COUNT(*) c FROM cases GROUP BY status") as $r) $byStatus[$r['status']]=$r['c'];
$ev=(int)db()->query("SELECT COUNT(*) FROM audit_log")->fetchColumn();
shell_top('Administración'); ?>
<h1>Panel de administración</h1>
<div class="cards">
  <div class="kpi"><b><?=array_sum($byRole)?></b><span>Usuarios</span></div>
  <div class="kpi"><b><?=(int)($byRole['abogado']??0)?></b><span>Abogados</span></div>
  <div class="kpi"><b><?=(int)($byRole['cliente']??0)?></b><span>Clientes</span></div>
  <div class="kpi"><b><?=array_sum($byStatus)?></b><span>Asuntos</span></div>
  <div class="kpi"><b><?=$ev?></b><span>Eventos (bitácora)</span></div>
</div>
<p><a class="btnlink" href="/portal/admin/usuarios.php">Gestionar usuarios</a> <a class="btnlink" href="/portal/admin/asuntos.php">Gestionar asuntos</a></p>
<p class="soon">Los leads del formulario llegan por correo a contacto@cglegal.com.mx (no se gestionan aquí).</p>
<?php shell_bottom();
