<?php require_once __DIR__.'/auth.php'; require_once __DIR__.'/models.php';
function nav_links(string $role): array {
  if ($role==='admin') return [['/portal/admin/','Inicio'],['/portal/admin/usuarios.php','Usuarios'],['/portal/admin/asuntos.php','Asuntos']];
  if ($role==='abogado') return [['/portal/abogado/','Mis asuntos']];
  return [['/portal/cliente/','Mis asuntos']];
}
function flash(): string {
  $o=$_GET['ok']??''; $e=$_GET['err']??''; if(!$o&&!$e) return '';
  $cls=$e?'flash err':'flash ok'; return '<p class="'.$cls.'">'.h($e?:$o).'</p>';
}
function shell_top(string $title){ $u=current_user(); ?>
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> — Culebro</title>
<link rel="icon" type="image/png" href="/assets/icono-color.png"><link rel="stylesheet" href="/portal/assets/portal.css"></head>
<body><header class="top"><a class="brand" href="/portal/"><img src="/assets/icono-color.png" alt=""><b>Portal Culebro</b></a>
<nav class="mainnav"><?php foreach(nav_links($u['role']) as [$href,$t]) echo '<a href="'.h($href).'">'.h($t).'</a>'; ?></nav>
<span class="who"><?=h($u['name'])?> · <span class="role"><?=h($u['role'])?></span> · <a href="/portal/logout.php">Salir</a></span></header>
<main class="wrap"><?php echo flash(); }
function shell_bottom(){ ?></main></body></html><?php }
function post_redirect(string $url,string $ok='',string $err=''){ $sep=(strpos($url,'?')!==false)?'&':'?'; $q=$ok?($sep.'ok='.urlencode($ok)):($err?($sep.'err='.urlencode($err)):''); header('Location: '.$url.$q); exit; }
