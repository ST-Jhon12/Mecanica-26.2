<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../model/Usuario.php';

class AuthController
{
    public function login(string $email, string $password): bool
    {
        $user = (new Usuario())->findByEmail(mb_strtolower(trim($email)));
        if (!$user || !(int) $user['activo'] || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'rol' => $user['rol'],
        ];
        return true;
    }

    public function register(string $name, string $email, string $password): void
    {
        if (mb_strlen(trim($name)) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ingresa un nombre y un correo electrónico válidos.');
        }
        if (strlen($password) < 10) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 10 caracteres.');
        }
        (new Usuario())->create(trim($name), mb_strtolower(trim($email)), $password);
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}