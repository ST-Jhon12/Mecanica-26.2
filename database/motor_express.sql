CREATE DATABASE IF NOT EXISTS motor_express_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE motor_express_db;
SET NAMES utf8mb4;

CREATE TABLE usuarios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL,
 email VARCHAR(180) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 rol ENUM('admin','recepcion','mecanico') NOT NULL DEFAULT 'recepcion',
 activo TINYINT(1) NOT NULL DEFAULT 1,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT chk_usuario_activo CHECK (activo IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE clientes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tipo_documento ENUM('DNI','RUC','CE','PASAPORTE') NOT NULL,
 numero_documento VARCHAR(20) NOT NULL,
 nombre VARCHAR(160) NOT NULL,
 telefono VARCHAR(25), email VARCHAR(180), direccion VARCHAR(255),
 activo TINYINT(1) NOT NULL DEFAULT 1,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_cliente_documento (tipo_documento,numero_documento),
 CONSTRAINT chk_cliente_activo CHECK (activo IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE vehiculos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 cliente_id INT UNSIGNED NOT NULL,
 placa VARCHAR(15) NOT NULL UNIQUE,
 marca VARCHAR(60) NOT NULL, modelo VARCHAR(80) NOT NULL,
 anio_fabricacion SMALLINT UNSIGNED NOT NULL,
 color VARCHAR(40), activo TINYINT(1) NOT NULL DEFAULT 1,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_vehiculo_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
 CONSTRAINT chk_vehiculo_anio CHECK (anio_fabricacion BETWEEN 1886 AND 2155),
 CONSTRAINT chk_vehiculo_activo CHECK (activo IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE especialidades (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL UNIQUE, descripcion TEXT,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT chk_especialidad_activo CHECK (activo IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE mecanicos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 especialidad_id INT UNSIGNED NOT NULL,
 numero_documento VARCHAR(20) NOT NULL UNIQUE,
 nombre VARCHAR(120) NOT NULL, telefono VARCHAR(25), email VARCHAR(180),
 activo TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT fk_mecanico_especialidad FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE RESTRICT,
 CONSTRAINT chk_mecanico_activo CHECK (activo IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE citas (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 vehiculo_id INT UNSIGNED NOT NULL, mecanico_id INT UNSIGNED,
 fecha_hora DATETIME NOT NULL, motivo TEXT NOT NULL,
 estado ENUM('programada','confirmada','atendida','cancelada','no_asistio') NOT NULL DEFAULT 'programada',
 observaciones TEXT,
 INDEX idx_cita_agenda (fecha_hora,estado),
 INDEX idx_cita_mecanico_fecha (mecanico_id,fecha_hora),
 CONSTRAINT fk_cita_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE RESTRICT,
 CONSTRAINT fk_cita_mecanico FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE ordenes_trabajo (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 numero VARCHAR(25) NOT NULL UNIQUE,
 vehiculo_id INT UNSIGNED NOT NULL, cliente_id INT UNSIGNED NOT NULL,
 cita_id INT UNSIGNED UNIQUE, usuario_id INT UNSIGNED NOT NULL,
 fecha_ingreso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fecha_entrega_estimada DATETIME, fecha_entrega_real DATETIME,
 kilometraje INT UNSIGNED NOT NULL DEFAULT 0,
 motivo_ingreso TEXT NOT NULL,
 estado ENUM('recibida','en_diagnostico','en_reparacion','terminada','entregada','cancelada') NOT NULL DEFAULT 'recibida',
 observaciones TEXT,
 INDEX idx_orden_estado_fecha (estado,fecha_ingreso),
 INDEX idx_orden_historial (vehiculo_id,fecha_ingreso),
 CONSTRAINT fk_orden_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE RESTRICT,
 CONSTRAINT fk_orden_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
 CONSTRAINT fk_orden_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE RESTRICT,
 CONSTRAINT fk_orden_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
 CONSTRAINT chk_orden_estimada CHECK (fecha_entrega_estimada IS NULL OR fecha_entrega_estimada >= fecha_ingreso),
 CONSTRAINT chk_orden_entrega CHECK (fecha_entrega_real IS NULL OR fecha_entrega_real >= fecha_ingreso),
 CONSTRAINT chk_orden_entregada CHECK (estado <> 'entregada' OR fecha_entrega_real IS NOT NULL)
) ENGINE=InnoDB;

CREATE TABLE orden_mecanicos (
 orden_id INT UNSIGNED NOT NULL, mecanico_id INT UNSIGNED NOT NULL,
 es_responsable TINYINT(1) NOT NULL DEFAULT 0,
 orden_responsable INT UNSIGNED GENERATED ALWAYS AS
   (CASE WHEN es_responsable = 1 THEN orden_id ELSE NULL END) STORED,
 PRIMARY KEY (orden_id,mecanico_id),
 UNIQUE KEY uq_orden_responsable (orden_responsable),
 CONSTRAINT fk_asignacion_orden FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo(id) ON DELETE RESTRICT,
 CONSTRAINT fk_asignacion_mecanico FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE RESTRICT,
 CONSTRAINT chk_asignacion_responsable CHECK (es_responsable IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE diagnosticos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 orden_id INT UNSIGNED NOT NULL, mecanico_id INT UNSIGNED NOT NULL,
 descripcion TEXT NOT NULL, fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 observaciones TEXT,
 CONSTRAINT fk_diagnostico_asignacion FOREIGN KEY (orden_id,mecanico_id)
   REFERENCES orden_mecanicos(orden_id,mecanico_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE servicios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL UNIQUE, descripcion TEXT,
 precio_base DECIMAL(12,2) NOT NULL DEFAULT 0,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT chk_servicio_precio CHECK (precio_base >= 0),
 CONSTRAINT chk_servicio_activo CHECK (activo IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE orden_servicios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 orden_id INT UNSIGNED NOT NULL, servicio_id INT UNSIGNED NOT NULL,
 cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
 precio_unitario DECIMAL(12,2) NOT NULL,
 estado ENUM('pendiente','en_proceso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
 CONSTRAINT fk_detalle_servicio_orden FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo(id) ON DELETE RESTRICT,
 CONSTRAINT fk_detalle_servicio_catalogo FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE RESTRICT,
 CONSTRAINT chk_detalle_servicio_cantidad CHECK (cantidad > 0),
 CONSTRAINT chk_detalle_servicio_precio CHECK (precio_unitario >= 0)
) ENGINE=InnoDB;

CREATE TABLE repuestos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(40) NOT NULL UNIQUE, nombre VARCHAR(120) NOT NULL,
 descripcion TEXT, costo_referencia DECIMAL(12,2) NOT NULL DEFAULT 0,
 precio_venta DECIMAL(12,2) NOT NULL DEFAULT 0,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT chk_repuesto_costo CHECK (costo_referencia >= 0),
 CONSTRAINT chk_repuesto_precio CHECK (precio_venta >= 0),
 CONSTRAINT chk_repuesto_activo CHECK (activo IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE orden_repuestos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 orden_id INT UNSIGNED NOT NULL, repuesto_id INT UNSIGNED NOT NULL,
 cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
 costo_unitario DECIMAL(12,2) NOT NULL, precio_unitario DECIMAL(12,2) NOT NULL,
 CONSTRAINT fk_detalle_repuesto_orden FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo(id) ON DELETE RESTRICT,
 CONSTRAINT fk_detalle_repuesto_catalogo FOREIGN KEY (repuesto_id) REFERENCES repuestos(id) ON DELETE RESTRICT,
 CONSTRAINT chk_detalle_repuesto_cantidad CHECK (cantidad > 0),
 CONSTRAINT chk_detalle_repuesto_costo CHECK (costo_unitario >= 0),
 CONSTRAINT chk_detalle_repuesto_precio CHECK (precio_unitario >= 0)
) ENGINE=InnoDB;

CREATE TABLE pagos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 orden_id INT UNSIGNED NOT NULL, usuario_id INT UNSIGNED NOT NULL,
 monto DECIMAL(12,2) NOT NULL,
 metodo ENUM('efectivo','tarjeta_credito','tarjeta_debito','yape') NOT NULL,
 referencia VARCHAR(100), fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 estado ENUM('registrado','anulado') NOT NULL DEFAULT 'registrado',
 motivo_anulacion VARCHAR(255),
 INDEX idx_pago_ingresos (estado,fecha_pago),
 CONSTRAINT fk_pago_orden FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo(id) ON DELETE RESTRICT,
 CONSTRAINT fk_pago_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
 CONSTRAINT chk_pago_monto CHECK (monto > 0),
 CONSTRAINT chk_pago_referencia CHECK (metodo = 'efectivo' OR (referencia IS NOT NULL AND CHAR_LENGTH(TRIM(referencia)) > 0)),
 CONSTRAINT chk_pago_anulacion CHECK (estado <> 'anulado' OR (motivo_anulacion IS NOT NULL AND CHAR_LENGTH(TRIM(motivo_anulacion)) > 0))
) ENGINE=InnoDB;

CREATE VIEW v_resumen_ordenes AS
SELECT o.id AS orden_id,o.numero,o.vehiculo_id,o.cliente_id,o.estado,o.fecha_ingreso,
 COALESCE(s.total_servicios,0) AS total_servicios,
 COALESCE(r.total_repuestos,0) AS total_repuestos,
 COALESCE(r.costo_repuestos,0) AS costo_repuestos,
 COALESCE(s.total_servicios,0)+COALESCE(r.total_repuestos,0) AS total,
 COALESCE(p.total_pagado,0) AS total_pagado,
 COALESCE(s.total_servicios,0)+COALESCE(r.total_repuestos,0)-COALESCE(p.total_pagado,0) AS saldo
FROM ordenes_trabajo o
LEFT JOIN (SELECT orden_id,SUM(cantidad*precio_unitario) AS total_servicios
 FROM orden_servicios WHERE estado <> 'cancelado' GROUP BY orden_id) s ON s.orden_id=o.id
LEFT JOIN (SELECT orden_id,SUM(cantidad*precio_unitario) AS total_repuestos,
 SUM(cantidad*costo_unitario) AS costo_repuestos FROM orden_repuestos GROUP BY orden_id) r ON r.orden_id=o.id
LEFT JOIN (SELECT orden_id,SUM(monto) AS total_pagado FROM pagos
 WHERE estado='registrado' GROUP BY orden_id) p ON p.orden_id=o.id;

CREATE VIEW v_ingresos_diarios AS
SELECT DATE(fecha_pago) AS fecha,metodo,COUNT(*) AS cantidad_pagos,SUM(monto) AS ingresos
FROM pagos WHERE estado='registrado' GROUP BY DATE(fecha_pago),metodo;

INSERT INTO especialidades (nombre,descripcion) VALUES
 ('Mecánica general','Diagnóstico y reparación general'),
 ('Electricidad automotriz','Sistemas eléctricos y electrónicos'),
 ('Frenos y suspensión','Mantenimiento de frenos, dirección y suspensión'),
 ('Motor y transmisión','Reparación de motor y transmisión');

