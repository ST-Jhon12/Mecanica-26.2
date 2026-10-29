<?php
$modulos = require __DIR__ . '/modulos.php';
$pagina = $pagina ?? 'home';
$titulo = $titulo ?? 'Panel general';
function e($texto) { return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'); }
$base = '/motor-express';
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($titulo) ?> | Motor Express</title><link rel="stylesheet" href="<?= $base ?>/assetes/css/estilos.css"></head><body>
<div class="topbar"><span>TALLER AUTOMOTRIZ</span><span>Precisión en cada reparación</span></div>
<header class="header"><a class="brand" href="<?= $base ?>/views/home.php"><span class="brand-icon">M<span>↗</span></span><span>MOTOR <b>EXPRESS</b><small>GESTIÓN DEL TALLER</small></span></a><div class="header-end"><span class="preview">Vista previa</span><span class="avatar">ME</span><button class="menu-toggle" aria-label="Mostrar menú" aria-expanded="false">☰</button></div></header>
<nav class="navbar" aria-label="Navegación principal"><a href="<?= $base ?>/views/home.php">Inicio</a><a href="<?= $base ?>/views/citas/index.php">Agenda</a><a href="<?= $base ?>/views/ordenes/index.php">Órdenes de trabajo</a><a href="<?= $base ?>/views/reportes/index.php">Reportes</a></nav>
<div class="workspace"><main><div class="page-heading"><div><p class="eyebrow">MOTOR EXPRESS / <?= e($titulo) ?></p><h1><?= e($titulo) ?></h1></div><span class="date"><?= date('d/m/Y') ?></span></div><?= $contenido ?></main><aside class="sidebar"><p class="menu-label">PRINCIPAL</p><a class="<?= $pagina==='home'?'selected':'' ?>" href="<?= $base ?>/views/home.php"><span>◈</span> Panel general</a><?php foreach($modulos as $clave=>$modulo): ?><a class="<?= $pagina===$clave?'selected':'' ?>" href="<?= $base ?>/views/<?= e($clave) ?>/index.php"><span>◇</span><?= e($modulo[0]) ?></a><?php endforeach ?><div class="sidebar-bottom"><span class="status-dot"></span> Tu taller, en orden<a href="<?= $base ?>/views/login.php">Volver al acceso →</a></div></aside></div>
<footer><span>© <?= date('Y') ?> Motor Express</span><span>Cuidado automotriz. Gestión eficiente.</span></footer><div id="toast" role="status"></div><script src="<?= $base ?>/assetes/js/app.js"></script></body></html>