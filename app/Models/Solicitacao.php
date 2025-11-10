<?php

namespace App\Models;

use App\Core\Database;

class Solicitacao extends Model
{
    protected string $table = 'solicitacoes';
    protected array $fillable = [
        'imobiliaria_id', 'categoria_id', 'subcategoria_id', 'status_id', 'condicao_id',
        'locatario_id', 'locatario_nome', 'locatario_telefone', 'locatario_email',
        'imovel_endereco', 'imovel_numero', 'imovel_complemento', 'imovel_bairro',
        'imovel_cidade', 'imovel_estado', 'imovel_cep',
        'descricao_problema', 'descricao_card', 'observacoes', 'prioridade',
        'data_agendamento', 'horario_agendamento', 'horario_confirmado', 'horario_confirmado_raw', 'confirmed_schedules', 'prestador_nome', 'prestador_telefone',
        'valor_orcamento', 'numero_ncp', 'avaliacao_satisfacao',
        // Novos campos para fluxo operacional
        'numero_solicitacao', 'numero_contrato', 'tipo_atendimento', 'datas_opcoes', 'horarios_opcoes', 'data_confirmada',
        'mawdy_id', 'mawdy_nome', 'mawdy_telefone', 'mawdy_email',
        'data_limite_cancelamento', 'data_limite_peca', 'data_ultimo_lembrete',
        'confirmacao_atendimento', 'avaliacao_imobiliaria', 'avaliacao_app',
        'avaliacao_prestador', 'comentarios_avaliacao', 'link_confirmacao',
        'token_confirmacao', 'whatsapp_enviado', 'lembretes_enviados',
        // Campos de reembolso e protocolo
        'precisa_reembolso', 'valor_reembolso', 'protocolo_seguradora',
        // Campo de horários indisponíveis
        'horarios_indisponiveis',
        // Campo de emergência fora do horário comercial
        'is_emergencial_fora_horario',
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
        'confirmed_schedules' => 'json',
        'lembretes_enviados' => 'int',
        'horario_confirmado' => 'boolean',
        'whatsapp_enviado' => 'boolean',
        'horarios_indisponiveis' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    private static array $colunaCache = [];

    public function hasDataLimiteCancelamento(): bool
    {
        return $this->colunaExiste('data_limite_cancelamento');
    }

    private function colunaExiste(string $coluna): bool
    {
        if (!array_key_exists($coluna, self::$colunaCache)) {
            $sql = "DESCRIBE {$this->table}";
            $resultado = Database::fetchAll($sql);

            self::$colunaCache = [];
            foreach ($resultado as $colunaInfo) {
                $nome = $colunaInfo['Field'] ?? '';
                if ($nome !== '') {
                    self::$colunaCache[$nome] = true;
                }
            }
        }

        return self::$colunaCache[$coluna] ?? false;
    }

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
                i.logo as imobiliaria_logo,
                s.is_emergencial_fora_horario,
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
                sc.is_emergencial as subcategoria_is_emergencial,
                i.nome as imobiliaria_nome,
                i.url_base as imobiliaria_url,
                i.telefone as imobiliaria_telefone,
                l.cpf as locatario_cpf,
                cond.nome as condicao_nome,
                cond.cor as condicao_cor
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN subcategorias sc ON s.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            LEFT JOIN locatarios l ON (s.locatario_id = l.id OR s.locatario_id = l.ksi_cliente_id)
            LEFT JOIN condicoes cond ON s.condicao_id = cond.id
            WHERE s.id = ?
        ";
        
        return Database::fetch($sql, [$id]);
    }

