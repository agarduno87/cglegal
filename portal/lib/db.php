<?php
function db(): PDO {
  static $pdo = null; if ($pdo) return $pdo;
  $f = __DIR__ . '/config.php';
  $cfg = file_exists($f) ? require $f : require __DIR__ . '/config.sample.php';
  if (($cfg['driver'] ?? 'sqlite') === 'sqlite') {
    $pdo = new PDO('sqlite:' . $cfg['sqlite_path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys=ON");
    $pdo->exec("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT UNIQUE NOT NULL,password_hash TEXT NOT NULL,role TEXT NOT NULL DEFAULT 'cliente',active INTEGER NOT NULL DEFAULT 1,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,action TEXT,ip TEXT,at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS leads(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,email TEXT,phone TEXT,message TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cases(id INTEGER PRIMARY KEY AUTOINCREMENT,title TEXT NOT NULL,area TEXT,client_id INTEGER,lawyer_id INTEGER,status TEXT NOT NULL DEFAULT 'nuevo',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS case_updates(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,author_id INTEGER,body TEXT NOT NULL,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    // --- Fase 2.1: expediente de trabajo (A/B/C/D) ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS deadlines(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,title TEXT NOT NULL,due_date TEXT NOT NULL,kind TEXT DEFAULT 'otro',done INTEGER NOT NULL DEFAULT 0,created_by INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS tasks(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,title TEXT NOT NULL,assignee_id INTEGER,due_date TEXT,status TEXT NOT NULL DEFAULT 'pendiente',created_by INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS parties(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,name TEXT NOT NULL,role TEXT DEFAULT 'tercero',org TEXT,email TEXT,phone TEXT,notes TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS documents(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,orig_name TEXT NOT NULL,stored_name TEXT NOT NULL,category TEXT DEFAULT 'otro',mime TEXT,size INTEGER,confidential INTEGER NOT NULL DEFAULT 1,visible_to_client INTEGER NOT NULL DEFAULT 0,uploaded_by INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS findings(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,title TEXT NOT NULL,severity TEXT NOT NULL DEFAULT 'medio',recommendation TEXT,body TEXT,created_by INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS checklist_items(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,label TEXT NOT NULL,category TEXT,done INTEGER NOT NULL DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS requests(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,item TEXT NOT NULL,status TEXT NOT NULL DEFAULT 'pedido',created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS time_entries(id INTEGER PRIMARY KEY AUTOINCREMENT,case_id INTEGER NOT NULL,user_id INTEGER,work_date TEXT,minutes INTEGER NOT NULL DEFAULT 0,rate REAL NOT NULL DEFAULT 0,billable INTEGER NOT NULL DEFAULT 1,invoiced INTEGER NOT NULL DEFAULT 0,note TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS conflicts(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,note TEXT,created_by INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
    // columnas nuevas en cases (migración idempotente para dev)
    $cols = [];
    foreach ($pdo->query("PRAGMA table_info(cases)") as $r) $cols[] = $r['name'];
    $add = [
      'matter_no' => "ALTER TABLE cases ADD COLUMN matter_no TEXT",
      'authority' => "ALTER TABLE cases ADD COLUMN authority TEXT",
      'amount'    => "ALTER TABLE cases ADD COLUMN amount REAL",
      'currency'  => "ALTER TABLE cases ADD COLUMN currency TEXT DEFAULT 'MXN'",
      'risk'      => "ALTER TABLE cases ADD COLUMN risk TEXT DEFAULT 'medio'",
    ];
    foreach ($add as $c => $sql) if (!in_array($c, $cols, true)) $pdo->exec($sql);
    // columna nueva en documents (visibilidad para el cliente)
    $dcols = []; foreach ($pdo->query("PRAGMA table_info(documents)") as $r) $dcols[] = $r['name'];
    if (!in_array('visible_to_client', $dcols, true)) $pdo->exec("ALTER TABLE documents ADD COLUMN visible_to_client INTEGER NOT NULL DEFAULT 0");
  } else {
    $m = $cfg['mysql'];
    $pdo = new PDO("mysql:host={$m['host']};dbname={$m['name']};charset=utf8mb4", $m['user'], $m['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  return $pdo;
}

/** Carpeta de documentos del expediente (fuera de la raíz pública). */
function uploads_dir(): string {
  $d = dirname(__DIR__, 2) . '/db/uploads';
  if (!is_dir($d)) { @mkdir($d, 0700, true); @file_put_contents($d.'/.htaccess', "Require all denied\n"); }
  return $d;
}
