<?php
/* TOTP (RFC 6238) en PHP puro — para 2FA con apps tipo Google Authenticator/Authy.
   SHA1, 6 dígitos, ventana de 30 s. Sin dependencias ni servicios externos. */

function totp_secret(int $bytes = 20): string { return base32_encode(random_bytes($bytes)); }

function base32_encode(string $bin): string {
  $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $out = ''; $buf = 0; $bits = 0;
  for ($i = 0; $i < strlen($bin); $i++) {
    $buf = ($buf << 8) | ord($bin[$i]); $bits += 8;
    while ($bits >= 5) { $bits -= 5; $out .= $alphabet[($buf >> $bits) & 31]; }
  }
  if ($bits > 0) $out .= $alphabet[($buf << (5 - $bits)) & 31];
  return $out;
}

function base32_decode(string $b32): string {
  $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
  $buf = 0; $bits = 0; $out = '';
  for ($i = 0; $i < strlen($b32); $i++) {
    $buf = ($buf << 5) | strpos($alphabet, $b32[$i]); $bits += 5;
    if ($bits >= 8) { $bits -= 8; $out .= chr(($buf >> $bits) & 0xFF); }
  }
  return $out;
}

function totp_code(string $secretB32, ?int $timeStep = null): string {
  $key = base32_decode($secretB32);
  $counter = $timeStep ?? intdiv(time(), 30);
  $bin = pack('N*', 0, $counter);               // contador de 8 bytes big-endian
  $hash = hash_hmac('sha1', $bin, $key, true);
  $off = ord($hash[19]) & 0x0F;
  $val = ((ord($hash[$off]) & 0x7F) << 24) | ((ord($hash[$off+1]) & 0xFF) << 16)
       | ((ord($hash[$off+2]) & 0xFF) << 8) | (ord($hash[$off+3]) & 0xFF);
  return str_pad((string)($val % 1000000), 6, '0', STR_PAD_LEFT);
}

/** Verifica con tolerancia de ±1 paso (reloj desfasado). */
function totp_verify(string $secretB32, string $code): bool {
  $code = preg_replace('/\D/', '', $code);
  if (strlen($code) !== 6) return false;
  $now = intdiv(time(), 30);
  for ($w = -1; $w <= 1; $w++) if (hash_equals(totp_code($secretB32, $now + $w), $code)) return true;
  return false;
}

/** URI otpauth:// para apps (alta por QR o manual). */
function totp_uri(string $secretB32, string $email): string {
  $issuer = rawurlencode('Culebro Abogados');
  $label  = $issuer . ':' . rawurlencode($email);
  return "otpauth://totp/$label?secret=$secretB32&issuer=$issuer&digits=6&period=30";
}
