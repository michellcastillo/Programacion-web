<?php
require_once __DIR__ . '/../models/Security.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AuthController
{
    public static function login(): void
    {
        Security::initSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/views/auth/login.php');
            exit;
        }

        $email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $csrf     = $_POST['csrf_token'] ?? '';

        if (!Security::verifyCsrf($csrf)) {
            AuditLog::register('csrf_fallido', 'Intento de login con CSRF token inválido');
            header('Location: ' . APP_URL . '/views/auth/login.php?error=csrf');
            exit;
        }

        if (!Security::validateEmail($email) || empty($password)) {
            AuditLog::register('login_fallido', "Intento de login con email inválido: $email");
            header('Location: ' . APP_URL . '/views/auth/login.php?error=credenciales');
            exit;
        }

        $user = User::authenticate($email, $password);

        if ($user) {
            Security::login((int)$user['id'], $user);
            AuditLog::register('login_exitoso', "Usuario {$user['nombre']} ha iniciado sesión", (int)$user['id']);
            header('Location: ' . APP_URL . '/views/dashboard/index.php');
            exit;
        }

        AuditLog::register('login_fallido', "Intento de inicio de sesión fallido para: $email");
        header('Location: ' . APP_URL . '/views/auth/login.php?error=credenciales');
        exit;
    }

    public static function logout(): void
    {
        Security::initSession();
        $userId = Security::getUserId();
        AuditLog::register('logout', 'El usuario ha cerrado sesión', $userId);
        Security::logout();
        header('Location: ' . APP_URL . '/views/auth/login.php');
        exit;
    }
}

$action = $_GET['action'] ?? '';
match ($action) {
    'login'  => AuthController::login(),
    'logout' => AuthController::logout(),
    default  => header('Location: ' . APP_URL . '/views/auth/login.php')
};
exit;
