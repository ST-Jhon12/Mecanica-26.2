# Motor Express

Sistema web para administrar clientes, vehículos, mecánicos, citas, órdenes de trabajo, diagnósticos, servicios, repuestos, pagos y reportes de un taller automotriz. Está desarrollado con PHP, MySQL, PDO y una estructura MVC.

## Requisitos

- PHP 8.0 o superior con las extensiones `pdo_mysql` y `mbstring`.
- MySQL 8.0.16 o superior.
- Apache con `mod_rewrite` no es necesario.

## Instalación en XAMPP

1. Copia el proyecto dentro de `C:\xampp\htdocs`.
2. Inicia Apache y MySQL desde el panel de XAMPP.
3. Importa `database\motor_express.sql` desde phpMyAdmin o ejecuta el script con MySQL. Este crea `motor_express_db` y las tablas, vistas e especialidades iniciales.
4. Abre `http://localhost/Mecanica-26.2/` (o la ruta que corresponda al nombre de la carpeta).
5. Registra el primer usuario desde **Regístrate** e inicia sesión.

La conexión usa por defecto `127.0.0.1`, base de datos `motor_express_db`, usuario `root` y contraseña vacía (configuración local habitual de XAMPP). Se puede configurar con las variables de entorno `MOTOR_DB_HOST`, `MOTOR_DB_NAME`, `MOTOR_DB_USER` y `MOTOR_DB_PASSWORD`.

## Funcionalidades

- Registro, inicio y cierre de sesión; las contraseñas se guardan con `password_hash`.
- CRUD de clientes, vehículos, mecánicos, citas, órdenes, diagnósticos, servicios, repuestos y pagos.
- Una orden vincula vehículo y cliente, admite mecánico responsable, cita relacionada, diagnósticos, servicios y repuestos; los precios aplicados quedan guardados como valores históricos.
- Registro de pagos en efectivo, tarjeta de crédito, tarjeta de débito o Yape. Los pagos anulados conservan su historial.
- Reportes de servicios realizados, vehículos atendidos, reparaciones pendientes e ingresos por rango de fechas.
- Dashboard con indicadores y actividad reciente, búsqueda en listados y diseño adaptable.
- Operaciones con PDO y consultas preparadas, validación de datos, tokens CSRF y escape de salida HTML.

El primer usuario registrado obtiene el rol predeterminado `recepcion`; el formulario público nunca permite escoger un rol privilegiado.

## Estructura

- `config/`: conexión a MySQL, sesión y utilidades compartidas.
- `controller/`: autenticación, CRUD y reportes.
- `model/`: acceso a datos de usuario y base compartida.
- `views/`: pantallas y formularios.
- `database/motor_express.sql`: esquema y datos iniciales.
- `assetes/`: estilos y JavaScript de interfaz.
