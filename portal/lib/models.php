<?php
require_once __DIR__ . '/db.php';
const ROLES = ['admin','abogado','cliente'];
const STATUSES = ['nuevo','en_proceso','cerrado'];

/* ---- Usuarios ---- */
function users_all(): array { return db()->query("SELECT id,name,email,role,active,twofa,created_at FROM users ORDER BY role,name")->fetchAll(PDO::FETCH_ASSOC); }
function users_by_role(string $r): array { $s=db()->prepare("SELECT id,name FROM users WHERE role=? AND active=1 ORDER BY name"); $s->execute([$r]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function user_get(int $id): ?array { $s=db()->prepare("SELECT * FROM users WHERE id=?"); $s->execute([$id]); return $s->fetch(PDO::FETCH_ASSOC) ?: null; }
function user_email_exists(string $e, int $except=0): bool { $s=db()->prepare("SELECT id FROM users WHERE email=? AND id<>?"); $s->execute([$e,$except]); return (bool)$s->fetch(); }
function user_create(string $n,string $e,string $p,string $r): void { db()->prepare("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)")->execute([$n,$e,password_hash($p,PASSWORD_DEFAULT),$r]); }
function user_update(int $id,string $n,string $r,int $active): void { db()->prepare("UPDATE users SET name=?,role=?,active=? WHERE id=?")->execute([$n,$r,$active,$id]); }
function user_reset_pw(int $id,string $p): void { db()->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([password_hash($p,PASSWORD_DEFAULT),$id]); }
function user_delete(int $id): void { db()->prepare("DELETE FROM users WHERE id=?")->execute([$id]); }
function admins_active_count(int $except=0): int { $s=db()->prepare("SELECT COUNT(*) FROM users WHERE role='admin' AND active=1 AND id<>?"); $s->execute([$except]); return (int)$s->fetchColumn(); }
function user_set_2fa(int $id,string $secret): void { db()->prepare("UPDATE users SET twofa=1,totp_secret=? WHERE id=?")->execute([$secret,$id]); }
function user_disable_2fa(int $id): void { db()->prepare("UPDATE users SET twofa=0,totp_secret=NULL WHERE id=?")->execute([$id]); }

/* ---- Asuntos ---- */
function cases_all(): array { return db()->query("SELECT c.*, cl.name AS client_name, lw.name AS lawyer_name FROM cases c LEFT JOIN users cl ON cl.id=c.client_id LEFT JOIN users lw ON lw.id=c.lawyer_id ORDER BY c.updated_at DESC, c.created_at DESC")->fetchAll(PDO::FETCH_ASSOC); }
function cases_for_lawyer(int $lid): array { $s=db()->prepare("SELECT c.*, cl.name AS client_name FROM cases c LEFT JOIN users cl ON cl.id=c.client_id WHERE c.lawyer_id=? ORDER BY c.created_at DESC"); $s->execute([$lid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function cases_for_client(int $cid): array { $s=db()->prepare("SELECT c.*, lw.name AS lawyer_name FROM cases c LEFT JOIN users lw ON lw.id=c.lawyer_id WHERE c.client_id=? ORDER BY c.created_at DESC"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function case_get(int $id): ?array { $s=db()->prepare("SELECT c.*, cl.name AS client_name, lw.name AS lawyer_name FROM cases c LEFT JOIN users cl ON cl.id=c.client_id LEFT JOIN users lw ON lw.id=c.lawyer_id WHERE c.id=?"); $s->execute([$id]); return $s->fetch(PDO::FETCH_ASSOC) ?: null; }
function case_create(string $t,string $area,?int $cid,?int $lid,string $st): int { db()->prepare("INSERT INTO cases(title,area,client_id,lawyer_id,status) VALUES(?,?,?,?,?)")->execute([$t,$area,$cid,$lid,$st]); return (int)db()->lastInsertId(); }
function case_update(int $id,string $t,string $area,?int $cid,?int $lid,string $st): void { db()->prepare("UPDATE cases SET title=?,area=?,client_id=?,lawyer_id=?,status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$t,$area,$cid,$lid,$st,$id]); }
function case_set_status(int $id,string $st): void { db()->prepare("UPDATE cases SET status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$st,$id]); }
function case_delete(int $id): void { db()->prepare("DELETE FROM cases WHERE id=?")->execute([$id]); }
function case_notes(int $id): array { $s=db()->prepare("SELECT u.*, us.name AS author FROM case_updates u LEFT JOIN users us ON us.id=u.author_id WHERE u.case_id=? ORDER BY u.created_at DESC"); $s->execute([$id]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function case_add_note(int $id,?int $author,string $body): void { db()->prepare("INSERT INTO case_updates(case_id,author_id,body) VALUES(?,?,?)")->execute([$id,$author,$body]); }
function case_set_meta(int $id,?string $matter_no,?string $authority,?float $amount,string $currency,string $risk): void { db()->prepare("UPDATE cases SET matter_no=?,authority=?,amount=?,currency=?,risk=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$matter_no,$authority,$amount,$currency,$risk,$id]); }

/* ================= Fase 2.1 — Expediente de trabajo ================= */
const DEADLINE_KINDS = ['procesal','contractual','fiscal','administrativo','otro'];
const TASK_STATUSES  = ['pendiente','en_proceso','hecha'];
const PARTY_ROLES    = ['cliente','contraparte','tercero','representante','notario','autoridad','co-counsel','perito'];
const DOC_CATEGORIES = ['contrato','escritura','poder','oficio','prueba','identificacion','financiero','otro'];
const SEVERITIES     = ['alto','medio','bajo'];
const REQ_STATUSES   = ['pedido','recibido','pendiente'];
const RISKS          = ['alto','medio','bajo'];

/* ---- Plazos (A1) ---- */
function deadlines_for(int $cid): array { $s=db()->prepare("SELECT * FROM deadlines WHERE case_id=? ORDER BY done, due_date"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function deadline_add(int $cid,string $title,string $due,string $kind,?int $by): void { db()->prepare("INSERT INTO deadlines(case_id,title,due_date,kind,created_by) VALUES(?,?,?,?,?)")->execute([$cid,$title,$due,$kind,$by]); }
function deadline_toggle(int $id,int $cid): void { db()->prepare("UPDATE deadlines SET done=1-done WHERE id=? AND case_id=?")->execute([$id,$cid]); }
function deadline_del(int $id,int $cid): void { db()->prepare("DELETE FROM deadlines WHERE id=? AND case_id=?")->execute([$id,$cid]); }
function deadlines_upcoming(string $cutoff,?int $lawyer=null): array { $w=$lawyer?" AND c.lawyer_id=".(int)$lawyer:""; $s=db()->prepare("SELECT d.*, c.title AS ctitle FROM deadlines d JOIN cases c ON c.id=d.case_id WHERE d.done=0 AND d.due_date<=?$w ORDER BY d.due_date"); $s->execute([$cutoff]); return $s->fetchAll(PDO::FETCH_ASSOC); }

/* ---- Tareas (A4) ---- */
function tasks_for(int $cid): array { $s=db()->prepare("SELECT t.*, u.name AS assignee FROM tasks t LEFT JOIN users u ON u.id=t.assignee_id WHERE t.case_id=? ORDER BY (t.status='hecha'), t.due_date IS NULL, t.due_date"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function task_add(int $cid,string $title,?int $assignee,?string $due,?int $by): void { db()->prepare("INSERT INTO tasks(case_id,title,assignee_id,due_date,created_by) VALUES(?,?,?,?,?)")->execute([$cid,$title,$assignee,$due?:null,$by]); }
function task_set_status(int $id,int $cid,string $st): void { db()->prepare("UPDATE tasks SET status=? WHERE id=? AND case_id=?")->execute([$st,$id,$cid]); }
function task_del(int $id,int $cid): void { db()->prepare("DELETE FROM tasks WHERE id=? AND case_id=?")->execute([$id,$cid]); }
function tasks_open_for(int $uid): array { $s=db()->prepare("SELECT t.*, c.title AS ctitle FROM tasks t JOIN cases c ON c.id=t.case_id WHERE t.assignee_id=? AND t.status<>'hecha' ORDER BY t.due_date IS NULL, t.due_date"); $s->execute([$uid]); return $s->fetchAll(PDO::FETCH_ASSOC); }

/* ---- Partes / contactos (A3) ---- */
function parties_for(int $cid): array { $s=db()->prepare("SELECT * FROM parties WHERE case_id=? ORDER BY role,name"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function party_add(int $cid,string $name,string $role,?string $org,?string $email,?string $phone,?string $notes): void { db()->prepare("INSERT INTO parties(case_id,name,role,org,email,phone,notes) VALUES(?,?,?,?,?,?,?)")->execute([$cid,$name,$role,$org,$email,$phone,$notes]); }
function party_del(int $id,int $cid): void { db()->prepare("DELETE FROM parties WHERE id=? AND case_id=?")->execute([$id,$cid]); }

/* ---- Documentos / expediente (A2) ---- */
function documents_for(int $cid): array { $s=db()->prepare("SELECT d.*, u.name AS uploader, u.role AS uploader_role FROM documents d LEFT JOIN users u ON u.id=d.uploaded_by WHERE d.case_id=? ORDER BY d.created_at DESC"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function documents_for_client(int $cid,int $client_uid): array { $s=db()->prepare("SELECT d.*, u.name AS uploader, u.role AS uploader_role FROM documents d LEFT JOIN users u ON u.id=d.uploaded_by WHERE d.case_id=? AND (d.visible_to_client=1 OR d.uploaded_by=?) ORDER BY d.created_at DESC"); $s->execute([$cid,$client_uid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function document_add(int $cid,string $orig,string $stored,string $cat,string $mime,int $size,int $conf,int $visible,?int $by): int { db()->prepare("INSERT INTO documents(case_id,orig_name,stored_name,category,mime,size,confidential,visible_to_client,uploaded_by) VALUES(?,?,?,?,?,?,?,?,?)")->execute([$cid,$orig,$stored,$cat,$mime,$size,$conf,$visible,$by]); return (int)db()->lastInsertId(); }
function document_get(int $id): ?array { $s=db()->prepare("SELECT * FROM documents WHERE id=?"); $s->execute([$id]); return $s->fetch(PDO::FETCH_ASSOC)?:null; }
function document_set_visibility(int $id,int $cid,int $v): void { db()->prepare("UPDATE documents SET visible_to_client=? WHERE id=? AND case_id=?")->execute([$v,$id,$cid]); }
function document_del(int $id,int $cid): void { db()->prepare("DELETE FROM documents WHERE id=? AND case_id=?")->execute([$id,$cid]); }
function client_docs_for_lawyer(int $lid): array { $s=db()->prepare("SELECT d.*, c.title AS ctitle, u.name AS uploader FROM documents d JOIN cases c ON c.id=d.case_id LEFT JOIN users u ON u.id=d.uploaded_by WHERE c.lawyer_id=? AND u.role='cliente' ORDER BY d.created_at DESC LIMIT 10"); $s->execute([$lid]); return $s->fetchAll(PDO::FETCH_ASSOC); }

/* ---- Hallazgos / due diligence (B8) ---- */
function findings_for(int $cid): array { $s=db()->prepare("SELECT f.*, u.name AS author FROM findings f LEFT JOIN users u ON u.id=f.created_by WHERE f.case_id=? ORDER BY CASE f.severity WHEN 'alto' THEN 0 WHEN 'medio' THEN 1 ELSE 2 END, f.created_at DESC"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function finding_add(int $cid,string $title,string $sev,?string $rec,?string $body,?int $by): void { db()->prepare("INSERT INTO findings(case_id,title,severity,recommendation,body,created_by) VALUES(?,?,?,?,?,?)")->execute([$cid,$title,$sev,$rec,$body,$by]); }
function finding_del(int $id,int $cid): void { db()->prepare("DELETE FROM findings WHERE id=? AND case_id=?")->execute([$id,$cid]); }

/* ---- Checklist (B6) ---- */
function checklist_for(int $cid): array { $s=db()->prepare("SELECT * FROM checklist_items WHERE case_id=? ORDER BY id"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function check_add(int $cid,string $label,?string $cat): void { db()->prepare("INSERT INTO checklist_items(case_id,label,category) VALUES(?,?,?)")->execute([$cid,$label,$cat]); }
function check_toggle(int $id,int $cid): void { db()->prepare("UPDATE checklist_items SET done=1-done WHERE id=? AND case_id=?")->execute([$id,$cid]); }
function check_del(int $id,int $cid): void { db()->prepare("DELETE FROM checklist_items WHERE id=? AND case_id=?")->execute([$id,$cid]); }
function checklist_templates(): array { return [
  'Due diligence inmobiliario'=>['Escritura / título de propiedad','Certificado de libertad de gravamen (RPP)','Zona restringida / fideicomiso','Uso de suelo y licencias','Predial y servicios al corriente','Avalúo'],
  'Due diligence corporativo'=>['Acta constitutiva y reformas','Libros corporativos y actas','Poderes vigentes','Inscripción en el RPC','Situación fiscal (opinión de cumplimiento)','Contratos relevantes'],
  'Comercio exterior'=>['Padrón de importadores','Registro / programa IMMEX','Clasificación arancelaria','Cumplimiento de regulaciones no arancelarias'],
]; }

/* ---- Requerimientos al cliente (B7) ---- */
function requests_for(int $cid): array { $s=db()->prepare("SELECT * FROM requests WHERE case_id=? ORDER BY (status='recibido'), created_at"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function requests_for_client(int $client_id): array { $s=db()->prepare("SELECT r.*, c.title AS ctitle FROM requests r JOIN cases c ON c.id=r.case_id WHERE c.client_id=? AND r.status<>'recibido' ORDER BY r.created_at"); $s->execute([$client_id]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function req_add(int $cid,string $item): void { db()->prepare("INSERT INTO requests(case_id,item) VALUES(?,?)")->execute([$cid,$item]); }
function req_set_status(int $id,int $cid,string $st): void { db()->prepare("UPDATE requests SET status=? WHERE id=? AND case_id=?")->execute([$st,$id,$cid]); }
function req_del(int $id,int $cid): void { db()->prepare("DELETE FROM requests WHERE id=? AND case_id=?")->execute([$id,$cid]); }

/* ---- Tiempo y honorarios (C10) ---- */
function time_for(int $cid): array { $s=db()->prepare("SELECT t.*, u.name AS who FROM time_entries t LEFT JOIN users u ON u.id=t.user_id WHERE t.case_id=? ORDER BY t.work_date DESC, t.id DESC"); $s->execute([$cid]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function time_add(int $cid,?int $uid,string $date,int $minutes,float $rate,int $billable,?string $note): void { db()->prepare("INSERT INTO time_entries(case_id,user_id,work_date,minutes,rate,billable,note) VALUES(?,?,?,?,?,?,?)")->execute([$cid,$uid,$date,$minutes,$rate,$billable,$note]); }
function time_del(int $id,int $cid): void { db()->prepare("DELETE FROM time_entries WHERE id=? AND case_id=?")->execute([$id,$cid]); }
function time_totals(int $cid): array { $s=db()->prepare("SELECT COALESCE(SUM(minutes),0) min, COALESCE(SUM(minutes*rate/60.0),0) amt, COALESCE(SUM(CASE WHEN billable=1 THEN minutes*rate/60.0 ELSE 0 END),0) billable FROM time_entries WHERE case_id=?"); $s->execute([$cid]); return $s->fetch(PDO::FETCH_ASSOC); }

/* ---- Conflicto de interés (D14) ---- */
function conflicts_all(): array { return db()->query("SELECT * FROM conflicts ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC); }
function conflict_add(string $name,?string $note,?int $by): void { db()->prepare("INSERT INTO conflicts(name,note,created_by) VALUES(?,?,?)")->execute([$name,$note,$by]); }
function conflict_del(int $id): void { db()->prepare("DELETE FROM conflicts WHERE id=?")->execute([$id]); }
function conflict_check(string $name): array {
  $name=trim($name); if($name==='') return [];
  $like='%'.$name.'%'; $hits=[];
  $s=db()->prepare("SELECT name FROM conflicts WHERE name LIKE ?"); $s->execute([$like]);
  foreach($s as $r) $hits[]='Lista de conflictos: '.$r['name'];
  $s=db()->prepare("SELECT p.name, c.title FROM parties p JOIN cases c ON c.id=p.case_id WHERE p.role='contraparte' AND p.name LIKE ?"); $s->execute([$like]);
  foreach($s as $r) $hits[]='Contraparte en «'.$r['title'].'»: '.$r['name'];
  return $hits;
}

/* ---- KPIs (C12) ---- */
function kpi_by(string $col): array { $out=[]; foreach(db()->query("SELECT $col k,COUNT(*) c FROM cases GROUP BY $col") as $r) $out[$r['k']??'—']=(int)$r['c']; return $out; }
function kpi_cases_by_lawyer(): array { return db()->query("SELECT COALESCE(lw.name,'(sin asignar)') name, COUNT(*) c FROM cases cs LEFT JOIN users lw ON lw.id=cs.lawyer_id GROUP BY cs.lawyer_id ORDER BY c DESC")->fetchAll(PDO::FETCH_ASSOC); }
function kpi_time_firm(): array { return db()->query("SELECT COALESCE(SUM(minutes),0) min, COALESCE(SUM(minutes*rate/60.0),0) amt, COALESCE(SUM(CASE WHEN billable=1 THEN minutes*rate/60.0 ELSE 0 END),0) billable FROM time_entries")->fetch(PDO::FETCH_ASSOC); }
function leads_count(): int { return (int)db()->query("SELECT COUNT(*) FROM leads")->fetchColumn(); }
