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
  case_set_meta($c1,'EXP-2026-014','Notaría 3, Los Cabos',8500000.0,'MXN','alto');
  deadline_add($c1,'Firma de compraventa',date('Y-m-d',strtotime('+10 days')),'contractual',(int)$ab);
  deadline_add($c1,'Vence anticipo',date('Y-m-d',strtotime('-3 days')),'contractual',(int)$ab); // vencido (demo)
  task_add($c1,'Solicitar certificado de libertad de gravamen (RPP)',(int)$ab,date('Y-m-d',strtotime('+5 days')),(int)$ab);
  party_add($c1,'Desarrolladora Palmilla, S.A. de C.V.','contraparte','Grupo Palmilla','contacto@palmilla.example','+52 624 000 0000',null);
  foreach(checklist_templates()['Due diligence inmobiliario'] as $lbl) check_add($c1,$lbl,'Due diligence inmobiliario');
  req_add($c1,'Copia de la escritura y último predial');
  finding_add($c1,'Gravamen hipotecario vigente sobre el predio','alto','Exigir liberación del gravamen antes de firmar.','Aparece hipoteca a favor de banco en el folio real.',(int)$ab);
  time_add($c1,(int)$ab,date('Y-m-d'),120,1500.0,1,'Revisión de título y antecedentes registrales');
  $c2=case_create('Constitución de S.A. de C.V.','Corporativo general',(int)$cl,(int)$ab,'nuevo');
  conflict_add('Inmobiliaria Rival, S.A. de C.V.','Parte contraria en otro asunto activo.',(int)$ab);
  echo "cases: 2 creados (con expediente demo)\n";
}
echo "seed listo\n";
