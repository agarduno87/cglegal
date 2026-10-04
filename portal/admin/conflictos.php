<?php require_once __DIR__.'/../lib/layout.php'; require_role('admin');
$base='/portal/admin/conflictos.php';
if (($_SERVER['REQUEST_METHOD']??'')==='POST') {
  if (!csrf_check($_POST['csrf']??'')) post_redirect($base,'','Sesión expirada.');
  $a=$_POST['action']??'';
  if ($a==='add') { $n=trim($_POST['name']??''); if($n!==''){ conflict_add($n,trim($_POST['note']??'')?:null,current_user()['id']); audit('conflict_add'); } post_redirect($base,'Registro agregado.'); }
  if ($a==='del') { conflict_del((int)$_POST['cid']); audit('conflict_del'); post_redirect($base,'Registro eliminado.'); }
}
$t=csrf_token(); $all=conflicts_all();
$q=trim($_GET['q']??''); $hits=$q!==''?conflict_check($q):null;
shell_top('Conflictos de interés'); ?>
<h1>Conflicto de interés</h1>
<section class="mcard"><h2>Verificar antes de aceptar un asunto</h2>
  <form method="get" class="row-form"><input name="q" value="<?=h($q)?>" placeholder="Nombre de cliente o contraparte" required><button>Verificar</button></form>
  <?php if($hits!==null): ?>
    <?php if($hits): ?><ul class="mlist"><?php foreach($hits as $x) echo '<li class="over"><span>⚠ '.h($x).'</span></li>'; ?></ul>
    <?php else: ?><p class="flash ok">Sin coincidencias para «<?=h($q)?>».</p><?php endif; ?>
  <?php endif; ?>
</section>
<section class="mcard"><h2>Lista de conflictos / partes vetadas</h2>
  <form method="post" class="row-form"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="add">
    <input name="name" placeholder="Nombre" required><input name="note" placeholder="Motivo / nota" style="flex:1"><button>Agregar</button></form>
  <ul class="mlist"><?php foreach($all as $c): ?>
    <li><span><b><?=h($c['name'])?></b><?php if($c['note'])echo ' · '.h($c['note']);?> <span class="dim"><?=h($c['created_at'])?></span></span>
      <span class="acts"><form method="post" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="csrf" value="<?=h($t)?>"><input type="hidden" name="action" value="del"><input type="hidden" name="cid" value="<?=$c['id']?>"><button class="mini danger">×</button></form></span></li>
  <?php endforeach; if(!$all) echo '<li class="soon">Sin registros.</li>'; ?></ul>
</section>
<?php shell_bottom();
