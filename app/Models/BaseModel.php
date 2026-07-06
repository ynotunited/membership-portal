<?php
namespace App\Models;

class BaseModel
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getConnection()
    {
        return $this->db;
    }
} 