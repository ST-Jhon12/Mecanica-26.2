<?php $notice = consume_flash(); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $registro ? 'Crear cuenta' : 'Iniciar sesión' ?> | Motor Express</title>
    <link rel="stylesheet" href="<?= e(app_base_url()) ?>/assetes/css/estilos.css">
</head>
<body class="auth">
<section class="auth-story">
    <a class="brand" href="<?= e(app_base_url()) ?>/views/login.php"><span class="brand-icon">M↗</span><span>MOTOR <b>EXPRESS</b><small>GESTIÓN DEL TALLER</small></span></a>
    <div><p class="eyebrow">EL MOTOR DE TU DÍA A DÍA</p><h1>Un buen servicio<br>empieza con<br><em>orden.</em></h1><p>Clientes, vehículos y reparaciones.<br>Todo en un mismo lugar.</p><img src="<?= e(app_base_url()) ?>/imagenes/vehiculo.svg" alt="Automóvil Motor Express"></div>
    <small>PRECISIÓN · CONFIANZA · SERVICIO</small>
</section>
<main class="auth-main"><div class="auth-card">
    <p class="eyebrow">BIENVENIDO A MOTOR EXPRESS</p>
    <h2><?= $registro ? 'Crea tu cuenta' : 'Volvamos al taller' ?></h2>
    <p><?= $registro ? 'Completa tus datos para registrarte.' : 'Ingresa tus datos para continuar.' ?></p>
    <?php if ($notice): ?><div class="notice <?= e($notice['type']) ?>" role="status"><?= e($notice['message']) ?></div><?php endif ?>
    <?php if ($error !== ''): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif ?>
    <form method="post" action="<?= $registro ? 'registro.php' : 'login.php' ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($registro): ?><label>Nombre completo<input name="nombre" required minlength="2" autocomplete="name" value="<?= e($_POST['nombre'] ?? '') ?>" placeholder="Tu nombre"></label><?php endif ?>
        <label>Correo electrónico<input name="email" type="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="nombre@correo.com"></label>
        <label>Contraseña<div class="password-field"><input name="password" type="password" required <?= $registro ? 'minlength="10" autocomplete="new-password"' : 'autocomplete="current-password"' ?> placeholder="<?= $registro ? 'Mínimo 10 caracteres' : 'Tu contraseña' ?>"><button type="button" class="password-toggle" aria-label="Mostrar contraseña">Ver</button></div></label>
        <?php if ($registro): ?><label>Confirmar contraseña<input name="password_confirm" type="password" required minlength="10" autocomplete="new-password" placeholder="Repite tu contraseña"></label><?php endif ?>
        <button class="button" type="submit"><?= $registro ? 'Crear cuenta' : 'Ingresar' ?> →</button>
    </form>
    <p class="auth-switch"><?= $registro ? '¿Ya tienes una cuenta?' : '¿Primera vez aquí?' ?> <a href="<?= $registro ? 'login' : 'registro' ?>.php"><?= $registro ? 'Inicia sesión' : 'Regístrate' ?></a></p>
    <small class="auth-note">Motor Express · Gestión segura del taller</small>
</div></main>
<script src="<?= e(app_base_url()) ?>/assetes/js/app.js"></script>
</body>
</html>
