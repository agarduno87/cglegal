<?php require_once __DIR__.'/lib/auth.php'; require_login(); header('Location: '.role_home(current_user()['role'])); exit;
