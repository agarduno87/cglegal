<?php require_once __DIR__.'/../lib/layout.php'; require_role('admin');
$u=(int)db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
$l=(int)db()->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$a=(int)db()->query("SELECT COUNT(*) FROM audit_log")->fetchColumn();
shell_top('Administración'); ?>
<h1>Panel de administración</h1>
<div class="cards">
  <div class="kpi"><b><?=$u?></b><span>Usuarios</span></div>
  <div class="kpi"><b><?=$l?></b><span>Leads</span></div>
  <div class="kpi"><b><?=$a?></b><span>Eventos (bitácora)</span></div>
</div>
<p class="soon">Próximo (CRUD): gestión de usuarios y roles, leads, asuntos y documentos.</p>
<?php shell_bottom();
