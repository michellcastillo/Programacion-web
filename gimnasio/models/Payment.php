<?php
require_once __DIR__ . '/Database.php';

class Payment
{
    public static function register(int $membresiaId, int $clienteId, float $monto, string $metodo, ?string $referencia = null): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO pagos (membresia_id, cliente_id, monto, metodo_pago, referencia, fecha_pago)
             VALUES (:membresia_id, :cliente_id, :monto, :metodo_pago, :referencia, NOW())"
        );
        $stmt->execute([
            ':membresia_id' => $membresiaId,
            ':cliente_id'   => $clienteId,
            ':monto'        => $monto,
            ':metodo_pago'  => $metodo,
            ':referencia'   => $referencia,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function getAll(int $limit = 50): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT p.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                    pl.nombre as plan_nombre
             FROM pagos p
             JOIN clientes c ON p.cliente_id = c.id
             JOIN membresias m ON p.membresia_id = m.id
             JOIN planes_membresia pl ON m.plan_id = pl.id
             ORDER BY p.fecha_pago DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getByClient(int $clientId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT p.*, pl.nombre as plan_nombre, m.fecha_inicio, m.fecha_fin
             FROM pagos p
             JOIN membresias m ON p.membresia_id = m.id
             JOIN planes_membresia pl ON m.plan_id = pl.id
             WHERE p.cliente_id = :cliente_id
             ORDER BY p.fecha_pago DESC"
        );
        $stmt->execute([':cliente_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function getTodayPayments(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT p.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido
             FROM pagos p
             JOIN clientes c ON p.cliente_id = c.id
             WHERE DATE(p.fecha_pago) = CURDATE()
             ORDER BY p.fecha_pago DESC"
        );
        return $stmt->fetchAll();
    }

    public static function getTodayTotal(): float
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT COALESCE(SUM(monto), 0) as total
             FROM pagos
             WHERE DATE(fecha_pago) = CURDATE()"
        );
        return (float)$stmt->fetch()['total'];
    }
}
