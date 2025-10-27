<?php

namespace App\Models;

use App\Core\Database;

class Status extends Model
{
    protected string $table = 'status';
    protected array $fillable = [
        'nome', 'cor', 'icone', 'ordem', 'template_mensagem', 'notificar_automatico', 'status', 'created_at', 'updated_at'
    ];
    protected array $casts = [
        'ordem' => 'int',
        'notificar_automatico' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getAtivos(): array
    {
        return $this->findAll(['status' => 'ATIVO'], 'ordem ASC');
    }

    public function getKanban(): array
    {
        // Retornar apenas os 4 status principais do Kanban
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE nome IN ('Nova Solicitação', 'Buscando Prestador', 'Serviço Agendado', 'Pendências')
            AND status = 'ATIVO'
            ORDER BY ordem ASC
        ";
        
        return Database::fetchAll($sql);
    }

    public function findByNome(string $nome): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE nome = ? AND status = 'ATIVO'";
        return Database::fetch($sql, [$nome]);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Se não foi definida ordem, usar a próxima disponível
        if (!isset($data['ordem'])) {
            $sql = "SELECT MAX(ordem) as max_ordem FROM {$this->table}";
            $result = Database::fetch($sql);
            $data['ordem'] = ($result['max_ordem'] ?? 0) + 1;
        }
        
        return parent::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }

    public function updateOrdem(array $statusIds): bool
    {
        Database::beginTransaction();
        
        try {
            foreach ($statusIds as $index => $statusId) {
                $this->update($statusId, ['ordem' => $index + 1]);
            }
            
            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollback();
            return false;
        }
    }

    public function getSolicitacoes(int $statusId): array
    {
        $sql = "
            SELECT 
                s.*,
                c.nome as categoria_nome,
                sc.nome as subcategoria_nome,
                i.nome as imobiliaria_nome
            FROM solicitacoes s
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN subcategorias sc ON s.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            WHERE s.status_id = ?
            ORDER BY s.created_at DESC
        ";
        
        return Database::fetchAll($sql, [$statusId]);
    }

    public function getEstatisticas(int $statusId, string $periodo = '30'): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_solicitacoes,
                AVG(TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at)) as tempo_medio_permanencia
            FROM solicitacoes s
            WHERE s.status_id = ? 
            AND s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ";
        
        return Database::fetch($sql, [$statusId, $periodo]) ?: [];
    }

    public function getProximosStatus(int $statusId): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE ordem > (SELECT ordem FROM {$this->table} WHERE id = ?) 
            AND status = 'ATIVO'
            ORDER BY ordem ASC
        ";
        
        return Database::fetchAll($sql, [$statusId]);
    }

    public function getStatusAnteriores(int $statusId): array
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE ordem < (SELECT ordem FROM {$this->table} WHERE id = ?) 
            AND status = 'ATIVO'
            ORDER BY ordem DESC
        ";
        
        return Database::fetchAll($sql, [$statusId]);
    }
}
