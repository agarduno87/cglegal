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
  } else {
    $m = $cfg['mysql'];
    $pdo = new PDO("mysql:host={$m['host']};dbname={$m['name']};charset=utf8mb4", $m['user'], $m['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  return $pdo;
}
