<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controller/BaseController.php';
require_login();

$db = database();
$orderId = filter_var($_GET['id'] ?? $_POST['orden_id'] ?? null, FILTER_VALIDATE_INT);
if (!$orderId) {
    http_response_code(400);
    exit('Selecciona una orden de trabajo válida.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'add_service') {
            $serviceId = filter_var($_POST['servicio_id'] ?? 0, FILTER_VALIDATE_INT);
            $quantity = filter_var($_POST['cantidad'] ?? 0, FILTER_VALIDATE_FLOAT);
            if (!$serviceId || !$quantity || $quantity <= 0) {
                throw new InvalidArgumentException('Selecciona un servicio y una cantidad válida.');
            }
            $service = $db->prepare('SELECT precio_base FROM servicios WHERE id = :id AND activo = 1');
            $service->execute(['id' => $serviceId]);
            $price = $service->fetchColumn();
            if ($price === false) {
                throw new InvalidArgumentException('El servicio seleccionado no está disponible.');
            }
            $insert = $db->prepare('INSERT INTO orden_servicios (orden_id, servicio_id, cantidad, precio_unitario) VALUES (:orden, :servicio, :cantidad, :precio)');
            $insert->execute(['orden' => $orderId, 'servicio' => $serviceId, 'cantidad' => $quantity, 'precio' => $price]);
            flash('success', 'El servicio se agregó a la orden.');
        } elseif ($action === 'add_part') {
            $partId = filter_var($_POST['repuesto_id'] ?? 0, FILTER_VALIDATE_INT);
            $quantity = filter_var($_POST['cantidad'] ?? 0, FILTER_VALIDATE_FLOAT);
            if (!$partId || !$quantity || $quantity <= 0) {
                throw new InvalidArgumentException('Selecciona un repuesto y una cantidad válida.');
            }
            $part = $db->prepare('SELECT costo_referencia, precio_venta FROM repuestos WHERE id = :id AND activo = 1');
            $part->execute(['id' => $partId]);
            $prices = $part->fetch();
            if (!$prices) {
                throw new InvalidArgumentException('El repuesto seleccionado no está disponible.');
            }
            $insert = $db->prepare('INSERT INTO orden_repuestos (orden_id, repuesto_id, cantidad, costo_unitario, precio_unitario) VALUES (:orden, :repuesto, :cantidad, :costo, :precio)');
            $insert->execute(['orden' => $orderId, 'repuesto' => $partId, 'cantidad' => $quantity, 'costo' => $prices['costo_referencia'], 'precio' => $prices['precio_venta']]);
            flash('success', 'El repuesto se agregó a la orden.');
        } elseif (in_array($action, ['delete_service', 'delete_part', 'update_service', 'update_part'], true)) {
            $isService = strpos($action, 'service') !== false;
            $table = $isService ? 'orden_servicios' : 'orden_repuestos';
            $lineId = filter_var($_POST['line_id'] ?? 0, FILTER_VALIDATE_INT);
            if (strpos($action, 'update_') === 0) {
                $quantity = filter_var($_POST['cantidad'] ?? 0, FILTER_VALIDATE_FLOAT);
                if (!$quantity || $quantity <= 0) {
                    throw new InvalidArgumentException('La cantidad debe ser mayor que cero.');
                }
                $update = $db->prepare("UPDATE {$table} SET cantidad = :cantidad WHERE id = :id AND orden_id = :orden");
                $update->execute(['cantidad' => $quantity, 'id' => $lineId, 'orden' => $orderId]);
                $exists = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE id = :id AND orden_id = :orden");
                $exists->execute(['id' => $lineId, 'orden' => $orderId]);
                if ((int) $exists->fetchColumn() !== 1) {
                    throw new InvalidArgumentException('No se encontró el detalle solicitado.');
                }
                flash('success', 'Se actualizó la cantidad del detalle.');
            } else {
                $delete = $db->prepare("DELETE FROM {$table} WHERE id = :id AND orden_id = :orden");
                $delete->execute(['id' => $lineId, 'orden' => $orderId]);
                if ($delete->rowCount() !== 1) {
                    throw new InvalidArgumentException('No se encontró el detalle solicitado.');
                }
                flash('success', 'El detalle se eliminó de la orden.');
            }
        } else {
            throw new InvalidArgumentException('La acción solicitada no es válida.');
        }
        redirect_to('/views/ordenes/detalle.php?id=' . $orderId);
    } catch (InvalidArgumentException $exception) {
        flash('error', $exception->getMessage());
        redirect_to('/views/ordenes/detalle.php?id=' . $orderId);
    } catch (PDOException $exception) {
        error_log('Motor Express order detail: ' . $exception->getMessage());
        flash('error', 'No se pudo actualizar la orden. Comprueba los datos e inténtalo nuevamente.');
        redirect_to('/views/ordenes/detalle.php?id=' . $orderId);
    }
}

