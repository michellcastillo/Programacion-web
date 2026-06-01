-- ============================================================
-- SISTEMA DE GESTIÓN DE GIMNASIO - ESQUEMA DE BASE DE DATOS
-- ============================================================

CREATE DATABASE IF NOT EXISTS gimnasio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gimnasio_db;

-- -----------------------------------------------------------
-- TABLA: roles
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLA: usuarios
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    rol_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLA: clientes
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    direccion TEXT DEFAULT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    eliminado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLA: planes_membresia
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS planes_membresia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    duracion_dias INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLA: membresias (asignación a clientes)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS membresias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    plan_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('activa', 'expirada', 'cancelada') DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES planes_membresia(id)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLA: pagos
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membresia_id INT NOT NULL,
    cliente_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    referencia VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membresia_id) REFERENCES membresias(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLA: cortes_caja
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS cortes_caja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_apertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATETIME DEFAULT NULL,
    monto_inicial DECIMAL(10,2) DEFAULT 0.00,
    monto_final DECIMAL(10,2) DEFAULT NULL,
    ingresos DECIMAL(10,2) DEFAULT 0.00,
    egresos DECIMAL(10,2) DEFAULT 0.00,
    estado ENUM('abierto', 'cerrado') DEFAULT 'abierto',
    observaciones TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLA: movimientos_caja (ingresos/egresos del día)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS movimientos_caja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    corte_id INT NOT NULL,
    tipo ENUM('ingreso', 'egreso') NOT NULL,
    categoria VARCHAR(100) DEFAULT NULL,
    monto DECIMAL(10,2) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    referencia_id INT DEFAULT NULL,
    fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (corte_id) REFERENCES cortes_caja(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLA: auditoria_log
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS auditoria_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- DATOS INICIALES
-- -----------------------------------------------------------
INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso total al sistema'),
('Recepcionista', 'Gestión de clientes, membresías y pagos'),
('Cliente', 'Acceso a su propio perfil y membresía');

INSERT INTO planes_membresia (nombre, descripcion, duracion_dias, precio) VALUES
('Mensual Básico', 'Acceso a todas las áreas del gimnasio por 1 mes', 30, 499.00),
('Trimestral', 'Acceso a todas las áreas por 3 meses', 90, 1299.00),
('Semestral', 'Acceso a todas las áreas por 6 meses', 180, 2299.00),
('Anual', 'Acceso a todas las áreas por 12 meses', 365, 3999.00);

-- Contraseña: Admin123! (cambiar en producción)
INSERT INTO usuarios (nombre, email, password, telefono, rol_id) VALUES
('Administrador', 'admin@gimnasio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0001', 1),
('Recepcionista', 'recepcion@gimnasio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0002', 2);
