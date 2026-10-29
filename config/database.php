<?php

function database(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('MOTOR_DB_HOST') ?: '127.0.0.1';
    $name = getenv('MOTOR_DB_NAME') ?: 'motor_express_db';
    $user = getenv('MOTOR_DB_USER') ?: 'root';
    $password = getenv('MOTOR_DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    $connection = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}