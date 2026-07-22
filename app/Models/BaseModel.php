<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $pdo;
    protected string $table = '';

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function all(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT {$limit}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn ($column) => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(',', $columns),
            implode(',', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }
}
