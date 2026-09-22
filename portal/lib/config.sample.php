<?php
// Copia a config.php y ajusta. config.php NUNCA se versiona (.gitignore).
return [
  'driver' => getenv('DB_DRIVER') ?: 'sqlite',           // 'mysql' en producción
  'sqlite_path' => __DIR__ . '/../../db/dev.sqlite',      // solo desarrollo
  'mysql' => [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'name' => getenv('DB_NAME') ?: 'cglegal',
    'user' => getenv('DB_USER') ?: '',
    'pass' => getenv('DB_PASS') ?: '',
  ],
];
