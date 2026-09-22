<?php require_once __DIR__.'/../lib/layout.php'; require_role('admin');
$AREAS=['Corporativo general','Inmobiliario y hotelero','Auditoría legal','Comercio Exterior','Gobierno e infraestructura','Actividades filantrópicas'];
$id=(int)($_GET['id']??$_POST['id']??0); $c=$id?case_get($id):null;
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) post_redirect('/portal/admin/asunto.php?id='.$id,'','Sesión expirada.');
  if (!$c) post_redirect('/portal/admin/asuntos.php','','Asunto no encontrado.');
  $a=$_POST['action']??'';
  if ($a==='update') {
    $t=trim($_POST['title']??''); $area=trim($_POST['area']??''); $st=$_POST['status']??'nuevo';
    $cid=($_POST['client_id']??'')!==''?(int)$_POST['client_id']:null; $lid=($_POST['lawyer_id']??'')!==''?(int)$_POST['lawyer_id']:null;
    if ($t==='') post_redirect('/portal/admin/asunto.php?id='.$id,'','Título inválido.');
    if (!in_array($st,STATUSES,true)) $st='nuevo';
    case_update($id,$t,$area,$cid,$lid,$st); audit('case_update:'.$id); post_redirect('/portal/admin/asunto.php?id='.$id,'Asunto actualizado.');
  }
  if ($a==='note') { $b=trim($_POST['body']??''); if($b!==''){ case_add_note($id,current_user()['id'],$b); audit('case_note:'.$id); } post_redirect('/portal/admin/asunto.php?id='.$id,'Nota agregada.'); }
  if ($a==='delete') { case_delete($id); audit('case_delete:'.$id); post_redirect('/portal/admin/asuntos.php','Asunto eliminado.'); }
}
if (!$c) { shell_top('Asunto'); echo '<p class="flash err">Asunto no encontrado.</p>'; shell_bottom(); exit; }
$t=csrf_token(); $clientes=users_by_role('cliente'); $abogados=users_by_role('abogado'); $notes=case_notes($id); shell_top('Asunto'); ?>
<p><a href="/portal/admin/asuntos.php">← Asuntos</a></p>
<h1>Asunto #<?=$id?></h1>
<form method="post" class="stack">
  <input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?=$id?>">
  <label>Título<input name="title" value="<?=h($c['title'])?>"></label>
  <label>Área<select name="area"><option value="">—</option><?php foreach($AREAS as $a) echo '<option'.($c['area']===$a?' selected':'').'>'.h($a).'</option>'; ?></select></label>
  <label>Cliente<select name="client_id"><option value="">—</option><?php foreach($clientes as $x) echo '<option value="'.$x['id'].'"'.($c['client_id']==$x['id']?' selected':'').'>'.h($x['name']).'</option>'; ?></select></label>
  <label>Abogado<select name="lawyer_id"><option value="">—</option><?php foreach($abogados as $x) echo '<option value="'.$x['id'].'"'.($c['lawyer_id']==$x['id']?' selected':'').'>'.h($x['name']).'</option>'; ?></select></label>
  <label>Estado<select name="status"><?php foreach(STATUSES as $s) echo '<option'.($c['status']===$s?' selected':'').'>'.$s.'</option>'; ?></select></label>
  <div><button>Guardar</button></div>
</form>
<form method="post" onsubmit="return confirm('¿Eliminar este asunto?')" class="mt"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$id?>"><button class="danger">Eliminar asunto</button></form>
<h2>Notas / seguimiento</h2>
<form method="post" class="stack"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="note"><input type="hidden" name="id" value="<?=$id?>">
  <textarea name="body" placeholder="Agregar nota…" required></textarea><div><button>Agregar nota</button></div></form>
<ul class="notes"><?php foreach($notes as $n): ?><li><b><?=h($n['author']?:'—')?></b> · <span class="dim"><?=h($n['created_at'])?></span><br><?=nl2br(h($n['body']))?></li><?php endforeach; if(!$notes) echo '<li class="soon">Sin notas.</li>'; ?></ul>
<?php shell_bottom();
