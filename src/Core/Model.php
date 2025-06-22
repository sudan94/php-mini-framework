<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primayKey = 'id';

    public function __construct(PDO $db){
        $this->db = $db;
    }

    public function all(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE id= :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): ?int
    {
        $columns = implode(",", array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $stmt = $this->db->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        $stmt->execute($data);
        $affected_rows = $stmt->rowCount();
        return $affected_rows;

    }

    public function update(int $id, array $data): ?int{
        $set = implode(", ", array_map(fn($col) => "$col =:$col", array_keys($data)));
        $data["id"] = $id;

        $stmt = $this->db->prepare("UPDATE {$this->table} SET $set WHERE id= :id");
        $stmt->execute($data);
        $affected_rows = $stmt->rowCount();
        return $affected_rows;
    }

    public function delete(int $id): ?int
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id= :id");
        $stmt->execute(["id" => $id]);
        $affected_rows = $stmt->rowCount();
        return $affected_rows;
    }

}