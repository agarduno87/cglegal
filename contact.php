<?php
/**
 * Culebro Abogados — recepción de leads por correo (GoDaddy / cPanel, PHP).
 * Defensa en profundidad (misma que logistika/DataraHub):
 *   honeypot + trampa de tiempo + rate limit + validación estricta + CORS cerrado
 *   + secretos por entorno + logging anonimizado (sin PII completa).
 * Además: AUTORESPUESTA automática al remitente (no-reply@).
 *
 * CONFIG por entorno (cPanel SetEnv o edita defaults):
 *   LEAD_TO, LEAD_FROM, ALLOWED_ORIGIN, RATE_LIMIT_MAX, RATE_LIMIT_WINDOW_SECONDS
 */
$LEAD_TO   = getenv('LEAD_TO')   ?: 'contacto@cglegal.com.mx';
$LEAD_FROM = getenv('LEAD_FROM') ?: 'no-reply@cglegal.com.mx';
$ALLOWED_ORIGIN = getenv('ALLOWED_ORIGIN') ?: 'https://www.cglegal.com.mx';
$RATE_LIMIT_MAX = (int)(getenv('RATE_LIMIT_MAX') ?: 5);
$RATE_LIMIT_WINDOW = (int)(getenv('RATE_LIMIT_WINDOW_SECONDS') ?: 3600);

$SITE_NAME = 'Culebro Abogados';
$MIN_FILL_SECONDS = 3;
$MAX_BODY_BYTES = 16 * 1024;
$ALLOWED_LOCALES = ['en','es'];

header('Vary: Origin');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === $ALLOWED_ORIGIN) {
    header("Access-Control-Allow-Origin: {$ALLOWED_ORIGIN}");
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    header('Access-Control-Max-Age: 600');
}
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); echo json_encode(['detail'=>'Method not allowed']); exit; }

function client_ip(): string {
    $fwd = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($fwd !== '') return trim(explode(',', $fwd)[0]);
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
function anonymise(string $ip): string {
    if (strpos($ip, ':') !== false) return implode(':', array_slice(explode(':', $ip), 0, 3)) . '::/48';
    $p = explode('.', $ip);
    return count($p) === 4 ? "{$p[0]}.{$p[1]}.{$p[2]}.0" : 'unknown';
}
function lead_log(string $m): void { error_log("[CulebroAbogados] {$m}"); }

$ip = client_ip(); $anon = anonymise($ip);

function rate_limited(string $ip, int $max, int $window): bool {
    $dir = sys_get_temp_dir() . '/cglegal_rl';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/' . hash('sha256', $ip) . '.json';
    $now = time(); $fh = @fopen($file, 'c+');
    if ($fh === false) return false;
    flock($fh, LOCK_EX);
    $hits = json_decode(stream_get_contents($fh) ?: '[]', true);
    if (!is_array($hits)) $hits = [];
    $hits = array_values(array_filter($hits, fn($t) => $now - $t <= $window));
    $limited = count($hits) >= $max;
    if (!$limited) $hits[] = $now;
    ftruncate($fh, 0); rewind($fh); fwrite($fh, json_encode($hits));
    flock($fh, LOCK_UN); fclose($fh);
    return $limited;
}

$raw = file_get_contents('php://input');
if (strlen($raw) > $MAX_BODY_BYTES) { http_response_code(413); echo json_encode(['detail'=>'payload too large']); exit; }
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$name    = trim($data['name'] ?? '');
$email   = trim($data['email'] ?? '');
$phone   = trim($data['phone'] ?? '');
$message = trim($data['message'] ?? '');
$locale  = trim($data['locale'] ?? 'es');
$website = trim($data['website'] ?? '');            // honeypot
$rendered_at = (int)($data['rendered_at'] ?? 0);    // trampa de tiempo (ms)

if ($website !== '') { lead_log("honeypot ip={$anon}"); echo json_encode(['status'=>'ok']); exit; }
if ($rendered_at > 0 && (time() - intdiv($rendered_at,1000)) < $MIN_FILL_SECONDS) {
    lead_log("timetrap ip={$anon}"); echo json_encode(['status'=>'ok']); exit;
}

$errors = [];
if ($name === '' || mb_strlen($name) > 120) $errors[] = 'name';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) $errors[] = 'email';
if (mb_strlen($phone) > 40) $errors[] = 'phone';
if ($message === '' || mb_strlen($message) > 4000) $errors[] = 'message';
if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $name . $message)) $errors[] = 'control-chars';
if (!in_array($locale, $ALLOWED_LOCALES, true)) $locale = 'es';
if ($errors) { http_response_code(422); echo json_encode(['detail'=>'validation error','fields'=>$errors]); exit; }

