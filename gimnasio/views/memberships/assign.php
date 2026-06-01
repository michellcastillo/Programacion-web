<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../models/Membership.php';
require_once __DIR__ . '/../../controllers/ClientController.php';
Security::requirePermission('memberships');

$planes = Membership::getAllPlans();
$clientes = ClientController::index();

$titulo = 'Asignar Membresía';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-plus-circle"></i> Asignar Membresía</h2>
    </div>
    <div class="col text-end">
        <a href="<?= APP_URL ?>/views/memberships/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form action="<?= APP_URL ?>/controllers/MembershipController.php?action=assign" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                    <select class="form-select" id="cliente_id" name="cliente_id" required>
                        <option value="">Seleccionar cliente...</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= Security::sanitizeInput($c['nombre'] . ' ' . $c['apellido']) ?> (<?= Security::sanitizeInput($c['email'] ?? '') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="plan_id" class="form-label">Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="plan_id" name="plan_id" required>
                        <option value="">Seleccionar plan...</option>
                        <?php foreach ($planes as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= Security::sanitizeInput($p['nombre']) ?> - $<?= number_format($p['precio'], 2) ?> (<?= $p['duracion_dias'] ?> días)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-6">
                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                    <select class="form-select" id="metodo_pago" name="metodo_pago">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle"></i> Asignar y Registrar Pago
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
