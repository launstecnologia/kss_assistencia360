<?php

namespace App\Models;

use App\Core\Database;

class Categoria extends Model
{
    protected string $table = 'categorias';
    protected array $fillable = [
        'nome', 'descricao', 'icone', 'cor', 'status', 'ordem', 'created_at', 'updated_at'
    ];
    protected array $casts = [
        'prazo_minimo' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getAtivas(): array
    {
        return $this->findAll(['status' => 'ATIVA'], 'nome ASC');
    }

    public function getByTipo(string $tipo): array
    {
        return $this->findAll(['tipo_assistencia' => $tipo, 'status' => 'ATIVA'], 'nome ASC');
    }

    public function getSubcategorias(int $categoriaId): array
    {
        $sql = "SELECT * FROM subcategorias WHERE categoria_id = ? AND status = 'ATIVA' ORDER BY nome ASC";
        return Database::fetchAll($sql, [$categoriaId]);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }

    public function getAll(): array
    {
        return $this->findAll([], 'ordem ASC, nome ASC');
    }

    public function getById(int $id): ?array
    {
        return $this->find($id);
    }

    public function countSolicitacoes(int $categoriaId): int
    {
        $sql = "SELECT COUNT(*) as total FROM solicitacoes WHERE categoria_id = ?";
        $result = Database::fetch($sql, [$categoriaId]);
        return $result['total'] ?? 0;
    }

    public function getEstatisticas(int $categoriaId, string $periodo = '30'): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_solicitacoes,
                COUNT(CASE WHEN st.nome = 'Concluído' THEN 1 END) as concluidas,
                AVG(TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at)) as tempo_medio_resolucao
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            WHERE s.categoria_id = ? 
            AND s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ";
        
        return Database::fetch($sql, [$categoriaId, $periodo]) ?: [];
    }
}
