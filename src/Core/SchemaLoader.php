<?php

namespace App\Core;

use PDO;

class SchemaLoader
{
    public function __construct(private PDO $db)
    {

    }

    public function runFromFile(string $filePath): void
    {
        if(!file_exists($filePath)){
            throw new \RuntimeException("Schema file not found: $filePath");
        }

        $sql = file_get_contents($filePath);
        $this->db->exec($sql);
    }
}