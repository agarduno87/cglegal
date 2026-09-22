<?php require_once __DIR__.'/../lib/layout.php'; require_role('admin');
$AREAS=['Corporativo general','Inmobiliario y hotelero','Auditoría legal','Comercio Exterior','Gobierno e infraestructura','Actividades filantrópicas'];
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) post_redirect('/portal/admin/asuntos.php','','Sesión expirada.');
  if (($_POST['action']??'')==='create') {
    $t=trim($_POST['title']??''); $area=trim($_POST['area']??''); $st=$_POST['status']??'nuevo';
    $cid=($_POST['client_id']??'')!==''?(int)$_POST['client_id']:null;
    $lid=($_POST['lawyer_id']??'')!==''?(int)$_POST['lawyer_id']:null;
    if ($t===''||mb_strlen($t)>200) post_redirect('/portal/admin/asuntos.php','','Título inválido.');
    if (!in_array($st,STATUSES,true)) $st='nuevo';
    $id=case_create($t,$area,$cid,$lid,$st); audit('case_create:'.$id); post_redirect('/portal/admin/asunto.php?id='.$id,'Asunto creado.');
  }
  post_redirect('/portal/admin/asuntos.php');
}
$t=csrf_token(); $cases=cases_all(); $clientes=users_by_role('cliente'); $abogados=users_by_role('abogado'); shell_top('Asuntos'); ?>
<h1>Asuntos</h1>
<details class="new"><summary>+ Nuevo asunto</summary>
<form method="post" class="row-form">
  <input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="create">
  <input name="title" placeholder="Título del asunto" required>
  <select name="area"><option value="">— Área —</option><?php foreach($AREAS as $a) echo '<option>'.h($a).'</option>'; ?></select>
  <select name="client_id"><option value="">— Cliente —</option><?php foreach($clientes as $c) echo '<option value="'.$c['id'].'">'.h($c['name']).'</option>'; ?></select>
  <select name="lawyer_id"><option value="">— Abogado —</option><?php foreach($abogados as $a) echo '<option value="'.$a['id'].'">'.h($a['name']).'</option>'; ?></select>
  <select name="status"><?php foreach(STATUSES as $s) echo '<option>'.$s.'</option>'; ?></select>
  <button>Crear</button>
</form></details>
<table class="tbl"><thead><tr><th>Título</th><th>Área</th><th>Cliente</th><th>Abogado</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach($cases as $c): ?>
<tr><td><?=h($c['title'])?></td><td><?=h($c['area'])?></td><td><?=h($c['client_name']?:'—')?></td><td><?=h($c['lawyer_name']?:'—')?></td>
<td><span class="badge b-<?=h($c['status'])?>"><?=h($c['status'])?></span></td>
<td><a class="btnlink" href="/portal/admin/asunto.php?id=<?=$c['id']?>">Abrir</a></td></tr>
<?php endforeach; if(!$cases) echo '<tr><td colspan="6" class="soon">Sin asuntos todavía.</td></tr>'; ?>
</tbody></table>
<?php shell_bottom();
