<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/app.php';

class AuditLog
{
    public static function register(string $action, string $description = '', ?int $userId = null): void
    {
        $db = Database::getConnection();
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        try {
            $stmt = $db->prepare(
                "INSERT INTO auditoria_log (usuario_id, accion, descripcion, ip_address, user_agent, created_at)
                 VALUES (:usuario_id, :accion, :descripcion, :ip_address, :user_agent, NOW())"
            );
            $stmt->execute([
                ':usuario_id'  => $userId,
                ':accion'      => $action,
                ':descripcion' => $description,
                ':ip_address'  => $ip,
                ':user_agent'  => $userAgent,
            ]);
        } catch (PDOException $e) {
            self::writeToFile($action, $description, $userId, $ip);
        }
    }

    private static function writeToFile(string $action, string $description, ?int $userId, string $ip): void
    {
        $logDir = LOG_DIR;
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/security.log';
        $timestamp = date('Y-m-d H:i:s');
        $userIdStr = $userId ?? '0';
        $logEntry = "[$timestamp] [USUARIO:$userIdStr] [IP:$ip] [$action] $description" . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public static function getRecent(int $limit = 50): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT l.*, u.nombre as usuario_nombre
             FROM auditoria_log l
             LEFT JOIN usuarios u ON l.usuario_id = u.id
             ORDER BY l.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
