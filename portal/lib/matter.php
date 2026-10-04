<?php
/* Expediente de trabajo compartido (admin + abogado).
   El llamador YA validó acceso al caso ($cid). Las sub-acciones usan el campo
   'm_action' para no chocar con el 'action' propio de cada página.
   Seguridad: CSRF, prepared statements (models), subida de documentos endurecida. */
require_once __DIR__.'/layout.php';

const DOC_ALLOWED = [
  'pdf'=>'application/pdf','doc'=>'application/msword',
  'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'xls'=>'application/vnd.ms-excel',
  'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','txt'=>'text/plain',
];
const DOC_MAX = 20971520; // 20 MB

function matter_handle_post(int $cid, string $base): void {
  $a = $_POST['m_action'] ?? ''; if ($a==='') return;
  if (!csrf_check($_POST['csrf']??'')) post_redirect($base,'','Sesión expirada.');
  $uid = current_user()['id'] ?? null;
  $gi = fn($k)=> (($_POST[$k]??'')!=='') ? (int)$_POST[$k] : null;
  $gs = fn($k)=> trim($_POST[$k]??'');
  switch ($a) {
    case 'deadline_add':
      if($gs('title')!=='' && $gs('due_date')!==''){ $k=$_POST['kind']??'otro'; if(!in_array($k,DEADLINE_KINDS,true))$k='otro';
        deadline_add($cid,$gs('title'),$gs('due_date'),$k,$uid); audit("deadline_add:$cid"); } post_redirect($base,'Plazo agregado.');
    case 'deadline_toggle': deadline_toggle((int)$_POST['did'],$cid); post_redirect($base,'Plazo actualizado.');
    case 'deadline_del': deadline_del((int)$_POST['did'],$cid); audit("deadline_del:$cid"); post_redirect($base,'Plazo eliminado.');
    case 'task_add':
      if($gs('title')!==''){ task_add($cid,$gs('title'),$gi('assignee_id'),$gs('due_date')?:null,$uid); audit("task_add:$cid"); } post_redirect($base,'Tarea agregada.');
    case 'task_status': $st=$_POST['status']??''; if(in_array($st,TASK_STATUSES,true)) task_set_status((int)$_POST['tid'],$cid,$st); post_redirect($base,'Tarea actualizada.');
    case 'task_del': task_del((int)$_POST['tid'],$cid); post_redirect($base,'Tarea eliminada.');
    case 'party_add':
      if($gs('name')!==''){ $r=$_POST['role']??'tercero'; if(!in_array($r,PARTY_ROLES,true))$r='tercero';
        party_add($cid,$gs('name'),$r,$gs('org')?:null,$gs('email')?:null,$gs('phone')?:null,$gs('notes')?:null); audit("party_add:$cid"); } post_redirect($base,'Parte agregada.');
    case 'party_del': party_del((int)$_POST['pid'],$cid); post_redirect($base,'Parte eliminada.');
    case 'doc_upload': matter_upload($cid,$base,$uid);
    case 'doc_del':
      $d=document_get((int)$_POST['docid']); if($d && (int)$d['case_id']===$cid){ @unlink(uploads_dir().'/'.$d['stored_name']); document_del((int)$d['id'],$cid); audit("doc_del:$cid"); } post_redirect($base,'Documento eliminado.');
    case 'finding_add':
      if($gs('title')!==''){ $sev=$_POST['severity']??'medio'; if(!in_array($sev,SEVERITIES,true))$sev='medio';
        finding_add($cid,$gs('title'),$sev,$gs('recommendation')?:null,$gs('body')?:null,$uid); audit("finding_add:$cid"); } post_redirect($base,'Hallazgo agregado.');
    case 'finding_del': finding_del((int)$_POST['fid'],$cid); post_redirect($base,'Hallazgo eliminado.');
    case 'check_add': if($gs('label')!=='') check_add($cid,$gs('label'),$gs('category')?:null); post_redirect($base,'Ítem agregado.');
    case 'check_toggle': check_toggle((int)$_POST['kid'],$cid); post_redirect($base,'Checklist actualizada.');
    case 'check_del': check_del((int)$_POST['kid'],$cid); post_redirect($base,'Ítem eliminado.');
    case 'check_seed':
      $tpl=$_POST['template']??''; $T=checklist_templates(); if(isset($T[$tpl])){ foreach($T[$tpl] as $lbl) check_add($cid,$lbl,$tpl); audit("check_seed:$cid"); } post_redirect($base,'Checklist cargada.');
    case 'req_add': if($gs('item')!=='') req_add($cid,$gs('item')); post_redirect($base,'Requerimiento agregado.');
    case 'req_status': $st=$_POST['status']??''; if(in_array($st,REQ_STATUSES,true)) req_set_status((int)$_POST['rid'],$cid,$st); post_redirect($base,'Requerimiento actualizado.');
    case 'req_del': req_del((int)$_POST['rid'],$cid); post_redirect($base,'Requerimiento eliminado.');
    case 'time_add':
      $min=(int)round(((float)($_POST['hours']??0))*60); $rate=(float)($_POST['rate']??0); $bill=isset($_POST['billable'])?1:0;
      if($min>0){ time_add($cid,$uid,$gs('work_date')?:date('Y-m-d'),$min,$rate,$bill,$gs('note')?:null); audit("time_add:$cid"); } post_redirect($base,'Tiempo registrado.');
    case 'time_del': time_del((int)$_POST['teid'],$cid); post_redirect($base,'Registro eliminado.');
  }
}

