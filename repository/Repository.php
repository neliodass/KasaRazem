<?php
require_once  "core/Database.php";
class Repository
{
    protected PDO $conn;

    public function __construct(Database $db = null)
    {
        $db = $db ?? Database::getInstance();
        $this->conn = $db->connect();
    }
}