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
        if ((new AuthController())->login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            redirect_to('/views/home.php');
        }
        $error = 'El correo o la contraseña no son correctos, o la cuenta está inactiva.';
    } catch (PDOException $exception) {
        error_log('Motor Express login: ' . $exception->getMessage());
        $error = 'No se pudo acceder al sistema. Verifica la conexión con la base de datos.';
    }
}
$registro = false;
require __DIR__ . '/auth-form.php';
