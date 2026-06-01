<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../models/CashRegister.php';
Security::requirePermission('cash-register');

$openRegister = CashRegister::getOpenRegister();
if ($openRegister) {
    header('Location: ' . APP_URL . '/views/cash-register/operations.php');
    exit;
}

$titulo = 'Abrir Caja';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-box-arrow-in-right"></i> Abrir Caja</h2>
    </div>
    <div class="col text-end">
        <a href="<?= APP_URL ?>/views/cash-register/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form action="<?= APP_URL ?>/controllers/CashRegisterController.php?action=open" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="monto_inicial" class="form-label">Monto Inicial <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="monto_inicial" name="monto_inicial"
                               step="0.01" min="0" value="0.00" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2"
                              placeholder="Notas sobre la apertura de caja..."></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle"></i> Abrir Caja
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
