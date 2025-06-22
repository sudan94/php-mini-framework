<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Session;
use PDO;

class User extends Model
{
    protected string $table = 'users';

    public function __construct(PDO $db){
        parent::__construct($db);
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function createUser(array $data): int
    {
        unset($data["confirm_password"]);
        $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);

        $data["created_at"] = date("y-m-d H:i:s");

        $stmt = $this->db->prepare("INSERT INTO {$this->table} SET name = :name, email = :email, password = :password, created_at = :created_at");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function UpdateUser(array $data) : int
    {
        $row = $this->update(Session::userId(), $data);
        return $row;
    }

       public function deleteUser() : int
    {
        $row = $this->delete(Session::userId());
        return $row;
    }
}