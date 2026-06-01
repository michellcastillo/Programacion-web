<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../models/Payment.php';
Security::requirePermission('payments');

$pagos = Payment::getAll();
$todayTotal = Payment::getTodayTotal();

$titulo = 'Pagos';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-cash-stack"></i> Pagos</h2>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body">
                <h6>Total Recaudado Hoy</h6>
                <h2 class="fw-bold mb-0">$<?= number_format($todayTotal, 2) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body">
                <h6>Total de Transacciones</h6>
                <h2 class="fw-bold mb-0"><?= count($pagos) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body">
                <h6>Asignar Nueva Membresía</h6>
                <a href="<?= APP_URL ?>/views/memberships/assign.php" class="btn btn-light mt-2">Ir a Asignar</a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Historial de Pagos</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Cliente</th><th>Plan</th><th>Monto</th><th>Método</th><th>Referencia</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                    <tr><td colspan="6" class="text-center py-3 text-muted">No hay pagos registrados</td></tr>
                    <?php else: ?>
                    <?php foreach ($pagos as $p): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($p['fecha_pago'])) ?></td>
                        <td><strong><?= Security::sanitizeInput($p['cliente_nombre'] . ' ' . $p['cliente_apellido']) ?></strong></td>
                        <td><?= Security::sanitizeInput($p['plan_nombre']) ?></td>
                        <td class="fw-bold text-success">$<?= number_format($p['monto'], 2) ?></td>
                        <td><span class="badge bg-secondary"><?= $p['metodo_pago'] ?></span></td>
                        <td><?= Security::sanitizeInput($p['referencia'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
