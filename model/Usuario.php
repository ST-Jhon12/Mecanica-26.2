<?php

require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        $query = $this->db->prepare(
            'SELECT id, nombre, email, password_hash, rol, activo FROM usuarios WHERE email = :email LIMIT 1'
        );
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        return $user ?: null;
    }

    public function create(string $name, string $email, string $password): void
    {
        $query = $this->db->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash) VALUES (:nombre, :email, :password_hash)'
        );
        $query->execute([
            'nombre' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
}