$orderQuery = $db->prepare(
    'SELECT o.*, v.placa, v.marca, v.modelo, c.nombre AS cliente, c.telefono, c.email, m.nombre AS mecanico
     FROM ordenes_trabajo o JOIN vehiculos v ON v.id = o.vehiculo_id
     JOIN clientes c ON c.id = o.cliente_id
     LEFT JOIN orden_mecanicos om ON om.orden_id = o.id AND om.es_responsable = 1
     LEFT JOIN mecanicos m ON m.id = om.mecanico_id WHERE o.id = :id'
);
$orderQuery->execute(['id' => $orderId]);
$order = $orderQuery->fetch();
if (!$order) {
    http_response_code(404);
    exit('No se encontró la orden de trabajo solicitada.');
}
$querySets = [
    'servicios' => ['SELECT os.id, s.nombre, os.cantidad, os.precio_unitario, os.estado, os.cantidad * os.precio_unitario AS total FROM orden_servicios os JOIN servicios s ON s.id = os.servicio_id WHERE os.orden_id = :id ORDER BY os.id DESC', 'delete_service'],
    'repuestos' => ['SELECT r.id AS repuesto_id, orp.id, r.codigo, r.nombre, orp.cantidad, orp.costo_unitario, orp.precio_unitario, orp.cantidad * orp.precio_unitario AS total FROM orden_repuestos orp JOIN repuestos r ON r.id = orp.repuesto_id WHERE orp.orden_id = :id ORDER BY orp.id DESC', 'delete_part'],
];
$details = [];
foreach ($querySets as $key => [$sql, $action]) {
    $query = $db->prepare($sql);
    $query->execute(['id' => $orderId]);
    $details[$key] = $query->fetchAll();
}
$diagnostics = $db->prepare('SELECT d.*, m.nombre AS mecanico FROM diagnosticos d JOIN mecanicos m ON m.id = d.mecanico_id WHERE d.orden_id = :id ORDER BY d.fecha DESC');
$diagnostics->execute(['id' => $orderId]);
$diagnostics = $diagnostics->fetchAll();
$payments = $db->prepare('SELECT monto, metodo, referencia, fecha_pago FROM pagos WHERE orden_id = :id AND estado = "registrado" ORDER BY fecha_pago DESC');
$payments->execute(['id' => $orderId]);
$payments = $payments->fetchAll();
$summary = $db->prepare('SELECT * FROM v_resumen_ordenes WHERE orden_id = :id');
$summary->execute(['id' => $orderId]);
$totals = $summary->fetch();
$controller = new BaseController();
$services = $controller->options('servicios');
$parts = $controller->options('repuestos');
$pagina = 'ordenes';
$titulo = 'Detalle de orden ' . $order['numero'];
ob_start();
?>
<section class="panel order-summary">
    <div class="section-heading"><div><p class="eyebrow"><?= e($order['placa']) ?> · <?= e($order['marca']) ?> <?= e($order['modelo']) ?></p><h2><?= e($order['cliente']) ?></h2></div><span class="status-badge"><?= e(str_replace('_', ' ', $order['estado'])) ?></span></div>
    <div class="detail-grid">
        <div><b>Ingreso</b><span><?= e($order['fecha_ingreso']) ?></span></div>
        <div><b>Kilometraje</b><span><?= number_format((int) $order['kilometraje']) ?> km</span></div>
        <div><b>Mecánico responsable</b><span><?= e($order['mecanico'] ?? 'Sin asignar') ?></span></div>
        <div><b>Motivo</b><span><?= e($order['motivo_ingreso']) ?></span></div>
    </div>
    <div class="stats order-totals"><article class="stat"><span>Total</span><strong>S/ <?= number_format((float) ($totals['total'] ?? 0), 2) ?></strong></article><article class="stat"><span>Pagado</span><strong>S/ <?= number_format((float) ($totals['total_pagado'] ?? 0), 2) ?></strong></article><article class="stat"><span>Saldo</span><strong>S/ <?= number_format((float) ($totals['saldo'] ?? 0), 2) ?></strong></article></div>
