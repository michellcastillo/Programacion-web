<?php
require_once __DIR__ . '/Database.php';

class Client
{
    public static function getAll(bool $includeDeleted = false): array
    {
        $db = Database::getConnection();
        $sql = "SELECT * FROM clientes WHERE 1=1";
        if (!$includeDeleted) {
            $sql .= " AND eliminado = 0";
        }
        $sql .= " ORDER BY nombre ASC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM clientes WHERE id = :id AND eliminado = 0");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function search(string $term): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT * FROM clientes
             WHERE eliminado = 0
             AND (nombre LIKE :term OR apellido LIKE :term2 OR email LIKE :term3 OR telefono LIKE :term4)
             ORDER BY nombre ASC
             LIMIT 20"
        );
        $likeTerm = "%{$term}%";
        $stmt->execute([
            ':term'  => $likeTerm,
            ':term2' => $likeTerm,
            ':term3' => $likeTerm,
            ':term4' => $likeTerm,
        ]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO clientes (nombre, apellido, email, telefono, direccion, fecha_nacimiento, foto)
             VALUES (:nombre, :apellido, :email, :telefono, :direccion, :fecha_nacimiento, :foto)"
        );
        $stmt->execute([
            ':nombre'           => $data['nombre'],
            ':apellido'         => $data['apellido'],
            ':email'            => $data['email'] ?? null,
            ':telefono'         => $data['telefono'] ?? null,
            ':direccion'        => $data['direccion'] ?? null,
            ':fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            ':foto'             => $data['foto'] ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::getConnection();
        $fields = [];
        $params = [':id' => $id];

        foreach (['nombre', 'apellido', 'email', 'telefono', 'direccion', 'fecha_nacimiento', 'foto'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) return;

        $sql = "UPDATE clientes SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    public static function softDelete(int $id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE clientes SET eliminado = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function getActiveMemberships(int $clientId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT m.*, p.nombre as plan_nombre, p.precio as plan_precio
             FROM membresias m
             JOIN planes_membresia p ON m.plan_id = p.id
             WHERE m.cliente_id = :cliente_id
             ORDER BY m.created_at DESC"
        );
        $stmt->execute([':cliente_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function getPaymentHistory(int $clientId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT p.*, m.fecha_inicio, m.fecha_fin, pl.nombre as plan_nombre
             FROM pagos p
             JOIN membresias m ON p.membresia_id = m.id
             JOIN planes_membresia pl ON m.plan_id = pl.id
             WHERE p.cliente_id = :cliente_id
             ORDER BY p.fecha_pago DESC"
        );
        $stmt->execute([':cliente_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function countActive(): int
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1 AND eliminado = 0");
        return (int)$stmt->fetch()['total'];
    }
}
