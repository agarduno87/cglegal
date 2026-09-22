<?php
// SOLO desarrollo (SQLite). Crea usuarios de prueba. No usar en producción.
require_once __DIR__.'/lib/db.php';
$f=__DIR__.'/lib/config.php'; $cfg=file_exists($f)?require $f:require __DIR__.'/lib/config.sample.php';
if (($cfg['driver']??'sqlite')!=='sqlite'){ exit("dev-seed solo corre en SQLite (dev).\n"); }
$users=[['Admin Prueba','admin@cglegal.com.mx','admin123','admin'],
        ['Abogado Prueba','abogado@cglegal.com.mx','abogado123','abogado'],
        ['Cliente Prueba','cliente@cglegal.com.mx','cliente123','cliente']];
foreach($users as [$n,$e,$p,$r]){
  $st=db()->prepare("SELECT id FROM users WHERE email=?"); $st->execute([$e]);
  if(!$st->fetch()){ db()->prepare("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)")
    ->execute([$n,$e,password_hash($p,PASSWORD_DEFAULT),$r]); echo "creado: $e ($r)\n"; }
  else echo "ya existe: $e\n";
}
