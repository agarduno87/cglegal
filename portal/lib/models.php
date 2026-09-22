<?php
require_once __DIR__ . '/db.php';
const ROLES = ['admin','abogado','cliente'];
const STATUSES = ['nuevo','en_proceso','cerrado'];

/* ---- Usuarios ---- */
function users_all(): array { return db()->query("SELECT id,name,email,role,active,created_at FROM users ORDER BY role,name")->fetchAll(PDO::FETCH_ASSOC); }
function users_by_role(string $r): array { $s=db()->prepare("SELECT id,name FROM users WHERE role=? AND active=1 ORDER BY name"); $s->execute([$r]); return $s->fetchAll(PDO::FETCH_ASSOC); }
function user_get(int $id): ?array { $s=db()->prepare("SELECT * FROM users WHERE id=?"); $s->execute([$id]); return $s->fetch(PDO::FETCH_ASSOC) ?: null; }
function user_email_exists(string $e, int $except=0): bool { $s=db()->prepare("SELECT id FROM users WHERE email=? AND id<>?"); $s->execute([$e,$except]); return (bool)$s->fetch(); }
function user_create(string $n,string $e,string $p,string $r): void { db()->prepare("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)")->execute([$n,$e,password_hash($p,PASSWORD_DEFAULT),$r]); }
function user_update(int $id,string $n,string $r,int $active): void { db()->prepare("UPDATE users SET name=?,role=?,active=? WHERE id=?")->execute([$n,$r,$active,$id]); }
function user_reset_pw(int $id,string $p): void { db()->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([password_hash($p,PASSWORD_DEFAULT),$id]); }
function user_delete(int $id): void { db()->prepare("DELETE FROM users WHERE id=?")->execute([$id]); }
function admins_active_count(int $except=0): int { $s=db()->prepare("SELECT COUNT(*) FROM users WHERE role='admin' AND active=1 AND id<>?"); $s->execute([$except]); return (int)$s->fetchColumn(); }

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
