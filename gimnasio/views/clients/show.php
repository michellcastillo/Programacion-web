<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../controllers/ClientController.php';
Security::requirePermission('clients');

$id = (int)($_GET['id'] ?? 0);
$cliente = ClientController::show($id);
if (!$cliente) {
    header('Location: ' . APP_URL . '/views/clients/index.php?error=notfound');
    exit;
}

$membresias = Client::getActiveMemberships($id);
$pagos = Client::getPaymentHistory($id);

$titulo = 'Detalle del Cliente';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-person"></i> <?= Security::sanitizeInput($cliente['nombre'] . ' ' . $cliente['apellido']) ?></h2>
    </div>
    <div class="col text-end">
        <a href="<?= APP_URL ?>/views/clients/edit.php?id=<?= $id ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="<?= APP_URL ?>/views/clients/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <?php if ($cliente['foto']): ?>
                <img src="<?= APP_URL ?>/public/uploads/<?= $cliente['foto'] ?>" alt="Foto" class="rounded-circle img-thumbnail" width="150" height="150" style="object-fit:cover">
                <?php else: ?>
                <i class="bi bi-person-circle display-1 text-muted"></i>
                <?php endif; ?>
                <h4 class="mt-3"><?= Security::sanitizeInput($cliente['nombre'] . ' ' . $cliente['apellido']) ?></h4>
                <span class="badge bg-<?= $cliente['activo'] ? 'success' : 'secondary' ?> fs-6">
                    <?= $cliente['activo'] ? 'Activo' : 'Inactivo' ?>
                </span>
            </div>
            <ul class="list-group list-group-flush text-start">
                <li class="list-group-item">
                    <i class="bi bi-envelope"></i> <?= Security::sanitizeInput($cliente['email'] ?? 'Sin email') ?>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-telephone"></i> <?= Security::sanitizeInput($cliente['telefono'] ?? 'Sin teléfono') ?>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-calendar"></i> Nac: <?= $cliente['fecha_nacimiento'] ? date('d/m/Y', strtotime($cliente['fecha_nacimiento'])) : 'No registrada' ?>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-geo-alt"></i> <?= Security::sanitizeInput($cliente['direccion'] ?? 'Sin dirección') ?>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-card-checklist"></i> Membresías</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Plan</th><th>Inicio</th><th>Fin</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($membresias)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Sin membresías registradas</td></tr>
                            <?php else: ?>
                            <?php foreach ($membresias as $m): ?>
                            <tr>
                                <td><?= Security::sanitizeInput($m['plan_nombre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($m['fecha_inicio'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($m['fecha_fin'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= match($m['estado']) { 'activa' => 'success', 'expirada' => 'secondary', 'cancelada' => 'danger', default => 'warning' } ?>">
                                        <?= $m['estado'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Historial de Pagos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Fecha</th><th>Plan</th><th>Monto</th><th>Método</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pagos)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Sin pagos registrados</td></tr>
                            <?php else: ?>
                            <?php foreach ($pagos as $p): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($p['fecha_pago'])) ?></td>
                                <td><?= Security::sanitizeInput($p['plan_nombre']) ?></td>
                                <td><strong>$<?= number_format($p['monto'], 2) ?></strong></td>
                                <td><?= $p['metodo_pago'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
