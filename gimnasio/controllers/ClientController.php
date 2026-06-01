<?php
require_once __DIR__ . '/../models/Security.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/AuditLog.php';

class ClientController
{
    public static function index(): array
    {
        return Client::getAll();
    }

    public static function search(): array
    {
        $term = filter_var($_GET['q'] ?? '', FILTER_SANITIZE_STRING);
        return Client::search($term);
    }

    public static function show(int $id): ?array
    {
        return Client::getById($id);
    }

    public static function store(): void
    {
        Security::requirePermission('clients');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/views/clients/index.php');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            die('Token CSRF inválido.');
        }

        $data = [
            'nombre'           => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'apellido'         => htmlspecialchars(trim($_POST['apellido'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'email'            => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
            'telefono'         => htmlspecialchars(trim($_POST['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'direccion'        => htmlspecialchars(trim($_POST['direccion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
        ];

        if (empty($data['nombre']) || empty($data['apellido'])) {
            header('Location: ' . APP_URL . '/views/clients/create.php?error=required');
            exit;
        }

        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = Security::uploadFile($_FILES['foto'], 'clients');
            if ($uploadResult['success']) {
                $foto = 'clients/' . $uploadResult['filename'];
            }
        }
        $data['foto'] = $foto;

        $clientId = Client::create($data);
        AuditLog::register('cliente_creado', "Cliente creado: {$data['nombre']} {$data['apellido']} (ID: $clientId)");

        header('Location: ' . APP_URL . '/views/clients/index.php?success=created');
        exit;
    }

    public static function update(int $id): void
    {
        Security::requirePermission('clients');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/views/clients/index.php');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            die('Token CSRF inválido.');
        }

        $client = Client::getById($id);
        if (!$client) {
            header('Location: ' . APP_URL . '/views/clients/index.php?error=notfound');
            exit;
        }

        $data = [];

        if (isset($_POST['nombre'])) {
            $data['nombre'] = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
        }
        if (isset($_POST['apellido'])) {
            $data['apellido'] = htmlspecialchars(trim($_POST['apellido']), ENT_QUOTES, 'UTF-8');
        }
        if (isset($_POST['email'])) {
            $data['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        }
        if (isset($_POST['telefono'])) {
            $data['telefono'] = htmlspecialchars(trim($_POST['telefono']), ENT_QUOTES, 'UTF-8');
        }
        if (isset($_POST['direccion'])) {
            $data['direccion'] = htmlspecialchars(trim($_POST['direccion']), ENT_QUOTES, 'UTF-8');
        }
        if (isset($_POST['fecha_nacimiento'])) {
            $data['fecha_nacimiento'] = $_POST['fecha_nacimiento'];
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = Security::uploadFile($_FILES['foto'], 'clients');
            if ($uploadResult['success']) {
                if ($client['foto']) {
                    $oldFile = UPLOAD_DIR . '/' . $client['foto'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $data['foto'] = 'clients/' . $uploadResult['filename'];
            }
        }

        Client::update($id, $data);
        AuditLog::register('cliente_actualizado', "Cliente actualizado ID: $id");

        header('Location: ' . APP_URL . '/views/clients/index.php?success=updated');
        exit;
    }

    public static function delete(int $id): void
    {
        Security::requirePermission('clients');

        $client = Client::getById($id);
        if (!$client) {
            header('Location: ' . APP_URL . '/views/clients/index.php?error=notfound');
            exit;
        }

        Client::softDelete($id);
        AuditLog::register('cliente_eliminado', "Cliente eliminado (lógico): {$client['nombre']} {$client['apellido']} (ID: $id)");

        header('Location: ' . APP_URL . '/views/clients/index.php?success=deleted');
        exit;
    }
}

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// Solo actuar si se especificó una acción concreta en la URL
if (!empty($action)) {
    match ($action) {
        'store'  => ClientController::store(),
        'update' => ClientController::update($id),
        'delete' => ClientController::delete($id),
        default  => header('Location: ' . APP_URL . '/views/clients/index.php')
    };
    exit; // Detiene la ejecución si se procesó una acción
}
