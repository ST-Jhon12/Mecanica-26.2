<?php

require_once __DIR__ . '/../config/app.php';

class BaseController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = database();
    }

    public static function modules(): array
    {
        return [
            'clientes' => [
                'title' => 'Clientes', 'description' => 'Personas que confían en el taller',
                'table' => 'clientes', 'fields' => [
                    'tipo_documento' => ['Tipo de documento', 'select', ['DNI' => 'DNI', 'RUC' => 'RUC', 'CE' => 'Carné de extranjería', 'PASAPORTE' => 'Pasaporte']],
                    'numero_documento' => ['Número de documento', 'text'],
                    'nombre' => ['Nombre completo o razón social', 'text'],
                    'telefono' => ['Teléfono', 'tel', false], 'email' => ['Correo electrónico', 'email', false],
                    'direccion' => ['Dirección', 'text', false],
                ],
                'list' => "SELECT id, nombre, CONCAT(tipo_documento, ' ', numero_documento) AS documento, telefono, email, direccion FROM clientes WHERE activo = 1 ORDER BY nombre",
                'columns' => ['nombre' => 'Nombre', 'documento' => 'Documento', 'telefono' => 'Teléfono', 'email' => 'Correo', 'direccion' => 'Dirección'],
            ],
            'vehiculos' => [
                'title' => 'Vehículos', 'description' => 'Información e historial de cada vehículo',
                'table' => 'vehiculos', 'fields' => [
                    'cliente_id' => ['Cliente', 'relation', 'clientes'], 'placa' => ['Placa', 'text'],
                    'marca' => ['Marca', 'text'], 'modelo' => ['Modelo', 'text'],
                    'anio_fabricacion' => ['Año de fabricación', 'number', 1886, date('Y') + 1],
                    'color' => ['Color', 'text', false],
                ],
                'list' => 'SELECT v.id, v.placa, c.nombre AS cliente, v.marca, v.modelo, v.anio_fabricacion, v.color FROM vehiculos v JOIN clientes c ON c.id = v.cliente_id WHERE v.activo = 1 ORDER BY v.placa',
                'columns' => ['placa' => 'Placa', 'cliente' => 'Cliente', 'marca' => 'Marca', 'modelo' => 'Modelo', 'anio_fabricacion' => 'Año', 'color' => 'Color'],
            ],
            'mecanicos' => [
                'title' => 'Mecánicos', 'description' => 'Equipo técnico y especialidades',
                'table' => 'mecanicos', 'fields' => [
                    'especialidad_id' => ['Especialidad', 'relation', 'especialidades'],
                    'numero_documento' => ['Número de documento', 'text'], 'nombre' => ['Nombre completo', 'text'],
                    'telefono' => ['Teléfono', 'tel', false], 'email' => ['Correo electrónico', 'email', false],
                ],
                'list' => 'SELECT m.id, m.nombre, m.numero_documento, e.nombre AS especialidad, m.telefono, m.email FROM mecanicos m JOIN especialidades e ON e.id = m.especialidad_id WHERE m.activo = 1 ORDER BY m.nombre',
                'columns' => ['nombre' => 'Nombre', 'numero_documento' => 'Documento', 'especialidad' => 'Especialidad', 'telefono' => 'Teléfono', 'email' => 'Correo'],
            ],
            'citas' => [
                'title' => 'Citas', 'description' => 'Organiza las próximas visitas al taller',
                'table' => 'citas', 'fields' => [
                    'vehiculo_id' => ['Vehículo', 'relation', 'vehiculos'],
                    'mecanico_id' => ['Mecánico asignado', 'relation', 'mecanicos', false],
                    'fecha_hora' => ['Fecha y hora', 'datetime'],
                    'motivo' => ['Motivo de la visita', 'textarea'],
                    'estado' => ['Estado', 'select', ['programada' => 'Programada', 'confirmada' => 'Confirmada', 'atendida' => 'Atendida', 'cancelada' => 'Cancelada', 'no_asistio' => 'No asistió']],
                    'observaciones' => ['Observaciones', 'textarea', false],
                ],
                'list' => 'SELECT c.id, c.vehiculo_id, v.placa AS vehiculo, COALESCE(m.nombre, "Sin asignar") AS mecanico, c.fecha_hora, c.motivo, c.estado FROM citas c JOIN vehiculos v ON v.id = c.vehiculo_id LEFT JOIN mecanicos m ON m.id = c.mecanico_id ORDER BY c.fecha_hora DESC',
                'columns' => ['vehiculo' => 'Vehículo', 'mecanico' => 'Mecánico', 'fecha_hora' => 'Fecha y hora', 'motivo' => 'Motivo', 'estado' => 'Estado'],
            ],
            'ordenes' => [
                'title' => 'Órdenes de trabajo', 'description' => 'Seguimiento de las reparaciones',
                'table' => 'ordenes_trabajo', 'fields' => [
                    'vehiculo_id' => ['Vehículo', 'relation', 'vehiculos'],
                    'cita_id' => ['Cita asociada', 'relation', 'citas', false],
                    'mecanico_id' => ['Mecánico responsable', 'relation', 'mecanicos', false],
                    'fecha_ingreso' => ['Fecha de ingreso', 'datetime'],
                    'kilometraje' => ['Kilometraje', 'number', 0, 9999999],
                    'motivo_ingreso' => ['Motivo de ingreso', 'textarea'],
                    'estado' => ['Estado', 'select', ['recibida' => 'Recibida', 'en_diagnostico' => 'En diagnóstico', 'en_reparacion' => 'En reparación', 'terminada' => 'Terminada', 'entregada' => 'Entregada', 'cancelada' => 'Cancelada']],
                    'observaciones' => ['Observaciones', 'textarea', false],
                ],
                'list' => 'SELECT o.id, o.numero, v.placa AS vehiculo, c.nombre AS cliente, o.fecha_ingreso, o.kilometraje, o.estado FROM ordenes_trabajo o JOIN vehiculos v ON v.id = o.vehiculo_id JOIN clientes c ON c.id = o.cliente_id ORDER BY o.fecha_ingreso DESC',
                'columns' => ['numero' => 'Número', 'vehiculo' => 'Vehículo', 'cliente' => 'Cliente', 'fecha_ingreso' => 'Ingreso', 'kilometraje' => 'Kilometraje', 'estado' => 'Estado'],
            ],
            'diagnosticos' => [
                'title' => 'Diagnósticos', 'description' => 'Evaluaciones y fallas detectadas',
                'table' => 'diagnosticos', 'fields' => [
                    'orden_id' => ['Orden de trabajo', 'relation', 'ordenes_trabajo'],
                    'mecanico_id' => ['Mecánico', 'relation', 'mecanicos'],
                    'descripcion' => ['Descripción del diagnóstico', 'textarea'],
                    'fecha' => ['Fecha y hora', 'datetime', false],
                    'observaciones' => ['Observaciones', 'textarea', false],
                ],
                'list' => 'SELECT d.id, o.numero AS orden, m.nombre AS mecanico, d.fecha, d.descripcion, d.observaciones FROM diagnosticos d JOIN ordenes_trabajo o ON o.id = d.orden_id JOIN mecanicos m ON m.id = d.mecanico_id ORDER BY d.fecha DESC',
                'columns' => ['orden' => 'Orden', 'mecanico' => 'Mecánico', 'fecha' => 'Fecha', 'descripcion' => 'Descripción', 'observaciones' => 'Observaciones'],
            ],
            'servicios' => [
                'title' => 'Servicios', 'description' => 'Catálogo de mantenimiento y reparación',
                'table' => 'servicios', 'fields' => [
                    'nombre' => ['Nombre del servicio', 'text'], 'descripcion' => ['Descripción', 'textarea', false],
                    'precio_base' => ['Precio base (S/)', 'number', 0, 99999999, '0.01'],
                ],
                'list' => 'SELECT id, nombre, descripcion, precio_base FROM servicios WHERE activo = 1 ORDER BY nombre',
                'columns' => ['nombre' => 'Nombre', 'descripcion' => 'Descripción', 'precio_base' => 'Precio base (S/)'],
            ],
            'repuestos' => [
                'title' => 'Repuestos', 'description' => 'Catálogo de piezas y costos',
                'table' => 'repuestos', 'fields' => [
                    'codigo' => ['Código', 'text'], 'nombre' => ['Nombre', 'text'], 'descripcion' => ['Descripción', 'textarea', false],
                    'costo_referencia' => ['Costo de referencia (S/)', 'number', 0, 99999999, '0.01'],
                    'precio_venta' => ['Precio de venta (S/)', 'number', 0, 99999999, '0.01'],
                ],
                'list' => 'SELECT id, codigo, nombre, descripcion, costo_referencia, precio_venta FROM repuestos WHERE activo = 1 ORDER BY nombre',
                'columns' => ['codigo' => 'Código', 'nombre' => 'Nombre', 'descripcion' => 'Descripción', 'costo_referencia' => 'Costo (S/)', 'precio_venta' => 'Precio (S/)'],
            ],
            'pagos' => [
                'title' => 'Pagos', 'description' => 'Consulta los abonos de cada orden',
                'table' => 'pagos', 'fields' => [
                    'orden_id' => ['Orden de trabajo', 'relation', 'ordenes_trabajo'],
                    'monto' => ['Monto (S/)', 'number', 0.01, 99999999, '0.01'],
                    'metodo' => ['Método de pago', 'select', ['efectivo' => 'Efectivo', 'tarjeta_credito' => 'Tarjeta de crédito', 'tarjeta_debito' => 'Tarjeta de débito', 'yape' => 'Yape']],
                    'referencia' => ['Referencia de operación (requerida excepto efectivo)', 'text', false],
                    'fecha_pago' => ['Fecha y hora de pago', 'datetime', false],
                ],
                'list' => 'SELECT p.id, o.numero AS orden, p.monto, p.metodo, p.referencia, p.fecha_pago FROM pagos p JOIN ordenes_trabajo o ON o.id = p.orden_id WHERE p.estado = "registrado" ORDER BY p.fecha_pago DESC',
                'columns' => ['orden' => 'Orden', 'monto' => 'Monto (S/)', 'metodo' => 'Método', 'referencia' => 'Referencia', 'fecha_pago' => 'Fecha de pago'],
            ],
        ];
    }

    public function records(string $module): array
    {
        $config = self::modules()[$module] ?? null;
        if (!$config) {
            throw new InvalidArgumentException('El módulo solicitado no existe.');
        }
        return $this->db->query($config['list'])->fetchAll();
    }

    public function find(string $module, int $id): ?array
    {
        $config = self::modules()[$module] ?? null;
        if (!$config || $id < 1) {
            return null;
        }
        $query = $this->db->prepare('SELECT * FROM ' . $config['table'] . ' WHERE id = :id');
        $query->execute(['id' => $id]);
        return $query->fetch() ?: null;
    }

    public function options(string $relation): array
    {
        $queries = [
            'clientes' => 'SELECT id, nombre AS label FROM clientes WHERE activo = 1 ORDER BY nombre',
            'vehiculos' => "SELECT v.id, CONCAT(v.placa, ' · ', v.marca, ' ', v.modelo, ' · ', c.nombre) AS label FROM vehiculos v JOIN clientes c ON c.id = v.cliente_id WHERE v.activo = 1 ORDER BY v.placa",
            'mecanicos' => 'SELECT m.id, CONCAT(m.nombre, " · ", e.nombre) AS label FROM mecanicos m JOIN especialidades e ON e.id = m.especialidad_id WHERE m.activo = 1 ORDER BY m.nombre',
            'especialidades' => 'SELECT id, nombre AS label FROM especialidades WHERE activo = 1 ORDER BY nombre',
            'ordenes_trabajo' => "SELECT o.id, CONCAT(o.numero, ' · ', v.placa) AS label FROM ordenes_trabajo o JOIN vehiculos v ON v.id = o.vehiculo_id ORDER BY o.fecha_ingreso DESC",
            'servicios' => 'SELECT id, CONCAT(nombre, " · S/ ", FORMAT(precio_base, 2)) AS label FROM servicios WHERE activo = 1 ORDER BY nombre',
            'repuestos' => 'SELECT id, CONCAT(codigo, " · ", nombre, " · S/ ", FORMAT(precio_venta, 2)) AS label FROM repuestos WHERE activo = 1 ORDER BY nombre',
            'citas' => 'SELECT c.id, CONCAT(DATE_FORMAT(c.fecha_hora, "%d/%m/%Y %H:%i"), " · ", v.placa, " · ", cl.nombre) AS label FROM citas c JOIN vehiculos v ON v.id = c.vehiculo_id JOIN clientes cl ON cl.id = v.cliente_id WHERE c.estado <> "cancelada" ORDER BY c.fecha_hora DESC',
        ];
        if (!isset($queries[$relation])) {
            throw new InvalidArgumentException('Relación de formulario no permitida.');
        }
        return $this->db->query($queries[$relation])->fetchAll();
    }

    public function save(string $module, ?int $id, array $input, int $userId): void
    {
        $config = self::modules()[$module] ?? null;
        if (!$config) {
            throw new InvalidArgumentException('El módulo solicitado no existe.');
        }
        $values = [];
        foreach ($config['fields'] as $column => $definition) {
            $required = !((isset($definition[2]) && $definition[2] === false) || (isset($definition[3]) && $definition[3] === false));
            $value = $input[$column] ?? '';
            if ($value === '' && !$required) {
                $values[$column] = null;
                continue;
            }
            if ($value === '' || is_array($value)) {
                throw new InvalidArgumentException('Completa el campo «' . $definition[0] . '».');
            }
            $type = $definition[1];
            if ($type === 'select' && !array_key_exists((string) $value, $definition[2])) {
                throw new InvalidArgumentException('Selecciona una opción válida para «' . $definition[0] . '».');
            }
            if ($type === 'relation') {
                $value = filter_var($value, FILTER_VALIDATE_INT);
                if (!$value || !$this->optionExists($definition[2], (int) $value)) {
                    throw new InvalidArgumentException('Selecciona una opción válida para «' . $definition[0] . '».');
                }
            } elseif ($type === 'number' && (!is_numeric($value) || (float) $value < (float) $definition[2] || (float) $value > (float) $definition[3])) {
                throw new InvalidArgumentException('El valor de «' . $definition[0] . '» no es válido.');
            } elseif ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Ingresa un correo electrónico válido.');
            }
            if ($type === 'datetime') {
                $value = str_replace('T', ' ', (string) $value);
                $date = DateTime::createFromFormat('Y-m-d H:i', $value);
                if (!$date || $date->format('Y-m-d H:i') !== $value) {
                    throw new InvalidArgumentException('Ingresa una fecha y hora válidas para «' . $definition[0] . '».');
                }
            }
            $values[$column] = trim((string) $value);
        }

        if ($module === 'pagos' && $values['metodo'] !== 'efectivo' && empty($values['referencia'])) {
            throw new InvalidArgumentException('La referencia es obligatoria para pagos con tarjeta o Yape.');
        }
        if ($module === 'ordenes') {
            $vehicle = $this->db->prepare('SELECT cliente_id FROM vehiculos WHERE id = :id AND activo = 1');
            $vehicle->execute(['id' => $values['vehiculo_id']]);
            $clientId = $vehicle->fetchColumn();
            if (!$clientId) {
                throw new InvalidArgumentException('El vehículo seleccionado no tiene un cliente activo.');
            }
            $values['cliente_id'] = (int) $clientId;
            if (!empty($values['cita_id'])) {
                $appointment = $this->db->prepare(
                    'SELECT c.vehiculo_id FROM citas c WHERE c.id = :id AND c.estado <> "cancelada"
                     AND NOT EXISTS (SELECT 1 FROM ordenes_trabajo o WHERE o.cita_id = c.id AND o.id <> :orden_id)'
                );
                $appointment->execute(['id' => $values['cita_id'], 'orden_id' => $id ?? 0]);
                $appointmentVehicle = $appointment->fetchColumn();
                if (!$appointmentVehicle || (int) $appointmentVehicle !== (int) $values['vehiculo_id']) {
                    throw new InvalidArgumentException('La cita debe pertenecer al vehículo seleccionado.');
                }
            }
        }

        $mechanicId = null;
        if ($module === 'ordenes') {
            $mechanicId = $values['mecanico_id'] ?? null;
            unset($values['mecanico_id']);
        }
        $this->db->beginTransaction();
        try {
            if ($id) {
                unset($values['fecha_ingreso']);
                if ($module === 'pagos' && empty($values['fecha_pago'])) {
                    unset($values['fecha_pago']);
                }
                if ($module === 'diagnosticos' && empty($values['fecha'])) {
                    unset($values['fecha']);
                }
                $set = [];
                foreach (array_keys($values) as $column) {
                    $set[] = "`{$column}` = :{$column}";
                }
                $values['id'] = $id;
                $statement = $this->db->prepare('UPDATE ' . $config['table'] . ' SET ' . implode(', ', $set) . ' WHERE id = :id');
                $statement->execute($values);
            } else {
                if ($module === 'ordenes') {
                    $values['numero'] = 'OT-' . date('Ymd-His') . '-' . random_int(10, 99);
                    $values['usuario_id'] = $userId;
                    if (empty($values['fecha_ingreso'])) {
                        unset($values['fecha_ingreso']);
                    }
                } elseif ($module === 'pagos') {
                    $values['usuario_id'] = $userId;
                    if (empty($values['fecha_pago'])) {
                        unset($values['fecha_pago']);
                    }
                } elseif ($module === 'diagnosticos' && empty($values['fecha'])) {
                    unset($values['fecha']);
                }
                $columns = array_keys($values);
                $quoted = array_map(static fn($column) => "`{$column}`", $columns);
                $placeholders = array_map(static fn($column) => ':' . $column, $columns);
                $statement = $this->db->prepare(
                    'INSERT INTO ' . $config['table'] . ' (' . implode(', ', $quoted) . ') VALUES (' . implode(', ', $placeholders) . ')'
                );
                $statement->execute($values);
                $id = (int) $this->db->lastInsertId();
            }

            if ($module === 'ordenes') {
                if (!empty($values['cita_id'])) {
                    $appointment = $this->db->prepare('UPDATE citas SET estado = "atendida" WHERE id = :id');
                    $appointment->execute(['id' => $values['cita_id']]);
                }
                $removeResponsibility = $this->db->prepare(
                    'UPDATE orden_mecanicos SET es_responsable = 0 WHERE orden_id = :orden AND es_responsable = 1'
                );
                $removeResponsibility->execute(['orden' => $id]);
                if ($mechanicId) {
                    $assignment = $this->db->prepare(
                        'INSERT INTO orden_mecanicos (orden_id, mecanico_id, es_responsable) VALUES (:orden, :mecanico, 1)
                         ON DUPLICATE KEY UPDATE es_responsable = 1'
                    );
                    $assignment->execute(['orden' => $id, 'mecanico' => $mechanicId]);
                }
            }
            if ($module === 'diagnosticos') {
                $assignment = $this->db->prepare(
                    'INSERT INTO orden_mecanicos (orden_id, mecanico_id) VALUES (:orden, :mecanico)
                     ON DUPLICATE KEY UPDATE mecanico_id = VALUES(mecanico_id)'
                );
                $assignment->execute(['orden' => $values['orden_id'], 'mecanico' => $values['mecanico_id']]);
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    private function optionExists(string $relation, int $id): bool
    {
        $tables = [
            'clientes' => 'clientes', 'vehiculos' => 'vehiculos', 'mecanicos' => 'mecanicos',
            'especialidades' => 'especialidades', 'ordenes_trabajo' => 'ordenes_trabajo',
            'servicios' => 'servicios', 'repuestos' => 'repuestos', 'citas' => 'citas',
        ];
        if (!isset($tables[$relation])) {
            return false;
        }
        $activeFilter = in_array($relation, ['clientes', 'vehiculos', 'mecanicos', 'especialidades', 'servicios', 'repuestos'], true)
            ? ' AND activo = 1'
            : '';
        $statement = $this->db->prepare("SELECT COUNT(*) FROM `{$tables[$relation]}` WHERE id = :id{$activeFilter}");
        $statement->execute(['id' => $id]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function delete(string $module, int $id): void
    {
        $config = self::modules()[$module] ?? null;
        if (!$config || $id < 1) {
            throw new InvalidArgumentException('El registro solicitado no existe.');
        }
        $softDelete = ['clientes', 'vehiculos', 'mecanicos', 'servicios', 'repuestos'];
        if (in_array($module, $softDelete, true)) {
            $statement = $this->db->prepare('UPDATE ' . $config['table'] . ' SET activo = 0 WHERE id = :id');
        } elseif ($module === 'pagos') {
            $statement = $this->db->prepare(
                "UPDATE pagos SET estado = 'anulado', motivo_anulacion = 'Anulado desde el sistema' WHERE id = :id AND estado = 'registrado'"
            );
        } else {
            $statement = $this->db->prepare('DELETE FROM ' . $config['table'] . ' WHERE id = :id');
        }
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() === 0) {
            throw new InvalidArgumentException('No se encontró el registro o ya fue eliminado.');
        }
    }
}