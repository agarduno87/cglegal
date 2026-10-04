<?php require_once __DIR__.'/../lib/layout.php'; require_once __DIR__.'/../lib/matter.php'; require_role('admin');
$AREAS=['Corporativo general','Inmobiliario y hotelero','Auditoría legal','Comercio Exterior','Gobierno e infraestructura','Actividades filantrópicas'];
$id=(int)($_GET['id']??$_POST['id']??0); $c=$id?case_get($id):null;
$base='/portal/admin/asunto.php?id='.$id;
if ($c) matter_handle_post($id,$base); // sub-acciones del expediente (m_action)
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) post_redirect($base,'','Sesión expirada.');
  if (!$c) post_redirect('/portal/admin/asuntos.php','','Asunto no encontrado.');
  $a=$_POST['action']??'';
  if ($a==='update') {
    $t=trim($_POST['title']??''); $area=trim($_POST['area']??''); $st=$_POST['status']??'nuevo';
    $cid=($_POST['client_id']??'')!==''?(int)$_POST['client_id']:null; $lid=($_POST['lawyer_id']??'')!==''?(int)$_POST['lawyer_id']:null;
    if ($t==='') post_redirect($base,'','Título inválido.');
    if (!in_array($st,STATUSES,true)) $st='nuevo';
    case_update($id,$t,$area,$cid,$lid,$st);
    $mn=trim($_POST['matter_no']??''); $au=trim($_POST['authority']??'');
    $amt=($_POST['amount']??'')!==''?(float)$_POST['amount']:null; $cur=in_array($_POST['currency']??'MXN',['MXN','USD','EUR'],true)?$_POST['currency']:'MXN';
    $rk=in_array($_POST['risk']??'medio',RISKS,true)?$_POST['risk']:'medio';
    case_set_meta($id,$mn?:null,$au?:null,$amt,$cur,$rk);
    audit('case_update:'.$id); post_redirect($base,'Asunto actualizado.');
  }
  if ($a==='note') { $b=trim($_POST['body']??''); if($b!==''){ case_add_note($id,current_user()['id'],$b); audit('case_note:'.$id); } post_redirect($base,'Nota agregada.'); }
  if ($a==='delete') { case_delete($id); audit('case_delete:'.$id); post_redirect('/portal/admin/asuntos.php','Asunto eliminado.'); }
}
if (!$c) { shell_top('Asunto'); echo '<p class="flash err">Asunto no encontrado.</p>'; shell_bottom(); exit; }
$t=csrf_token(); $clientes=users_by_role('cliente'); $abogados=users_by_role('abogado'); $notes=case_notes($id); shell_top('Asunto'); ?>
<p><a href="/portal/admin/asuntos.php">← Asuntos</a></p>
<h1>Asunto #<?=$id?> <span class="badge b-<?=h($c['status'])?>"><?=h($c['status'])?></span> <span class="sev s-<?=h($c['risk']?:'medio')?>">riesgo <?=h($c['risk']?:'medio')?></span></h1>
<form method="post" class="stack">
  <input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?=$id?>">
  <label>Título<input name="title" value="<?=h($c['title'])?>"></label>
  <div class="grid2">
    <label>No. de expediente<input name="matter_no" value="<?=h($c['matter_no']??'')?>" placeholder="Interno / juzgado"></label>
    <label>Autoridad / foro<input name="authority" value="<?=h($c['authority']??'')?>" placeholder="Juzgado, dependencia…"></label>
  </div>
  <label>Área<select name="area"><option value="">—</option><?php foreach($AREAS as $a) echo '<option'.($c['area']===$a?' selected':'').'>'.h($a).'</option>'; ?></select></label>
  <div class="grid2">
    <label>Cliente<select name="client_id"><option value="">—</option><?php foreach($clientes as $x) echo '<option value="'.$x['id'].'"'.($c['client_id']==$x['id']?' selected':'').'>'.h($x['name']).'</option>'; ?></select></label>
    <label>Abogado<select name="lawyer_id"><option value="">—</option><?php foreach($abogados as $x) echo '<option value="'.$x['id'].'"'.($c['lawyer_id']==$x['id']?' selected':'').'>'.h($x['name']).'</option>'; ?></select></label>
  </div>
  <div class="grid2">
    <label>Cuantía<input name="amount" type="number" step="0.01" min="0" value="<?=h($c['amount']??'')?>"></label>
    <label>Moneda<?=sel(['MXN','USD','EUR'],$c['currency']?:'MXN','currency')?></label>
  </div>
  <div class="grid2">
    <label>Estado<select name="status"><?php foreach(STATUSES as $s) echo '<option'.($c['status']===$s?' selected':'').'>'.$s.'</option>'; ?></select></label>
    <label>Riesgo<?=sel(RISKS,$c['risk']?:'medio','risk')?></label>
  </div>
  <div><button>Guardar</button></div>
</form>
<form method="post" onsubmit="return confirm('¿Eliminar este asunto?')" class="mt"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$id?>"><button class="danger">Eliminar asunto</button></form>

<?php matter_sections($id,$base,$t,$abogados); ?>

<h2>Notas / seguimiento</h2>
<form method="post" class="stack"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="note"><input type="hidden" name="id" value="<?=$id?>">
  <textarea name="body" placeholder="Agregar nota…" required></textarea><div><button>Agregar nota</button></div></form>
<ul class="notes"><?php foreach($notes as $n): ?><li><b><?=h($n['author']?:'—')?></b> · <span class="dim"><?=h($n['created_at'])?></span><br><?=nl2br(h($n['body']))?></li><?php endforeach; if(!$notes) echo '<li class="soon">Sin notas.</li>'; ?></ul>
<?php matter_audit($id); shell_bottom();