    /**
     * Verifica se uma coluna existe na tabela (cacheada)
     */
    public function colunaExisteBanco(string $coluna): bool
    {
        if (!array_key_exists($coluna, self::$colunaCache)) {
            $sql = "DESCRIBE {$this->table}";
            $resultado = Database::fetchAll($sql);

            self::$colunaCache = [];
            foreach ($resultado as $colunaInfo) {
                $nome = $colunaInfo['Field'] ?? '';
                if ($nome !== '') {
                    self::$colunaCache[$nome] = true;
                }
            }
        }

        return self::$colunaCache[$coluna] ?? false;
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

    private function montarFiltrosRelatorio(array $filtros): array
    {
        $condicoes = [];
        $params = [];
        $temDataLimite = $this->hasDataLimiteCancelamento();

        if (!empty($filtros['status_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filtros['status_ids']), '?'));
            $condicoes[] = "s.status_id IN ({$placeholders})";
            $params = array_merge($params, $filtros['status_ids']);
        } elseif (!empty($filtros['status_id'])) {
            $condicoes[] = 's.status_id = ?';
            $params[] = $filtros['status_id'];
        }

        if (!empty($filtros['condicao_id'])) {
            $condicoes[] = 's.condicao_id = ?';
            $params[] = $filtros['condicao_id'];
        }

        if (!empty($filtros['imobiliaria_id'])) {
            $condicoes[] = 's.imobiliaria_id = ?';
            $params[] = $filtros['imobiliaria_id'];
        }

        if (!empty($filtros['categoria_id'])) {
            $condicoes[] = 's.categoria_id = ?';
            $params[] = $filtros['categoria_id'];
        }

        if (!empty($filtros['subcategoria_id'])) {
            $condicoes[] = 's.subcategoria_id = ?';
            $params[] = $filtros['subcategoria_id'];
        }

        if (!empty($filtros['cpf'])) {
            $cpf = preg_replace('/[^0-9]/', '', $filtros['cpf']);
            if ($cpf !== '') {
                $condicoes[] = "REPLACE(REPLACE(REPLACE(COALESCE(s.locatario_cpf, l.cpf, ''), '.', ''), '-', ''), '/', '') LIKE ?";
                $params[] = '%' . $cpf . '%';
            }
        }

        if (!empty($filtros['numero_contrato'])) {
            $condicoes[] = 's.numero_contrato LIKE ?';
            $params[] = '%' . $filtros['numero_contrato'] . '%';
        }

        if (!empty($filtros['locatario_nome'])) {
            $condicoes[] = '(s.locatario_nome LIKE ? OR l.nome LIKE ? OR s.locatario_email LIKE ?)';
            $termo = '%' . $filtros['locatario_nome'] . '%';
            $params[] = $termo;
            $params[] = $termo;
            $params[] = $termo;
        }

        if (!empty($filtros['data_inicio'])) {
            $condicoes[] = 'DATE(s.created_at) >= ?';
            $params[] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $condicoes[] = 'DATE(s.created_at) <= ?';
            $params[] = $filtros['data_fim'];
        }

        if (!empty($filtros['agendamento_inicio'])) {
            $condicoes[] = 'DATE(s.data_agendamento) >= ?';
            $params[] = $filtros['agendamento_inicio'];
        }

        if (!empty($filtros['agendamento_fim'])) {
            $condicoes[] = 'DATE(s.data_agendamento) <= ?';
            $params[] = $filtros['agendamento_fim'];
        }

        if (!empty($filtros['sla_atrasado']) && $temDataLimite) {
            $condicoes[] = "(s.data_limite_cancelamento IS NOT NULL AND s.data_limite_cancelamento < NOW() AND (st.nome IS NULL OR st.nome NOT IN ('Concluído', 'Cancelado', 'Cancelada')) )";
        }

        if (array_key_exists('precisa_reembolso', $filtros)) {
            $condicoes[] = 's.precisa_reembolso = ?';
            $params[] = $filtros['precisa_reembolso'] ? 1 : 0;
        }

        if (array_key_exists('whatsapp_enviado', $filtros) && $this->colunaExiste('whatsapp_enviado')) {
            $condicoes[] = 's.whatsapp_enviado = ?';
            $params[] = $filtros['whatsapp_enviado'] ? 1 : 0;
        }

        $whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

        return [$whereSql, $params];
    }

    public function getRelatorioResumo(array $filtros = []): array
    {
        [$whereSql, $params] = $this->montarFiltrosRelatorio($filtros);
        $temDataLimite = $this->hasDataLimiteCancelamento();
        $slaSelect = $temDataLimite
            ? "COUNT(CASE WHEN s.data_limite_cancelamento IS NOT NULL AND s.data_limite_cancelamento < NOW() AND (st.nome IS NULL OR st.nome NOT IN ('Concluído', 'Cancelado', 'Cancelada')) THEN 1 END) as total_sla_atrasado"
            : '0 as total_sla_atrasado';

        $sql = "
            SELECT
                COUNT(*) as total_solicitacoes,
                COUNT(CASE WHEN st.nome IN ('Pendências', 'Aguardando Peça', 'Aguardando Confirmação Mawdy', 'Aguardando Confirmação Locatário') THEN 1 END) as total_pendentes,
                COUNT(CASE WHEN st.nome IN ('Serviço Agendado') THEN 1 END) as total_agendados,
                COUNT(CASE WHEN st.nome IN ('Buscando Prestador') THEN 1 END) as total_buscando_prestador,
                COUNT(CASE WHEN st.nome IN ('Concluído') THEN 1 END) as total_concluidos,
                COUNT(CASE WHEN st.nome IN ('Cancelado', 'Cancelada') THEN 1 END) as total_cancelados,
                COUNT(CASE WHEN s.precisa_reembolso = 1 THEN 1 END) as total_reembolsos,
                {$slaSelect}
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN condicoes cond ON s.condicao_id = cond.id
            LEFT JOIN locatarios l ON (s.locatario_id = l.id OR s.locatario_id = l.ksi_cliente_id)
            {$whereSql}
        ";

        $resultado = Database::fetch($sql, $params) ?: [];

        return [
            'total_solicitacoes' => (int) ($resultado['total_solicitacoes'] ?? 0),
            'total_pendentes' => (int) ($resultado['total_pendentes'] ?? 0),
            'total_agendados' => (int) ($resultado['total_agendados'] ?? 0),
            'total_buscando_prestador' => (int) ($resultado['total_buscando_prestador'] ?? 0),
            'total_concluidos' => (int) ($resultado['total_concluidos'] ?? 0),
            'total_cancelados' => (int) ($resultado['total_cancelados'] ?? 0),
            'total_reembolsos' => (int) ($resultado['total_reembolsos'] ?? 0),
            'total_sla_atrasado' => (int) ($resultado['total_sla_atrasado'] ?? 0),
        ];
    }

    public function getRelatorioSolicitacoes(array $filtros = [], int $limite = 100): array
    {
        [$whereSql, $params] = $this->montarFiltrosRelatorio($filtros);

        $limite = max(1, min($limite, 1000));
        $paramsLista = array_merge($params, [$limite]);

        $temWhatsApp = $this->colunaExiste('whatsapp_enviado');
        $temReembolso = $this->colunaExiste('precisa_reembolso');
        $temNumeroSolicitacao = $this->colunaExiste('numero_solicitacao');

        $sql = "
            SELECT
                s.id,
                " . ($temNumeroSolicitacao ? 's.numero_solicitacao' : 'NULL') . " as numero_solicitacao,
                s.numero_contrato,
                s.locatario_nome,
                COALESCE(s.locatario_cpf, l.cpf) as locatario_cpf,
                st.nome as status_nome,
                st.cor as status_cor,
                cond.nome as condicao_nome,
                cond.cor as condicao_cor,
                c.nome as categoria_nome,
                sc.nome as subcategoria_nome,
                s.prioridade,
                " . ($temReembolso ? 's.precisa_reembolso' : 'NULL') . " as precisa_reembolso,
                " . ($temWhatsApp ? 's.whatsapp_enviado' : 'NULL') . " as whatsapp_enviado,
                s.created_at,
                s.data_agendamento,
                s.updated_at,
                i.nome as imobiliaria_nome
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN condicoes cond ON s.condicao_id = cond.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN subcategorias sc ON s.subcategoria_id = sc.id
            LEFT JOIN locatarios l ON (s.locatario_id = l.id OR s.locatario_id = l.ksi_cliente_id)
            {$whereSql}
            ORDER BY s.created_at DESC
            LIMIT ?
        ";

        return Database::fetchAll($sql, $paramsLista);
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
        $hoje->setTime(0, 0, 0); // Resetar hora para comparar apenas datas
        
        foreach ($datas as $index => $data) {
            try {
                $dataObj = new \DateTime($data);
                
                // Verificar se a data não é no passado
                if ($dataObj < $hoje) {
                    $errors[] = "Data " . ($index + 1) . " não pode ser no passado";
                    continue;
                }
                
                // Verificar horário comercial (8h às 20h)
                $hora = (int)$dataObj->format('H');
                if ($hora < 8 || $hora > 20) {
                    $errors[] = "Data " . ($index + 1) . " deve estar no horário comercial (8h às 20h)";
                }
                
                // Verificar prazo mínimo (24h) - apenas se a data for hoje
                if ($dataObj->format('Y-m-d') === $hoje->format('Y-m-d')) {
                    $errors[] = "Data " . ($index + 1) . " deve ter pelo menos 24h de antecedência";
                }
            } catch (\Exception $e) {
                $errors[] = "Data " . ($index + 1) . " é inválida: " . $e->getMessage();
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
        
        // Se nunca enviou lembrete ou enviou há mais de 1 dia (lembretes diários)
        if (!$ultimoLembrete) {
            return true;
        }
        
        // Verificar se passou pelo menos 1 dia desde o último lembrete
        $diferenca = $hoje->diff($ultimoLembrete)->days;
        return $diferenca >= 1;
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

    /**
     * Gera um token público permanente para visualização de status
     * Este token não expira e pode ser usado múltiplas vezes
     * 
     * @param int $solicitacaoId ID da solicitação
     * @return string Token público
     */
    public function gerarTokenPublico(int $solicitacaoId): string
    {
        // Usar uma chave secreta para gerar o token de forma determinística
        // Isso garante que o mesmo ID sempre gere o mesmo token
        $secretKey = 'kss_public_token_secret_2024'; // Chave secreta para gerar tokens públicos
        $hash = hash_hmac('sha256', $solicitacaoId, $secretKey);
        
        // Retornar apenas os primeiros 32 caracteres para um token mais curto
        return 'public_' . substr($hash, 0, 32);
    }

    /**
     * Valida um token público e retorna o ID da solicitação
     * 
     * @param string $token Token público
     * @return int|null ID da solicitação ou null se inválido
     */
    public function validarTokenPublico(string $token): ?int
    {
        // Remover prefixo "public_" se existir
        $token = str_replace('public_', '', $token);
        
        if (strlen($token) !== 32) {
            return null;
        }

        // Como o token é determinístico, precisamos buscar todas as solicitações
        // e verificar qual tem o token correspondente
        // Para otimizar, vamos buscar apenas IDs (mais leve)
        $sql = "SELECT id FROM solicitacoes ORDER BY id DESC LIMIT 1000";
        $solicitacoes = Database::fetchAll($sql);
        
        foreach ($solicitacoes as $solicitacao) {
            $tokenGerado = $this->gerarTokenPublico($solicitacao['id']);
            $tokenGeradoLimpo = str_replace('public_', '', $tokenGerado);
            
            if ($tokenGeradoLimpo === $token) {
                return (int) $solicitacao['id'];
            }
        }
        
        return null;
    }

    /**
     * Gera um token de cancelamento permanente para a solicitação
     * Este token não expira e pode ser usado para cancelar a solicitação
     * 
     * @param int $solicitacaoId ID da solicitação
     * @return string Token de cancelamento
     */
    public function gerarTokenCancelamento(int $solicitacaoId): string
    {
        // Usar uma chave secreta específica para cancelamento
        $secretKey = 'kss_cancel_token_secret_2024';
        $hash = hash_hmac('sha256', $solicitacaoId, $secretKey);
        
        // Retornar apenas os primeiros 32 caracteres para um token mais curto
        return 'cancel_' . substr($hash, 0, 32);
    }

    /**
     * Valida um token de cancelamento e retorna o ID da solicitação
     * 
     * @param string $token Token de cancelamento
     * @return int|null ID da solicitação ou null se inválido
     */
    public function validarTokenCancelamento(string $token): ?int
    {
        // Remover prefixo "cancel_" se existir
        $token = str_replace('cancel_', '', $token);
        
        if (strlen($token) !== 32) {
            return null;
        }

        // Buscar todas as solicitações e verificar qual tem o token correspondente
        $sql = "SELECT id FROM solicitacoes ORDER BY id DESC LIMIT 1000";
        $solicitacoes = Database::fetchAll($sql);
        
        foreach ($solicitacoes as $solicitacao) {
            $tokenGerado = $this->gerarTokenCancelamento($solicitacao['id']);
            $tokenGeradoLimpo = str_replace('cancel_', '', $tokenGerado);
            
            if ($tokenGeradoLimpo === $token) {
                return (int) $solicitacao['id'];
            }
        }
        
        return null;
    }

    public function getSolicitacoesParaLembrete(): array
    {
        $sql = "
            SELECT s.*, st.nome as status_nome, c.nome as condicao_nome
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN condicoes c ON s.condicao_id = c.id
            WHERE (c.nome = 'Comprar peças' OR st.nome = 'Aguardando Peça' OR st.nome = 'Pendente')
            AND s.data_limite_peca IS NOT NULL
            AND s.data_limite_peca > NOW()
            AND (s.data_ultimo_lembrete IS NULL OR s.data_ultimo_lembrete < DATE_SUB(NOW(), INTERVAL 1 DAY))
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
