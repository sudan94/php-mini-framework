<?php

namespace App\Core;

use PDO;
use DateTime;

class DatabaseLogger extends Logger
{

    private PDO $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function log(string $level, string $message): void
    {
        $stmt = $this->db->prepare("INSERT INTO logs (level, message, created_at) VALUES (:level, :message, :created_at)");
        $stmt->execute([
            'level' => $level,
            'message' => $message,
            'created_at' => (new DateTime())->format("Y-m-d H:i:s")
        ]);
    }
}
