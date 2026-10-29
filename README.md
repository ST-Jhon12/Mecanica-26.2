# Motor Express

Sistema de gestión para un taller de reparación de vehículos con PHP, MySQL y estructura MVC.

## Ejecutar
Iniciar Apache y MySQL en XAMPP y abrir http://localhost/motor-express/.
El acceso visual al panel está disponible desde el enlace de vista previa del login.

## Estado
Base de datos creada. Vistas de acceso, registro, dashboard y módulos disponibles.
Los formularios todavía no guardan datos. Los controladores, modelos y autenticación están pendientes.

## Organización
- config/: conexión a la base de datos.
- controller/: controladores y validaciones.
- model/: acceso a datos mediante PDO y consultas preparadas.
- views/: pantallas, layout y vistas por módulo.
- assetes/: estilos y scripts.
- imagenes/: recursos gráficos.
- database/motor_express.sql: esquema de motor_express_db.
