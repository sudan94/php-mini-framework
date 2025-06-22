<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $host = $_ENV["DB_HOST"];
        $dbname = $_ENV["DB_NAME"];
        $username = $_ENV["DB_USER"];
        $password = $_ENV["DB_PASS"];
        try{
            $this->connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            die("Connection Failed: ".$e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if(self::$instance === null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO{
        return $this->connection;
    }
}