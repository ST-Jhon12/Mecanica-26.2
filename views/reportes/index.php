<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controller/ReporteController.php';
require_login();

$pagina = 'reportes';
$titulo = 'Reportes';
$resultado = null;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $resultado = (new ReporteController())->consultar(
            (string) ($_POST['tipo'] ?? ''),
            (string) ($_POST['desde'] ?? ''),
            (string) ($_POST['hasta'] ?? '')
        );
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log('Motor Express report: ' . $exception->getMessage());
        $error = 'No se pudo consultar el reporte. Verifica la conexión y vuelve a intentarlo.';
    }
}
$reportTypes = [
    'servicios' => 'Servicios realizados',
    'vehiculos' => 'Vehículos atendidos',
    'pendientes' => 'Reparaciones pendientes',
    'ingresos' => 'Ingresos obtenidos',
];
ob_start();
?>
<p class="page-description">Consulta la actividad y los ingresos del taller en un rango de fechas.</p>
<?php if ($error !== ''): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif ?>
<section class="panel form-panel">
    <h2>Consultar reporte</h2>
    <form method="post" action="index.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid">
            <label>Tipo de reporte
                <select name="tipo" required>
                    <option value="">Selecciona un reporte</option>
                    <?php foreach ($reportTypes as $key => $label): ?><option value="<?= e($key) ?>" <?= ($_POST['tipo'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach ?>
                </select>
            </label>
            <label>Desde<input type="date" name="desde" value="<?= e($_POST['desde'] ?? date('Y-m-01')) ?>" required></label>
            <label>Hasta<input type="date" name="hasta" value="<?= e($_POST['hasta'] ?? date('Y-m-d')) ?>" required></label>
        </div>
        <div class="form-actions"><button class="button" type="submit">Consultar reporte</button></div>
    </form>
</section>
<?php require __DIR__ . '/lista.php'; ?>
<?php
$contenido = ob_get_clean();
require __DIR__ . '/../layout.php';