function matter_upload(int $cid, string $base, ?int $uid): void {
  if (!isset($_FILES['file']) || ($_FILES['file']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)
    post_redirect($base,'','No se recibió el archivo.');
  $f=$_FILES['file'];
  if ($f['size']>DOC_MAX) post_redirect($base,'','El archivo supera 20 MB.');
  $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
  if (!isset(DOC_ALLOWED[$ext])) post_redirect($base,'','Tipo de archivo no permitido.');
  $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) ?: '';
  if ($mime!==DOC_ALLOWED[$ext]) post_redirect($base,'','El contenido no coincide con la extensión.');
  $stored=bin2hex(random_bytes(16)).'.'.$ext;
  if (!move_uploaded_file($f['tmp_name'], uploads_dir().'/'.$stored)) post_redirect($base,'','No se pudo guardar.');
  $cat=$_POST['category']??'otro'; if(!in_array($cat,DOC_CATEGORIES,true))$cat='otro';
  $conf=isset($_POST['confidential'])?1:0;
  document_add($cid, substr($f['name'],0,200), $stored, $cat, $mime, (int)$f['size'], $conf, $uid);
  audit("doc_upload:$cid"); post_redirect($base,'Documento subido.');
}

/* ---------- utilidades de render ---------- */
function _overdue(string $d): bool { return $d!=='' && $d < date('Y-m-d'); }
function _fmtmin(int $m): string { return sprintf('%d:%02d h', intdiv($m,60), $m%60); }
function sel(array $opts,string $cur,string $name): string { $o=''; foreach($opts as $v) $o.='<option'.($v===$cur?' selected':'').'>'.h($v).'</option>'; return '<select name="'.h($name).'">'.$o.'</select>'; }