if (rate_limited($ip, $RATE_LIMIT_MAX, $RATE_LIMIT_WINDOW)) {
    lead_log("rate limited ip={$anon}"); http_response_code(429); echo json_encode(['detail'=>'too many requests']); exit;
}

// --- Correo interno (al despacho) ---
$subject = "[{$SITE_NAME}] Nuevo contacto — {$name}";
$body  = "Nuevo lead desde el sitio\n---------------------------\n";
$body .= "Nombre:  {$name}\nCorreo:  {$email}\nTel.:    {$phone}\nIdioma:  {$locale}\n";
$body .= "Mensaje:\n{$message}\n---------------------------\nFecha: " . date('Y-m-d H:i:s') . "\n";
$headers  = "From: {$SITE_NAME} <{$LEAD_FROM}>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";
$enc = fn($s) => '=?UTF-8?B?' . base64_encode($s) . '?=';
$sent = @mail($LEAD_TO, $enc($subject), $body, $headers);

// --- Autorespuesta al remitente (no-reply@) ---
if ($locale === 'en') {
    $arSubj = "We received your message — {$SITE_NAME}";
    $arBody = "Hello {$name},\n\nThank you for reaching out to {$SITE_NAME}. We have received your message and it is now with our team.\n\nWithin the next business hours, one of our specialist attorneys will contact you to review your matter, answer your questions and walk you through the next steps, including the details of a possible quote.\n\nThis is an automated message; please do not reply. If your matter is urgent, reach us on WhatsApp or call our offices in Los Cabos or Querétaro.\n\nKind regards,\n{$SITE_NAME}\nCG Legal & Real Estate Consulting, S.C.\n";
} else {
    $arSubj = "Recibimos tu mensaje — {$SITE_NAME}";
    $arBody = "Hola {$name}:\n\nGracias por ponerte en contacto con {$SITE_NAME}. Hemos recibido tu mensaje y ya está en manos de nuestro equipo.\n\nEn las próximas horas hábiles, uno de nuestros abogados especialistas se comunicará contigo para revisar tu caso, resolver tus dudas y explicarte los siguientes pasos, incluidos los detalles de una posible cotización.\n\nEste es un mensaje automático; por favor no respondas a este correo. Si tu asunto es urgente, escríbenos por WhatsApp o llámanos a nuestras oficinas de Los Cabos o Querétaro.\n\nUn saludo cordial,\n{$SITE_NAME}\nCG Legal & Real Estate Consulting, S.C.\n";
}
$arHeaders = "From: {$SITE_NAME} <{$LEAD_FROM}>\r\nContent-Type: text/plain; charset=utf-8\r\n";
@mail($email, $enc($arSubj), $arBody, $arHeaders);

$emailDomain = substr(strrchr($email, '@') ?: '@?', 1);
lead_log(($sent ? 'lead sent' : 'mail FAILED') . " email_domain={$emailDomain} ip={$anon}");
if (!$sent) { http_response_code(502); echo json_encode(['detail'=>'mail failed']); exit; }
echo json_encode(['status'=>'ok']);
