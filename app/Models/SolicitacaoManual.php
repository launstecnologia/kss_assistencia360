<?php

namespace App\Models;

use App\Core\Database;

class SolicitacaoManual extends Model
{
    protected string $table = 'solicitacoes_manuais';
    
    protected array $fillable = [
        'imobiliaria_id', 'nome_completo', 'cpf', 'whatsapp',
        'tipo_imovel', 'subtipo_imovel', 'cep', 'endereco', 'numero', 
        'complemento', 'bairro', 'cidade', 'estado', 'numero_contrato',
        'categoria_id', 'subcategoria_id', 'descricao_problema',
        'horarios_preferenciais', 'fotos', 'termos_aceitos',
        'status_id', 'migrada_para_solicitacao_id', 'migrada_em', 
        'migrada_por_usuario_id', 'created_at', 'updated_at'
    ];
    
    protected array $casts = [
        'horarios_preferenciais' => 'json',
        'fotos' => 'json',
        'termos_aceitos' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'migrada_em' => 'datetime'
    ];

    /**
     * Buscar todas as solicitações manuais com filtros
     */
    public function getAll(array $filtros = []): array
    {
        $sql = "
            SELECT 
                sm.*,
                st.nome as status_nome,
                st.cor as status_cor,
                st.icone as status_icone,
                c.nome as categoria_nome,
                sc.nome as subcategoria_nome,
                i.nome as imobiliaria_nome,
                i.logo as imobiliaria_logo,
                u.nome as migrada_por_nome,
                CASE WHEN sm.migrada_para_solicitacao_id IS NOT NULL THEN 1 ELSE 0 END as migrada
            FROM solicitacoes_manuais sm
            LEFT JOIN status st ON sm.status_id = st.id
            LEFT JOIN categorias c ON sm.categoria_id = c.id
            LEFT JOIN subcategorias sc ON sm.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON sm.imobiliaria_id = i.id
            LEFT JOIN usuarios u ON sm.migrada_por_usuario_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Filtro por imobiliária
        if (!empty($filtros['imobiliaria_id'])) {
            $sql .= " AND sm.imobiliaria_id = ?";
            $params[] = $filtros['imobiliaria_id'];
        }
        
        // Filtro por status
        if (!empty($filtros['status_id'])) {
            $sql .= " AND sm.status_id = ?";
            $params[] = $filtros['status_id'];
        }
        
        // Filtro por CPF
        if (!empty($filtros['cpf'])) {
            $sql .= " AND sm.cpf = ?";
            $params[] = $filtros['cpf'];
        }
        
        // Filtro por migrada
        if (isset($filtros['migrada'])) {
            if ($filtros['migrada']) {
                $sql .= " AND sm.migrada_para_solicitacao_id IS NOT NULL";
            } else {
                $sql .= " AND sm.migrada_para_solicitacao_id IS NULL";
            }
        }
        
        // Busca por texto
        if (!empty($filtros['busca'])) {
            $sql .= " AND (sm.nome_completo LIKE ? OR sm.cpf LIKE ? OR sm.descricao_problema LIKE ?)";
            $busca = '%' . $filtros['busca'] . '%';
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
        }
        
        $sql .= " ORDER BY sm.created_at DESC";
        
        // Limit
        if (!empty($filtros['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filtros['limit'];
        }
        
        return Database::fetchAll($sql, $params);
    }

    /**
     * Buscar detalhes de uma solicitação manual por ID
     */
    public function getDetalhes(int $id): ?array
    {
        $sql = "
            SELECT 
                sm.*,
                st.nome as status_nome,
                st.cor as status_cor,
                st.icone as status_icone,
                c.nome as categoria_nome,
                c.icone as categoria_icone,
                sc.nome as subcategoria_nome,
                sc.descricao as subcategoria_descricao,
                i.nome as imobiliaria_nome,
                i.telefone as imobiliaria_telefone,
                u.nome as migrada_por_nome
            FROM solicitacoes_manuais sm
            LEFT JOIN status st ON sm.status_id = st.id
            LEFT JOIN categorias c ON sm.categoria_id = c.id
            LEFT JOIN subcategorias sc ON sm.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON sm.imobiliaria_id = i.id
            LEFT JOIN usuarios u ON sm.migrada_por_usuario_id = u.id
            WHERE sm.id = ?
        ";
        
        return Database::fetch($sql, [$id]);
    }

    /**
     * Montar observações para migração
     */
    private function montarObservacoesMigracao(array $solicitacaoManual, int $id): string
    {
        $observacoes = "⚠️ SOLICITAÇÃO CRIADA MANUALMENTE (ID Manual: {$id})\n";
        $observacoes .= "Solicitação realizada por usuário não autenticado através do formulário público.\n";
        $observacoes .= "CPF informado: " . $solicitacaoManual['cpf'];
        
        return $observacoes;
    }

    /**
     * Migrar solicitação manual para o sistema principal
     */
    public function migrarParaSistema(int $id, int $usuarioId): array
    {
        try {
            Database::beginTransaction();
            
            // Buscar dados da solicitação manual
            $solicitacaoManual = $this->getDetalhes($id);
            
            if (!$solicitacaoManual) {
                throw new \Exception('Solicitação manual não encontrada');
            }
            
            // Verificar se já foi migrada
            if ($solicitacaoManual['migrada_para_solicitacao_id']) {
                throw new \Exception('Esta solicitação já foi migrada');
            }
            
            // Buscar status inicial do sistema principal
            $statusModel = new Status();
            $statusInicial = $statusModel->findByNome('Nova Solicitação') 
                          ?? $statusModel->findByNome('Nova')
                          ?? ['id' => 1];
            
            // Preparar dados para criar solicitação normal
            $solicitacaoModel = new Solicitacao();
            $dadosSolicitacao = [
                'imobiliaria_id' => $solicitacaoManual['imobiliaria_id'],
                'categoria_id' => $solicitacaoManual['categoria_id'],
                'subcategoria_id' => $solicitacaoManual['subcategoria_id'],
                'status_id' => $statusInicial['id'],
                
                // Dados do locatário
                'locatario_id' => 0, // ID 0 indica que veio de solicitação manual
                'locatario_nome' => $solicitacaoManual['nome_completo'],
                'locatario_cpf' => $solicitacaoManual['cpf'],
                'locatario_telefone' => $solicitacaoManual['whatsapp'],
                'locatario_email' => null,
                
                // Dados do imóvel
                'imovel_endereco' => $solicitacaoManual['endereco'],
                'imovel_numero' => $solicitacaoManual['numero'],
                'imovel_complemento' => $solicitacaoManual['complemento'],
                'imovel_bairro' => $solicitacaoManual['bairro'],
                'imovel_cidade' => $solicitacaoManual['cidade'],
                'imovel_estado' => $solicitacaoManual['estado'],
                'imovel_cep' => $solicitacaoManual['cep'],
                
                // Descrição e detalhes
                'descricao_problema' => $solicitacaoManual['descricao_problema'],
                'observacoes' => $this->montarObservacoesMigracao($solicitacaoManual, $id),
                'prioridade' => 'NORMAL',
                
                // Horários preferenciais
                'horarios_opcoes' => is_string($solicitacaoManual['horarios_preferenciais']) 
                    ? $solicitacaoManual['horarios_preferenciais'] 
                    : json_encode($solicitacaoManual['horarios_preferenciais'])
            ];
            
            // Criar solicitação no sistema principal
            $solicitacaoId = $solicitacaoModel->create($dadosSolicitacao);
            
            if (!$solicitacaoId) {
                throw new \Exception('Erro ao criar solicitação no sistema principal');
            }
            
            // Se há fotos, copiar para a tabela de fotos
            if (!empty($solicitacaoManual['fotos'])) {
                $fotos = is_string($solicitacaoManual['fotos']) 
                    ? json_decode($solicitacaoManual['fotos'], true) 
                    : $solicitacaoManual['fotos'];
                
                if (is_array($fotos) && count($fotos) > 0) {
                    foreach ($fotos as $foto) {
                        // Extrair nome do arquivo do caminho
                        $nomeArquivo = basename($foto);
                        
                        $sqlFoto = "INSERT INTO fotos (solicitacao_id, nome_arquivo, url_arquivo, created_at) 
                                    VALUES (?, ?, ?, NOW())";
                        Database::query($sqlFoto, [$solicitacaoId, $nomeArquivo, $foto]);
                    }
                }
            }
            
            // Atualizar solicitação manual com ID da migração
            $this->update($id, [
                'migrada_para_solicitacao_id' => $solicitacaoId,
                'migrada_em' => date('Y-m-d H:i:s'),
                'migrada_por_usuario_id' => $usuarioId
            ]);
            
            // Verificar se o usuário existe antes de registrar no histórico
            $usuarioExiste = Database::fetch("SELECT id FROM usuarios WHERE id = ?", [$usuarioId]);
            $usuarioIdValido = $usuarioExiste ? $usuarioId : null;
            
            // Registrar no histórico de status
            $sqlHistorico = "
                INSERT INTO historico_status (solicitacao_id, status_id, usuario_id, observacoes, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ";
            Database::query($sqlHistorico, [
                $solicitacaoId, 
                $statusInicial['id'], 
                $usuarioIdValido,
                'Solicitação migrada do sistema manual (ID: ' . $id . ')'
            ]);
            
            Database::commit();
            
            return [
                'success' => true,
                'solicitacao_id' => $solicitacaoId,
                'message' => 'Solicitação migrada com sucesso para o sistema principal'
            ];
            
        } catch (\Exception $e) {
            Database::rollback();
            error_log('Erro ao migrar solicitação manual: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar CPF
     */
    public function validarCPF(string $cpf): bool
    {
        // Remover caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verificar se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verificar se não é uma sequência de números iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        // Validar primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += $cpf[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;
        
        if ($cpf[9] != $digito1) {
            return false;
        }
        
        // Validar segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += $cpf[$i] * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        
        if ($cpf[10] != $digito2) {
            return false;
        }
        
        return true;
    }

    /**
     * Verificar se CPF já existe
     */
    public function cpfExiste(string $cpf, int $imobiliariaId): bool
    {
        $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
        
        $sql = "SELECT COUNT(*) as total FROM solicitacoes_manuais 
                WHERE cpf = ? AND imobiliaria_id = ?";
        $result = Database::fetch($sql, [$cpfLimpo, $imobiliariaId]);
        
        return $result['total'] > 0;
    }

    /**
     * Contar solicitações manuais por status
     */
    public function contarPorStatus(): array
    {
        $sql = "
            SELECT 
                st.nome as status,
                st.cor as cor,
                COUNT(*) as total
            FROM solicitacoes_manuais sm
            LEFT JOIN status st ON sm.status_id = st.id
            WHERE sm.migrada_para_solicitacao_id IS NULL
            GROUP BY st.id, st.nome, st.cor
            ORDER BY total DESC
        ";
        
        return Database::fetchAll($sql);
    }

    /**
     * Buscar solicitações não migradas
     */
    public function getNaoMigradas(int $limit = 50): array
    {
        $sql = "
            SELECT 
                sm.*,
                st.nome as status_nome,
                st.cor as status_cor,
                c.nome as categoria_nome,
                sc.nome as subcategoria_nome,
                i.nome as imobiliaria_nome
            FROM solicitacoes_manuais sm
            LEFT JOIN status st ON sm.status_id = st.id
            LEFT JOIN categorias c ON sm.categoria_id = c.id
            LEFT JOIN subcategorias sc ON sm.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON sm.imobiliaria_id = i.id
            WHERE sm.migrada_para_solicitacao_id IS NULL
            ORDER BY sm.created_at DESC
            LIMIT ?
        ";
        
        return Database::fetchAll($sql, [$limit]);
    }

    /**
     * Override do método create para adicionar validações
     */
    public function create(array $data): int
    {
        // Garantir timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Converter arrays para JSON se necessário
        if (isset($data['horarios_preferenciais']) && is_array($data['horarios_preferenciais'])) {
            $data['horarios_preferenciais'] = json_encode($data['horarios_preferenciais']);
        }
        
        if (isset($data['fotos']) && is_array($data['fotos'])) {
            $data['fotos'] = json_encode($data['fotos']);
        }
        
        // Limpar CPF (remover máscara)
        if (isset($data['cpf'])) {
            $data['cpf'] = preg_replace('/[^0-9]/', '', $data['cpf']);
        }
        
        // Definir status inicial se não fornecido
        if (!isset($data['status_id'])) {
            $sql = "SELECT id FROM status WHERE nome = 'Nova Solicitação' LIMIT 1";
            $status = Database::fetch($sql);
            $data['status_id'] = $status['id'] ?? 1;
        }
        
        return parent::create($data);
    }
}

