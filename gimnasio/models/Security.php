<?php
require_once __DIR__ . '/../config/app.php';

class Security
{
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);
            session_start();
        }
    }

    public static function regenerateSession(): void
    {
        session_regenerate_id(true);
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function getUser(): ?array
    {
        return self::isLoggedIn() ? $_SESSION['user'] ?? null : null;
    }

    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUserRole(): ?int
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function hasPermission(string $module): bool
    {
        $role = self::getUserRole();
        if (!$role) return false;
        $perms = ROLE_PERMISSIONS;
        return in_array($module, $perms[$role] ?? []);
    }

    public static function requireAuth(): void
    {
        self::initSession();
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . '/views/auth/login.php');
            exit;
        }
    }

    public static function requirePermission(string $module): void
    {
        self::requireAuth();
        if (!self::hasPermission($module)) {
            AuditLog::register('acceso_denegado', "Intento de acceso al módulo: $module");
            http_response_code(403);
            die('Acceso denegado. No tienes permisos para este módulo.');
        }
    }

    public static function login(int $userId, array $user): void
    {
        self::initSession();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = (int)$user['rol_id'];
        $_SESSION['user'] = [
            'id'       => $userId,
            'nombre'   => $user['nombre'],
            'email'    => $user['email'],
            'rol_id'   => (int)$user['rol_id'],
            'rol'      => ROLES[(int)$user['rol_id']] ?? 'Desconocido'
        ];
        $_SESSION['created'] = time();
    }

    public static function logout(): void
    {
        self::initSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool
    {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function sanitizeInput(string $data): string
    {
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateInt($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function validateFloat($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    public static function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $filename);
        return strtolower($filename);
    }

    public static function generateRandomFilename(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    public static function validateFile(array $file): array
    {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'errors' => ['Error al subir el archivo.']];
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return ['success' => false, 'errors' => ['El archivo excede el tamaño máximo de 5MB.']];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, UPLOAD_ALLOWED_TYPES)) {
            return ['success' => false, 'errors' => ['Tipo de archivo no permitido. Solo JPG, PNG y WebP.']];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, UPLOAD_ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'errors' => ['Extensión de archivo no permitida.']];
        }

        return ['success' => true, 'extension' => $extension];
    }

    public static function uploadFile(array $file, string $subdir = ''): array
    {
        $validation = self::validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }

        $uploadPath = UPLOAD_DIR . '/' . ($subdir ? $subdir . '/' : '');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $newFilename = self::generateRandomFilename($validation['extension']);
        $destPath = $uploadPath . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'errors' => ['Error al mover el archivo subido.']];
        }

        return ['success' => true, 'filename' => $newFilename, 'path' => $destPath];
    }
}
