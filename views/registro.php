<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../controller/AuthController.php';

if (!empty($_SESSION['user']['id'])) {
    redirect_to('/views/home.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (($_POST['password'] ?? '') !== ($_POST['password_confirm'] ?? '')) {
            throw new InvalidArgumentException('Las contraseñas no coinciden.');
        }
        (new AuthController())->register(
            (string) ($_POST['nombre'] ?? ''),
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );
        flash('success', 'Tu cuenta se creó correctamente. Ya puedes iniciar sesión.');
        redirect_to('/views/login.php');
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log('Motor Express register: ' . $exception->getMessage());
        $error = (string) $exception->getCode() === '23000'
            ? 'Ya existe una cuenta con ese correo electrónico.'
            : 'No se pudo completar el registro. Verifica la conexión con la base de datos.';
    }
}
$registro = true;
require __DIR__ . '/auth-form.php';
