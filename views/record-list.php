<div class="table-wrap">
    <table class="records-table">
        <thead>
            <tr>
                <?php foreach ($config['columns'] as $label): ?><th><?= e($label) ?></th><?php endforeach ?>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr data-search="<?= e(implode(' ', array_map('strval', $record))) ?>">
                    <?php foreach ($config['columns'] as $column => $label):
                        $cell = $record[$column] ?? '';
                        if (in_array($column, ['estado', 'metodo'], true)) {
                            $cell = ucfirst(str_replace('_', ' ', (string) $cell));
                        }
                        ?>
                        <td><?= e($cell) ?></td>
                    <?php endforeach ?>
                    <td class="actions">
                        <?php if ($pagina === 'ordenes'): ?><a href="detalle.php?id=<?= (int) $record['id'] ?>">Detalle</a><?php endif ?>
                        <?php if ($pagina === 'citas' && !in_array($record['estado'], ['atendida', 'cancelada'], true)): ?>
                            <a href="../ordenes/form.php?cita_id=<?= (int) $record['id'] ?>&amp;vehiculo_id=<?= (int) $record['vehiculo_id'] ?>">Crear orden</a>
                        <?php endif ?>
                        <a href="index.php?id=<?= (int) $record['id'] ?>">Editar</a>
                        <form method="post" action="index.php" onsubmit="return confirm('¿Deseas eliminar este registro?')">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                            <button type="submit" class="link-button">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
            <?php if (!$records): ?>
                <tr><td colspan="<?= count($config['columns']) + 1 ?>">
                    <div class="empty"><span class="empty-icon">▤</span><h3>No hay registros para mostrar</h3><p>Agrega el primer registro con el botón «Nuevo registro».</p></div>
                </td></tr>
            <?php else: ?>
                <tr class="search-empty" hidden><td colspan="<?= count($config['columns']) + 1 ?>">
                    <div class="empty"><h3>Sin coincidencias</h3><p>No se encontraron registros para esta búsqueda.</p></div>
                </td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
