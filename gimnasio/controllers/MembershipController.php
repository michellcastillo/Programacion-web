<?php
require_once __DIR__ . '/../models/Security.php';
require_once __DIR__ . '/../models/Membership.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/CashRegister.php';
require_once __DIR__ . '/../models/AuditLog.php';

class MembershipController
{
    public static function assign(): void
    {
        Security::requirePermission('memberships');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/views/memberships/index.php');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            die('Token CSRF inválido.');
        }

        $clienteId = (int)($_POST['cliente_id'] ?? 0);
        $planId    = (int)($_POST['plan_id'] ?? 0);
        $startDate = $_POST['fecha_inicio'] ?? date('Y-m-d');
        $metodoPago = $_POST['metodo_pago'] ?? 'efectivo';

        if (!$clienteId || !$planId) {
            header('Location: ' . APP_URL . '/views/memberships/assign.php?error=required');
            exit;
        }

        $client = Client::getById($clienteId);
        $plan   = Membership::getPlanById($planId);

        if (!$client || !$plan) {
            header('Location: ' . APP_URL . '/views/memberships/assign.php?error=notfound');
            exit;
        }

        $membresiaId = Membership::assignToClient($clienteId, $planId, $startDate);

        Payment::register($membresiaId, $clienteId, $plan['precio'], $metodoPago, 'Pago ' . $plan['nombre']);

        $openRegister = CashRegister::getOpenRegister();
        if ($openRegister) {
            CashRegister::addIncome(
                $openRegister['id'],
                $plan['precio'],
                'Membresía',
                "Pago membresía {$plan['nombre']} - {$client['nombre']} {$client['apellido']}",
                $membresiaId
            );
        }

        AuditLog::register('membresia_asignada',
            "Membresía {$plan['nombre']} asignada a {$client['nombre']} {$client['apellido']} (ID: $membresiaId)"
        );

        header('Location: ' . APP_URL . '/views/memberships/index.php?success=assigned');
        exit;
    }

    public static function cancel(int $id): void
    {
        Security::requirePermission('memberships');

        Membership::cancel($id);
        AuditLog::register('membresia_cancelada', "Membresía cancelada ID: $id");

        header('Location: ' . APP_URL . '/views/memberships/index.php?success=cancelled');
        exit;
    }
}

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
match ($action) {
    'assign' => MembershipController::assign(),
    'cancel' => MembershipController::cancel($id),
    default  => header('Location: ' . APP_URL . '/views/memberships/index.php')
};
exit;
