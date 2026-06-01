<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Security.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AuthController
{
    public static function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/views/auth/login.php');
            exit;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        $user = User::authenticate($email, $password);

        if ($user) {
            // SOLUCIÓN: Usamos la función nativa de tu Security.php para armar la sesión global
            Security::login((int)$user['id'], $user);

            // Inyectamos de forma segura el 'rol_nombre' dentro del array por si acaso
            $_SESSION['user']['rol_nombre'] = $user['rol_nombre'];

            try {
                AuditLog::register('login_exitoso', "Usuario {$user['email']} inició sesión.");
            } catch (Exception $e) {}

            // Redirección segura
            header('Location: ' . APP_URL . '/views/dashboard/index.php');
            exit;
        } else {
            header('Location: ' . APP_URL . '/views/auth/login.php?error=credenciales');
            exit;
        }
    }

    public static function logout(): void
    {
        try {
            Security::initSession();
            $email = $_SESSION['user']['email'] ?? 'Desconocido';
            AuditLog::register('logout', "Usuario $email cerró sesión.");
        } catch (Exception $e) {}
        
        Security::logout();
        header('Location: ' . APP_URL . '/views/auth/login.php');
        exit;
    }
}

// Enrutador directo
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($action)) {
    AuthController::login();
    exit;
}

if ($action === 'login') {
    AuthController::login();
} elseif ($action === 'logout') {
    AuthController::logout();
} else {
    header('Location: ' . APP_URL . '/views/auth/login.php');
}
exit;