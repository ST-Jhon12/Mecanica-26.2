<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../controller/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/views/login.php');
}
verify_csrf();
(new AuthController())->logout();
redirect_to('/views/login.php');
