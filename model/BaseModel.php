<?php

require_once __DIR__ . '/../config/database.php';

class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = database();
    }
}