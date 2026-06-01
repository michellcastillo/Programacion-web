<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../models/CashRegister.php';
Security::requirePermission('cash-register');

$openRegister = CashRegister::getOpenRegister();
$history = CashRegister::getHistory();

$titulo = 'Cortes de Caja';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-box"></i> Cortes de Caja</h2>
    </div>
    <div class="col text-end">
        <?php if (!$openRegister): ?>
        <a href="<?= APP_URL ?>/views/cash-register/open.php" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Abrir Caja
        </a>
        <?php else: ?>
        <a href="<?= APP_URL ?>/views/cash-register/operations.php" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Caja Abierta - Operaciones
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($openRegister): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i>
    <strong>Caja abierta</strong> por <?= Security::sanitizeInput($openRegister['usuario_nombre']) ?>
    desde <?= date('d/m/Y H:i', strtotime($openRegister['fecha_apertura'])) ?>
    | Monto inicial: $<?= number_format($openRegister['monto_inicial'], 2) ?>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Historial de Cortes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Usuario</th><th>Apertura</th><th>Cierre</th><th>Inicial</th><th>Final</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                    <tr><td colspan="7" class="text-center py-3 text-muted">No hay cortes registrados</td></tr>
                    <?php else: ?>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= $h['id'] ?></td>
                        <td><?= Security::sanitizeInput($h['usuario_nombre']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($h['fecha_apertura'])) ?></td>
                        <td><?= $h['fecha_cierre'] ? date('d/m/Y H:i', strtotime($h['fecha_cierre'])) : '-' ?></td>
                        <td>$<?= number_format($h['monto_inicial'], 2) ?></td>
                        <td>$<?= number_format($h['monto_final'] ?? 0, 2) ?></td>
                        <td>
                            <span class="badge bg-<?= $h['estado'] === 'abierto' ? 'success' : 'secondary' ?>">
                                <?= $h['estado'] ?>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
