<?php
require_once __DIR__ . '/Database.php';

class Membership
{
    public static function getAllPlans(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM planes_membresia WHERE activo = 1 ORDER BY precio ASC");
        return $stmt->fetchAll();
    }

    public static function getPlanById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM planes_membresia WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function createPlan(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO planes_membresia (nombre, descripcion, duracion_dias, precio)
             VALUES (:nombre, :descripcion, :duracion_dias, :precio)"
        );
        $stmt->execute([
            ':nombre'        => $data['nombre'],
            ':descripcion'   => $data['descripcion'] ?? null,
            ':duracion_dias' => $data['duracion_dias'],
            ':precio'        => $data['precio'],
        ]);
        return (int)$db->lastInsertId();
    }

    public static function assignToClient(int $clientId, int $planId, string $startDate): int
    {
        $db = Database::getConnection();
        $plan = self::getPlanById($planId);
        if (!$plan) {
            throw new Exception("Plan no encontrado.");
        }

        $endDate = date('Y-m-d', strtotime($startDate . " + {$plan['duracion_dias']} days"));

        $stmt = $db->prepare(
            "INSERT INTO membresias (cliente_id, plan_id, fecha_inicio, fecha_fin, estado)
             VALUES (:cliente_id, :plan_id, :fecha_inicio, :fecha_fin, 'activa')"
        );
        $stmt->execute([
            ':cliente_id'   => $clientId,
            ':plan_id'      => $planId,
            ':fecha_inicio' => $startDate,
            ':fecha_fin'    => $endDate,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function getAllActive(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT m.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                    p.nombre as plan_nombre, p.precio as plan_precio
             FROM membresias m
             JOIN clientes c ON m.cliente_id = c.id
             JOIN planes_membresia p ON m.plan_id = p.id
             WHERE m.estado = 'activa'
             ORDER BY m.fecha_fin ASC"
        );
        return $stmt->fetchAll();
    }

    public static function getExpiringSoon(int $days = 7): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT m.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                    c.telefono as cliente_telefono, c.email as cliente_email,
                    p.nombre as plan_nombre
             FROM membresias m
             JOIN clientes c ON m.cliente_id = c.id
             JOIN planes_membresia p ON m.plan_id = p.id
             WHERE m.estado = 'activa'
             AND m.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY m.fecha_fin ASC"
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getByClient(int $clientId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT m.*, p.nombre as plan_nombre, p.precio as plan_precio, p.duracion_dias
             FROM membresias m
             JOIN planes_membresia p ON m.plan_id = p.id
             WHERE m.cliente_id = :cliente_id
             ORDER BY m.created_at DESC"
        );
        $stmt->execute([':cliente_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function cancel(int $id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE membresias SET estado = 'cancelada' WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function expireOld(): int
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "UPDATE membresias SET estado = 'expirada'
             WHERE estado = 'activa' AND fecha_fin < CURDATE()"
        );
        return $stmt->rowCount();
    }

    public static function countActive(): int
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) as total FROM membresias WHERE estado = 'activa'");
        return (int)$stmt->fetch()['total'];
    }

    public static function getMonthlyIncome(): float
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT COALESCE(SUM(monto), 0) as total
             FROM pagos
             WHERE MONTH(fecha_pago) = MONTH(CURDATE())
             AND YEAR(fecha_pago) = YEAR(CURDATE())"
        );
        return (float)$stmt->fetch()['total'];
    }
}
