<?php

namespace App\Models;

use App\Core\Database;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return Database::fetch($sql, [$id]);
    }

    public function findAll(array $conditions = [], string $orderBy = null, int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "$field = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return Database::fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $fillableData = $this->filterFillable($data);
        $fields = array_keys($fillableData);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        Database::query($sql, array_values($fillableData));
        return (int) Database::lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fillableData = $this->filterFillable($data);
        
        // Se não há dados para atualizar, retorna true (não é erro)
        if (empty($fillableData)) {
            return true;
        }
        
        $fields = array_keys($fillableData);
        $setClause = array_map(fn($field) => "$field = ?", $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = ?";
        $params = array_merge(array_values($fillableData), [$id]);
        
        try {
            $stmt = Database::query($sql, $params);
            // Retorna true se a query foi executada sem erros
            // Não importa se linhas foram afetadas ou não
            return true;
        } catch (\PDOException $e) {
            error_log("Erro no update: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = Database::query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function exists(int $id): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return Database::fetch($sql, [$id]) !== null;
    }

    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "$field = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        $result = Database::fetch($sql, $params);
        return (int) $result['count'];
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function cast(array $data): array
    {
        foreach ($this->casts as $field => $type) {
            if (isset($data[$field])) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $data[$field] = (int) $data[$field];
                        break;
                    case 'float':
                    case 'double':
                        $data[$field] = (float) $data[$field];
                        break;
                    case 'bool':
                    case 'boolean':
                        $data[$field] = (bool) $data[$field];
                        break;
                    case 'json':
                        $data[$field] = json_decode($data[$field], true);
                        break;
                }
            }
        }

        return $data;
    }

    protected function hide(array $data): array
    {
        return array_diff_key($data, array_flip($this->hidden));
    }
}
