<?php
require_once __DIR__ . '/Database.php';

class User
{
    public static function authenticate(string $email, string $password): ?array
{
    $db = Database::getConnection();
    $stmt = $db->prepare(
        "SELECT u.*, r.nombre as rol_nombre
         FROM usuarios u
         JOIN roles r ON u.rol_id = r.id
         WHERE u.email = :email AND u.activo = 1
         LIMIT 1"
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // DEFINICIÓN DE EMERGENCIA: Si no existe la constante, usamos el costo por defecto de PHP (10)
    $costo = defined('BCRYPT_COST') ? BCRYPT_COST : 10;

    if ($user) {
        $loginValido = false;

        // Si la contraseña en BD es texto plano (coincidencia directa tras importar SQL)
        if ($user['password'] === $password) {
            // Generamos el hash nativo de la PC actual
            $newHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => $costo]);
            $updateStmt = $db->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
            $updateStmt->execute([':password' => $newHash, ':id' => $user['id']]);
            
            $user['password'] = $newHash;
            $loginValido = true;
        } 
        // Validación estándar por Hash
        elseif (password_verify($password, $user['password'])) {
            $loginValido = true;
        }

        if ($loginValido) {
            // Re-hashear si cambiaste de servidor y el costo actual difiere
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT, ['cost' => $costo])) {
                $newHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => $costo]);
                $updateStmt = $db->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $updateStmt->execute([':password' => $newHash, ':id' => $user['id']]);
            }

            $stmtUpdate = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
            $stmtUpdate->execute([':id' => $user['id']]);

            return $user;
        }
    }

    return null;
}
    public static function getById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT u.*, r.nombre as rol_nombre
             FROM usuarios u
             JOIN roles r ON u.rol_id = r.id
             WHERE u.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            "SELECT u.*, r.nombre as rol_nombre
             FROM usuarios u
             JOIN roles r ON u.rol_id = r.id
             ORDER BY u.nombre ASC"
        );
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $hash = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => BCRYPT_COST]);
        $stmt = $db->prepare(
            "INSERT INTO usuarios (nombre, email, password, telefono, rol_id)
             VALUES (:nombre, :email, :password, :telefono, :rol_id)"
        );
        $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':email'    => $data['email'],
            ':password' => $hash,
            ':telefono' => $data['telefono'] ?? null,
            ':rol_id'   => $data['rol_id'],
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::getConnection();
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nombre'])) {
            $fields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['telefono'])) {
            $fields[] = "telefono = :telefono";
            $params[':telefono'] = $data['telefono'];
        }
        if (isset($data['rol_id'])) {
            $fields[] = "rol_id = :rol_id";
            $params[':rol_id'] = $data['rol_id'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => BCRYPT_COST]);
        }

        if (empty($fields)) return;

        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE usuarios SET activo = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
