<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Solicitacao;
use App\Models\Imobiliaria;
use App\Models\Usuario;
use App\Models\Categoria;

class DashboardController extends Controller
{
    private Solicitacao $solicitacaoModel;
    private Imobiliaria $imobiliariaModel;
    private Usuario $usuarioModel;
    private Categoria $categoriaModel;
    private \App\Models\Status $statusModel;
    private \App\Models\Condicao $condicaoModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->solicitacaoModel = new Solicitacao();
        $this->imobiliariaModel = new Imobiliaria();
        $this->usuarioModel = new Usuario();
        $this->categoriaModel = new Categoria();
        $this->statusModel = new \App\Models\Status();
        $this->condicaoModel = new \App\Models\Condicao();
    }

    public function index(): void
    {
        $user = $this->getUser();
        $periodo = $this->input('periodo', '30');

        // Estatísticas gerais
        $estatisticas = $this->solicitacaoModel->getEstatisticas($periodo);
        
        // Dados do Kanban
        $kanbanData = $this->solicitacaoModel->getKanbanData();
        
        // Solicitações recentes
        $solicitacoesRecentes = array_slice($kanbanData, 0, 10);
        
        // Solicitações pendentes (aguardando há mais de 10 dias)
        $solicitacoesPendentes = $this->solicitacaoModel->getSolicitacoesPendentes();

        // Dados específicos por nível de usuário
        $dadosAdicionais = [];
        
        if ($this->usuarioModel->isAdmin($user)) {
            $dadosAdicionais = [
                'total_imobiliarias' => $this->imobiliariaModel->count(['status' => 'ATIVA']),
                'total_usuarios' => $this->usuarioModel->count(['status' => 'ATIVO']),
                'total_categorias' => $this->categoriaModel->count(['status' => 'ATIVA']),
                'imobiliarias' => $this->imobiliariaModel->getAtivas()
            ];
        }

        $this->view('dashboard.index', array_merge([
            'estatisticas' => $estatisticas,
            'kanbanData' => $kanbanData,
            'solicitacoesRecentes' => $solicitacoesRecentes,
            'solicitacoesPendentes' => $solicitacoesPendentes,
            'periodo' => $periodo,
            'user' => $user
        ], $dadosAdicionais));
    }

    public function estatisticas(): void
    {
        $periodo = $this->input('periodo', '30');
        $imobiliariaId = $this->input('imobiliaria_id');
        
        $estatisticas = $this->solicitacaoModel->getEstatisticas($periodo);
        
        if ($imobiliariaId) {
            $estatisticasImobiliaria = $this->imobiliariaModel->getEstatisticas($imobiliariaId, $periodo);
            $estatisticas = array_merge($estatisticas, $estatisticasImobiliaria);
        }

        $this->json($estatisticas);
    }

    public function solicitacoesPorStatus(): void
    {
        $periodo = $this->input('periodo', '30');
        
        $sql = "
            SELECT 
                st.nome as status_nome,
                st.cor as status_cor,
                COUNT(*) as quantidade
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY st.id, st.nome, st.cor
            ORDER BY st.ordem ASC
        ";
        
        $dados = \App\Core\Database::fetchAll($sql, [$periodo]);
        
        $this->json($dados);
    }

    public function solicitacoesPorImobiliaria(): void
    {
        $periodo = $this->input('periodo', '30');
        
        $sql = "
            SELECT 
                COALESCE(i.nome, i.nome_fantasia, 'Sem imobiliária') as imobiliaria_nome,
                COUNT(*) as quantidade
            FROM solicitacoes s
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY i.id, i.nome, i.nome_fantasia
            HAVING quantidade > 0
            ORDER BY quantidade DESC
        ";
        
        $dados = \App\Core\Database::fetchAll($sql, [$periodo]);
        
        $this->json($dados);
    }

    public function solicitacoesPorCategoria(): void
    {
        $periodo = $this->input('periodo', '30');
        
        $sql = "
            SELECT 
                c.nome as categoria_nome,
                COUNT(*) as quantidade
            FROM solicitacoes s
            LEFT JOIN categorias c ON s.categoria_id = c.id
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY c.id, c.nome
            ORDER BY quantidade DESC
        ";
        
        $dados = \App\Core\Database::fetchAll($sql, [$periodo]);
        
        $this->json($dados);
    }

    public function tempoMedioResolucao(): void
    {
        $periodo = $this->input('periodo', '30');
        
        $sql = "
            SELECT 
                DATE(s.created_at) as data,
                AVG(TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at)) as tempo_medio_horas
            FROM solicitacoes s
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND s.status_id = (SELECT id FROM status WHERE nome = 'Concluído')
            GROUP BY DATE(s.created_at)
            ORDER BY data ASC
        ";
        
        $dados = \App\Core\Database::fetchAll($sql, [$periodo]);
        
        $this->json($dados);
    }

    public function kanban(): void
    {
        $user = $this->getUser();
        $imobiliariaId = $this->input('imobiliaria_id');
        
        // Buscar status do Kanban
        $statusKanban = $this->statusModel->getKanban();
        
        // Buscar solicitações organizadas por status
        $solicitacoesPorStatus = [];
        foreach ($statusKanban as $status) {
            $sql = "
                SELECT 
                    s.*,
                    c.nome as categoria_nome,
                    sc.nome as subcategoria_nome,
                    sc.is_emergencial as subcategoria_is_emergencial,
                    i.nome as imobiliaria_nome,
                    i.logo as imobiliaria_logo,
                    st.nome as status_nome,
                    st.cor as status_cor,
                    cond.nome as condicao_nome,
                    cond.cor as condicao_cor,
                    wi.id as whatsapp_instance_id,
                    wi.nome as whatsapp_instance_nome,
                    wi.status as whatsapp_instance_status,
                    s.chat_atendimento_ativo
                FROM solicitacoes s
                LEFT JOIN categorias c ON s.categoria_id = c.id
                LEFT JOIN subcategorias sc ON s.subcategoria_id = sc.id
                LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
                LEFT JOIN status st ON s.status_id = st.id
                LEFT JOIN condicoes cond ON s.condicao_id = cond.id
                LEFT JOIN whatsapp_instances wi ON s.chat_whatsapp_instance_id = wi.id
                WHERE s.status_id = ?
            ";
            
            $params = [$status['id']];
            
            if ($imobiliariaId) {
                $sql .= " AND s.imobiliaria_id = ?";
                $params[] = $imobiliariaId;
            }
            
            $sql .= " ORDER BY s.created_at DESC LIMIT 50";
            
            $solicitacoesPorStatus[$status['id']] = \App\Core\Database::fetchAll($sql, $params);
        }
        
        // Buscar imobiliárias para filtro
        $imobiliarias = $this->imobiliariaModel->getAtivas();
        
        // Buscar todos os status ativos para o select do modal
        $todosStatus = $this->statusModel->getAtivos();
        
        // Buscar todas as condições ativas para o select do modal
        $todasCondicoes = $this->condicaoModel->getAtivos();
        
        $this->view('kanban.index', [
            'statusKanban' => $statusKanban,
            'solicitacoesPorStatus' => $solicitacoesPorStatus,
            'imobiliarias' => $imobiliarias,
            'imobiliariaId' => $imobiliariaId,
            'user' => $user,
            'todosStatus' => $todosStatus,
            'todasCondicoes' => $todasCondicoes
        ]);
    }

    /**
     * Endpoint AJAX para buscar novas solicitações
     * Retorna apenas solicitações com status "Nova Solicitação"
     */
    public function novasSolicitacoes(): void
    {
        $this->requireAuth();
        
        $imobiliariaId = $this->input('imobiliaria_id');
        
        // Buscar ID do status "Nova Solicitação"
        $statusNova = $this->statusModel->findByNome('Nova Solicitação');
        
        if (!$statusNova) {
            $this->json(['success' => true, 'solicitacoes' => []]);
            return;
        }
        
        // Buscar novas solicitações
        $sql = "
            SELECT 
                s.*,
                c.nome as categoria_nome,
                sc.nome as subcategoria_nome,
                sc.is_emergencial as subcategoria_is_emergencial,
                i.nome as imobiliaria_nome,
                i.logo as imobiliaria_logo,
                st.nome as status_nome,
                st.cor as status_cor,
                cond.nome as condicao_nome,
                cond.cor as condicao_cor,
                wi.id as whatsapp_instance_id,
                wi.nome as whatsapp_instance_nome,
                wi.status as whatsapp_instance_status,
                s.chat_atendimento_ativo
            FROM solicitacoes s
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN subcategorias sc ON s.subcategoria_id = sc.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN condicoes cond ON s.condicao_id = cond.id
            LEFT JOIN whatsapp_instances wi ON s.chat_whatsapp_instance_id = wi.id
            WHERE s.status_id = ?
        ";
        
        $params = [$statusNova['id']];
        
        if ($imobiliariaId) {
            $sql .= " AND s.imobiliaria_id = ?";
            $params[] = $imobiliariaId;
        }
        
        $sql .= " ORDER BY s.created_at DESC LIMIT 50";
        
        $solicitacoes = \App\Core\Database::fetchAll($sql, $params);
        
        $this->json([
            'success' => true,
            'solicitacoes' => $solicitacoes,
            'status_id' => $statusNova['id'],
            'count' => count($solicitacoes)
        ]);
    }

    public function atualizarCondicao(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        // Ler JSON do body da requisição
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $solicitacaoId = $data['solicitacao_id'] ?? null;
        $condicaoId = $data['condicao_id'] ?? null;
        
        if (!$solicitacaoId) {
            $this->json(['error' => 'ID da solicitação não informado'], 400);
            return;
        }
        
        try {
            // Buscar solicitação atual para verificar se a condição mudou
            $solicitacaoAtual = $this->solicitacaoModel->find($solicitacaoId);
            if (!$solicitacaoAtual) {
                $this->json(['error' => 'Solicitação não encontrada'], 404);
                return;
            }
            
            // Converter condicaoId vazio para null
            $condicaoIdValue = (!empty($condicaoId) && $condicaoId !== '0' && $condicaoId !== '') ? (int)$condicaoId : null;
            
            // Verificar se a condição realmente mudou
            $condicaoAtual = $solicitacaoAtual['condicao_id'] ?? null;
            if ($condicaoAtual == $condicaoIdValue) {
                // Condição não mudou, não fazer nada
                $this->json([
                    'success' => true,
                    'message' => 'Condição não foi alterada',
                    'condicao_id' => $condicaoIdValue
                ]);
                return;
            }
            
            // Atualizar condição da solicitação
            $updateData = ['condicao_id' => $condicaoIdValue];
            
            error_log("🔍 Atualizando condição - Solicitação ID: {$solicitacaoId}, Condição ID: " . ($condicaoIdValue ?? 'NULL'));
            
            $result = $this->solicitacaoModel->update($solicitacaoId, $updateData);
            
            error_log("✅ Resultado do update: " . ($result ? 'SUCESSO' : 'FALHOU'));
            
            // Registrar mudança de condição no histórico
            $user = $this->getUser();
            $this->solicitacaoModel->registrarMudancaCondicao($solicitacaoId, $condicaoIdValue, $user['id'] ?? null);
            
            // Verificar se foi salvo corretamente
            $solicitacaoAtualizada = $this->solicitacaoModel->find($solicitacaoId);
            error_log("🔍 Condição após update: " . ($solicitacaoAtualizada['condicao_id'] ?? 'NULL'));
            
            $this->json([
                'success' => true,
                'message' => 'Condição atualizada com sucesso',
                'condicao_id' => $condicaoIdValue
            ]);
        } catch (\Exception $e) {
            error_log("❌ Erro ao atualizar condição: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            $this->json([
                'error' => 'Erro ao atualizar condição: ' . $e->getMessage()
            ], 500);
        }
    }

    public function moverCard(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        // Ler JSON do body da requisição
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $solicitacaoId = $data['solicitacao_id'] ?? null;
        $novoStatusId = $data['novo_status_id'] ?? null;
        $user = $this->getUser();

        if (!$solicitacaoId || !$novoStatusId) {
            $this->json(['error' => 'Dados incompletos'], 400);
            return;
        }

        try {
            // Buscar a solicitação para verificar status atual
            $solicitacao = $this->solicitacaoModel->find($solicitacaoId);
            if (!$solicitacao) {
                $this->json(['error' => 'Solicitação não encontrada'], 404);
                return;
            }
            
            // Buscar nome do status atual e de destino
            $sqlAtual = "SELECT nome FROM status WHERE id = ?";
            $statusAtualObj = \App\Core\Database::fetch($sqlAtual, [$solicitacao['status_id']]);
            $statusAtual = $statusAtualObj['nome'] ?? null;
            
            $sql = "SELECT nome FROM status WHERE id = ?";
            $statusDestino = \App\Core\Database::fetch($sql, [$novoStatusId]);
            $statusNovo = $statusDestino['nome'] ?? null;
            
            // Verificar se está tentando mover para "Serviço Agendado"
            if ($statusDestino && $statusDestino['nome'] === 'Serviço Agendado') {
                if (empty($solicitacao['protocolo_seguradora'])) {
                    $this->json([
                        'error' => 'É obrigatório preencher o protocolo da seguradora para mover para "Serviço Agendado"',
                        'requires_protocol' => true
                    ], 400);
                    return;
                }
            }
            
            $success = $this->solicitacaoModel->updateStatus($solicitacaoId, $novoStatusId, $user['id']);
            
            if ($success) {
                // ✅ Se mudou para "Serviço Agendado", atualizar condição para "Agendamento Confirmado"
                if ($statusNovo === 'Serviço Agendado') {
                    $condicaoModel = new \App\Models\Condicao();
                    $condicaoConfirmada = $condicaoModel->findByNome('Agendamento Confirmado');
                    if (!$condicaoConfirmada) {
                        $condicaoConfirmada = $condicaoModel->findByNome('Data Aceita pelo Prestador');
                    }
                    if (!$condicaoConfirmada) {
                        $sqlCondicao = "SELECT * FROM condicoes WHERE (nome LIKE '%Agendamento Confirmado%' OR nome LIKE '%Data Aceita pelo Prestador%') AND status = 'ATIVO' LIMIT 1";
                        $condicaoConfirmada = \App\Core\Database::fetch($sqlCondicao);
                    }
                    
                    if ($condicaoConfirmada) {
                        $this->solicitacaoModel->update($solicitacaoId, ['condicao_id' => $condicaoConfirmada['id']]);
                        error_log("DEBUG moverCard [ID:{$solicitacaoId}] - ✅ Condição alterada para 'Agendamento Confirmado' (ID: {$condicaoConfirmada['id']})");
                    } else {
                        error_log("DEBUG moverCard [ID:{$solicitacaoId}] - ⚠️ Condição 'Agendamento Confirmado' não encontrada no banco de dados");
                    }
                }
                
                // ✅ Se mudou de "Buscando Prestador" para "Serviço Agendado", enviar "Horário Confirmado"
                if ($statusAtual === 'Buscando Prestador' && $statusNovo === 'Serviço Agendado') {
                    // Buscar dados de agendamento da solicitação atualizada
                    $solicitacaoAtualizada = $this->solicitacaoModel->find($solicitacaoId);
                    $dataAgendamento = $solicitacaoAtualizada['data_agendamento'] ?? null;
                    $horarioAgendamento = $solicitacaoAtualizada['horario_agendamento'] ?? null;
                    
                    // Formatar horário completo
                    $horarioCompleto = '';
                    if ($dataAgendamento && $horarioAgendamento) {
                        $dataFormatada = date('d/m/Y', strtotime($dataAgendamento));
                        $horarioCompleto = $dataFormatada . ' - ' . $horarioAgendamento;
                    }
                    
                    // Enviar apenas "Horário Confirmado"
                    $this->enviarNotificacaoWhatsApp($solicitacaoId, 'Horário Confirmado', [
                        'data_agendamento' => $dataAgendamento ? date('d/m/Y', strtotime($dataAgendamento)) : '',
                        'horario_agendamento' => $horarioAgendamento ?? '',
                        'horario_servico' => $horarioCompleto
                    ]);
                } else {
                    // Para outras mudanças de status, enviar "Atualização de Status"
                    $this->enviarNotificacaoWhatsApp($solicitacaoId, 'Atualização de Status', [
                        'status_atual' => $statusNovo ?? 'Atualizado'
                    ]);
                }
                
                $this->json(['success' => true, 'message' => 'Status atualizado com sucesso']);
            } else {
                $this->json(['error' => 'Erro ao atualizar status'], 500);
            }
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao atualizar status: ' . $e->getMessage()], 500);
        }
    }

    private function enviarNotificacaoWhatsApp(int $solicitacaoId, string $tipo, array $extraData = []): void
    {
        try {
            $whatsappService = new \App\Services\WhatsAppService();
            $result = $whatsappService->sendMessage($solicitacaoId, $tipo, $extraData);
            
            if (!$result['success']) {
                error_log('Erro WhatsApp [DashboardController]: ' . $result['message']);
            }
        } catch (\Exception $e) {
            error_log('Erro ao enviar WhatsApp [DashboardController]: ' . $e->getMessage());
        }
    }
}
