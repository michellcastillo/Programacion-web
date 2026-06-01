<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../controllers/ReportController.php';
require_once __DIR__ . '/../../models/AuditLog.php';
Security::requirePermission('reports');

$stats = ReportController::getDashboard();
$monthlyIncome = ReportController::getMonthlyIncome(12);
$expiring = ReportController::getExpiringMemberships();
$auditLogs = AuditLog::getRecent(20);

$titulo = 'Reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-graph-up"></i> Reportes y Estadísticas</h2>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-primary border-start border-4 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Clientes Activos</h6>
                <h3 class="fw-bold text-primary"><?= $stats['clientes_activos'] ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success border-start border-4 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Membresías Activas</h6>
                <h3 class="fw-bold text-success"><?= $stats['membresias_activas'] ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning border-start border-4 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Ingresos del Mes</h6>
                <h3 class="fw-bold text-warning">$<?= number_format($stats['ingresos_mes'], 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger border-start border-4 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Por Vencer (7d)</h6>
                <h3 class="fw-bold text-danger"><?= $stats['membresias_por_vencer'] ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-cash"></i> Ingresos Mensuales (12 meses)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Mes</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthlyIncome)): ?>
                            <tr><td colspan="2" class="text-center py-3 text-muted">Sin datos</td></tr>
                            <?php else: ?>
                            <?php foreach ($monthlyIncome as $m): ?>
                            <tr>
                                <td><?= date('F Y', strtotime($m['mes'] . '-01')) ?></td>
                                <td class="fw-bold text-success">$<?= number_format($m['total'], 2) ?></td>
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
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Membresías por Vencer</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Cliente</th><th>Plan</th><th>Vence</th><th>Contacto</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($expiring)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Sin membresías por vencer</td></tr>
                            <?php else: ?>
                            <?php foreach ($expiring as $e): ?>
                            <tr>
                                <td><?= Security::sanitizeInput($e['cliente_nombre'] . ' ' . $e['cliente_apellido']) ?></td>
                                <td><?= Security::sanitizeInput($e['plan_nombre']) ?></td>
                                <td><span class="badge bg-warning"><?= date('d/m/Y', strtotime($e['fecha_fin'])) ?></span></td>
                                <td>
                                    <small><?= Security::sanitizeInput($e['cliente_telefono'] ?? '') ?>
                                    <?= $e['cliente_email'] ? '<br>' . Security::sanitizeInput($e['cliente_email']) : '' ?></small>
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
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Auditoría de Seguridad</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Descripción</th><th>IP</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($auditLogs as $log): ?>
                    <tr>
                        <td class="small"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                        <td class="small"><?= Security::sanitizeInput($log['usuario_nombre'] ?? 'Sistema') ?></td>
                        <td><span class="badge bg-secondary"><?= Security::sanitizeInput($log['accion']) ?></span></td>
                        <td class="small"><?= Security::sanitizeInput($log['descripcion'] ?? '') ?></td>
                        <td class="small"><?= $log['ip_address'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
