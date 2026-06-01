<?php
require_once __DIR__ . '/Database.php';

class CashRegister
{
    public static function getOpenRegister(): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT cc.*, u.nombre as usuario_nombre
             FROM cortes_caja cc
             JOIN usuarios u ON cc.usuario_id = u.id
             WHERE cc.estado = 'abierto'
             ORDER BY cc.fecha_apertura DESC
             LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    public static function open(int $usuarioId, float $montoInicial, string $observaciones = ''): int
    {
        $existing = self::getOpenRegister();
        if ($existing) {
            throw new Exception("Ya hay una caja abierta. Debe cerrarla antes de abrir una nueva.");
        }

        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO cortes_caja (usuario_id, monto_inicial, monto_final, ingresos, egresos, estado, observaciones)
             VALUES (:usuario_id, :monto_inicial, 0.00, 0.00, 0.00, 'abierto', :observaciones)"
        );
        $stmt->execute([
            ':usuario_id'     => $usuarioId,
            ':monto_inicial'  => $montoInicial,
            ':observaciones'  => $observaciones,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function close(int $corteId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "UPDATE cortes_caja
             SET estado = 'cerrado',
                 fecha_cierre = NOW(),
                 monto_final = monto_inicial + ingresos - egresos
             WHERE id = :id AND estado = 'abierto'"
        );
        $stmt->execute([':id' => $corteId]);
    }

    public static function addIncome(int $corteId, float $monto, string $categoria, string $descripcion = '', ?int $referenciaId = null): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO movimientos_caja (corte_id, tipo, categoria, monto, descripcion, referencia_id)
             VALUES (:corte_id, 'ingreso', :categoria, :monto, :descripcion, :referencia_id)"
        );
        $stmt->execute([
            ':corte_id'      => $corteId,
            ':categoria'     => $categoria,
            ':monto'         => $monto,
            ':descripcion'   => $descripcion,
            ':referencia_id' => $referenciaId,
        ]);

        $updateStmt = $db->prepare(
            "UPDATE cortes_caja SET ingresos = ingresos + :monto WHERE id = :id"
        );
        $updateStmt->execute([':monto' => $monto, ':id' => $corteId]);

        return (int)$db->lastInsertId();
    }

    public static function addExpense(int $corteId, float $monto, string $categoria, string $descripcion = ''): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO movimientos_caja (corte_id, tipo, categoria, monto, descripcion)
             VALUES (:corte_id, 'egreso', :categoria, :monto, :descripcion)"
        );
        $stmt->execute([
            ':corte_id'    => $corteId,
            ':categoria'   => $categoria,
            ':monto'       => $monto,
            ':descripcion' => $descripcion,
        ]);

        $updateStmt = $db->prepare(
            "UPDATE cortes_caja SET egresos = egresos + :monto WHERE id = :id"
        );
        $updateStmt->execute([':monto' => $monto, ':id' => $corteId]);

        return (int)$db->lastInsertId();
    }

    public static function getMovements(int $corteId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT * FROM movimientos_caja WHERE corte_id = :corte_id ORDER BY fecha_movimiento ASC"
        );
        $stmt->execute([':corte_id' => $corteId]);
        return $stmt->fetchAll();
    }

    public static function getHistory(int $limit = 20): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT cc.*, u.nombre as usuario_nombre
             FROM cortes_caja cc
             JOIN usuarios u ON cc.usuario_id = u.id
             ORDER BY cc.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT cc.*, u.nombre as usuario_nombre
             FROM cortes_caja cc
             JOIN usuarios u ON cc.usuario_id = u.id
             WHERE cc.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
