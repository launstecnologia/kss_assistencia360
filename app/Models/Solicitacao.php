<?php

namespace App\Models;

use App\Core\Database;

class Solicitacao extends Model
{
    protected string $table = 'solicitacoes';
    protected array $fillable = [
        'imobiliaria_id', 'categoria_id', 'subcategoria_id', 'status_id',
        'locatario_id', 'locatario_nome', 'locatario_telefone', 'locatario_email',
        'imovel_endereco', 'imovel_numero', 'imovel_complemento', 'imovel_bairro',
        'imovel_cidade', 'imovel_estado', 'imovel_cep',
        'descricao_problema', 'observacoes', 'prioridade',
        'data_agendamento', 'horario_agendamento', 'prestador_nome', 'prestador_telefone',
        'valor_orcamento', 'numero_ncp', 'avaliacao_satisfacao',
        // Novos campos para fluxo operacional
        'numero_solicitacao', 'tipo_atendimento', 'datas_opcoes', 'horarios_opcoes', 'data_confirmada',
        'mawdy_id', 'mawdy_nome', 'mawdy_telefone', 'mawdy_email',
        'data_limite_cancelamento', 'data_limite_peca', 'data_ultimo_lembrete',
        'confirmacao_atendimento', 'avaliacao_imobiliaria', 'avaliacao_app',
        'avaliacao_prestador', 'comentarios_avaliacao', 'link_confirmacao',
        'token_confirmacao', 'whatsapp_enviado', 'lembretes_enviados',
        // Campos de reembolso e protocolo
        'precisa_reembolso', 'valor_reembolso', 'protocolo_seguradora',
        'created_at', 'updated_at'
    ];
    protected array $casts = [
        'data_agendamento' => 'date',
        'data_confirmada' => 'date',
        'data_limite_cancelamento' => 'date',
        'data_limite_peca' => 'date',
        'data_ultimo_lembrete' => 'datetime',
        'valor_orcamento' => 'float',
        'avaliacao_satisfacao' => 'int',
        'avaliacao_imobiliaria' => 'int',
        'avaliacao_app' => 'int',
        'avaliacao_prestador' => 'int',
        'datas_opcoes' => 'json',
        'lembretes_enviados' => 'int',
        'whatsapp_enviado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getByStatus(int $statusId): array
    {
        return $this->findAll(['status_id' => $statusId], 'created_at DESC');
    }

    public function getByImobiliaria(int $imobiliariaId): array
    {
        return $this->findAll(['imobiliaria_id' => $imobiliariaId], 'created_at DESC');
    }

    public function getByLocatario(string $locatarioId): array
    {
        $sql = "
            SELECT 
                s.*,
                st.nome as status_nome,
                st.cor as status_cor,
                st.icone as status_icone,
                c.nome as categoria_nome,
                sc.nome as subcategoria_nome,
                i.nome as imobiliaria_nome
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN subcategorias sc ON s.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            WHERE s.locatario_id = ?
            ORDER BY s.created_at DESC
        ";
        
        return Database::fetchAll($sql, [$locatarioId]);
    }

    public function getKanbanData(): array
    {
        $sql = "
            SELECT 
                s.*,
                st.nome as status_nome,
                st.cor as status_cor,
                st.icone as status_icone,
                c.nome as categoria_nome,
                sc.nome as subcategoria_nome,
                i.nome as imobiliaria_nome,
                CASE 
                    WHEN st.nome = 'Nova Solicitação' THEN 1
                    WHEN st.nome = 'Buscando Prestador' THEN 2
                    WHEN st.nome = 'Serviço Agendado' THEN 3
                    WHEN st.nome IN ('Pendências', 'Aguardando Peça', 'Aguardando Confirmação Mawdy', 'Aguardando Confirmação Locatário') THEN 4
                    ELSE 5
                END as kanban_ordem,
                CASE 
                    WHEN st.nome IN ('Aguardando Peça', 'Aguardando Confirmação Mawdy', 'Aguardando Confirmação Locatário') THEN 'Pendências'
                    ELSE st.nome
                END as kanban_coluna
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN subcategorias sc ON s.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            ORDER BY kanban_ordem ASC, s.created_at DESC
        ";
        
        return Database::fetchAll($sql);
    }

    public function getDetalhes(int $id): ?array
    {
        $sql = "
            SELECT 
                s.*,
                st.nome as status_nome,
                st.cor as status_cor,
                st.icone as status_icone,
                c.nome as categoria_nome,
                sc.nome as subcategoria_nome,
                sc.prazo_minimo,
                i.nome as imobiliaria_nome,
                i.url_base as imobiliaria_url,
                i.telefone as imobiliaria_telefone,
                l.cpf as locatario_cpf
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN subcategorias sc ON s.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            LEFT JOIN locatarios l ON (s.locatario_id = l.id OR s.locatario_id = l.ksi_cliente_id)
            WHERE s.id = ?
        ";
        
        return Database::fetch($sql, [$id]);
    }

    public function getFotos(int $solicitacaoId): array
    {
        $sql = "SELECT * FROM fotos WHERE solicitacao_id = ? ORDER BY created_at ASC";
        return Database::fetchAll($sql, [$solicitacaoId]);
    }

    public function getHistoricoStatus(int $solicitacaoId): array
    {
        $sql = "
            SELECT 
                hs.*,
                u.nome as usuario_nome,
                st.nome as status_nome
            FROM historico_status hs
            LEFT JOIN usuarios u ON hs.usuario_id = u.id
            LEFT JOIN status st ON hs.status_id = st.id
            WHERE hs.solicitacao_id = ?
            ORDER BY hs.created_at DESC
        ";
        
        return Database::fetchAll($sql, [$solicitacaoId]);
    }

    public function updateStatus(int $id, int $statusId, int $usuarioId, string $observacoes = null): bool
    {
        Database::beginTransaction();
        
        try {
            // Buscar status atual
            $solicitacaoAtual = $this->find($id);
            
            // Se o status já é o mesmo, não fazer nada
            if ($solicitacaoAtual && $solicitacaoAtual['status_id'] == $statusId) {
                Database::rollback();
                return true; // Retorna sucesso, mas sem fazer alterações
            }
            
            // Atualizar status da solicitação
            $this->update($id, ['status_id' => $statusId]);
            
            // Registrar no histórico
            $sql = "
                INSERT INTO historico_status (solicitacao_id, status_id, usuario_id, observacoes, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ";
            Database::query($sql, [$id, $statusId, $usuarioId, $observacoes]);
            
            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollback();
            return false;
        }
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Definir status inicial como "Nova Solicitação"
        if (!isset($data['status_id'])) {
            $sql = "SELECT id FROM status WHERE nome = 'Nova Solicitação' LIMIT 1";
            $status = Database::fetch($sql);
            $data['status_id'] = $status['id'] ?? 1;
        }
        
        return parent::create($data);
    }

    public function getEstatisticas(string $periodo = '30'): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN st.nome = 'Concluído' THEN 1 END) as concluidas,
                COUNT(CASE WHEN st.nome = 'Nova Solicitação' THEN 1 END) as novas,
                COUNT(CASE WHEN st.nome = 'Serviço Agendado' THEN 1 END) as agendados,
                COUNT(CASE WHEN st.nome = 'Aguardando Peça' THEN 1 END) as aguardando_peca,
                AVG(TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at)) as tempo_medio_resolucao,
                AVG(s.avaliacao_satisfacao) as satisfacao_media
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ";
        
