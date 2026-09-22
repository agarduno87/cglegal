<?php require_once __DIR__.'/../lib/layout.php'; require_role('cliente');
$cases=cases_for_client(current_user()['id']); shell_top('Mis asuntos'); ?>
<h1>Mis asuntos</h1>
<?php if(!$cases) echo '<p class="soon">Aún no tienes asuntos registrados. Un abogado los dará de alta.</p>'; ?>
<?php foreach($cases as $c): $notes=case_notes((int)$c['id']); ?>
<section class="casecard">
  <div class="ch"><h2><?=h($c['title'])?></h2><span class="badge b-<?=h($c['status'])?>"><?=h($c['status'])?></span></div>
  <p class="dim">Área: <?=h($c['area']?:'—')?> · Abogado: <?=h($c['lawyer_name']?:'por asignar')?></p>
  <h3>Seguimiento</h3>
  <ul class="notes"><?php foreach($notes as $n): ?><li><b><?=h($n['author']?:'—')?></b> · <span class="dim"><?=h($n['created_at'])?></span><br><?=nl2br(h($n['body']))?></li><?php endforeach; if(!$notes) echo '<li class="soon">Sin actualizaciones por ahora.</li>'; ?></ul>
</section>
<?php endforeach; shell_bottom();
