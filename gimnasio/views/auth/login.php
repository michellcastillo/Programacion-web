<?php
require_once __DIR__ . '/../../models/Security.php';
Security::initSession();
if (Security::isLoggedIn()) {
    header('Location: ' . APP_URL . '/views/dashboard/index.php');
    exit;
}
$csrf = Security::csrfToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-building fs-1 text-primary"></i>
                        <h2 class="fw-bold"><?= APP_NAME ?></h2>
                        <p class="text-muted">Sistema de Gestión de Gimnasio</p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?= match($_GET['error']) {
                            'credenciales' => 'Credenciales inválidas. Verifica tu email y contraseña.',
                            'csrf' => 'Error de seguridad. Recarga la página e intenta de nuevo.',
                            default => 'Error al iniciar sesión.'
                        } ?>
                    </div>
                    <?php endif; ?>

                    <form action="<?= APP_URL ?>/controllers/AuthController.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="admin@gimnasio.com" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Ingresa tu contraseña" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </button>
                    </form>

                    <hr class="my-4">
                    <div class="text-center small text-muted">
                        <p>Credenciales de prueba:</p>
                        <p><strong>Admin:</strong> admin@gimnasio.com / Admin123!</p>
                        <p><strong>Recepcionista:</strong> recepcion@gimnasio.com / Admin123!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
