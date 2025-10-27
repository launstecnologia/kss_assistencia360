<?php

namespace App\Models;

use App\Core\Database;

class Locatario extends Model
{
    protected string $table = 'locatarios';
    
    protected array $fillable = [
        'imobiliaria_id', 'ksi_cliente_id', 'nome', 'cpf', 'email', 
        'telefone', 'whatsapp', 'endereco_logradouro', 'endereco_numero',
        'endereco_complemento', 'endereco_bairro', 'endereco_cidade',
        'endereco_estado', 'endereco_cep', 'status', 'ultima_sincronizacao'
    ];

    protected array $casts = [
        'ultima_sincronizacao' => 'datetime'
    ];

    /**
     * Buscar locatário por CPF e imobiliária
     */
    public function findByCpfAndImobiliaria(string $cpf, int $imobiliariaId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE cpf = ? AND imobiliaria_id = ? AND status = 'ATIVO'";
        return Database::fetch($sql, [$cpf, $imobiliariaId]);
    }

    /**
     * Buscar locatário por ID do KSI e imobiliária
     */
    public function findByKsiIdAndImobiliaria(string $ksiId, int $imobiliariaId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE ksi_cliente_id = ? AND imobiliaria_id = ? AND status = 'ATIVO'";
        return Database::fetch($sql, [$ksiId, $imobiliariaId]);
    }

    /**
     * Criar ou atualizar locatário
     */
    public function createOrUpdate(array $data): array
    {
        $existing = $this->findByKsiIdAndImobiliaria($data['ksi_cliente_id'], $data['imobiliaria_id']);
        
        if ($existing) {
            // Atualizar dados existentes
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['ultima_sincronizacao'] = date('Y-m-d H:i:s');
            
            $this->update($existing['id'], $data);
            return $existing;
        } else {
            // Criar novo locatário
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['ultima_sincronizacao'] = date('Y-m-d H:i:s');
            
            $id = $this->create($data);
            return $this->find($id);
        }
    }

    /**
     * Buscar imóveis do locatário
     */
    public function getImoveis(int $locatarioId): array
    {
        $sql = "
            SELECT * FROM imoveis_locatarios 
            WHERE locatario_id = ? AND status = 'ATIVO' 
            ORDER BY created_at DESC
        ";
        return Database::fetchAll($sql, [$locatarioId]);
    }

    /**
     * Adicionar imóvel ao locatário
     */
    public function addImovel(int $locatarioId, array $imovelData): int
    {
        $imovelData['locatario_id'] = $locatarioId;
        $imovelData['created_at'] = date('Y-m-d H:i:s');
        $imovelData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($imovelData, 'imoveis_locatarios');
    }

    /**
     * Atualizar WhatsApp do locatário
     */
    public function updateWhatsapp(int $locatarioId, string $whatsapp): bool
    {
        $sql = "UPDATE {$this->table} SET whatsapp = ?, updated_at = NOW() WHERE id = ?";
        return Database::execute($sql, [$whatsapp, $locatarioId]);
    }

    /**
     * Atualizar dados pessoais do locatário
     */
    public function updateDadosPessoais(int $locatarioId, array $dados): bool
    {
        $allowedFields = ['nome', 'email', 'telefone', 'whatsapp', 'endereco_logradouro', 
                         'endereco_numero', 'endereco_complemento', 'endereco_bairro', 
                         'endereco_cidade', 'endereco_estado', 'endereco_cep'];
        
        $updateFields = [];
        $values = [];
        
        foreach ($dados as $field => $value) {
            if (in_array($field, $allowedFields) && $value !== null) {
                $updateFields[] = "{$field} = ?";
                $values[] = $value;
            }
        }
        
        if (empty($updateFields)) {
            return false;
        }
        
        $values[] = $locatarioId;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
        
        return Database::execute($sql, $values);
    }
}
