<?php

namespace App\Models;

use App\Core\Database;

class Subcategoria extends Model
{
    protected string $table = 'subcategorias';
    protected array $fillable = [
        'categoria_id', 'nome', 'descricao', 'tempo_estimado', 'status', 'ordem', 'created_at', 'updated_at'
    ];
    protected array $casts = [
        'categoria_id' => 'int',
        'prazo_minimo' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getByCategoria(int $categoriaId): array
    {
        return $this->findAll(['categoria_id' => $categoriaId], 'ordem ASC, nome ASC');
    }

    public function getById(int $id): ?array
    {
        return $this->find($id);
    }

    public function countByCategoria(int $categoriaId): int
    {
        $sql = "SELECT COUNT(*) as total FROM subcategorias WHERE categoria_id = ?";
        $result = Database::fetch($sql, [$categoriaId]);
        return $result['total'] ?? 0;
    }

    public function countSolicitacoes(int $subcategoriaId): int
    {
        $sql = "SELECT COUNT(*) as total FROM solicitacoes WHERE subcategoria_id = ?";
        $result = Database::fetch($sql, [$subcategoriaId]);
        return $result['total'] ?? 0;
    }

    public function getAtivas(): array
    {
        return $this->findAll(['status' => 'ATIVA'], 'nome ASC');
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

    public function getCategoria(int $subcategoriaId): ?array
    {
        $sql = "
            SELECT c.* 
            FROM categorias c
            INNER JOIN subcategorias sc ON c.id = sc.categoria_id
            WHERE sc.id = ?
        ";
        
        return Database::fetch($sql, [$subcategoriaId]);
    }

    public function getEstatisticas(int $subcategoriaId, string $periodo = '30'): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_solicitacoes,
                COUNT(CASE WHEN st.nome = 'Concluído' THEN 1 END) as concluidas,
                AVG(TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at)) as tempo_medio_resolucao
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            WHERE s.subcategoria_id = ? 
            AND s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ";
        
        return Database::fetch($sql, [$subcategoriaId, $periodo]) ?: [];
    }

    public function calcularDataLimiteAgendamento(int $subcategoriaId): \DateTime
    {
        $subcategoria = $this->find($subcategoriaId);
        $prazoMinimo = $subcategoria['prazo_minimo'] ?? 1;
        
        $dataLimite = new \DateTime();
        $dataLimite->add(new \DateInterval("P{$prazoMinimo}D"));
        
        // Não permitir agendamento em fins de semana
        while (in_array($dataLimite->format('N'), [6, 7])) {
            $dataLimite->add(new \DateInterval('P1D'));
        }
        
        return $dataLimite;
    }

    public function getHorariosDisponiveis(int $subcategoriaId, string $data): array
    {
        $subcategoria = $this->find($subcategoriaId);
        $prazoMinimo = $subcategoria['prazo_minimo'] ?? 1;
        
        $dataSolicitada = new \DateTime($data);
        $hoje = new \DateTime();
        
        // Verificar se a data está dentro do prazo mínimo
        if ($dataSolicitada < $this->calcularDataLimiteAgendamento($subcategoriaId)) {
            return [];
        }
        
        // Horários comerciais (8h às 18h)
        $horarios = [];
        for ($hora = 8; $hora <= 17; $hora++) {
            $horarios[] = sprintf('%02d:00', $hora);
            $horarios[] = sprintf('%02d:30', $hora);
        }
        
        return $horarios;
    }
}
