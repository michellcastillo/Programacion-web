<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $titulo ?? 'Panel de Control' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/style.css" rel="stylesheet">
</head>
<body>
<?php if (Security::isLoggedIn()): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= APP_URL ?>/views/dashboard/index.php">
            <i class="bi bi-building"></i> <?= APP_NAME ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (Security::hasPermission('dashboard')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/views/dashboard/index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <?php endif; ?>
                <?php if (Security::hasPermission('clients')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/views/clients/index.php">Listado</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/views/clients/create.php">Nuevo Cliente</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if (Security::hasPermission('memberships')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-card-checklist"></i> Membresías
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/views/memberships/index.php">Todas</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/views/memberships/assign.php">Asignar</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if (Security::hasPermission('payments')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/views/payments/index.php">
                        <i class="bi bi-cash-stack"></i> Pagos
                    </a>
                </li>
                <?php endif; ?>
                <?php if (Security::hasPermission('cash-register')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-box"></i> Caja
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/views/cash-register/index.php">Cortes</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/views/cash-register/open.php">Abrir Caja</a></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/views/cash-register/operations.php">Operaciones</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if (Security::hasPermission('reports') && isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'Administrador'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/views/reports/index.php">
                        <i class="bi bi-graph-up"></i> Reportes
                     </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= Security::sanitizeInput($_SESSION['user']['nombre'] ?? 'Usuario') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted">
                            <i class="bi bi-shield-lock"></i> <?= Security::sanitizeInput($_SESSION['user']['rol_nombre'] ?? 'Sin Rol') ?>
                        </span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/controllers/AuthController.php?action=logout">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="container-fluid mt-3">
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Security::sanitizeInput(match($_GET['success']) {
            'created' => 'Registro creado exitosamente.',
            'updated' => 'Registro actualizado exitosamente.',
            'deleted' => 'Registro eliminado exitosamente.',
            'assigned' => 'Membresía asignada exitosamente.',
            'cancelled' => 'Membresía cancelada.',
            'opened' => 'Caja abierta exitosamente.',
            'closed' => 'Caja cerrada exitosamente.',
            'moved' => 'Movimiento registrado exitosamente.',
            default => 'Operación exitosa.'
        }) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Security::sanitizeInput(match($_GET['error']) {
            'required' => 'Por favor completa todos los campos requeridos.',
            'notfound' => 'Registro no encontrado.',
            'csrf' => 'Error de seguridad. Intenta de nuevo.',
            'credenciales' => 'Credenciales inválidas.',
            'invalid_amount' => 'Monto inválido.',
            'already_open' => 'Ya hay una caja abierta.',
            'closed' => 'La caja está cerrada.',
            default => 'Ocurrió un error.'
        }) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