</section>
<div class="dashboard-grid order-detail-grid">
    <section class="panel">
        <div class="section-heading"><h2>Servicios de reparación</h2></div>
        <form class="inline-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="orden_id" value="<?= (int) $orderId ?>"><input type="hidden" name="action" value="add_service">
            <label>Servicio<select name="servicio_id" required><option value="">Seleccionar servicio</option><?php foreach ($services as $item): ?><option value="<?= (int) $item['id'] ?>"><?= e($item['label']) ?></option><?php endforeach ?></select></label>
            <label>Cantidad<input type="number" name="cantidad" min="0.01" step="0.01" value="1" required></label><button class="button" type="submit">Agregar</button>
        </form>
        <?php render_order_lines($details['servicios'], 'service'); ?>
    </section>
    <section class="panel">
        <div class="section-heading"><h2>Repuestos utilizados</h2></div>
        <form class="inline-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="orden_id" value="<?= (int) $orderId ?>"><input type="hidden" name="action" value="add_part">
            <label>Repuesto<select name="repuesto_id" required><option value="">Seleccionar repuesto</option><?php foreach ($parts as $item): ?><option value="<?= (int) $item['id'] ?>"><?= e($item['label']) ?></option><?php endforeach ?></select></label>
            <label>Cantidad<input type="number" name="cantidad" min="0.01" step="0.01" value="1" required></label><button class="button" type="submit">Agregar</button>
        </form>
        <?php render_order_lines($details['repuestos'], 'part'); ?>
    </section>
    <section class="panel"><div class="section-heading"><h2>Diagnósticos</h2><a href="../diagnosticos/form.php?orden_id=<?= (int) $orderId ?>">+ Agregar</a></div>
        <?php if ($diagnostics): ?><ul class="detail-list"><?php foreach ($diagnostics as $diagnostic): ?><li><b><?= e($diagnostic['mecanico']) ?> · <?= e($diagnostic['fecha']) ?></b><span><?= e($diagnostic['descripcion']) ?></span><?php if ($diagnostic['observaciones']): ?><small><?= e($diagnostic['observaciones']) ?></small><?php endif ?></li><?php endforeach ?></ul><?php else: ?><p class="muted">Todavía no hay diagnósticos.</p><?php endif ?>
    </section>
    <section class="panel"><div class="section-heading"><h2>Pagos</h2><a href="../pagos/form.php?orden_id=<?= (int) $orderId ?>">+ Registrar pago</a></div>
        <?php if ($payments): ?><div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Método</th><th>Referencia</th><th>Monto</th></tr></thead><tbody><?php foreach ($payments as $payment): ?><tr><td><?= e($payment['fecha_pago']) ?></td><td><?= e(str_replace('_', ' ', $payment['metodo'])) ?></td><td><?= e($payment['referencia']) ?></td><td>S/ <?= number_format((float) $payment['monto'], 2) ?></td></tr><?php endforeach ?></tbody></table></div><?php else: ?><p class="muted">Todavía no se registran pagos.</p><?php endif ?>
    </section>
</div>
<p><a class="button secondary" href="index.php">← Volver a órdenes</a></p>
<?php
function render_order_lines(array $rows, string $kind): void
{
    if (!$rows) {
        echo '<p class="muted">No hay elementos registrados.</p>';
        return;
    }
    ?>
    <div class="table-wrap"><table><thead><tr><th><?= $kind === 'service' ? 'Servicio' : 'Repuesto' ?></th><th>Cantidad</th><th>Precio</th><th>Total</th><th></th></tr></thead><tbody>
        <?php foreach ($rows as $row): ?><tr><td><?= e($row['nombre']) ?></td><td><form class="quantity-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="orden_id" value="<?= (int) ($_GET['id'] ?? 0) ?>"><input type="hidden" name="line_id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="action" value="<?= $kind === 'service' ? 'update_service' : 'update_part' ?>"><input type="number" name="cantidad" min="0.01" step="0.01" value="<?= e($row['cantidad']) ?>" required><button class="link-button" type="submit">Actualizar</button></form></td><td>S/ <?= number_format((float) $row['precio_unitario'], 2) ?></td><td>S/ <?= number_format((float) $row['total'], 2) ?></td><td><form method="post" onsubmit="return confirm('¿Eliminar este detalle?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="orden_id" value="<?= (int) ($_GET['id'] ?? 0) ?>"><input type="hidden" name="line_id" value="<?= (int) $row['id'] ?>"><input type="hidden" name="action" value="<?= $kind === 'service' ? 'delete_service' : 'delete_part' ?>"><button class="link-button" type="submit">Quitar</button></form></td></tr><?php endforeach ?>
    </tbody></table></div>
    <?php
}
$contenido = ob_get_clean();
require __DIR__ . '/../layout.php';
