<?php
require_once __DIR__ . '/../models/Security.php';
require_once __DIR__ . '/../models/CashRegister.php';
require_once __DIR__ . '/../models/AuditLog.php';

class CashRegisterController
{
    public static function open(): void
    {
        Security::requirePermission('cash-register');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/views/cash-register/open.php');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            die('Token CSRF inválido.');
        }

        $montoInicial  = (float)($_POST['monto_inicial'] ?? 0);
        $observaciones = htmlspecialchars(trim($_POST['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8');

        if ($montoInicial < 0) {
            header('Location: ' . APP_URL . '/views/cash-register/open.php?error=invalid_amount');
            exit;
        }

        try {
            $corteId = CashRegister::open(Security::getUserId(), $montoInicial, $observaciones);
            AuditLog::register('caja_abierta', "Caja abierta con monto inicial: $$montoInicial (ID: $corteId)");
            header('Location: ' . APP_URL . '/views/cash-register/operations.php?success=opened');
        } catch (Exception $e) {
            header('Location: ' . APP_URL . '/views/cash-register/open.php?error=already_open');
        }
        exit;
    }

    public static function addMovement(): void
    {
        Security::requirePermission('cash-register');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/views/cash-register/operations.php');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            die('Token CSRF inválido.');
        }

        $corteId    = (int)($_POST['corte_id'] ?? 0);
        $tipo       = $_POST['tipo'] ?? '';
        $monto      = (float)($_POST['monto'] ?? 0);
        $categoria  = htmlspecialchars(trim($_POST['categoria'] ?? ''), ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8');

        if (!$corteId || !$tipo || $monto <= 0) {
            header('Location: ' . APP_URL . '/views/cash-register/operations.php?error=required');
            exit;
        }

        $register = CashRegister::getById($corteId);
        if (!$register || $register['estado'] !== 'abierto') {
            header('Location: ' . APP_URL . '/views/cash-register/operations.php?error=closed');
            exit;
        }

        if ($tipo === 'ingreso') {
            CashRegister::addIncome($corteId, $monto, $categoria, $descripcion);
        } else {
            CashRegister::addExpense($corteId, $monto, $categoria, $descripcion);
        }

        AuditLog::register('movimiento_caja', "$tipo registrado: $$monto - $categoria");

        header('Location: ' . APP_URL . '/views/cash-register/operations.php?success=moved');
        exit;
    }

    public static function close(): void
    {
        Security::requirePermission('cash-register');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/views/cash-register/operations.php');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            die('Token CSRF inválido.');
        }

        $corteId = (int)($_POST['corte_id'] ?? 0);
        if (!$corteId) {
            header('Location: ' . APP_URL . '/views/cash-register/operations.php?error=required');
            exit;
        }

        CashRegister::close($corteId);

        $register = CashRegister::getById($corteId);
        AuditLog::register('caja_cerrada',
            "Caja cerrada. Total: $" . number_format($register['monto_final'], 2) . " (ID: $corteId)"
        );

        header('Location: ' . APP_URL . '/views/cash-register/index.php?success=closed');
        exit;
    }
}

$action = $_GET['action'] ?? '';
match ($action) {
    'open'          => CashRegisterController::open(),
    'addMovement'   => CashRegisterController::addMovement(),
    'close'         => CashRegisterController::close(),
    default         => header('Location: ' . APP_URL . '/views/cash-register/index.php')
};
exit;
