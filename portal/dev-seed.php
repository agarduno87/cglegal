<?php
// SOLO desarrollo (SQLite). No usar en producción.
require_once __DIR__.'/lib/models.php';
$f=__DIR__.'/lib/config.php'; $cfg=file_exists($f)?require $f:require __DIR__.'/lib/config.sample.php';
if (($cfg['driver']??'sqlite')!=='sqlite'){ exit("dev-seed solo corre en SQLite (dev).\n"); }
$users=[['Admin Prueba','admin@cglegal.com.mx','admin123','admin'],
        ['Abogado Prueba','abogado@cglegal.com.mx','abogado123','abogado'],
        ['Cliente Prueba','cliente@cglegal.com.mx','cliente123','cliente']];
foreach($users as [$n,$e,$p,$r]){ if(!user_email_exists($e)){ user_create($n,$e,$p,$r); echo "user: $e ($r)\n"; } }
$ab=db()->query("SELECT id FROM users WHERE role='abogado' LIMIT 1")->fetchColumn();
$cl=db()->query("SELECT id FROM users WHERE role='cliente' LIMIT 1")->fetchColumn();
if(db()->query("SELECT COUNT(*) FROM cases")->fetchColumn()==0){
  $c1=case_create('Compra de terreno en Palmilla','Inmobiliario y hotelero',(int)$cl,(int)$ab,'en_proceso');
  case_add_note($c1,(int)$ab,'Revisando el fideicomiso de zona restringida.');
  $c2=case_create('Constitución de S.A. de C.V.','Corporativo general',(int)$cl,(int)$ab,'nuevo');
  echo "cases: 2 creados\n";
}
echo "seed listo\n";
