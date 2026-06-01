<?php
require_once __DIR__ . '/../../models/Security.php';
require_once __DIR__ . '/../../models/Client.php';
require_once __DIR__ . '/../../controllers/ClientController.php';

Security::requirePermission('clients');

$clientes = ClientController::index();
$titulo = 'Clientes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-people"></i> Clientes</h2>
    </div>
    <div class="col text-end">
        <a href="<?= APP_URL ?>/views/clients/create.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nuevo Cliente
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Membresía</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No hay clientes registrados</td></tr>
                    <?php else: ?>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td>
                            <?php if ($c['foto']): ?>
                            <img src="<?= APP_URL ?>/public/uploads/<?= $c['foto'] ?>" alt="Foto" class="rounded-circle" width="40" height="40" style="object-fit:cover">
                            <?php else: ?>
                            <i class="bi bi-person-circle fs-3 text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= Security::sanitizeInput($c['nombre'] . ' ' . $c['apellido']) ?></strong></td>
                        <td><?= Security::sanitizeInput($c['email'] ?? '-') ?></td>
                        <td><?= Security::sanitizeInput($c['telefono'] ?? '-') ?></td>
                        <td>
                            <?php
                            $mems = Client::getActiveMemberships($c['id']);
                            $active = array_filter($mems, fn($m) => $m['estado'] === 'activa');
                            echo $active ? '<span class="badge bg-success">' . count($active) . ' activa(s)</span>' : '<span class="badge bg-secondary">Sin membresía</span>';
                            ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $c['activo'] ? 'success' : 'secondary' ?>">
                                <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/views/clients/show.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= APP_URL ?>/views/clients/edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?= APP_URL ?>/controllers/ClientController.php?action=delete&id=<?= $c['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este cliente?');">
                                <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
