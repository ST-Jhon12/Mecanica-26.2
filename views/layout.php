<?php
require_once __DIR__ . '/../config/app.php';
$modules = require __DIR__ . '/modulos.php';
$page = $pagina ?? 'home';
$title = $titulo ?? 'Panel general';
$base = app_base_url();
$currentUser = $_SESSION['user'] ?? null;
$flashMessage = consume_flash();
function layout_url(string $path): string
{
    return app_base_url() . $path;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | Motor Express</title>
    <link rel="stylesheet" href="<?= e($base) ?>/assetes/css/estilos.css">
</head>
<body>
<div class="topbar"><span>TALLER AUTOMOTRIZ</span><span>Precisión en cada reparación</span></div>
<header class="header">
    <a class="brand" href="<?= e(layout_url('/views/home.php')) ?>"><span class="brand-icon">M<span>↗</span></span><span>MOTOR <b>EXPRESS</b><small>GESTIÓN DEL TALLER</small></span></a>
    <div class="header-end">
        <span class="preview"><?= e($currentUser['nombre'] ?? '') ?></span>
        <span class="avatar"><?= e(strtoupper(substr($currentUser['nombre'] ?? 'ME', 0, 2))) ?></span>
        <form class="logout-form" action="<?= e(layout_url('/views/logout.php')) ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit">Cerrar sesión</button>
        </form>
        <button class="menu-toggle" aria-label="Mostrar menú" aria-expanded="false">☰</button>
    </div>
</header>
<nav class="navbar" aria-label="Navegación principal">
    <a href="<?= e(layout_url('/views/home.php')) ?>">Inicio</a>
    <a href="<?= e(layout_url('/views/citas/index.php')) ?>">Agenda</a>
    <a href="<?= e(layout_url('/views/ordenes/index.php')) ?>">Órdenes de trabajo</a>
    <a href="<?= e(layout_url('/views/reportes/index.php')) ?>">Reportes</a>
</nav>
<div class="workspace">
    <main>
        <div class="page-heading"><div><p class="eyebrow">MOTOR EXPRESS / <?= e($title) ?></p><h1><?= e($title) ?></h1></div><span class="date"><?= date('d/m/Y') ?></span></div>
        <?php if ($flashMessage): ?><div class="notice <?= e($flashMessage['type']) ?>" role="status"><?= e($flashMessage['message']) ?></div><?php endif ?>
        <?= $contenido ?? '' ?>
    </main>
    <aside class="sidebar">
        <p class="menu-label">PRINCIPAL</p>
        <a class="<?= $page === 'home' ? 'selected' : '' ?>" href="<?= e(layout_url('/views/home.php')) ?>"><span>◈</span> Panel general</a>
        <?php foreach ($modules as $key => $module): ?>
            <a class="<?= $page === $key ? 'selected' : '' ?>" href="<?= e(layout_url('/views/' . $key . '/index.php')) ?>"><span>◇</span><?= e($module[0]) ?></a>
        <?php endforeach ?>
        <div class="sidebar-bottom"><span class="status-dot"></span> Tu taller, en orden</div>
    </aside>
</div>
<footer><span>© <?= date('Y') ?> Motor Express</span><span>Cuidado automotriz. Gestión eficiente.</span></footer>
<script src="<?= e($base) ?>/assetes/js/app.js"></script>
</body>
</html>
