<?php require_once __DIR__.'/../lib/layout.php'; require_role('abogado');
$cases=cases_for_lawyer(current_user()['id']); shell_top('Mis asuntos'); ?>
<h1>Mis asuntos</h1>
<table class="tbl"><thead><tr><th>Título</th><th>Cliente</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach($cases as $c): ?>
<tr><td><?=h($c['title'])?></td><td><?=h($c['client_name']?:'—')?></td><td><span class="badge b-<?=h($c['status'])?>"><?=h($c['status'])?></span></td>
<td><a class="btnlink" href="/portal/abogado/asunto.php?id=<?=$c['id']?>">Abrir</a></td></tr>
<?php endforeach; if(!$cases) echo '<tr><td colspan="4" class="soon">No tienes asuntos asignados.</td></tr>'; ?>
</tbody></table>
<?php shell_bottom();
