<?php require_once __DIR__.'/../lib/layout.php'; require_role('abogado');
$me=current_user()['id']; $cases=cases_for_lawyer($me);
$cutoff=date('Y-m-d', strtotime('+14 days')); $soon=deadlines_upcoming($cutoff,(int)$me);
$tasks=tasks_open_for((int)$me); $today=date('Y-m-d');
shell_top('Mis asuntos'); ?>
<h1>Mi panel</h1>
<div class="cards">
  <div class="kpi"><b><?=count($cases)?></b><span>Mis asuntos</span></div>
  <div class="kpi"><b><?=count($soon)?></b><span>Plazos ≤14 días</span></div>
  <div class="kpi"><b><?=count($tasks)?></b><span>Tareas abiertas</span></div>
</div>
<div class="cols2">
  <section class="mcard"><h2>Mis próximos plazos</h2>
    <ul class="mlist"><?php foreach($soon as $d): $ov=$d['due_date']<$today; ?>
      <li class="<?=$ov?'over':''?>"><span><b><?=h($d['due_date'])?></b> · <?=h($d['ctitle'])?> — <?=h($d['title'])?></span></li>
    <?php endforeach; if(!$soon) echo '<li class="soon">Sin plazos próximos.</li>'; ?></ul>
  </section>
  <section class="mcard"><h2>Mis tareas</h2>
    <ul class="mlist"><?php foreach($tasks as $tk): $ov=$tk['due_date']&&$tk['due_date']<$today; ?>
      <li class="<?=$ov?'over':''?>"><span><?=h($tk['title'])?> · <span class="dim"><?=h($tk['ctitle'])?></span><?php if($tk['due_date'])echo ' · '.h($tk['due_date']);?></span></li>
    <?php endforeach; if(!$tasks) echo '<li class="soon">Sin tareas abiertas.</li>'; ?></ul>
  </section>
</div>
<h2>Mis asuntos</h2>
<table class="tbl"><thead><tr><th>Título</th><th>Cliente</th><th>Riesgo</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach($cases as $c): ?>
<tr><td><?=h($c['title'])?></td><td><?=h($c['client_name']?:'—')?></td><td><span class="sev s-<?=h($c['risk']?:'medio')?>"><?=h($c['risk']?:'medio')?></span></td><td><span class="badge b-<?=h($c['status'])?>"><?=h($c['status'])?></span></td>
<td><a class="btnlink" href="/portal/abogado/asunto.php?id=<?=$c['id']?>">Abrir</a></td></tr>
<?php endforeach; if(!$cases) echo '<tr><td colspan="5" class="soon">No tienes asuntos asignados.</td></tr>'; ?>
</tbody></table>
<?php shell_bottom();
