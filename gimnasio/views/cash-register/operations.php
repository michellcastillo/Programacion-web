<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../models/CashRegister.php';
Security::requirePermission('cash-register');

$openRegister = CashRegister::getOpenRegister();
if (!$openRegister) {
    header('Location: ' . APP_URL . '/views/cash-register/open.php');
    exit;
}

$movements = CashRegister::getMovements($openRegister['id']);

$titulo = 'Operaciones de Caja';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-pencil-square"></i> Operaciones de Caja</h2>
    </div>
    <div class="col text-end">
        <a href="<?= APP_URL ?>/views/cash-register/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body">
                <h6>Monto Inicial</h6>
                <h3 class="fw-bold">$<?= number_format($openRegister['monto_inicial'], 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body">
                <h6>Ingresos</h6>
                <h3 class="fw-bold">$<?= number_format($openRegister['ingresos'], 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow-sm">
            <div class="card-body">
                <h6>Egresos</h6>
                <h3 class="fw-bold">$<?= number_format($openRegister['egresos'], 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body">
                <h6>Saldo Actual</h6>
                <h3 class="fw-bold">$<?= number_format($openRegister['monto_inicial'] + $openRegister['ingresos'] - $openRegister['egresos'], 2) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle text-success"></i> Registrar Movimiento</h5>
            </div>
            <div class="card-body">
                <form action="<?= APP_URL ?>/controllers/CashRegisterController.php?action=addMovement" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
                    <input type="hidden" name="corte_id" value="<?= $openRegister['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo" id="tipo_ingreso" value="ingreso" checked>
                            <label class="btn btn-outline-success" for="tipo_ingreso"><i class="bi bi-plus-circle"></i> Ingreso</label>
                            <input type="radio" class="btn-check" name="tipo" id="tipo_egreso" value="egreso">
                            <label class="btn btn-outline-danger" for="tipo_egreso"><i class="bi bi-dash-circle"></i> Egreso</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" placeholder="Ej: Membresía, Venta, Servicio..." required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Registrar Movimiento
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list"></i> Movimientos</h5>
                <form action="<?= APP_URL ?>/controllers/CashRegisterController.php?action=close" method="POST"
                      onsubmit="return confirm('¿Seguro que deseas cerrar la caja? Se calculará el total final.');">
                    <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
                    <input type="hidden" name="corte_id" value="<?= $openRegister['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Caja
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Hora</th><th>Tipo</th><th>Categoría</th><th>Monto</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movements)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Sin movimientos en esta caja</td></tr>
                            <?php else: ?>
                            <?php foreach ($movements as $mov): ?>
                            <tr>
                                <td><?= date('H:i', strtotime($mov['fecha_movimiento'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $mov['tipo'] === 'ingreso' ? 'success' : 'danger' ?>">
                                        <?= $mov['tipo'] ?>
                                    </span>
                                </td>
                                <td><?= Security::sanitizeInput($mov['categoria']) ?></td>
                                <td class="fw-bold text-<?= $mov['tipo'] === 'ingreso' ? 'success' : 'danger' ?>">
                                    <?= $mov['tipo'] === 'ingreso' ? '+' : '-' ?>$<?= number_format($mov['monto'], 2) ?>
                                </td>
                                <td class="small"><?= Security::sanitizeInput($mov['descripcion'] ?? '-') ?></td>
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
