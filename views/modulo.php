<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../controller/BaseController.php';
require_login();

$controller = new BaseController();
$allModules = BaseController::modules();
$pagina = $pagina ?? '';
if (!isset($allModules[$pagina])) {
    http_response_code(404);
    exit('Módulo no encontrado.');
}
$config = $allModules[$pagina];
$titulo = $config['title'];
$error = '';
$recordId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$editRecord = $recordId ? $controller->find($pagina, $recordId) : [];
if (!$recordId && in_array($pagina, ['diagnosticos', 'pagos'], true) && isset($_GET['orden_id'])) {
    $editRecord['orden_id'] = filter_var($_GET['orden_id'], FILTER_VALIDATE_INT) ?: '';
}
if (!$recordId && $pagina === 'ordenes') {
    foreach (['cita_id', 'vehiculo_id'] as $field) {
        if (isset($_GET[$field])) {
            $editRecord[$field] = filter_var($_GET[$field], FILTER_VALIDATE_INT) ?: '';
        }
    }
}
if ($recordId && !$editRecord) {
    flash('error', 'No se encontró el registro solicitado.');
    redirect_to('/views/' . $pagina . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (($_POST['action'] ?? '') === 'delete') {
            $controller->delete($pagina, filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0);
            flash('success', 'El registro se eliminó correctamente.');
        } else {
            $controller->save($pagina, filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null, $_POST, (int) $_SESSION['user']['id']);
            flash('success', 'Los cambios se guardaron correctamente.');
        }
        redirect_to('/views/' . $pagina . '/index.php');
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
        $editRecord = array_merge($editRecord ?? [], $_POST);
    } catch (PDOException $exception) {
        error_log('Motor Express: ' . $exception->getMessage());
        $error = 'No se pudo guardar el registro. Revisa que sus datos no estén duplicados y que no existan registros relacionados.';
        $editRecord = array_merge($editRecord ?? [], $_POST);
    }
}

$mostrarFormulario = isset($_GET['nuevo']) || ($mostrarFormulario ?? false) || $recordId !== null || ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'delete');
ob_start();
?>
<p class="page-description"><?= e($config['description']) ?></p>
<?php if ($error !== ''): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif ?>
<?php if ($mostrarFormulario): ?>
    <section class="panel form-panel">
        <div class="section-heading"><h2><?= $recordId ? 'Editar registro' : 'Nuevo registro' ?></h2><a href="index.php">← Volver</a></div>
        <form method="post" action="<?= e($recordId ? 'index.php?id=' . $recordId : 'index.php?nuevo=1') ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <?php if ($recordId): ?><input type="hidden" name="id" value="<?= (int) $recordId ?>"><?php endif ?>
            <div class="form-grid">
                <?php foreach ($config['fields'] as $name => $definition):
                    $label = $definition[0];
                    $type = $definition[1];
                    $required = !((isset($definition[2]) && $definition[2] === false) || (isset($definition[3]) && $definition[3] === false));
                    $value = $editRecord[$name] ?? '';
                    if ($type === 'datetime' && $value !== '') {
                        $value = date('Y-m-d\TH:i', strtotime($value));
                    }
                    ?>
                    <label><?= e($label) ?>
                        <?php if ($type === 'select' || $type === 'relation'): ?>
                            <?php $options = $type === 'select' ? array_map(static fn($key, $text) => ['id' => $key, 'label' => $text], array_keys($definition[2]), array_values($definition[2])) : $controller->options($definition[2]); ?>
                            <select name="<?= e($name) ?>" <?= $required ? 'required' : '' ?>>
                                <option value=""><?= $required ? 'Selecciona una opción' : 'Sin asignar' ?></option>
                                <?php foreach ($options as $option): ?>
                                    <option value="<?= e($option['id']) ?>" <?= (string) $value === (string) $option['id'] ? 'selected' : '' ?>><?= e($option['label']) ?></option>
                                <?php endforeach ?>
                            </select>
                        <?php elseif ($type === 'textarea'): ?>
                            <textarea name="<?= e($name) ?>" rows="3" <?= $required ? 'required' : '' ?>><?= e($value) ?></textarea>
                        <?php else: ?>
                            <input name="<?= e($name) ?>" type="<?= e($type === 'datetime' ? 'datetime-local' : $type) ?>" value="<?= e($value) ?>" <?= $required ? 'required' : '' ?>
                                <?php if ($type === 'number'): ?>min="<?= e($definition[2]) ?>" max="<?= e($definition[3]) ?>" step="<?= e($definition[4] ?? '1') ?>"<?php endif ?>>
                        <?php endif ?>
                    </label>
                <?php endforeach ?>
            </div>
            <div class="form-actions"><a class="button secondary" href="index.php">Cancelar</a><button class="button" type="submit">Guardar registro</button></div>
        </form>
    </section>
<?php else: ?>
    <?php $records = $controller->records($pagina); ?>
    <section class="panel">
        <div class="toolbar">
            <label class="search-box"><span>⌕</span><input type="search" placeholder="Buscar en <?= e(mb_strtolower($titulo)) ?>" aria-label="Buscar registros"></label>
            <a class="button" href="index.php?nuevo=1">+ Nuevo registro</a>
        </div>
        <?php require __DIR__ . '/' . $pagina . '/lista.php'; ?>
    </section>
<?php endif ?>
<?php
$contenido = ob_get_clean();
require __DIR__ . '/layout.php';
