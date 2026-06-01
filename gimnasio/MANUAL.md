# GymManager - Sistema de Gestión de Gimnasio

## Documentación Técnica y Manual de Usuario

---

## Índice

1. [Guía de Instalación](#1-guía-de-instalación)
2. [Estructura del Proyecto](#2-estructura-del-proyecto)
3. [Manual de Uso](#3-manual-de-uso)
4. [Arquitectura de Seguridad](#4-arquitectura-de-seguridad)
5. [API de Referencia](#5-api-de-referencia)

---

## 1. Guía de Instalación

### 1.1 Requisitos del Sistema

- **Servidor Web:** Apache 2.4+ con mod_rewrite habilitado
- **PHP:** 8.1 o superior
- **MySQL:** 5.7+ o MariaDB 10.3+
- **Extensiones PHP:** PDO, PDO_MySQL, mbstring, fileinfo, openssl

### 1.2 Base de Datos

1. Abre tu gestor de base de datos (phpMyAdmin, MySQL Workbench, o línea de comandos).
2. Ejecuta el archivo `database.sql` incluido en la raíz del proyecto.

   ```bash
   mysql -u root -p < database.sql
   ```

   O importa el archivo `database.sql` desde phpMyAdmin.

3. El script crea la base de datos `gimnasio_db` con todas las tablas necesarias y datos de prueba.

### 1.3 Configuración de la Conexión

1. Abre el archivo `config/database.php` y ajusta los parámetros según tu entorno:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gimnasio_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_CHARSET', 'utf8mb4');
   ```

2. Opcionalmente, puedes definir estas variables como variables de entorno:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
   - `APP_URL`

### 1.4 Configuración del Servidor Web

#### Apache
Asegúrate de que el módulo `mod_rewrite` esté habilitado. El archivo `.htaccess` en la raíz redirige todas las peticiones a `public/index.php`.

#### Estructura de URLs
La aplicación funciona con URLs directas del tipo:
```
http://localhost/gimnasio/views/auth/login.php
http://localhost/gimnasio/views/dashboard/index.php
http://localhost/gimnasio/controllers/AuthController.php?action=login
```

### 1.5 Verificación

1. Accede a `http://localhost/gimnasio/` desde tu navegador.
2. Deberías ver la pantalla de inicio de sesión.
3. Usa las credenciales de prueba:

   | Rol | Email | Contraseña |
   |-----|-------|-----------|
   | Administrador | admin@gimnasio.com | Admin123! |
   | Recepcionista | recepcion@gimnasio.com | Admin123! |

---

## 2. Estructura del Proyecto

```
gimnasio/
├── config/
│   ├── database.php       # Configuración de conexión MySQL
│   └── app.php            # Constantes de la aplicación (roles, permisos, rutas)
├── models/
│   ├── Database.php       # Conexión PDO (Singleton)
│   ├── Security.php       # Clase de seguridad: sesiones, CSRF, validación, archivos
│   ├── AuditLog.php       # Registro de auditoría (BD y archivo)
│   ├── User.php           # Modelo de usuarios y autenticación
│   ├── Client.php         # CRUD de clientes
│   ├── Membership.php     # Planes y asignación de membresías
│   ├── Payment.php        # Registro de pagos
│   ├── CashRegister.php   # Cortes de caja y movimientos
│   └── Report.php         # Estadísticas y reportes
├── controllers/
│   ├── AuthController.php         # Login/Logout
│   ├── ClientController.php       # CRUD clientes
│   ├── MembershipController.php   # Asignar/cancelar membresías
│   ├── CashRegisterController.php # Abrir/cerrar caja, movimientos
│   └── ReportController.php       # Datos para dashboard
├── views/
│   ├── layouts/
│   │   ├── header.php     # Navbar, menú, alertas
│   │   └── footer.php     # Scripts y cierre
│   ├── auth/
│   │   └── login.php      # Pantalla de inicio de sesión
│   ├── dashboard/
│   │   └── index.php      # Panel principal con estadísticas
│   ├── clients/
│   │   ├── index.php      # Listado de clientes
│   │   ├── create.php     # Formulario de creación
│   │   ├── edit.php       # Formulario de edición
│   │   └── show.php       # Detalle del cliente
│   ├── memberships/
│   │   ├── index.php      # Membresías activas y planes
│   │   └── assign.php     # Asignar membresía a cliente
│   ├── payments/
│   │   └── index.php      # Historial de pagos
│   ├── cash-register/
│   │   ├── index.php      # Historial de cortes
│   │   ├── open.php       # Abrir caja
│   │   └── operations.php # Operaciones y cierre
│   └── reports/
│       └── index.php      # Reportes y auditoría
├── public/
│   ├── index.php          # Punto de entrada
│   ├── css/
│   │   └── style.css      # Estilos personalizados
│   ├── js/
│   │   └── app.js         # JavaScript del frontend
│   └── uploads/
│       ├── .htaccess      # Protege el directorio pero permite imágenes
│       └── clients/       # Fotos de perfil de clientes
├── logs/
│   └── .htaccess          # Deniega acceso directo
├── .htaccess              # Rewrite rules y protección
├── database.sql           # Esquema de base de datos
└── MANUAL.md              # Este documento
```

---

## 3. Manual de Uso

### 3.1 Inicio de Sesión

1. Accede a la URL del sistema.
2. Ingresa tu **correo electrónico** y **contraseña**.
3. Haz clic en **Iniciar Sesión**.
4. El sistema te redirigirá al Dashboard según tu rol.

**Seguridad:**
- Las contraseñas se verifican contra hashes bcrypt.
- Se genera un nuevo ID de sesión en cada inicio de sesión.
- Los intentos fallidos se registran en el log de auditoría.

### 3.2 Dashboard

El Dashboard muestra un resumen general:
- **Clientes Activos:** Total de clientes registrados y activos.
- **Membresías Activas:** Membresías vigentes actualmente.
- **Ingresos del Mes:** Suma total de pagos del mes actual.
- **Por Vencer:** Membresías que expiran en los próximos 7 días.
- **Ingresos de Hoy:** Total recaudado el día de hoy.

### 3.3 Gestión de Clientes

#### Listado de Clientes
- Muestra todos los clientes con foto, nombre, email, teléfono y estado de membresía.
- Acciones disponibles: Ver, Editar, Eliminar (borrado lógico).

#### Crear Cliente
1. Haz clic en **Nuevo Cliente**.
2. Completa los campos obligatorios (Nombre y Apellido).
3. Opcionalmente, agrega foto de perfil, email, teléfono, dirección y fecha de nacimiento.
4. Haz clic en **Guardar Cliente**.

#### Editar Cliente
1. Haz clic en el icono de lápiz en el listado.
2. Modifica los campos necesarios.
3. Puedes cambiar la foto de perfil subiendo un nuevo archivo.
4. Haz clic en **Actualizar Cliente**.

#### Ver Cliente
- Muestra la información completa del cliente.
- Lista sus membresías activas e historial de pagos.

### 3.4 Membresías y Pagos

#### Planes Disponibles
El sistema incluye 4 planes predefinidos:
- **Mensual Básico:** 30 días / $499
- **Trimestral:** 90 días / $1,299
- **Semestral:** 180 días / $2,299
- **Anual:** 365 días / $3,999

#### Asignar Membresía
1. Ve a **Membresías > Asignar**.
2. Selecciona el **Cliente** y el **Plan**.
3. Define la **Fecha de Inicio** y el **Método de Pago**.
4. Haz clic en **Asignar y Registrar Pago**.
5. El sistema registra automáticamente el pago y, si hay una caja abierta, agrega el ingreso.

#### Historial de Pagos
- Muestra todos los pagos registrados, ordenados del más reciente al más antiguo.
- Incluye: fecha, cliente, plan, monto, método de pago y referencia.

### 3.5 Cortes de Caja

#### Abrir Caja
1. Ve a **Caja > Abrir Caja**.
2. Ingresa el **Monto Inicial** (puede ser 0).
3. Opcionalmente, agrega observaciones.
4. Haz clic en **Abrir Caja**.

#### Operaciones de Caja
- **Registrar Ingreso/Egreso:** Selecciona el tipo, ingresa monto, categoría y descripción.
- Los movimientos se muestran en la tabla de la derecha.
- El panel superior muestra el resumen: inicial, ingresos, egresos y saldo actual.

#### Cerrar Caja
1. Haz clic en **Cerrar Caja**.
2. Confirma la operación. El sistema calcula automáticamente el monto final.
3. Una vez cerrada, no se pueden agregar más movimientos.

### 3.6 Reportes y Auditoría

- **Estadísticas:** Muestra los mismos indicadores del Dashboard.
- **Ingresos Mensuales:** Tabla con ingresos de los últimos 12 meses.
- **Membresías por Vencer:** Lista de membresías próximas a expirar con datos de contacto.
- **Auditoría de Seguridad:** Registro de eventos importantes (logins, accesos denegados, altas/bajas).

---

## 4. Arquitectura de Seguridad

### 4.1 Protección de Contraseñas (Hashing)

Todas las contraseñas se almacenan usando `password_hash()` con `PASSWORD_DEFAULT` (bcrypt).

**Implementación:**
- `models/User.php:68` - Hash al crear usuario.
- `models/User.php:12` - Verificación con `password_verify()`.
- `models/User.php:15` - Re-hash automático si el algoritmo cambia.
- Costo configurable en `config/app.php` (`BCRYPT_COST = 12`).

### 4.2 Prevención de Inyección SQL (SQLi)

Todas las consultas a la base de datos usan **sentencias preparadas (Prepared Statements)** con PDO.

**Implementación:**
- `models/Database.php` - Conexión PDO con `EMULATE_PREPARES = false`.
- Todos los modelos (`Client.php`, `User.php`, etc.) usan `$stmt->execute([...])` con parámetros nombrados.
- Ninguna consulta concatena valores directamente del usuario.

### 4.3 Protección de Sesiones

**Implementación en `models/Security.php`:**
- `session.cookie_httponly = 1` - Cookies no accesibles desde JavaScript.
- `session.cookie_secure = 1` - Cookies solo por HTTPS.
- `session.use_strict_mode = 1` - Rechaza IDs de sesión no inicializados.
- `session.cookie_samesite = Strict` - Protección contra CSRF.
- `session_regenerate_id(true)` en cada login (línea 56).
- Destrucción completa de la sesión en logout (líneas 72-82).

### 4.4 Control de Acceso Basado en Roles (RBAC)

**Roles definidos:**
| ID | Rol | Permisos |
|----|-----|----------|
| 1 | Administrador | Todos los módulos |
| 2 | Recepcionista | Dashboard, Clientes, Membresías, Pagos, Caja, Reportes |
| 3 | Cliente | Solo Dashboard |

**Implementación:**
- `config/app.php` - Matriz `ROLE_PERMISSIONS`.
- `models/Security.php:36` - `hasPermission($module)` verifica el rol contra la matriz.
- Cada vista protegida llama `Security::requirePermission('module')` al inicio.
- Los controladores verifican permisos antes de cualquier operación.

### 4.5 Protección contra XSS

**Implementación:**
- En las vistas, todos los datos del usuario se escapan con `Security::sanitizeInput()` que usa `htmlspecialchars()`.
- Las URLs se generan con `APP_URL` y no con datos del usuario.
- Los atributos HTML como `value` también están escapados.

### 4.6 Protección CSRF

**Implementación en `models/Security.php`:**
- `csrfToken()` - Genera tokens aleatorios de 32 bytes almacenados en sesión.
- `verifyCsrf($token)` - Comparación segura con `hash_equals()`.
- Todos los formularios POST incluyen `csrf_token`.
- Los controladores verifican el token antes de procesar.

### 4.7 Validación de Archivos

**Implementación en `models/Security.php:103-132`:**
- Verificación del código de error de subida.
- Límite de tamaño: 5MB (`UPLOAD_MAX_SIZE`).
- Verificación de tipo MIME real usando `finfo` (no confía en la extensión).
- Extensiones permitidas: `jpg, jpeg, png, webp`.
- Renombrado aleatorio con `bin2hex(random_bytes(16))`.
- Almacenamiento fuera del webroot con `.htaccess` protector.

### 4.8 Protección de Directorios

**Archivos `.htaccess`:**
- `logs/.htaccess` - `Require all denied` (logs inaccesibles).
- `public/uploads/.htaccess` - Deniega todo, excepto archivos de imagen.
- `.htaccess` raíz - `Options -Indexes`, protege archivos `.sql`, `.md`, `.log`.

### 4.9 Logs de Auditoría

**Implementación en `models/AuditLog.php`:**
- **Doble sistema:** Primero intenta guardar en BD (tabla `auditoria_log`). Si falla, escribe a archivo `logs/security.log`.
- **Eventos registrados:**
  - Intentos de login exitosos y fallidos.
  - Accesos denegados por permisos.
  - CSRF fallidos.
  - Creación, actualización y eliminación de clientes.
  - Asignación y cancelación de membresías.
  - Apertura/cierre de caja y movimientos.
- Cada entrada incluye: usuario, acción, descripción, IP, User-Agent y timestamp.

### 4.10 Sanitización de Entradas

Todas las entradas del usuario pasan por alguna de estas funciones:
- `filter_var($input, FILTER_SANITIZE_EMAIL)` para emails.
- `filter_var($input, FILTER_VALIDATE_INT)` para IDs y enteros.
- `htmlspecialchars()` con `ENT_QUOTES | UTF-8` para texto general.
- `Security::sanitizeInput()` que combina trim, stripslashes y htmlspecialchars.

---

## 5. API de Referencia

### 5.1 Modelos

| Clase | Método Principal | Descripción |
|-------|-----------------|-------------|
| `Database` | `getConnection()` | Retorna instancia PDO Singleton |
| `Security` | `initSession()`, `login()`, `logout()` | Gestión de sesiones seguras |
| `Security` | `csrfToken()`, `verifyCsrf()` | Protección CSRF |
| `Security` | `validateFile()`, `uploadFile()` | Subida segura de archivos |
| `User` | `authenticate()`, `create()`, `update()` | CRUD de usuarios |
| `Client` | `getAll()`, `create()`, `update()`, `softDelete()` | CRUD de clientes |
| `Membership` | `assignToClient()`, `getAllActive()`, `getExpiringSoon()` | Gestión de membresías |
| `Payment` | `register()`, `getAll()`, `getByClient()` | Registro de pagos |
| `CashRegister` | `open()`, `close()`, `addIncome()`, `addExpense()` | Cortes de caja |
| `Report` | `getDashboardStats()`, `getMonthlyIncome()` | Estadísticas |
| `AuditLog` | `register()`, `getRecent()` | Auditoría |

### 5.2 Controladores

| Controlador | Acciones |
|-------------|----------|
| `AuthController` | `login`, `logout` |
| `ClientController` | `store`, `update`, `delete` |
| `MembershipController` | `assign`, `cancel` |
| `CashRegisterController` | `open`, `addMovement`, `close` |
| `ReportController` | `getDashboard`, `getMonthlyIncome`, `getExpiringMemberships` |

### 5.3 Base de Datos

| Tabla | Propósito |
|-------|-----------|
| `roles` | Catálogo de roles del sistema |
| `usuarios` | Usuarios del sistema |
| `clientes` | Clientes del gimnasio |
| `planes_membresia` | Planes/membresías disponibles |
| `membresias` | Asignación de planes a clientes |
| `pagos` | Registro de pagos |
| `cortes_caja` | Cortes de caja (apertura/cierre) |
| `movimientos_caja` | Ingresos y egresos del día |
| `auditoria_log` | Registro de eventos de seguridad |

---

## Créditos

Desarrollado con PHP Nativo (POO), MySQL, Bootstrap 5, JavaScript Vanilla y HTML5/CSS3.
