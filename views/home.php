<?php
require_once __DIR__ . '/../config/app.php';
require_login();
$pagina = 'home';
$titulo = 'Panel general';
$db = database();
$hoy = date('Y-m-d');
$stats = [
    'Órdenes activas' => (int) $db->query("SELECT COUNT(*) FROM ordenes_trabajo WHERE estado NOT IN ('entregada', 'cancelada')")->fetchColumn(),
    'Citas de hoy' => (int) $db->query('SELECT COUNT(*) FROM citas WHERE DATE(fecha_hora) = CURDATE() AND estado <> "cancelada"')->fetchColumn(),
    'Vehículos atendidos' => (int) $db->query('SELECT COUNT(DISTINCT vehiculo_id) FROM ordenes_trabajo')->fetchColumn(),
    'Ingresos del día' => (float) $db->query('SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = "registrado" AND DATE(fecha_pago) = CURDATE()')->fetchColumn(),
];
$recent = $db->query('SELECT o.id, o.numero, v.placa, c.nombre AS cliente, o.estado, o.fecha_ingreso FROM ordenes_trabajo o JOIN vehiculos v ON v.id = o.vehiculo_id JOIN clientes c ON c.id = o.cliente_id ORDER BY o.fecha_ingreso DESC LIMIT 5')->fetchAll();
ob_start();
?>
<section class="hero"><div><p class="eyebrow">CADA DETALLE CUENTA</p><h2>Tu taller.<br>En la mejor marcha.</h2><p>Organiza las visitas, acompaña cada reparación<br>y mantén el control de tu día.</p><a class="button" href="citas/form.php">Programar cita ↗</a></div><img src="../imagenes/vehiculo.svg" alt="Ilustración de un automóvil en el taller"></section>
<div class="stats"><?php foreach ($stats as $label => $value): ?><article class="stat"><span><?= e($label) ?></span><strong><?= $label === 'Ingresos del día' ? 'S/ ' . number_format($value, 2) : number_format($value) ?></strong><small><?= $value ? 'Datos actualizados' : 'Sin registros todavía' ?></small></article><?php endforeach ?></div>
<div class="dashboard-grid">
    <section class="panel"><div class="section-heading"><div><p class="eyebrow">SEGUIMIENTO</p><h2>Trabajos recientes</h2></div><a href="ordenes/index.php">Ver órdenes →</a></div>
        <?php if ($recent): ?><div class="table-wrap"><table><thead><tr><th>Orden</th><th>Vehículo</th><th>Cliente</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach ($recent as $order): ?><tr><td><?= e($order['numero']) ?></td><td><?= e($order['placa']) ?></td><td><?= e($order['cliente']) ?></td><td><?= e($order['estado']) ?></td><td><a href="ordenes/detalle.php?id=<?= (int) $order['id'] ?>">Detalle</a></td></tr><?php endforeach ?></tbody></table></div>
        <?php else: ?><div class="empty"><span class="empty-icon">⌁</span><h3>El próximo trabajo empieza aquí</h3><p>Las órdenes de reparación aparecerán en este espacio.</p><a class="button secondary" href="ordenes/form.php">Nueva orden</a></div><?php endif ?>
    </section>
    <section class="panel quick"><p class="eyebrow">A UN CLIC</p><h2>Prepara tu jornada</h2><a href="clientes/form.php"><span>01</span><div><b>Registrar cliente</b><small>El primer paso de una buena atención</small></div>↗</a><a href="vehiculos/form.php"><span>02</span><div><b>Agregar vehículo</b><small>Su información en un solo lugar</small></div>↗</a><a href="citas/index.php"><span>03</span><div><b>Revisar agenda</b><small>Organiza las próximas visitas</small></div>↗</a></section>
</div>
<?php $contenido = ob_get_clean(); require __DIR__ . '/layout.php'; ?>