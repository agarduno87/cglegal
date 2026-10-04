<?php require_once __DIR__.'/../lib/layout.php'; require_role('cliente');
$me=current_user()['id']; $cases=cases_for_client($me); $reqs=requests_for_client((int)$me);
shell_top('Mis asuntos'); ?>
<h1>Mis asuntos</h1>
<?php if($reqs): ?>
<section class="mcard"><h2>📥 Lo que necesitamos de ti</h2>
  <ul class="mlist"><?php foreach($reqs as $r): ?><li><span><?=h($r['item'])?> <span class="dim">· <?=h($r['ctitle'])?></span></span><span class="tag r-<?=h($r['status'])?>"><?=h($r['status'])?></span></li><?php endforeach; ?></ul>
  <p class="dim">Envía estos documentos o datos a tu abogado para avanzar.</p>
</section>
<?php endif; ?>
<?php if(!$cases) echo '<p class="soon">Aún no tienes asuntos registrados. Un abogado los dará de alta.</p>'; ?>
<?php foreach($cases as $c): $cid=(int)$c['id']; $notes=case_notes($cid); $dl=deadlines_for($cid); $docs=documents_for($cid); ?>
<section class="casecard">
  <div class="ch"><h2><?=h($c['title'])?></h2><span class="badge b-<?=h($c['status'])?>"><?=h($c['status'])?></span></div>
  <p class="dim">Área: <?=h($c['area']?:'—')?> · Abogado: <?=h($c['lawyer_name']?:'por asignar')?></p>
  <?php $pend=array_filter($dl,fn($d)=>!$d['done']); if($pend): ?>
  <h3>Próximos plazos</h3>
  <ul class="mlist"><?php foreach($pend as $d): ?><li><span><b><?=h($d['due_date'])?></b> · <?=h($d['title'])?></span></li><?php endforeach; ?></ul>
  <?php endif; ?>
  <?php if($docs): ?>
  <h3>Documentos</h3>
  <ul class="mlist"><?php foreach($docs as $d): ?><li>📄 <a href="/portal/download.php?doc=<?=$d['id']?>"><?=h($d['orig_name'])?></a> <span class="tag"><?=h($d['category'])?></span></li><?php endforeach; ?></ul>
  <?php endif; ?>
  <h3>Seguimiento</h3>
  <ul class="notes"><?php foreach($notes as $n): ?><li><b><?=h($n['author']?:'—')?></b> · <span class="dim"><?=h($n['created_at'])?></span><br><?=nl2br(h($n['body']))?></li><?php endforeach; if(!$notes) echo '<li class="soon">Sin actualizaciones por ahora.</li>'; ?></ul>
</section>
<?php endforeach; shell_bottom();
