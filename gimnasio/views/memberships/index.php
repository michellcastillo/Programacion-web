<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../models/Membership.php';
Security::requirePermission('memberships');

Membership::expireOld();
$membresias = Membership::getAllActive();
$planes = Membership::getAllPlans();

$titulo = 'Membresías';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-card-checklist"></i> Membresías Activas</h2>
    </div>
    <div class="col text-end">
        <a href="<?= APP_URL ?>/views/memberships/assign.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Asignar Membresía
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($planes as $p): ?>
    <div class="col-md-3">
        <div class="card border-primary shadow-sm h-100">
            <div class="card-body text-center">
                <h5 class="card-title"><?= Security::sanitizeInput($p['nombre']) ?></h5>
                <h2 class="text-primary fw-bold">$<?= number_format($p['precio'], 2) ?></h2>
                <p class="text-muted"><?= $p['duracion_dias'] ?> días</p>
                <p class="small"><?= Security::sanitizeInput($p['descripcion'] ?? '') ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Listado de Membresías Activas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Cliente</th><th>Plan</th><th>Inicio</th><th>Fin</th><th>Días Restantes</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($membresias)): ?>
                    <tr><td colspan="6" class="text-center py-3 text-muted">No hay membresías activas</td></tr>
                    <?php else: ?>
                    <?php foreach ($membresias as $m): ?>
                    <?php
                        $daysLeft = (strtotime($m['fecha_fin']) - time()) / 86400;
                        $badge = $daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success');
                    ?>
                    <tr>
                        <td><strong><?= Security::sanitizeInput($m['cliente_nombre'] . ' ' . $m['cliente_apellido']) ?></strong></td>
                        <td><?= Security::sanitizeInput($m['plan_nombre']) ?></td>
                        <td><?= date('d/m/Y', strtotime($m['fecha_inicio'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($m['fecha_fin'])) ?></td>
                        <td><span class="badge bg-<?= $badge ?>"><?= max(0, round($daysLeft)) ?> días</span></td>
                        <td><span class="badge bg-success">Activa</span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
