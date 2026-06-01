<?php
require_once __DIR__ . '/../../models/Security.php';
Security::requirePermission('clients');

$titulo = 'Nuevo Cliente';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-person-plus"></i> Nuevo Cliente</h2>
    </div>
    <div class="col text-end">
        <a href="<?= APP_URL ?>/views/clients/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form action="<?= APP_URL ?>/controllers/ClientController.php?action=store" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="col-md-6">
                    <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono">
                </div>
                <div class="col-md-6">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                </div>
                <div class="col-md-6">
                    <label for="foto" class="form-label">Foto de Perfil</label>
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">Formatos: JPG, PNG, WebP. Máximo 5MB.</div>
                </div>
                <div class="col-12">
                    <label for="direccion" class="form-label">Dirección</label>
                    <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
