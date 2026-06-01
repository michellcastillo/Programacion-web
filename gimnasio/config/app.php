<?php
define('APP_NAME', 'GymManager');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/gimnasio');
define('UPLOAD_DIR', __DIR__ . '/../public/uploads');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('LOG_DIR', __DIR__ . '/../logs');
define('SESSION_LIFETIME', 7200);
define('BCRYPT_COST', 12);

define('ROLES', [
    1 => 'Administrador',
    2 => 'Recepcionista',
    3 => 'Cliente'
]);

define('ROLE_PERMISSIONS', [
    1 => ['dashboard', 'clients', 'memberships', 'payments', 'cash-register', 'reports', 'users', 'config'],
    2 => ['dashboard', 'clients', 'memberships', 'payments', 'cash-register', 'reports'],
    3 => ['dashboard']
]);
