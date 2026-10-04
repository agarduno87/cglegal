<?php require_once __DIR__.'/../lib/layout.php'; require_role('admin');
$byRole=[]; foreach(db()->query("SELECT role,COUNT(*) c FROM users GROUP BY role") as $r) $byRole[$r['role']]=(int)$r['c'];
$byStatus=kpi_by('status'); $byArea=kpi_by('area');
$byLawyer=kpi_cases_by_lawyer(); $tf=kpi_time_firm();
$ev=(int)db()->query("SELECT COUNT(*) FROM audit_log")->fetchColumn();
$cutoff=date('Y-m-d', strtotime('+14 days')); $soon=deadlines_upcoming($cutoff);
$today=date('Y-m-d'); $overdue=array_filter($soon,fn($d)=>$d['due_date']<$today);
shell_top('Administración'); ?>
<h1>Panel de administración</h1>
<div class="cards">
  <div class="kpi"><b><?=array_sum($byStatus)?></b><span>Asuntos</span></div>
  <div class="kpi"><b><?=(int)($byStatus['en_proceso']??0)?></b><span>En proceso</span></div>
  <div class="kpi"><b><?=count($soon)?></b><span>Plazos ≤14 días</span></div>
  <div class="kpi alert"><b><?=count($overdue)?></b><span>Plazos vencidos</span></div>
  <div class="kpi"><b>$<?=number_format((float)$tf['billable'],0)?></b><span>Honorarios facturables</span></div>
  <div class="kpi"><b><?=array_sum($byRole)?></b><span>Usuarios</span></div>
  <div class="kpi"><b><?=leads_count()?></b><span>Leads (BD)</span></div>
</div>

<div class="cols2">
  <section class="mcard"><h2>Próximos vencimientos</h2>
    <ul class="mlist"><?php foreach(array_slice($soon,0,8) as $d): $ov=$d['due_date']<$today; ?>
      <li class="<?=$ov?'over':''?>"><span><b><?=h($d['due_date'])?></b> · <?=h($d['ctitle'])?> — <?=h($d['title'])?><?=$ov?' <span class="tag over">vencido</span>':''?></span></li>
    <?php endforeach; if(!$soon) echo '<li class="soon">Sin plazos próximos.</li>'; ?></ul>
  </section>
  <section class="mcard"><h2>Carga por abogado</h2>
    <ul class="mlist"><?php foreach($byLawyer as $r): ?><li><span><?=h($r['name'])?></span><span class="tag"><?=$r['c']?></span></li><?php endforeach; ?></ul>
    <h2 style="margin-top:16px">Asuntos por área</h2>
    <ul class="mlist"><?php foreach($byArea as $k=>$v): ?><li><span><?=h($k)?></span><span class="tag"><?=$v?></span></li><?php endforeach; if(!$byArea) echo '<li class="soon">—</li>'; ?></ul>
  </section>
</div>

<p class="mt"><a class="btnlink" href="/portal/admin/usuarios.php">Usuarios</a> <a class="btnlink" href="/portal/admin/asuntos.php">Asuntos</a> <a class="btnlink" href="/portal/admin/conflictos.php">Conflictos de interés</a></p>
<p class="soon">Horas totales registradas: <?=sprintf('%d:%02d h', intdiv((int)$tf['min'],60), (int)$tf['min']%60)?> · importe $<?=number_format((float)$tf['amt'],2)?>. Los leads del formulario llegan por correo a contacto@cglegal.com.mx.</p>
<?php shell_bottom();
