<?php require_once __DIR__.'/../lib/layout.php'; require_once __DIR__.'/../lib/matter.php'; require_role('cliente');
$me=(int)current_user()['id']; $base='/portal/cliente/';
// El cliente sube documentos SOLO a sus propios asuntos.
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) post_redirect($base,'','Sesión expirada.');
  $ma=$_POST['m_action']??'';
  if ($ma==='client_upload') {
    $cid=(int)($_POST['case_id']??0); $c=$cid?case_get($cid):null;
    if (!$c || (int)$c['client_id']!==$me) post_redirect($base,'','Asunto no válido.');
    [$ok,$msg,$docid]=doc_store($cid, $_FILES['file']??[], 'otro', 1, 1, $me); // visible al cliente y al abogado
    if (!$ok) post_redirect($base,'',$msg);
    $nm=substr($_FILES['file']['name']??'documento',0,200);
    case_add_note($cid,$me,'📎 Documento enviado por el cliente: '.$nm); // aviso al abogado (seguimiento)
    $rid=(int)($_POST['req_id']??0);
    if ($rid) { // si responde a un requerimiento, márcalo recibido
      foreach(requests_for($cid) as $r) if((int)$r['id']===$rid){ req_set_status($rid,$cid,'recibido'); break; }
    }
    audit("client_upload:$cid:$docid"); post_redirect($base,'Documento enviado a tu abogado.');
  }
  if ($ma==='client_doc_del') {
    $d=document_get((int)($_POST['docid']??0));
    if ($d) { $c=case_get((int)$d['case_id']);
      if ($c && (int)$c['client_id']===$me && (int)$d['uploaded_by']===$me) { // solo lo que TÚ subiste
        @unlink(uploads_dir().'/'.$d['stored_name']); document_del((int)$d['id'],(int)$d['case_id']);
        audit('client_doc_del:'.$d['case_id']); post_redirect($base,'Documento eliminado.');
      } }
    post_redirect($base,'','No se pudo eliminar ese documento.');
  }
  post_redirect($base);
}
$t=csrf_token(); $cases=cases_for_client($me); $reqs=requests_for_client($me);
shell_top('Mis asuntos'); ?>
<h1>Mis asuntos</h1>
<?php if($reqs): ?>
<section class="mcard"><h2>📥 Lo que necesitamos de ti</h2>
  <ul class="mlist"><?php foreach($reqs as $r): ?>
    <li><span><?=h($r['item'])?> <span class="dim">· <?=h($r['ctitle'])?></span> <span class="tag r-<?=h($r['status'])?>"><?=h($r['status'])?></span></span>
      <span class="acts"><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="m_action" value="client_upload"><input type="hidden" name="case_id" value="<?=$r['case_id']?>"><input type="hidden" name="req_id" value="<?=$r['id']?>"><input type="file" name="file" required><button class="mini">Subir</button></form></span></li>
  <?php endforeach; ?></ul>
  <p class="dim">Sube aquí el documento solicitado; tu abogado lo recibe al instante.</p>
</section>
<?php endif; ?>
<?php if(!$cases) echo '<p class="soon">Aún no tienes asuntos registrados. Un abogado los dará de alta.</p>'; ?>
<?php foreach($cases as $c): $cid=(int)$c['id']; $notes=case_notes($cid); $dl=deadlines_for($cid); $docs=documents_for_client($cid,$me); ?>
<section class="casecard">
  <div class="ch"><h2><?=h($c['title'])?></h2><span class="badge b-<?=h($c['status'])?>"><?=h($c['status'])?></span></div>
  <p class="dim">Área: <?=h($c['area']?:'—')?> · Abogado: <?=h($c['lawyer_name']?:'por asignar')?></p>
  <?php $pend=array_filter($dl,fn($d)=>!$d['done']); if($pend): ?>
  <h3>Próximos plazos</h3>
  <ul class="mlist"><?php foreach($pend as $d): ?><li><span><b><?=h($d['due_date'])?></b> · <?=h($d['title'])?></span></li><?php endforeach; ?></ul>
  <?php endif; ?>
  <h3>Documentos</h3>
  <ul class="mlist"><?php foreach($docs as $d): $mine=((int)$d['uploaded_by']===$me); ?>
    <li><span>📄 <a href="/portal/download.php?doc=<?=$d['id']?>"><?=h($d['orig_name'])?></a> <span class="tag"><?=h($d['category'])?></span><?=$mine?' <span class="tag conf">enviado por ti</span>':''?></span>
      <?php if($mine): ?><span class="acts"><form method="post" onsubmit="return confirm('¿Eliminar este documento?')"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="m_action" value="client_doc_del"><input type="hidden" name="docid" value="<?=$d['id']?>"><button class="mini danger">Eliminar</button></form></span><?php endif; ?></li>
  <?php endforeach; if(!$docs) echo '<li class="soon">Sin documentos por ahora.</li>'; ?></ul>
  <form method="post" enctype="multipart/form-data" class="row-form"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="m_action" value="client_upload"><input type="hidden" name="case_id" value="<?=$cid?>">
    <input type="file" name="file" required><button>Enviar documento a mi abogado</button></form>
  <h3>Seguimiento</h3>
  <ul class="notes"><?php foreach($notes as $n): ?><li><b><?=h($n['author']?:'—')?></b> · <span class="dim"><?=h($n['created_at'])?></span><br><?=nl2br(h($n['body']))?></li><?php endforeach; if(!$notes) echo '<li class="soon">Sin actualizaciones por ahora.</li>'; ?></ul>
</section>
<?php endforeach; shell_bottom();
