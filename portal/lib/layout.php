<?php require_once __DIR__.'/auth.php';
function shell_top(string $title){ $u=current_user(); ?>
<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> — Culebro</title>
<link rel="icon" type="image/png" href="/assets/icono-color.png"><link rel="stylesheet" href="/portal/assets/portal.css"></head>
<body><header class="top"><a class="brand" href="/portal/"><img src="/assets/icono-color.png" alt=""><b>Portal Culebro</b></a>
<span class="who"><?=h($u['name'])?> · <span class="role"><?=h($u['role'])?></span> · <a href="/portal/logout.php">Salir</a></span></header>
<main class="wrap"><?php }
function shell_bottom(){ ?></main></body></html><?php }
