<?php
/* Descarga controlada de documentos del expediente.
   Nunca se sirven por URL directa: se validan login + acceso al asunto. */
require_once __DIR__.'/lib/models.php';
require_once __DIR__.'/lib/auth.php';
require_login();
$u = current_user();
$doc = document_get((int)($_GET['doc'] ?? 0));
if (!$doc) { http_response_code(404); echo 'No encontrado.'; exit; }
$c = case_get((int)$doc['case_id']);
if (!$c) { http_response_code(404); echo 'No encontrado.'; exit; }

$role = $u['role'] ?? '';
$allowed = ($role==='admin')
  || ($role==='abogado' && (int)$c['lawyer_id']===(int)$u['id'])
  || ($role==='cliente' && (int)$c['client_id']===(int)$u['id']
        && ((int)$doc['visible_to_client']===1 || (int)$doc['uploaded_by']===(int)$u['id']));
if (!$allowed) { http_response_code(403); echo 'Acceso denegado.'; exit; }

$path = uploads_dir().'/'.basename($doc['stored_name']);
if (!is_file($path)) { http_response_code(404); echo 'Archivo no disponible.'; exit; }
audit('doc_download:'.$doc['case_id'].':'.$doc['id']);

header('Content-Type: '.($doc['mime'] ?: 'application/octet-stream'));
header('Content-Length: '.filesize($path));
header('Content-Disposition: attachment; filename="'.rawurlencode($doc['orig_name']).'"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($path);
