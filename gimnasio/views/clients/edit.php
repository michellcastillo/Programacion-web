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

$titulo = 'Editar Cliente';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-pencil-square"></i> Editar Cliente</h2>
    </div>
    <div class="col text-end">
        <a href="<?= APP_URL ?>/views/clients/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form action="<?= APP_URL ?>/controllers/ClientController.php?action=update&id=<?= $id ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= Security::sanitizeInput($cliente['nombre']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?= Security::sanitizeInput($cliente['apellido']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= Security::sanitizeInput($cliente['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?= Security::sanitizeInput($cliente['telefono'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $cliente['fecha_nacimiento'] ?? '' ?>">
                </div>
                <div class="col-md-6">
                    <label for="foto" class="form-label">Foto de Perfil</label>
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                    <?php if ($cliente['foto']): ?>
                    <div class="mt-2">
                        <img src="<?= APP_URL ?>/public/uploads/<?= $cliente['foto'] ?>" alt="Foto actual" height="60" class="rounded">
                        <small class="text-muted ms-2">Foto actual. Sube una nueva para reemplazarla.</small>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label for="direccion" class="form-label">Dirección</label>
                    <textarea class="form-control" id="direccion" name="direccion" rows="2"><?= Security::sanitizeInput($cliente['direccion'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Actualizar Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
