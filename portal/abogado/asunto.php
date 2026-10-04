<?php require_once __DIR__.'/../lib/layout.php'; require_once __DIR__.'/../lib/matter.php'; require_role('abogado');
$id=(int)($_GET['id']??$_POST['id']??0); $c=$id?case_get($id):null;
if (!$c || (int)$c['lawyer_id']!==(int)current_user()['id']) { http_response_code(403); echo 'Acceso denegado a este asunto.'; exit; }
$base='/portal/abogado/asunto.php?id='.$id;
matter_handle_post($id,$base); // sub-acciones del expediente (m_action)
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) post_redirect($base,'','Sesión expirada.');
  $a=$_POST['action']??'';
  if ($a==='status') { $st=$_POST['status']??''; if(in_array($st,STATUSES,true)){ case_set_status($id,$st); audit('case_status:'.$id.':'.$st); } post_redirect($base,'Estado actualizado.'); }
  if ($a==='note') { $b=trim($_POST['body']??''); if($b!==''){ case_add_note($id,current_user()['id'],$b); audit('case_note:'.$id); } post_redirect($base,'Nota agregada.'); }
}
$t=csrf_token(); $notes=case_notes($id); $abogados=users_by_role('abogado'); shell_top('Asunto'); ?>
<p><a href="/portal/abogado/">← Mis asuntos</a></p>
<h1><?=h($c['title'])?> <span class="sev s-<?=h($c['risk']?:'medio')?>">riesgo <?=h($c['risk']?:'medio')?></span></h1>
<p class="dim">Área: <?=h($c['area']?:'—')?> · Cliente: <?=h($c['client_name']?:'—')?><?php if($c['matter_no'])echo ' · Exp. '.h($c['matter_no']); if($c['authority'])echo ' · '.h($c['authority']); if($c['amount']!==null&&$c['amount']!=='')echo ' · Cuantía $'.number_format((float)$c['amount'],2).' '.h($c['currency']?:'MXN');?></p>
<form method="post" class="row-form"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=$id?>">
  <label>Estado <select name="status"><?php foreach(STATUSES as $s) echo '<option'.($c['status']===$s?' selected':'').'>'.$s.'</option>'; ?></select></label><button>Actualizar</button></form>

<?php matter_sections($id,$base,$t,$abogados); ?>

<h2>Notas / seguimiento</h2>
<form method="post" class="stack"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="note"><input type="hidden" name="id" value="<?=$id?>">
  <textarea name="body" placeholder="Agregar nota para el expediente…" required></textarea><div><button>Agregar nota</button></div></form>
<ul class="notes"><?php foreach($notes as $n): ?><li><b><?=h($n['author']?:'—')?></b> · <span class="dim"><?=h($n['created_at'])?></span><br><?=nl2br(h($n['body']))?></li><?php endforeach; if(!$notes) echo '<li class="soon">Sin notas.</li>'; ?></ul>
<?php matter_audit($id); shell_bottom();
