<?php
require_once __DIR__ . '/Database.php';

class Report
{
    public static function getDashboardStats(): array
    {
        $db = Database::getConnection();

        $clientesActivos = $db->query(
            "SELECT COUNT(*) as total FROM clientes WHERE activo = 1 AND eliminado = 0"
        )->fetch()['total'];

        $membresiasActivas = $db->query(
            "SELECT COUNT(*) as total FROM membresias WHERE estado = 'activa'"
        )->fetch()['total'];

        $ingresosMes = $db->query(
            "SELECT COALESCE(SUM(monto), 0) as total
             FROM pagos
             WHERE MONTH(fecha_pago) = MONTH(CURDATE())
             AND YEAR(fecha_pago) = YEAR(CURDATE())"
        )->fetch()['total'];

        $membresiasPorVencer = $db->query(
            "SELECT COUNT(*) as total
             FROM membresias
             WHERE estado = 'activa'
             AND fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        )->fetch()['total'];

        $ingresosHoy = $db->query(
            "SELECT COALESCE(SUM(monto), 0) as total
             FROM pagos
             WHERE DATE(fecha_pago) = CURDATE()"
        )->fetch()['total'];

        return [
            'clientes_activos'      => (int)$clientesActivos,
            'membresias_activas'    => (int)$membresiasActivas,
            'ingresos_mes'          => (float)$ingresosMes,
            'membresias_por_vencer' => (int)$membresiasPorVencer,
            'ingresos_hoy'          => (float)$ingresosHoy,
        ];
    }

    public static function getMonthlyIncome(int $months = 12): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT DATE_FORMAT(fecha_pago, '%Y-%m') as mes,
                    COALESCE(SUM(monto), 0) as total
             FROM pagos
             WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
             ORDER BY mes ASC"
        );
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getRecentMemberships(int $limit = 10): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT m.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                    p.nombre as plan_nombre
             FROM membresias m
             JOIN clientes c ON m.cliente_id = c.id
             JOIN planes_membresia p ON m.plan_id = p.id
             ORDER BY m.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getMembershipPlanStats(): array
    {
        $db = Database::getConnection();
        return $db->query(
            "SELECT p.nombre, COUNT(m.id) as total
             FROM planes_membresia p
             LEFT JOIN membresias m ON p.id = m.plan_id AND m.estado = 'activa'
             GROUP BY p.id, p.nombre
             ORDER BY total DESC"
        )->fetchAll();
    }
}