        return Database::fetch($sql, [$periodo]) ?: [];
    }

    public function getSolicitacoesPendentes(): array
    {
        $sql = "
            SELECT s.*, st.nome as status_nome
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            WHERE st.nome IN ('Aguardando Peça', 'Buscando Prestador')
            AND s.created_at < DATE_SUB(NOW(), INTERVAL 10 DAY)
            ORDER BY s.created_at ASC
        ";
        
        return Database::fetchAll($sql);
    }

    // Métodos para o fluxo operacional
    public function gerarNumeroSolicitacao(): string
    {
        $ano = date('Y');
        $mes = date('m');
        
        // Buscar último número do mês
        $sql = "SELECT numero_solicitacao FROM solicitacoes 
                WHERE numero_solicitacao LIKE 'KSI{$ano}{$mes}%' 
                ORDER BY numero_solicitacao DESC LIMIT 1";
        $ultimo = Database::fetch($sql);
        
        if ($ultimo) {
            $numero = (int) substr($ultimo['numero_solicitacao'], -4) + 1;
        } else {
            $numero = 1;
        }
        
        return 'KSI' . $ano . $mes . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function validarDatasOpcoes(array $datas): array
    {
        $errors = [];
        $hoje = new \DateTime();
        
        foreach ($datas as $index => $data) {
            $dataObj = new \DateTime($data);
            
            // Verificar se é maioridade (18 anos)
            $idadeMinima = $hoje->diff($dataObj)->y;
            if ($idadeMinima < 18) {
                $errors[] = "Data " . ($index + 1) . " deve ser de uma pessoa maior de idade";
            }
            
            // Verificar horário comercial (8h às 18h)
            $hora = $dataObj->format('H');
            if ($hora < 8 || $hora > 18) {
                $errors[] = "Data " . ($index + 1) . " deve estar no horário comercial (8h às 18h)";
            }
            
            // Verificar prazo mínimo (24h)
            $diferenca = $hoje->diff($dataObj)->days;
            if ($diferenca < 1) {
                $errors[] = "Data " . ($index + 1) . " deve ter pelo menos 24h de antecedência";
            }
        }
        
        return $errors;
    }

    public function podeCancelar(int $solicitacaoId): bool
    {
        $sql = "SELECT data_limite_cancelamento FROM solicitacoes WHERE id = ?";
        $solicitacao = Database::fetch($sql, [$solicitacaoId]);
        
        if (!$solicitacao || !$solicitacao['data_limite_cancelamento']) {
            return false;
        }
        
        $hoje = new \DateTime();
        $limite = new \DateTime($solicitacao['data_limite_cancelamento']);
        
        return $hoje <= $limite;
    }

    public function precisaLembrete(int $solicitacaoId): bool
    {
        $sql = "SELECT data_ultimo_lembrete, data_limite_peca FROM solicitacoes WHERE id = ?";
        $solicitacao = Database::fetch($sql, [$solicitacaoId]);
        
        if (!$solicitacao) {
            return false;
        }
        
        $hoje = new \DateTime();
        $ultimoLembrete = $solicitacao['data_ultimo_lembrete'] ? new \DateTime($solicitacao['data_ultimo_lembrete']) : null;
        $limitePeca = $solicitacao['data_limite_peca'] ? new \DateTime($solicitacao['data_limite_peca']) : null;
        
        // Se não há limite de peça, não precisa lembrete
        if (!$limitePeca) {
            return false;
        }
        
        // Se já passou do limite, não precisa lembrete
        if ($hoje > $limitePeca) {
            return false;
        }
        
        // Se nunca enviou lembrete ou enviou há mais de 2 dias
        if (!$ultimoLembrete) {
            return true;
        }
        
        $diferenca = $hoje->diff($ultimoLembrete)->days;
        return $diferenca >= 2;
    }

    public function atualizarLembrete(int $solicitacaoId): void
    {
        $sql = "UPDATE solicitacoes SET data_ultimo_lembrete = NOW(), lembretes_enviados = lembretes_enviados + 1 WHERE id = ?";
        Database::query($sql, [$solicitacaoId]);
    }

    public function gerarTokenConfirmacao(): string
    {
        return 'confirm_' . uniqid() . '_' . bin2hex(random_bytes(16));
    }

    public function getSolicitacoesParaLembrete(): array
    {
        $sql = "
            SELECT s.*, st.nome as status_nome
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            WHERE st.nome = 'Aguardando Peça'
            AND s.data_limite_peca IS NOT NULL
            AND s.data_limite_peca > NOW()
            AND (s.data_ultimo_lembrete IS NULL OR s.data_ultimo_lembrete < DATE_SUB(NOW(), INTERVAL 2 DAY))
            ORDER BY s.created_at ASC
        ";
        
        return Database::fetchAll($sql);
    }

    public function getSolicitacoesExpiradas(): array
    {
        $sql = "
            SELECT s.*, st.nome as status_nome
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            WHERE st.nome = 'Aguardando Peça'
            AND s.data_limite_peca IS NOT NULL
            AND s.data_limite_peca < NOW()
            ORDER BY s.created_at ASC
        ";
        
        return Database::fetchAll($sql);
    }

    public function confirmarAtendimento(int $solicitacaoId, string $confirmacao, array $avaliacoes = []): bool
    {
        $data = [
            'confirmacao_atendimento' => $confirmacao,
            'avaliacao_imobiliaria' => $avaliacoes['imobiliaria'] ?? null,
            'avaliacao_app' => $avaliacoes['app'] ?? null,
            'avaliacao_prestador' => $avaliacoes['prestador'] ?? null,
            'comentarios_avaliacao' => $avaliacoes['comentarios'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Se confirmou atendimento, gerar NCP
        if ($confirmacao === 'atendido') {
            $data['numero_ncp'] = $this->gerarNumeroNCP();
            $data['status_id'] = $this->getStatusId('Concluído (NCP)');
        }
        
        return $this->update($solicitacaoId, $data);
    }

    private function gerarNumeroNCP(): string
    {
        $ano = date('Y');
        $mes = date('m');
        
        $sql = "SELECT numero_ncp FROM solicitacoes 
                WHERE numero_ncp LIKE 'NCP{$ano}{$mes}%' 
                ORDER BY numero_ncp DESC LIMIT 1";
        $ultimo = Database::fetch($sql);
        
        if ($ultimo) {
            $numero = (int) substr($ultimo['numero_ncp'], -4) + 1;
        } else {
            $numero = 1;
        }
        
        return 'NCP' . $ano . $mes . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    private function getStatusId(string $statusNome): int
    {
        $sql = "SELECT id FROM status WHERE nome = ? LIMIT 1";
        $status = Database::fetch($sql, [$statusNome]);
        return $status['id'] ?? 1;
    }
}