/* Renderiza TODAS las secciones del expediente. $abogados para asignar tareas. */
function matter_sections(int $cid, string $base, string $csrf, array $abogados): void {
  $hid='<input type="hidden" name="csrf" value="'.h($csrf).'"><input type="hidden" name="id" value="'.$cid.'">';
  $deadlines=deadlines_for($cid); $tasks=tasks_for($cid); $parties=parties_for($cid);
  $docs=documents_for($cid); $finds=findings_for($cid); $checks=checklist_for($cid);
  $reqs=requests_for($cid); $times=time_for($cid); $tot=time_totals($cid);
  ?>
  <div class="mtabs">

  <section class="mcard"><h2>⏱ Plazos y vencimientos</h2>
    <form method="post" class="row-form"><?=$hid?><input type="hidden" name="m_action" value="deadline_add">
      <input name="title" placeholder="Vencimiento (p. ej. contestar demanda)" required>
      <input type="date" name="due_date" required><?=sel(DEADLINE_KINDS,'otro','kind')?><button>Agregar</button></form>
    <ul class="mlist"><?php foreach($deadlines as $d): $ov=!$d['done']&&_overdue($d['due_date']); ?>
      <li class="<?=$d['done']?'done':($ov?'over':'')?>">
        <span><b><?=h($d['due_date'])?></b> · <?=h($d['title'])?> <span class="tag"><?=h($d['kind'])?></span><?=$ov?' <span class="tag over">vencido</span>':''?></span>
        <span class="acts">
          <form method="post"><?=$hid?><input type="hidden" name="m_action" value="deadline_toggle"><input type="hidden" name="did" value="<?=$d['id']?>"><button class="mini"><?=$d['done']?'Reabrir':'Cumplido'?></button></form>
          <form method="post" onsubmit="return confirm('¿Eliminar plazo?')"><?=$hid?><input type="hidden" name="m_action" value="deadline_del"><input type="hidden" name="did" value="<?=$d['id']?>"><button class="mini danger">×</button></form>
        </span></li>
    <?php endforeach; if(!$deadlines) echo '<li class="soon">Sin plazos registrados.</li>'; ?></ul>
  </section>

  <section class="mcard"><h2>✓ Tareas</h2>
    <form method="post" class="row-form"><?=$hid?><input type="hidden" name="m_action" value="task_add">
      <input name="title" placeholder="Tarea / pendiente" required>
      <select name="assignee_id"><option value="">— Responsable —</option><?php foreach($abogados as $x) echo '<option value="'.$x['id'].'">'.h($x['name']).'</option>'; ?></select>
      <input type="date" name="due_date"><button>Agregar</button></form>
    <ul class="mlist"><?php foreach($tasks as $tk): $ov=$tk['status']!=='hecha'&&$tk['due_date']&&_overdue($tk['due_date']); ?>
      <li class="<?=$tk['status']==='hecha'?'done':($ov?'over':'')?>">
        <span><?=h($tk['title'])?> <?php if($tk['assignee'])echo '<span class="tag">'.h($tk['assignee']).'</span>'; if($tk['due_date'])echo ' · '.h($tk['due_date']);?></span>
        <span class="acts">
          <form method="post"><?=$hid?><input type="hidden" name="m_action" value="task_status"><input type="hidden" name="tid" value="<?=$tk['id']?>"><?=sel(TASK_STATUSES,$tk['status'],'status')?><button class="mini">OK</button></form>
          <form method="post" onsubmit="return confirm('¿Eliminar tarea?')"><?=$hid?><input type="hidden" name="m_action" value="task_del"><input type="hidden" name="tid" value="<?=$tk['id']?>"><button class="mini danger">×</button></form>
        </span></li>
    <?php endforeach; if(!$tasks) echo '<li class="soon">Sin tareas.</li>'; ?></ul>
  </section>

  <section class="mcard"><h2>👥 Partes y contactos</h2>
    <form method="post" class="row-form"><?=$hid?><input type="hidden" name="m_action" value="party_add">
      <input name="name" placeholder="Nombre" required><?=sel(PARTY_ROLES,'tercero','role')?>
      <input name="org" placeholder="Organización"><input name="email" placeholder="Correo"><input name="phone" placeholder="Teléfono"><button>Agregar</button></form>
    <ul class="mlist"><?php foreach($parties as $p): ?>
      <li><span><b><?=h($p['name'])?></b> <span class="tag"><?=h($p['role'])?></span><?php if($p['org'])echo ' · '.h($p['org']); if($p['email'])echo ' · '.h($p['email']); if($p['phone'])echo ' · '.h($p['phone']);?></span>
        <span class="acts"><form method="post" onsubmit="return confirm('¿Eliminar parte?')"><?=$hid?><input type="hidden" name="m_action" value="party_del"><input type="hidden" name="pid" value="<?=$p['id']?>"><button class="mini danger">×</button></form></span></li>
    <?php endforeach; if(!$parties) echo '<li class="soon">Sin partes registradas.</li>'; ?></ul>
  </section>

  <section class="mcard"><h2>📎 Documentos del expediente</h2>
    <p class="dim">Confidencial por secreto profesional (LFPDPPP). Almacenamiento fuera de la raíz pública; descarga con control de acceso.</p>
    <form method="post" enctype="multipart/form-data" class="row-form"><?=$hid?><input type="hidden" name="m_action" value="doc_upload">
      <input type="file" name="file" required><?=sel(DOC_CATEGORIES,'otro','category')?>
      <label class="chk"><input type="checkbox" name="confidential" checked> Confidencial</label><button>Subir</button></form>
    <ul class="mlist"><?php foreach($docs as $d): ?>
      <li><span>📄 <a href="/portal/download.php?doc=<?=$d['id']?>"><?=h($d['orig_name'])?></a> <span class="tag"><?=h($d['category'])?></span><?=$d['confidential']?' <span class="tag conf">confidencial</span>':''?> · <span class="dim"><?=round($d['size']/1024)?> KB · <?=h($d['uploader']?:'—')?></span></span>
        <span class="acts"><form method="post" onsubmit="return confirm('¿Eliminar documento?')"><?=$hid?><input type="hidden" name="m_action" value="doc_del"><input type="hidden" name="docid" value="<?=$d['id']?>"><button class="mini danger">×</button></form></span></li>
    <?php endforeach; if(!$docs) echo '<li class="soon">Sin documentos.</li>'; ?></ul>
  </section>

  <section class="mcard"><h2>🔎 Investigación — Checklist</h2>
    <form method="post" class="row-form"><?=$hid?><input type="hidden" name="m_action" value="check_seed">
      <select name="template"><?php foreach(array_keys(checklist_templates()) as $t) echo '<option>'.h($t).'</option>'; ?></select><button>Cargar plantilla</button></form>
    <form method="post" class="row-form"><?=$hid?><input type="hidden" name="m_action" value="check_add"><input name="label" placeholder="Ítem a revisar" required><button>Agregar</button></form>
    <?php $done=count(array_filter($checks,fn($c)=>$c['done'])); $tt=count($checks); ?>
    <?php if($tt): ?><div class="prog"><div style="width:<?=round($done*100/$tt)?>%"></div></div><p class="dim"><?=$done?>/<?=$tt?> completado</p><?php endif; ?>
    <ul class="mlist"><?php foreach($checks as $c): ?>
      <li class="<?=$c['done']?'done':''?>">
        <span><form method="post" style="display:inline"><?=$hid?><input type="hidden" name="m_action" value="check_toggle"><input type="hidden" name="kid" value="<?=$c['id']?>"><button class="mini"><?=$c['done']?'☑':'☐'?></button></form> <?=h($c['label'])?><?php if($c['category'])echo ' <span class="tag">'.h($c['category']).'</span>';?></span>
        <span class="acts"><form method="post"><?=$hid?><input type="hidden" name="m_action" value="check_del"><input type="hidden" name="kid" value="<?=$c['id']?>"><button class="mini danger">×</button></form></span></li>
    <?php endforeach; if(!$checks) echo '<li class="soon">Sin ítems. Carga una plantilla por tipo de asunto.</li>'; ?></ul>
  </section>

  <section class="mcard"><h2>📥 Requerimientos al cliente</h2>
    <form method="post" class="row-form"><?=$hid?><input type="hidden" name="m_action" value="req_add"><input name="item" placeholder="Documento/dato que debe entregar el cliente" required><button>Agregar</button></form>
    <ul class="mlist"><?php foreach($reqs as $r): ?>
      <li class="<?=$r['status']==='recibido'?'done':''?>"><span><?=h($r['item'])?> <span class="tag r-<?=h($r['status'])?>"><?=h($r['status'])?></span></span>
        <span class="acts"><form method="post"><?=$hid?><input type="hidden" name="m_action" value="req_status"><input type="hidden" name="rid" value="<?=$r['id']?>"><?=sel(REQ_STATUSES,$r['status'],'status')?><button class="mini">OK</button></form>
        <form method="post"><?=$hid?><input type="hidden" name="m_action" value="req_del"><input type="hidden" name="rid" value="<?=$r['id']?>"><button class="mini danger">×</button></form></span></li>
    <?php endforeach; if(!$reqs) echo '<li class="soon">Sin requerimientos.</li>'; ?></ul>
  </section>

  <section class="mcard"><h2>⚠ Hallazgos (due diligence)</h2>
    <form method="post" class="stack"><?=$hid?><input type="hidden" name="m_action" value="finding_add">
      <div class="row-form"><input name="title" placeholder="Hallazgo" required style="flex:1"><?=sel(SEVERITIES,'medio','severity')?></div>
      <input name="recommendation" placeholder="Recomendación"><textarea name="body" placeholder="Detalle / sustento"></textarea><div><button>Agregar hallazgo</button></div></form>
    <ul class="mlist"><?php foreach($finds as $f): ?>
      <li><span><span class="sev s-<?=h($f['severity'])?>"><?=h($f['severity'])?></span> <b><?=h($f['title'])?></b><?php if($f['recommendation'])echo '<br><span class="dim">Rec.: '.h($f['recommendation']).'</span>'; if($f['body'])echo '<br>'.nl2br(h($f['body']));?></span>
        <span class="acts"><form method="post" onsubmit="return confirm('¿Eliminar hallazgo?')"><?=$hid?><input type="hidden" name="m_action" value="finding_del"><input type="hidden" name="fid" value="<?=$f['id']?>"><button class="mini danger">×</button></form></span></li>
    <?php endforeach; if(!$finds) echo '<li class="soon">Sin hallazgos.</li>'; ?></ul>
  </section>

  <section class="mcard"><h2>💵 Tiempo y honorarios</h2>
    <form method="post" class="row-form"><?=$hid?><input type="hidden" name="m_action" value="time_add">
      <input type="date" name="work_date" value="<?=date('Y-m-d')?>">
      <input name="hours" type="number" step="0.25" min="0" placeholder="Horas" required style="width:90px">
      <input name="rate" type="number" step="1" min="0" placeholder="Tarifa/h" style="width:100px">
      <label class="chk"><input type="checkbox" name="billable" checked> Facturable</label>
      <input name="note" placeholder="Concepto" style="flex:1"><button>Registrar</button></form>
    <p class="dim">Total: <b><?=_fmtmin((int)$tot['min'])?></b> · Importe: <b>$<?=number_format((float)$tot['amt'],2)?></b> · Facturable: $<?=number_format((float)$tot['billable'],2)?></p>
    <ul class="mlist"><?php foreach($times as $e): ?>
      <li><span><?=h($e['work_date'])?> · <?=_fmtmin((int)$e['minutes'])?> · $<?=number_format($e['minutes']*$e['rate']/60,2)?> <?=$e['billable']?'':'<span class="tag">no fact.</span>'?><?php if($e['note'])echo ' · '.h($e['note']); ?> <span class="dim"><?=h($e['who']?:'—')?></span></span>
        <span class="acts"><form method="post"><?=$hid?><input type="hidden" name="m_action" value="time_del"><input type="hidden" name="teid" value="<?=$e['id']?>"><button class="mini danger">×</button></form></span></li>
    <?php endforeach; if(!$times) echo '<li class="soon">Sin registros de tiempo.</li>'; ?></ul>
  </section>

  </div>
<?php }

/* Bitácora de auditoría filtrada a este asunto (D16). */
function matter_audit(int $cid): void {
  $s=db()->prepare("SELECT a.*, u.name AS who FROM audit_log a LEFT JOIN users u ON u.id=a.user_id WHERE a.action LIKE ? OR a.action LIKE ? ORDER BY a.at DESC LIMIT 40");
  $s->execute(["%:$cid", "%:$cid:%"]); $rows=$s->fetchAll(PDO::FETCH_ASSOC);
  echo '<section class="mcard"><h2>🧾 Bitácora del asunto</h2><ul class="mlist">';
  foreach($rows as $r) echo '<li><span class="dim">'.h($r['at']).' · '.h($r['who']?:'—').'</span> — '.h($r['action']).'</li>';
  if(!$rows) echo '<li class="soon">Sin eventos.</li>';
  echo '</ul></section>';
}
