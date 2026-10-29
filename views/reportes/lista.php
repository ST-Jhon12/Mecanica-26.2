<?php if ($resultado): ?>
    <section class="panel report-results">
        <div class="section-heading"><h2><?= e($reportTypes[$_POST['tipo']]) ?></h2><span><?= e($resultado['desde']) ?> — <?= e($resultado['hasta']) ?></span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><?php foreach ($resultado['columns'] as $label): ?><th><?= e($label) ?></th><?php endforeach ?></tr></thead>
                <tbody>
                    <?php foreach ($resultado['rows'] as $row): ?>
                        <tr><?php foreach ($resultado['columns'] as $key => $label): ?><td><?= e($row[$key] ?? '') ?></td><?php endforeach ?></tr>
                    <?php endforeach ?>
                    <?php if (!$resultado['rows']): ?>
                        <tr><td colspan="<?= count($resultado['columns']) ?>"><div class="empty"><h3>Sin resultados</h3><p>No hay datos para el periodo seleccionado.</p></div></td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif ?>
