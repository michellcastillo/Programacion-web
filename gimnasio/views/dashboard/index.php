<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../controllers/ReportController.php';
Security::requireAuth();

$stats = ReportController::getDashboard();
$expiring = ReportController::getExpiringMemberships();

$titulo = 'Dashboard';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
        <p class="text-muted">Resumen general del sistema</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-primary border-start border-4 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Clientes Activos</h6>
                        <h3 class="fw-bold mb-0"><?= $stats['clientes_activos'] ?></h3>
                    </div>
                    <i class="bi bi-people fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success border-start border-4 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Membresías Activas</h6>
                        <h3 class="fw-bold mb-0"><?= $stats['membresias_activas'] ?></h3>
                    </div>
                    <i class="bi bi-card-checklist fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning border-start border-4 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Ingresos del Mes</h6>
                        <h3 class="fw-bold mb-0">$<?= number_format($stats['ingresos_mes'], 2) ?></h3>
                    </div>
                    <i class="bi bi-cash-stack fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger border-start border-4 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Por Vencer (7 días)</h6>
                        <h3 class="fw-bold mb-0"><?= $stats['membresias_por_vencer'] ?></h3>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock"></i> Membresías por Vencer</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Plan</th>
                                <th>Vence</th>
                                <th>Días</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($expiring)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">No hay membresías por vencer</td></tr>
                            <?php else: ?>
                            <?php foreach ($expiring as $m): ?>
                            <tr>
                                <td><?= Security::sanitizeInput($m['cliente_nombre'] . ' ' . $m['cliente_apellido']) ?></td>
                                <td><?= Security::sanitizeInput($m['plan_nombre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($m['fecha_fin'])) ?></td>
                                <td>
                                    <?php
                                    $daysLeft = (strtotime($m['fecha_fin']) - time()) / 86400;
                                    $badge = $daysLeft <= 3 ? 'danger' : 'warning';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= max(0, round($daysLeft)) ?> días</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-cash"></i> Ingresos Hoy</h5>
            </div>
            <div class="card-body text-center py-4">
                <h1 class="display-4 fw-bold text-success">$<?= number_format($stats['ingresos_hoy'], 2) ?></h1>
                <p class="text-muted">Total de ingresos del día de hoy</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
