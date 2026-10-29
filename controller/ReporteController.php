<?php

require_once __DIR__ . '/../config/app.php';

class ReporteController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = database();
    }

    public function consultar(string $tipo, string $desde, string $hasta): array
    {
        $desde = $desde !== '' ? $desde : date('Y-m-01');
        $hasta = $hasta !== '' ? $hasta : date('Y-m-d');
        $startDate = DateTime::createFromFormat('!Y-m-d', $desde);
        $endDate = DateTime::createFromFormat('!Y-m-d', $hasta);
        if (
            !$startDate || $startDate->format('Y-m-d') !== $desde
            || !$endDate || $endDate->format('Y-m-d') !== $hasta
            || $desde > $hasta
        ) {
            throw new InvalidArgumentException('Selecciona un rango de fechas válido.');
        }
        $queries = [
            'servicios' => [
                'SELECT s.nombre AS servicio, SUM(os.cantidad) AS cantidad, SUM(os.cantidad * os.precio_unitario) AS total FROM orden_servicios os JOIN servicios s ON s.id = os.servicio_id JOIN ordenes_trabajo o ON o.id = os.orden_id WHERE os.estado <> "cancelado" AND DATE(o.fecha_ingreso) BETWEEN :desde AND :hasta GROUP BY s.id, s.nombre ORDER BY total DESC',
                ['servicio' => 'Servicio', 'cantidad' => 'Cantidad', 'total' => 'Total (S/)'],
            ],
            'vehiculos' => [
                'SELECT v.placa, v.marca, v.modelo, c.nombre AS cliente, COUNT(o.id) AS visitas FROM ordenes_trabajo o JOIN vehiculos v ON v.id = o.vehiculo_id JOIN clientes c ON c.id = o.cliente_id WHERE DATE(o.fecha_ingreso) BETWEEN :desde AND :hasta GROUP BY v.id, v.placa, v.marca, v.modelo, c.nombre ORDER BY visitas DESC',
                ['placa' => 'Placa', 'marca' => 'Marca', 'modelo' => 'Modelo', 'cliente' => 'Cliente', 'visitas' => 'Atenciones'],
            ],
            'pendientes' => [
                'SELECT o.numero, v.placa, c.nombre AS cliente, o.fecha_ingreso, o.estado, COALESCE(r.saldo, 0) AS saldo FROM ordenes_trabajo o JOIN vehiculos v ON v.id = o.vehiculo_id JOIN clientes c ON c.id = o.cliente_id LEFT JOIN v_resumen_ordenes r ON r.orden_id = o.id WHERE o.estado NOT IN ("entregada", "cancelada") AND DATE(o.fecha_ingreso) BETWEEN :desde AND :hasta ORDER BY o.fecha_ingreso',
                ['numero' => 'Orden', 'placa' => 'Vehículo', 'cliente' => 'Cliente', 'fecha_ingreso' => 'Ingreso', 'estado' => 'Estado', 'saldo' => 'Saldo (S/)'],
            ],
            'ingresos' => [
                'SELECT DATE(fecha_pago) AS fecha, metodo, COUNT(*) AS pagos, SUM(monto) AS total FROM pagos WHERE estado = "registrado" AND DATE(fecha_pago) BETWEEN :desde AND :hasta GROUP BY DATE(fecha_pago), metodo ORDER BY fecha DESC',
                ['fecha' => 'Fecha', 'metodo' => 'Método', 'pagos' => 'Pagos', 'total' => 'Ingresos (S/)'],
            ],
        ];
        if (!isset($queries[$tipo])) {
            throw new InvalidArgumentException('Selecciona un tipo de reporte válido.');
        }
        [$sql, $columns] = $queries[$tipo];
        $statement = $this->db->prepare($sql);
        $statement->execute(['desde' => $desde, 'hasta' => $hasta]);
        return ['columns' => $columns, 'rows' => $statement->fetchAll(), 'desde' => $desde, 'hasta' => $hasta];
    }
}