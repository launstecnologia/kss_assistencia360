<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Solicitacao;
use App\Models\Status;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Imobiliaria;

class SolicitacoesController extends Controller
{
    private Solicitacao $solicitacaoModel;
    private Status $statusModel;
    private Categoria $categoriaModel;
    private Subcategoria $subcategoriaModel;
    private Imobiliaria $imobiliariaModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->solicitacaoModel = new Solicitacao();
        $this->statusModel = new Status();
        $this->categoriaModel = new Categoria();
        $this->subcategoriaModel = new Subcategoria();
        $this->imobiliariaModel = new Imobiliaria();
    }

    public function index(): void
    {
        $filtros = [
            'imobiliaria_id' => $this->input('imobiliaria_id'),
            'status_id' => $this->input('status_id'),
            'categoria_id' => $this->input('categoria_id'),
            'data_inicio' => $this->input('data_inicio'),
            'data_fim' => $this->input('data_fim')
        ];

        // Remover filtros vazios
        $filtros = array_filter($filtros, fn($value) => !empty($value));

        $solicitacoes = $this->solicitacaoModel->getKanbanData();
        
        // Aplicar filtros
        if (!empty($filtros)) {
            $solicitacoes = array_filter($solicitacoes, function($solicitacao) use ($filtros) {
                foreach ($filtros as $campo => $valor) {
                    if ($campo === 'data_inicio') {
                        if ($solicitacao['created_at'] < $valor) return false;
                    } elseif ($campo === 'data_fim') {
                        if ($solicitacao['created_at'] > $valor) return false;
                    } else {
                        if ($solicitacao[$campo] != $valor) return false;
                    }
                }
                return true;
            });
        }

        $status = $this->statusModel->getKanban();
        $categorias = $this->categoriaModel->getAtivas();
        $imobiliarias = $this->imobiliariaModel->getAtivas();

        $this->view('solicitacoes.index', [
            'solicitacoes' => $solicitacoes,
            'status' => $status,
            'categorias' => $categorias,
            'imobiliarias' => $imobiliarias,
            'filtros' => $filtros
        ]);
    }

    public function show(int $id): void
    {
        $solicitacao = $this->solicitacaoModel->getDetalhes($id);
        
        if (!$solicitacao) {
            $this->view('errors.404');
            return;
        }

        // ✅ Garantir que confirmed_schedules seja parseado corretamente
        if (!empty($solicitacao['confirmed_schedules'])) {
            if (is_string($solicitacao['confirmed_schedules'])) {
                $parsed = json_decode($solicitacao['confirmed_schedules'], true);
                $solicitacao['confirmed_schedules'] = is_array($parsed) ? $parsed : null;
            }
        } else {
            $solicitacao['confirmed_schedules'] = null;
        }

        // Buscar fotos (se tabela existir)
        try {
            $fotos = $this->solicitacaoModel->getFotos($id);
        } catch (\Exception $e) {
            $fotos = [];
        }
        
        // Buscar histórico
        try {
            $historico = $this->solicitacaoModel->getHistoricoStatus($id);
        } catch (\Exception $e) {
            $historico = [];
        }
        
        $statusDisponiveis = $this->statusModel->getAtivos();

        $this->view('solicitacoes.show', [
            'solicitacao' => $solicitacao,
            'fotos' => $fotos,
            'historico' => $historico,
            'statusDisponiveis' => $statusDisponiveis
        ]);
    }

    public function api(int $id): void
    {
        $solicitacao = $this->solicitacaoModel->getDetalhes($id);
        
        if (!$solicitacao) {
            $this->json([
                'success' => false,
                'message' => 'Solicitação não encontrada'
            ], 404);
            return;
        }

        // ✅ Garantir que confirmed_schedules seja um array ou null (não string vazia)
        if (!empty($solicitacao['confirmed_schedules'])) {
            // Se for string, tentar parsear
            if (is_string($solicitacao['confirmed_schedules'])) {
                $parsed = json_decode($solicitacao['confirmed_schedules'], true);
                $solicitacao['confirmed_schedules'] = is_array($parsed) ? $parsed : null;
            }
        } else {
            $solicitacao['confirmed_schedules'] = null;
        }

        $this->json([
            'success' => true,
            'solicitacao' => $solicitacao
        ]);
    }

    public function edit(int $id): void
    {
        $solicitacao = $this->solicitacaoModel->getDetalhes($id);
        
        if (!$solicitacao) {
            $this->view('errors.404');
            return;
        }

        $categorias = $this->categoriaModel->getAtivas();
        $subcategorias = $this->subcategoriaModel->getByCategoria($solicitacao['categoria_id']);
        $status = $this->statusModel->getAtivos();
        $imobiliarias = $this->imobiliariaModel->getAtivas();

        $this->view('solicitacoes.edit', [
            'solicitacao' => $solicitacao,
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
            'status' => $status,
            'imobiliarias' => $imobiliarias
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect("/solicitacoes/$id/edit");
        }

        $data = [
            'categoria_id' => $this->input('categoria_id'),
            'subcategoria_id' => $this->input('subcategoria_id'),
            'status_id' => $this->input('status_id'),
            'locatario_nome' => $this->input('locatario_nome'),
            'locatario_telefone' => $this->input('locatario_telefone'),
            'locatario_email' => $this->input('locatario_email'),
            'imovel_endereco' => $this->input('imovel_endereco'),
            'imovel_numero' => $this->input('imovel_numero'),
            'imovel_complemento' => $this->input('imovel_complemento'),
            'imovel_bairro' => $this->input('imovel_bairro'),
            'imovel_cidade' => $this->input('imovel_cidade'),
            'imovel_estado' => $this->input('imovel_estado'),
            'imovel_cep' => $this->input('imovel_cep'),
            'descricao_problema' => $this->input('descricao_problema'),
            'observacoes' => $this->input('observacoes'),
            'prioridade' => $this->input('prioridade'),
            'data_agendamento' => $this->input('data_agendamento'),
            'horario_agendamento' => $this->input('horario_agendamento'),
            'prestador_nome' => $this->input('prestador_nome'),
            'prestador_telefone' => $this->input('prestador_telefone'),
            'valor_orcamento' => $this->input('valor_orcamento'),
            'numero_ncp' => $this->input('numero_ncp'),
            'avaliacao_satisfacao' => $this->input('avaliacao_satisfacao')
        ];

        // ✅ REMOVIDO: Não adicionar disponibilidade na descrição do problema
        // A descrição deve permanecer como o usuário escreveu, sem modificações automáticas

        $errors = $this->validate([
            'categoria_id' => 'required',
            'subcategoria_id' => 'required',
            'status_id' => 'required',
            'locatario_nome' => 'required|min:3',
            'locatario_telefone' => 'required',
            'imovel_endereco' => 'required|min:5'
        ], $data);

        if (!empty($errors)) {
            $solicitacao = $this->solicitacaoModel->getDetalhes($id);
            $categorias = $this->categoriaModel->getAtivas();
            $subcategorias = $this->subcategoriaModel->getByCategoria($data['categoria_id']);
            $status = $this->statusModel->getAtivos();
            $imobiliarias = $this->imobiliariaModel->getAtivas();

            $this->view('solicitacoes.edit', [
                'solicitacao' => $solicitacao,
                'categorias' => $categorias,
                'subcategorias' => $subcategorias,
                'status' => $status,
                'imobiliarias' => $imobiliarias,
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        try {
            $this->solicitacaoModel->update($id, $data);
            $this->redirect("/solicitacoes/$id");
        } catch (\Exception $e) {
            $this->view('solicitacoes.edit', [
                'error' => 'Erro ao atualizar solicitação: ' . $e->getMessage(),
                'solicitacao' => $this->solicitacaoModel->getDetalhes($id)
            ]);
        }
    }

    public function updateStatus(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $statusId = $this->input('status_id');
        $observacoes = $this->input('observacoes');
        $user = $this->getUser();

        if (!$statusId) {
            $this->json(['error' => 'Status é obrigatório'], 400);
            return;
        }

        try {
            $success = $this->solicitacaoModel->updateStatus($id, $statusId, $user['id'], $observacoes);
            
            if ($success) {
                // Buscar nome do status
                $sql = "SELECT nome FROM status WHERE id = ?";
                $status = \App\Core\Database::fetch($sql, [$statusId]);
                
                // Enviar notificação WhatsApp
                $this->enviarNotificacaoWhatsApp($id, 'Atualização de Status', [
                    'status_atual' => $status['nome'] ?? 'Atualizado'
                ]);
                
                $this->json(['success' => true, 'message' => 'Status atualizado com sucesso']);
            } else {
                $this->json(['error' => 'Erro ao atualizar status'], 500);
            }
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao atualizar status: ' . $e->getMessage()], 500);
        }
    }

    public function getSubcategorias(): void
    {
        $categoriaId = $this->input('categoria_id');
        
        if (!$categoriaId) {
            $this->json(['error' => 'Categoria é obrigatória'], 400);
            return;
        }

        $subcategorias = $this->subcategoriaModel->getByCategoria($categoriaId);
        $this->json($subcategorias);
    }

    public function getHorariosDisponiveis(): void
    {
        $subcategoriaId = $this->input('subcategoria_id');
        $data = $this->input('data');
        
        if (!$subcategoriaId || !$data) {
            $this->json(['error' => 'Subcategoria e data são obrigatórios'], 400);
            return;
        }

        $horarios = $this->subcategoriaModel->getHorariosDisponiveis($subcategoriaId, $data);
        $this->json($horarios);
    }

    // Métodos para o fluxo operacional
    public function criarSolicitacao(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $data = [
            'imobiliaria_id' => $this->input('imobiliaria_id'),
            'categoria_id' => $this->input('categoria_id'),
            'subcategoria_id' => $this->input('subcategoria_id'),
            'locatario_id' => $this->input('locatario_id'),
            'locatario_nome' => $this->input('locatario_nome'),
            'locatario_telefone' => $this->input('locatario_telefone'),
            'locatario_email' => $this->input('locatario_email'),
            'imovel_endereco' => $this->input('imovel_endereco'),
            'imovel_numero' => $this->input('imovel_numero'),
            'imovel_complemento' => $this->input('imovel_complemento'),
            'imovel_bairro' => $this->input('imovel_bairro'),
            'imovel_cidade' => $this->input('imovel_cidade'),
            'imovel_estado' => $this->input('imovel_estado'),
            'imovel_cep' => $this->input('imovel_cep'),
            'descricao_problema' => $this->input('descricao_problema'),
            'tipo_atendimento' => $this->input('tipo_atendimento', 'RESIDENCIAL'),
            'datas_opcoes' => json_decode($this->input('datas_opcoes', '[]'), true),
            'prioridade' => $this->input('prioridade', 'NORMAL')
        ];

        // Validar campos obrigatórios
        $errors = $this->validate([
            'imobiliaria_id' => 'required',
            'categoria_id' => 'required',
            'subcategoria_id' => 'required',
            'locatario_nome' => 'required|min:3',
            'locatario_telefone' => 'required|min:10',
            'imovel_endereco' => 'required|min:5',
            'imovel_numero' => 'required',
            'imovel_bairro' => 'required',
            'imovel_cidade' => 'required',
            'imovel_estado' => 'required|min:2',
            'imovel_cep' => 'required|min:8',
            'descricao_problema' => 'required|min:10',
            'datas_opcoes' => 'required'
        ], $data);

        if (!empty($errors)) {
            $this->json(['error' => 'Dados inválidos', 'details' => $errors], 400);
            return;
        }

        // Validar datas
        $datasErrors = $this->solicitacaoModel->validarDatasOpcoes($data['datas_opcoes']);
        if (!empty($datasErrors)) {
            $this->json(['error' => 'Datas inválidas', 'details' => $datasErrors], 400);
            return;
        }

        try {
            // Gerar número da solicitação
            $data['numero_solicitacao'] = $this->solicitacaoModel->gerarNumeroSolicitacao();
            
            // Gerar token de confirmação
            $data['token_confirmacao'] = $this->solicitacaoModel->gerarTokenConfirmacao();
            
            // Definir data limite para cancelamento (1 dia antes da primeira data)
            $primeiraData = new \DateTime($data['datas_opcoes'][0]);
            $data['data_limite_cancelamento'] = $primeiraData->modify('-1 day')->format('Y-m-d');
            
            // Criar solicitação
            $solicitacaoId = $this->solicitacaoModel->create($data);
            
            // Enviar notificação WhatsApp
            $this->enviarNotificacaoWhatsApp($solicitacaoId, 'Nova Solicitação');
            
            $this->json([
                'success' => true,
                'solicitacao_id' => $solicitacaoId,
                'numero_solicitacao' => $data['numero_solicitacao'],
                'message' => 'Solicitação criada com sucesso'
            ]);
            
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao criar solicitação: ' . $e->getMessage()], 500);
        }
    }

    public function confirmarDatas(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $solicitacaoId = $this->input('solicitacao_id');
        $dataConfirmada = $this->input('data_confirmada');
        $mawdyData = [
            'mawdy_id' => $this->input('mawdy_id'),
            'mawdy_nome' => $this->input('mawdy_nome'),
            'mawdy_telefone' => $this->input('mawdy_telefone'),
            'mawdy_email' => $this->input('mawdy_email')
        ];

        try {
            $data = [
                'data_confirmada' => $dataConfirmada,
                'data_agendamento' => $dataConfirmada,
                'mawdy_id' => $mawdyData['mawdy_id'],
                'mawdy_nome' => $mawdyData['mawdy_nome'],
                'mawdy_telefone' => $mawdyData['mawdy_telefone'],
                'mawdy_email' => $mawdyData['mawdy_email'],
                'status_id' => $this->getStatusId('Serviço Agendado')
            ];

            $this->solicitacaoModel->update($solicitacaoId, $data);
            
            // Buscar dados da solicitação para enviar no WhatsApp
            $solicitacao = $this->solicitacaoModel->find($solicitacaoId);
            
            // Enviar notificação WhatsApp
            $this->enviarNotificacaoWhatsApp($solicitacaoId, 'agendado', [
                'data_agendamento' => $dataConfirmada ? date('d/m/Y', strtotime($dataConfirmada)) : '',
                'horario_agendamento' => $solicitacao['horario_agendamento'] ?? 'A confirmar'
            ]);
            
            $this->json(['success' => true, 'message' => 'Datas confirmadas com sucesso']);
            
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao confirmar datas: ' . $e->getMessage()], 500);
        }
    }

    public function cancelarSolicitacao(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $solicitacaoId = $this->input('solicitacao_id');
        $motivo = $this->input('motivo', 'Cancelado pelo locatário');

        try {
            // Verificar se pode cancelar
            if (!$this->solicitacaoModel->podeCancelar($solicitacaoId)) {
                $this->json(['error' => 'Não é possível cancelar esta solicitação'], 400);
                return;
            }

            $this->solicitacaoModel->update($solicitacaoId, [
                'status_id' => $this->getStatusId('Cancelado'),
                'observacoes' => $motivo
            ]);

            $this->json(['success' => true, 'message' => 'Solicitação cancelada com sucesso']);
            
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao cancelar solicitação: ' . $e->getMessage()], 500);
        }
    }

    public function confirmarAtendimento(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $token = $this->input('token');
        $confirmacao = $this->input('confirmacao');
        $avaliacoes = [
            'imobiliaria' => $this->input('avaliacao_imobiliaria'),
            'app' => $this->input('avaliacao_app'),
            'prestador' => $this->input('avaliacao_prestador'),
            'comentarios' => $this->input('comentarios_avaliacao')
        ];

        try {
            // Buscar solicitação pelo token
            $sql = "SELECT id FROM solicitacoes WHERE token_confirmacao = ?";
            $solicitacao = \App\Core\Database::fetch($sql, [$token]);
            
            if (!$solicitacao) {
                $this->json(['error' => 'Token inválido'], 400);
                return;
            }

            $this->solicitacaoModel->confirmarAtendimento($solicitacao['id'], $confirmacao, $avaliacoes);
            
            // Enviar notificação WhatsApp
            $this->enviarNotificacaoWhatsApp($solicitacao['id'], 'concluido');
            
            $this->json(['success' => true, 'message' => 'Atendimento confirmado com sucesso']);
            
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao confirmar atendimento: ' . $e->getMessage()], 500);
        }
    }

    public function informarCompraPeca(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $solicitacaoId = $this->input('solicitacao_id');
        $novasDatas = json_decode($this->input('novas_datas', '[]'), true);

        try {
            // Validar novas datas
            $datasErrors = $this->solicitacaoModel->validarDatasOpcoes($novasDatas);
            if (!empty($datasErrors)) {
                $this->json(['error' => 'Datas inválidas', 'details' => $datasErrors], 400);
                return;
            }

            $this->solicitacaoModel->update($solicitacaoId, [
                'datas_opcoes' => $novasDatas,
                'status_id' => $this->getStatusId('Buscando Prestador'),
                'data_limite_peca' => null,
                'data_ultimo_lembrete' => null,
                'lembretes_enviados' => 0
            ]);

            $this->json(['success' => true, 'message' => 'Compra de peça informada com sucesso']);
            
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao informar compra de peça: ' . $e->getMessage()], 500);
        }
    }

    public function enviarLembretes(): void
    {
        try {
            $solicitacoes = $this->solicitacaoModel->getSolicitacoesParaLembrete();
            
            foreach ($solicitacoes as $solicitacao) {
                $this->enviarNotificacaoWhatsApp($solicitacao['id'], 'lembrete_peca');
                $this->solicitacaoModel->atualizarLembrete($solicitacao['id']);
            }

            $this->json([
                'success' => true,
                'message' => 'Lembretes enviados',
                'count' => count($solicitacoes)
            ]);
            
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao enviar lembretes: ' . $e->getMessage()], 500);
        }
    }

    public function expirarSolicitacoes(): void
    {
        try {
            $solicitacoes = $this->solicitacaoModel->getSolicitacoesExpiradas();
            
            foreach ($solicitacoes as $solicitacao) {
                $this->solicitacaoModel->update($solicitacao['id'], [
                    'status_id' => $this->getStatusId('Expirado')
                ]);
            }

            $this->json([
                'success' => true,
                'message' => 'Solicitações expiradas',
                'count' => count($solicitacoes)
            ]);
            
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao expirar solicitações: ' . $e->getMessage()], 500);
        }
    }

    private function enviarNotificacaoWhatsApp(int $solicitacaoId, string $tipo, array $extraData = []): void
    {
        try {
            $whatsappService = new \App\Services\WhatsAppService();
            $result = $whatsappService->sendMessage($solicitacaoId, $tipo, $extraData);
            
            if (!$result['success']) {
                error_log('Erro WhatsApp: ' . $result['message']);
            }
        } catch (\Exception $e) {
            error_log('Erro ao enviar WhatsApp: ' . $e->getMessage());
        }
    }

    private function getStatusId(string $statusNome): int
    {
        $sql = "SELECT id FROM status WHERE nome = ? LIMIT 1";
        $status = \App\Core\Database::fetch($sql, [$statusNome]);
        return $status['id'] ?? 1;
    }

    public function confirmarHorario(int $id): void
    {
        // ✅ Limpar TODOS os buffers ANTES de qualquer coisa
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // ✅ Desabilitar exibição de erros IMEDIATAMENTE
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        
        // ✅ Função para SEMPRE retornar JSON válido
        $retornarJson = function($success, $message = '', $error = '') {
            // Limpar TODOS os buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Desabilitar exibição de erros
            @ini_set('display_errors', '0');
            
            // Limpar qualquer output anterior
            @ob_end_clean();
            
            // Retornar JSON válido
            @http_response_code($success ? 200 : 500);
            @header('Content-Type: application/json; charset=utf-8');
            @header('Cache-Control: no-cache, must-revalidate');
            
            $response = ['success' => $success];
            if ($success && !empty($message)) {
                $response['message'] = $message;
            }
            if (!$success && !empty($error)) {
                $response['error'] = $error;
            }
            
            $json = @json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                $json = json_encode(['success' => false, 'error' => 'Erro ao serializar resposta'], JSON_UNESCAPED_UNICODE);
            }
            
            echo $json;
            @flush();
            @exit;
        };
        
        // ✅ Registrar erro fatal handler
        register_shutdown_function(function() use ($retornarJson) {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
                error_log('Erro FATAL em confirmarHorario: ' . $error['message'] . ' em ' . $error['file'] . ':' . $error['line']);
                $retornarJson(false, '', 'Erro fatal: ' . $error['message']);
            }
        });
        
        $horario = null;
        $user = null;
        $jaSalvou = false;
        
        try {
            if (!$this->isPost()) {
                $retornarJson(false, '', 'Método não permitido');
                return;
            }

            // ✅ Ler JSON do body (caso seja enviado via fetch)
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            
            // ✅ Aceitar horário do JSON ou do form
            $horario = $json['horario'] ?? $this->input('horario');
            $user = $this->getUser();

            if (!$horario) {
                $retornarJson(false, '', 'Horário é obrigatório');
                return;
            }

            // Buscar status "Serviço Agendado"
            $sql = "SELECT id FROM status WHERE nome = 'Serviço Agendado' LIMIT 1";
            $statusAgendado = \App\Core\Database::fetch($sql);
            
            if (!$statusAgendado || !isset($statusAgendado['id'])) {
                $retornarJson(false, '', 'Status "Serviço Agendado" não encontrado');
                return;
            }
            
            // Validar formato do horário
            $timestamp = strtotime($horario);
            if ($timestamp === false) {
                // Tentar parsear formato ISO ou outros formatos
                try {
                    $dt = new \DateTime($horario);
                    $timestamp = $dt->getTimestamp();
                } catch (\Exception $e) {
                    error_log('Erro ao parsear horário: ' . $horario . ' - ' . $e->getMessage());
                    $retornarJson(false, '', 'Formato de horário inválido: ' . $horario);
                    return;
                }
            }
            
            if ($timestamp === false) {
                error_log('Erro: timestamp ainda é false após tentar parsear: ' . $horario);
                $retornarJson(false, '', 'Formato de horário inválido: ' . $horario);
                return;
            }
            
            $dataAg = date('Y-m-d', $timestamp);
            $horaAg = date('H:i:s', $timestamp);
            
            if ($dataAg === false || $horaAg === false) {
                error_log('Erro ao formatar data/hora do timestamp: ' . $timestamp);
                $retornarJson(false, '', 'Erro ao processar data/hora');
                return;
            }

            // ✅ Processar horário e adicionar ao confirmed_schedules
            $solicitacaoAtual = $this->solicitacaoModel->find($id);
            if (!$solicitacaoAtual) {
                $retornarJson(false, '', 'Solicitação não encontrada');
                return;
            }
            
            // Formatar horário para raw (mesmo formato do offcanvas)
            $horarioFormatado = date('d/m/Y', $timestamp) . ' - ' . date('H:i', $timestamp) . '-' . date('H:i', strtotime('+3 hours', $timestamp));
            
            // Buscar confirmed_schedules existentes
            $confirmedExistentes = [];
            if (!empty($solicitacaoAtual['confirmed_schedules'])) {
                try {
                    // Se já for array, usar diretamente; se for string, parsear
                    if (is_array($solicitacaoAtual['confirmed_schedules'])) {
                        $confirmedExistentes = $solicitacaoAtual['confirmed_schedules'];
                    } else {
                        $confirmedExistentes = json_decode($solicitacaoAtual['confirmed_schedules'], true) ?? [];
                    }
                    if (!is_array($confirmedExistentes)) {
                        $confirmedExistentes = [];
                    }
                } catch (\Exception $e) {
                    error_log('Erro ao parsear confirmed_schedules: ' . $e->getMessage());
                    $confirmedExistentes = [];
                }
            }
            
            // ✅ Função auxiliar para normalizar horários
            $normalizarHorario = function($raw) {
                $raw = trim((string)$raw);
                $raw = preg_replace('/\s+/', ' ', $raw); // Normalizar espaços múltiplos
                return $raw;
            };
            
            // ✅ Função auxiliar para comparar horários de forma precisa (mesma lógica do atualizarDetalhes)
            $compararHorarios = function($raw1, $raw2) {
                $raw1Norm = preg_replace('/\s+/', ' ', trim((string)$raw1));
                $raw2Norm = preg_replace('/\s+/', ' ', trim((string)$raw2));
                
                // Comparação exata primeiro (mais precisa)
                if ($raw1Norm === $raw2Norm) {
                    return true;
                }
                
                // Comparação por regex - extrair data e hora inicial E FINAL EXATAS
                // Formato esperado: "dd/mm/yyyy - HH:MM-HH:MM"
                $regex = '/(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}:\d{2})-(\d{2}:\d{2})/';
                $match1 = preg_match($regex, $raw1Norm, $m1);
                $match2 = preg_match($regex, $raw2Norm, $m2);
                
                if ($match1 && $match2) {
                    // ✅ Comparar data, hora inicial E hora final EXATAS (não apenas data e hora inicial)
                    // Isso garante que apenas horários EXATOS sejam considerados iguais
                    return ($m1[1] === $m2[1] && $m1[2] === $m2[2] && $m1[3] === $m2[3]);
                }
                
                // Se não conseguir comparar por regex, retornar false (não é match)
                return false;
            };
            
            $horarioFormatadoNorm = $normalizarHorario($horarioFormatado);
            
            // ✅ Verificar se já existe este horário confirmado (usando comparação precisa)
            $horarioJaConfirmado = false;
            $horarioExistente = null;
            foreach ($confirmedExistentes as $existente) {
                if (!isset($existente['raw']) || empty($existente['raw'])) {
                    continue;
                }
                $existenteRawNorm = $normalizarHorario($existente['raw']);
                if ($compararHorarios($horarioFormatadoNorm, $existenteRawNorm)) {
                    $horarioJaConfirmado = true;
                    $horarioExistente = $existente;
                    break;
                }
            }
            
            // Se não existe, adicionar aos confirmados
            if (!$horarioJaConfirmado) {
                $confirmedExistentes[] = [
                    'date' => $dataAg,
                    'time' => date('H:i', $timestamp) . '-' . date('H:i', strtotime('+3 hours', $timestamp)),
                    'raw' => $horarioFormatadoNorm, // ✅ Usar formato normalizado
                    'source' => 'operator',
                    'confirmed_at' => date('c')
                ];
            } else {
                // ✅ Se já existe, garantir que está usando o formato normalizado
                $confirmedExistentes = array_map(function($item) use ($horarioFormatadoNorm, $normalizarHorario, $compararHorarios) {
                    if (isset($item['raw']) && $compararHorarios($item['raw'], $horarioFormatadoNorm)) {
                        $item['raw'] = $horarioFormatadoNorm; // ✅ Normalizar formato
                    }
                    return $item;
                }, $confirmedExistentes);
            }

            // Atualizar solicitação
            // ✅ Usar horarioFormatadoNorm em vez de horarioFormatado para consistência
            $dadosUpdate = [
                'data_agendamento' => $dataAg,
                'horario_agendamento' => $horaAg,
                'status_id' => $statusAgendado['id'],
                'horario_confirmado' => 1,
                'horario_confirmado_raw' => $horarioFormatadoNorm, // ✅ Usar formato normalizado
                'confirmed_schedules' => json_encode($confirmedExistentes)
            ];
            
            // ✅ DEBUG: Log antes de remover duplicatas
            error_log("DEBUG confirmarHorario [ID:{$id}] - confirmedExistentes ANTES de remover duplicatas: " . json_encode($confirmedExistentes));
            error_log("DEBUG confirmarHorario [ID:{$id}] - horarioFormatadoNorm: {$horarioFormatadoNorm}");
            error_log("DEBUG confirmarHorario [ID:{$id}] - Total antes de remover duplicatas: " . count($confirmedExistentes));
            
            // ✅ Remover duplicatas finais (segurança extra) - ANTES de salvar
            $confirmedFinalUnicos = [];
            $rawsJaAdicionados = [];
            foreach ($confirmedExistentes as $index => $item) {
                if (!isset($item['raw']) || empty($item['raw'])) {
                    error_log("DEBUG confirmarHorario [ID:{$id}] - Item {$index} sem raw, pulando");
                    continue;
                }
                $rawNorm = $normalizarHorario($item['raw']);
                
                // Verificar se já foi adicionado
                $jaAdicionado = false;
                foreach ($rawsJaAdicionados as $idx => $rawJaAdd) {
                    if ($compararHorarios($rawNorm, $rawJaAdd)) {
                        $jaAdicionado = true;
                        error_log("DEBUG confirmarHorario [ID:{$id}] - ⚠️ DUPLICATA DETECTADA! Item {$index} com raw '{$rawNorm}' já existe no índice {$idx} como '{$rawJaAdd}'");
                        break;
                    }
                }
                
                if (!$jaAdicionado) {
                    $confirmedFinalUnicos[] = $item;
                    $rawsJaAdicionados[] = $rawNorm;
                    error_log("DEBUG confirmarHorario [ID:{$id}] - ✅ Item {$index} adicionado: '{$rawNorm}'");
                }
            }
            
            // ✅ DEBUG: Log após remover duplicatas
            error_log("DEBUG confirmarHorario [ID:{$id}] - confirmedFinalUnicos APÓS remover duplicatas: " . json_encode($confirmedFinalUnicos));
            error_log("DEBUG confirmarHorario [ID:{$id}] - Total APÓS remover duplicatas: " . count($confirmedFinalUnicos));
            
            // Validar que confirmed_schedules é JSON válido
            $confirmedJsonFinal = json_encode($confirmedFinalUnicos);
            if ($confirmedJsonFinal === false) {
                error_log('Erro ao serializar confirmed_schedules: ' . json_last_error_msg());
                $retornarJson(false, '', 'Erro ao processar horários confirmados');
                return;
            }
            $dadosUpdate['confirmed_schedules'] = $confirmedJsonFinal;
            
            // ✅ Verificar se já salvou (proteção contra duplicação)
            if ($jaSalvou) {
                error_log('AVISO: Tentativa de salvar confirmarHorario duas vezes para ID: ' . $id);
                $retornarJson(false, '', 'Operação já foi processada');
                return;
            }
            
            try {
                $this->solicitacaoModel->update($id, $dadosUpdate);
                $jaSalvou = true; // ✅ Marcar como salvo
            } catch (\Exception $e) {
                error_log('Erro ao atualizar solicitação: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                $retornarJson(false, '', 'Erro ao salvar solicitação: ' . $e->getMessage());
                return;
            }
            
            // Registrar histórico
            if ($user && isset($user['id'])) {
                try {
                    $this->solicitacaoModel->updateStatus($id, $statusAgendado['id'], $user['id'], 
                        'Horário confirmado: ' . $horarioFormatado);
                } catch (\Exception $e) {
                    // Log do erro mas não bloquear a resposta
                    error_log('Erro ao atualizar status no histórico: ' . $e->getMessage());
                }
            }
            
            // ✅ Enviar notificação WhatsApp (em background, não bloquear)
            try {
                // Buscar dados atualizados da solicitação para garantir que temos o telefone correto
                $solicitacaoAtual = $this->solicitacaoModel->find($id);
                
                // Verificar se tem telefone antes de enviar
                $telefone = $solicitacaoAtual['locatario_telefone'] ?? null;
                if (empty($telefone) && !empty($solicitacaoAtual['locatario_id'])) {
                    // Buscar telefone do locatário
                    $sqlLocatario = "SELECT telefone FROM locatarios WHERE id = ?";
                    $locatario = \App\Core\Database::fetch($sqlLocatario, [$solicitacaoAtual['locatario_id']]);
                    $telefone = $locatario['telefone'] ?? null;
                }
                
                if (!empty($telefone)) {
                    // Formatar horário completo para exibição
                    $horarioCompleto = $horarioFormatadoNorm ?? date('d/m/Y', $timestamp) . ' - ' . date('H:i', $timestamp) . '-' . date('H:i', strtotime('+3 hours', $timestamp));
                    
                    $this->enviarNotificacaoWhatsApp($id, 'Horário Confirmado', [
                        'data_agendamento' => date('d/m/Y', $timestamp),
                        'horario_agendamento' => date('H:i', $timestamp) . '-' . date('H:i', strtotime('+3 hours', $timestamp)),
                        'horario_servico' => $horarioCompleto,
                        'horario_confirmado_raw' => $horarioFormatadoNorm ?? $horarioFormatado
                    ]);
                    
                    error_log("DEBUG WhatsApp [ID:{$id}] - WhatsApp enviado para telefone: {$telefone}");
                } else {
                    error_log("DEBUG WhatsApp [ID:{$id}] - ⚠️ Telefone não encontrado, WhatsApp NÃO enviado");
                }
            } catch (\Exception $e) {
                // Ignorar erro de WhatsApp, não bloquear a resposta
                error_log('Erro ao enviar WhatsApp [ID:' . $id . ']: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
            }
            
            // ✅ Retornar sucesso
            $retornarJson(true, 'Horário confirmado com sucesso');
            
        } catch (\Exception $e) {
            error_log('Erro em confirmarHorario [ID:' . $id . ']: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('Horário recebido: ' . var_export($horario, true));
            
            $retornarJson(false, '', 'Erro ao confirmar horário: ' . $e->getMessage());
            
        } catch (\Throwable $e) {
            error_log('Erro fatal em confirmarHorario [ID:' . $id . ']: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('Horário recebido: ' . var_export($horario ?? 'N/A', true));
            
            $retornarJson(false, '', 'Erro inesperado ao confirmar horário: ' . $e->getMessage());
            
        } catch (\Exception $e) {
            error_log('Erro EXCEPCIONAL em confirmarHorario [ID:' . $id . ']: ' . $e->getMessage());
            $retornarJson(false, '', 'Erro ao confirmar horário: ' . $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Erro FATAL em confirmarHorario [ID:' . $id . ']: ' . $e->getMessage());
            $retornarJson(false, '', 'Erro inesperado ao confirmar horário: ' . $e->getMessage());
        }
    }

    public function confirmarHorariosBulk(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $schedules = $payload['schedules'] ?? []; // [{date: 'YYYY-MM-DD', time: 'HH:MM'|'HH:MM-HH:MM', raw: '...'}]

        if (empty($schedules) || !is_array($schedules)) {
            $this->json(['error' => 'Nenhum horário informado'], 400);
            return;
        }

        try {
            // Normalizar confirmados para salvar como JSON
            $confirmed = [];
            foreach ($schedules as $s) {
                $confirmed[] = [
                    'date' => $s['date'] ?? null,
                    'time' => $s['time'] ?? null,
                    'raw'  => $s['raw'] ?? trim(($s['date'] ?? '') . ' ' . ($s['time'] ?? '')),
                    'source' => 'operator',
                    'confirmed_at' => date('c')
                ];
            }

            // Último será o agendamento principal
            $last = end($confirmed);
            $dataAg = (!empty($last['date'])) ? date('Y-m-d', strtotime($last['date'])) : null;
            // Se time for faixa, inclui apenas início
            $horaRaw = $last['time'] ?? '';
            $horaAg = preg_match('/^\d{2}:\d{2}/', $horaRaw, $m) ? ($m[0] . ':00') : (!empty($horaRaw) ? $horaRaw : null);

            // Atualizar registro
            $this->solicitacaoModel->update($id, [
                'data_agendamento' => $dataAg,
                'horario_agendamento' => $horaAg,
                'horario_confirmado' => 1,
                'horario_confirmado_raw' => $last['raw'],
                'confirmed_schedules' => json_encode($confirmed)
            ]);

            // Enviar WhatsApp (opcional)
            $this->enviarNotificacaoWhatsApp($id, 'Horário Confirmado', [
                'data_agendamento' => (!empty($dataAg)) ? date('d/m/Y', strtotime($dataAg)) : '',
                'horario_agendamento' => (is_string($horaRaw) ? $horaRaw : ((!empty($horaAg)) ? date('H:i', strtotime($horaAg)) : ''))
            ]);

            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function desconfirmarHorario(int $id): void
    {
        // ✅ Limpar TODOS os buffers ANTES de qualquer coisa
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // ✅ Desabilitar exibição de erros IMEDIATAMENTE
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        
        // ✅ Função para SEMPRE retornar JSON válido
        $retornarJson = function($success, $message = '', $error = '') {
            // Limpar TODOS os buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Desabilitar exibição de erros
            @ini_set('display_errors', '0');
            
            // Limpar qualquer output anterior
            @ob_end_clean();
            
            // Retornar JSON válido
            @http_response_code($success ? 200 : 500);
            @header('Content-Type: application/json; charset=utf-8');
            @header('Cache-Control: no-cache, must-revalidate');
            
            $response = ['success' => $success];
            if ($success && !empty($message)) {
                $response['message'] = $message;
            }
            if (!$success && !empty($error)) {
                $response['error'] = $error;
            }
            
            $json = @json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                $json = json_encode(['success' => false, 'error' => 'Erro ao serializar resposta'], JSON_UNESCAPED_UNICODE);
            }
            
            echo $json;
            @flush();
            @exit;
        };
        
        $oldErrorReporting = error_reporting(E_ALL);
        $oldDisplayErrors = ini_set('display_errors', '0');
        
        $horario = null;
        $user = null;
        
        try {
            if (!$this->isPost()) {
                $retornarJson(false, '', 'Método não permitido');
                return;
            }

            // ✅ Ler JSON do body (caso seja enviado via fetch)
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            
            // ✅ Aceitar horário do JSON ou do form
            $horario = $json['horario'] ?? $this->input('horario');
            $user = $this->getUser();
            
            if (!$user || !isset($user['id'])) {
                $retornarJson(false, '', 'Usuário não autenticado');
                return;
            }

            // ✅ Buscar solicitação atual
            $solicitacaoAtual = $this->solicitacaoModel->find($id);
            if (!$solicitacaoAtual) {
                $retornarJson(false, '', 'Solicitação não encontrada');
                return;
            }
            
            // ✅ Buscar confirmed_schedules existentes
            $confirmedExistentes = [];
            if (!empty($solicitacaoAtual['confirmed_schedules'])) {
                try {
                    if (is_array($solicitacaoAtual['confirmed_schedules'])) {
                        $confirmedExistentes = $solicitacaoAtual['confirmed_schedules'];
                    } else {
                        $confirmedExistentes = json_decode($solicitacaoAtual['confirmed_schedules'], true) ?? [];
                    }
                    if (!is_array($confirmedExistentes)) {
                        $confirmedExistentes = [];
                    }
                } catch (\Exception $e) {
                    error_log('Erro ao parsear confirmed_schedules: ' . $e->getMessage());
                    $confirmedExistentes = [];
                }
            }
            
            // ✅ Função auxiliar para normalizar horários
            $normalizarHorario = function($raw) {
                $raw = trim((string)$raw);
                $raw = preg_replace('/\s+/', ' ', $raw);
                return $raw;
            };
            
            // ✅ Função auxiliar para comparar horários de forma precisa
            $compararHorarios = function($raw1, $raw2) {
                $raw1Norm = preg_replace('/\s+/', ' ', trim((string)$raw1));
                $raw2Norm = preg_replace('/\s+/', ' ', trim((string)$raw2));
                
                // Comparação exata primeiro
                if ($raw1Norm === $raw2Norm) {
                    return true;
                }
                
                // Comparação por regex - extrair data e hora inicial E FINAL EXATAS
                $regex = '/(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}:\d{2})-(\d{2}:\d{2})/';
                $match1 = preg_match($regex, $raw1Norm, $m1);
                $match2 = preg_match($regex, $raw2Norm, $m2);
                
                if ($match1 && $match2) {
                    // ✅ Comparar data, hora inicial E hora final EXATAS
                    return ($m1[1] === $m2[1] && $m1[2] === $m2[2] && $m1[3] === $m2[3]);
                }
                
                return false;
            };
            
            // ✅ Se horário foi especificado, remover apenas esse horário
            if (!empty($horario)) {
                $horarioFormatadoNorm = $normalizarHorario($horario);
                
                // ✅ Remover apenas o horário específico do array
                $confirmedFinal = [];
                foreach ($confirmedExistentes as $item) {
                    if (!isset($item['raw']) || empty($item['raw'])) {
                        continue;
                    }
                    $itemRawNorm = $normalizarHorario($item['raw']);
                    
                    // ✅ Se for o horário a ser removido, não adicionar
                    if ($compararHorarios($itemRawNorm, $horarioFormatadoNorm)) {
                        error_log("DEBUG desconfirmarHorario [ID:{$id}] - Removendo horário: {$itemRawNorm}");
                        continue; // Pular este item
                    }
                    
                    // Adicionar os outros horários
                    $confirmedFinal[] = $item;
                }
                
                error_log("DEBUG desconfirmarHorario [ID:{$id}] - Total antes: " . count($confirmedExistentes));
                error_log("DEBUG desconfirmarHorario [ID:{$id}] - Total depois: " . count($confirmedFinal));
                
                $confirmedExistentes = $confirmedFinal;
            } else {
                // ✅ Se não especificou horário, limpar todos (comportamento antigo)
                $confirmedExistentes = [];
            }
            
            // ✅ Buscar status "Nova Solicitação" ou "Pendente" se não há mais horários confirmados
            $statusNova = null;
            if (empty($confirmedExistentes)) {
                $sqlStatus = "SELECT id FROM status WHERE nome IN ('Nova Solicitação', 'Pendente') LIMIT 1";
                $statusNova = \App\Core\Database::fetch($sqlStatus);
            }
            
            // ✅ Preparar dados de atualização
            $dadosUpdate = [
                'confirmed_schedules' => json_encode($confirmedExistentes)
            ];
            
            // ✅ Se não há mais horários confirmados, limpar campos de agendamento
            if (empty($confirmedExistentes)) {
                $dadosUpdate['data_agendamento'] = null;
                $dadosUpdate['horario_agendamento'] = null;
                $dadosUpdate['horario_confirmado'] = 0;
                $dadosUpdate['horario_confirmado_raw'] = null;
                
                if ($statusNova && isset($statusNova['id'])) {
                    $dadosUpdate['status_id'] = $statusNova['id'];
                }
            } else {
                // ✅ Se ainda há horários confirmados, atualizar com o último horário
                $last = end($confirmedExistentes);
                $dataAg = (!empty($last['date'])) ? date('Y-m-d', strtotime($last['date'])) : null;
                $horaRaw = $last['time'] ?? '';
                $horaAg = preg_match('/^\d{2}:\d{2}/', $horaRaw, $m) ? ($m[0] . ':00') : (!empty($horaRaw) ? $horaRaw : null);
                
                $dadosUpdate['data_agendamento'] = $dataAg;
                $dadosUpdate['horario_agendamento'] = $horaAg;
                $dadosUpdate['horario_confirmado'] = 1;
                $dadosUpdate['horario_confirmado_raw'] = $last['raw'] ?? null;
            }
            
            // ✅ Atualizar solicitação
            $this->solicitacaoModel->update($id, $dadosUpdate);
            
            // ✅ Registrar histórico
            if ($user && isset($user['id'])) {
                $statusId = $statusNova && isset($statusNova['id']) ? $statusNova['id'] : $solicitacaoAtual['status_id'];
                $mensagem = !empty($horario) ? "Horário desconfirmado: {$horario}" : 'Todos os horários foram desconfirmados';
                $this->solicitacaoModel->updateStatus($id, $statusId, $user['id'], $mensagem);
            }
            
            $retornarJson(true, 'Horário desconfirmado com sucesso');
            
        } catch (\Exception $e) {
            error_log('Erro em desconfirmarHorario [ID:' . $id . ']: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('Horário recebido: ' . var_export($horario, true));
            $retornarJson(false, '', 'Erro ao desconfirmar horário: ' . $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Erro fatal em desconfirmarHorario [ID:' . $id . ']: ' . $e->getMessage());
            $retornarJson(false, '', 'Erro inesperado ao desconfirmar horário: ' . $e->getMessage());
        }
    }
    
    public function solicitarNovosHorarios(int $id): void
    {
        // ✅ Iniciar output buffering ANTES de qualquer coisa (captura qualquer output indesejado)
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        // ✅ Desabilitar exibição de erros para evitar HTML na resposta
        $oldErrorReporting = error_reporting(E_ALL);
        $oldDisplayErrors = ini_set('display_errors', '0');
        
        try {
            if (!$this->isPost()) {
                $this->json(['success' => false, 'error' => 'Método não permitido'], 405);
                return;
            }

            // ✅ Ler JSON do body (caso seja enviado via fetch)
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            
            // ✅ Aceitar observação do JSON ou do form
            $observacao = $json['observacao'] ?? $this->input('observacao');
            $user = $this->getUser();
            
            if (!$user || !isset($user['id'])) {
                $this->json(['success' => false, 'error' => 'Usuário não autenticado'], 401);
                return;
            }

            // Limpar horários atuais
            $this->solicitacaoModel->update($id, [
                'horarios_opcoes' => null
            ]);
            
            // Registrar no histórico
            $solicitacao = $this->solicitacaoModel->find($id);
            if (!$solicitacao) {
                $this->json(['success' => false, 'error' => 'Solicitação não encontrada'], 404);
                return;
            }
            
            $this->solicitacaoModel->updateStatus($id, 
                $solicitacao['status_id'], 
                $user['id'], 
                'Horários indisponíveis. Motivo: ' . ($observacao ?? 'Não informado'));
            
            // Enviar notificação WhatsApp solicitando novos horários (em background, não bloquear)
            try {
                $this->enviarNotificacaoWhatsApp($id, 'Horário Sugerido', [
                    'data_agendamento' => 'A definir',
                    'horario_agendamento' => 'Aguardando novas opções'
                ]);
            } catch (\Exception $e) {
                // Ignorar erro de WhatsApp, não bloquear a resposta
                error_log('Erro ao enviar WhatsApp: ' . $e->getMessage());
            }
            
            $this->json(['success' => true, 'message' => 'Solicitação de novos horários enviada']);
            
        } catch (\Exception $e) {
            error_log('Erro em solicitarNovosHorarios: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $this->json(['success' => false, 'error' => 'Erro ao solicitar novos horários: ' . $e->getMessage()], 500);
        } catch (\Throwable $e) {
            error_log('Erro fatal em solicitarNovosHorarios: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Erro inesperado ao solicitar novos horários'], 500);
        } finally {
            // ✅ Limpar qualquer output buffer antes de retornar JSON
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Restaurar configurações anteriores
            error_reporting($oldErrorReporting);
            if ($oldDisplayErrors !== false) {
                ini_set('display_errors', $oldDisplayErrors);
            }
        }
    }

    /**
     * Confirma realização do serviço
     */
    public function confirmarServico(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $servicoRealizado = $this->input('servico_realizado');
        $prestadorCompareceu = $this->input('prestador_compareceu');
        $precisaComprarPecas = $this->input('precisa_comprar_pecas');
        $observacoes = $this->input('observacoes');
        $user = $this->getUser();

        try {
            $solicitacao = $this->solicitacaoModel->find($id);
            
            if (!$solicitacao) {
                $this->json(['error' => 'Solicitação não encontrada'], 404);
                return;
            }

            // Atualizar observações
            if (!empty($observacoes)) {
                $this->solicitacaoModel->update($id, [
                    'observacoes' => $observacoes
                ]);
            }

            // Montar mensagem de histórico
            $historico = "Confirmação de serviço:\n";
            $historico .= $servicoRealizado ? "✅ Serviço realizado\n" : "";
            $historico .= !$prestadorCompareceu ? "🚫 Prestador não compareceu\n" : "";
            $historico .= $precisaComprarPecas ? "🔧 Precisa comprar peças\n" : "";
            $historico .= $observacoes ? "📝 Obs: $observacoes" : "";
            
            // Registrar histórico
            $this->solicitacaoModel->updateStatus($id, $solicitacao['status_id'], $user['id'], $historico);

            // Enviar notificação WhatsApp
            $this->enviarNotificacaoWhatsApp($id, 'Confirmação de Serviço', [
                'horario_servico' => date('d/m/Y H:i', strtotime($solicitacao['data_agendamento']))
            ]);

            $this->json(['success' => true, 'message' => 'Confirmação registrada com sucesso']);
            
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function atualizarDetalhes(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $observacoes = $this->input('observacoes');
        $precisaReembolso = $this->input('precisa_reembolso');
        $valorReembolso = $this->input('valor_reembolso');
        $protocoloSeguradora = $this->input('protocolo_seguradora');
        $horariosIndisponiveis = $this->input('horarios_indisponiveis');

        // Tentar ler JSON cru (caso o front envie via fetch JSON)
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $schedulesFromJson = null; // null = não foi enviado, array = foi enviado (pode ser vazio)
        $schedulesFoiEnviado = false;
        
        // Verificar se schedules foi enviado no JSON
        if (is_array($json) && array_key_exists('schedules', $json)) {
            $schedulesFromJson = is_array($json['schedules']) ? $json['schedules'] : [];
            $schedulesFoiEnviado = true;
        }
        
        // Também aceitar schedules por form (pode ser string JSON ou array já parseado)
        $schedulesForm = $this->input('schedules');
        if ($schedulesForm !== null && $schedulesForm !== '') {
            // ✅ Se já for array (do JSON parseado pelo Controller), usar diretamente
            if (is_array($schedulesForm)) {
                $schedulesFromJson = $schedulesForm;
                $schedulesFoiEnviado = true;
            } elseif (is_string($schedulesForm)) {
                // ✅ Se for string, tentar parsear
                $tmp = json_decode($schedulesForm, true);
                if (is_array($tmp)) {
                    $schedulesFromJson = $tmp;
                    $schedulesFoiEnviado = true;
                }
            }
        }

        try {
            $dados = [
                'observacoes' => $observacoes
            ];

            // Adicionar protocolo se fornecido
            if ($protocoloSeguradora !== null && $protocoloSeguradora !== '') {
                $dados['protocolo_seguradora'] = $protocoloSeguradora;
            }

            // Adicionar campos de reembolso
            if ($precisaReembolso === true || $precisaReembolso === 'true' || $precisaReembolso === 1) {
                $dados['precisa_reembolso'] = 1;
                $valorConvertido = floatval($valorReembolso);
                $dados['valor_reembolso'] = $valorConvertido > 0 ? $valorConvertido : null;
            } else {
                $dados['precisa_reembolso'] = 0;
                $dados['valor_reembolso'] = null;
            }
            
            // Adicionar campo de horários indisponíveis
            if ($horariosIndisponiveis === true || $horariosIndisponiveis === 'true' || $horariosIndisponiveis === 1) {
                $dados['horarios_indisponiveis'] = 1;
            } else {
                $dados['horarios_indisponiveis'] = 0;
            }

            // Debug log
            error_log('Dados recebidos: ' . json_encode([
                'id' => $id,
                'precisa_reembolso' => $precisaReembolso,
                'valor_reembolso_raw' => $valorReembolso,
                'valor_reembolso_convertido' => isset($dados['valor_reembolso']) ? $dados['valor_reembolso'] : 'null'
            ]));

            // ✅ Se schedules foi enviado (mesmo que vazio), processar confirmação
            // IMPORTANTE: schedulesFromJson contém apenas os horários MARCADOS (checked)
            // Se um horário estava confirmado e não está na lista, significa que foi DESMARCADO
            if ($schedulesFoiEnviado) {
                // ✅ Buscar solicitação atual e horários disponíveis
                $solicitacaoAtual = $this->solicitacaoModel->find($id);
                $confirmedExistentes = [];
                
                if (!empty($solicitacaoAtual['confirmed_schedules'])) {
                    try {
                        $confirmedExistentes = json_decode($solicitacaoAtual['confirmed_schedules'], true) ?? [];
                        if (!is_array($confirmedExistentes)) {
                            $confirmedExistentes = [];
                        }
                    } catch (\Exception $e) {
                        $confirmedExistentes = [];
                    }
                }
                
                // ✅ Se schedulesFromJson está vazio (todos desmarcados), limpar todos os confirmados
                if (is_array($schedulesFromJson) && empty($schedulesFromJson)) {
                    // Usuário desmarcou todos - limpar confirmações
                    $dados['horario_confirmado'] = 0;
                    $dados['horario_confirmado_raw'] = null;
                    $dados['data_agendamento'] = null;
                    $dados['horario_agendamento'] = null;
                    $dados['confirmed_schedules'] = json_encode([]);
                    // Voltar status para "Nova Solicitação" se estava agendado
                    try {
                        $statusNova = $this->getStatusId('Nova Solicitação');
                        if ($statusNova) {
                            $dados['status_id'] = $statusNova;
                        }
                    } catch (\Exception $e) {
                        // Ignorar erro de status, manter status atual
                    }
                } else if (!empty($schedulesFromJson)) {
                    // ✅ Processar horários selecionados (MARCADOS)
                    // IMPORTANTE: schedulesFromJson contém apenas os checkboxes MARCADOS
                    
                    // ✅ DEBUG: Log do que está sendo recebido
                    error_log("DEBUG atualizarDetalhes [ID:{$id}] - schedulesFromJson recebido: " . json_encode($schedulesFromJson));
                    error_log("DEBUG atualizarDetalhes [ID:{$id}] - confirmedExistentes: " . json_encode($confirmedExistentes));
                    
                    $confirmedFinal = [];
                    $rawsSelecionados = [];
                    
                    // 1. Coletar raws dos horários selecionados (REMOVER DUPLICATAS JÁ AQUI)
                    $rawsUnicos = [];
                    foreach ($schedulesFromJson as $s) {
                        $raw = trim($s['raw'] ?? trim(($s['date'] ?? '') . ' ' . ($s['time'] ?? '')));
                        $rawNorm = preg_replace('/\s+/', ' ', trim((string)$raw));
                        
                        // ✅ Verificar se já está na lista de únicos (evitar duplicatas no input)
                        $jaExiste = false;
                        foreach ($rawsUnicos as $rawUnico) {
                            if ($rawNorm === $rawUnico) {
                                $jaExiste = true;
                                break;
                            }
                        }
                        
                        if (!$jaExiste) {
                            $rawsUnicos[] = $rawNorm;
                            $rawsSelecionados[] = $rawNorm;
                        }
                    }
                    
                    error_log("DEBUG atualizarDetalhes [ID:{$id}] - rawsSelecionados (após remover duplicatas): " . json_encode($rawsSelecionados));
                    
                    // ✅ Função auxiliar para normalizar e comparar horários de forma precisa
                    $normalizarHorario = function($raw) {
                        // Normalizar: remover espaços extras, padronizar formato
                        $raw = trim((string)$raw);
                        $raw = preg_replace('/\s+/', ' ', $raw); // Normalizar espaços múltiplos
                        return $raw;
                    };
                    
                    // ✅ Função auxiliar para comparar horários de forma precisa
                    $compararHorarios = function($raw1, $raw2) {
                        $raw1Norm = preg_replace('/\s+/', ' ', trim((string)$raw1));
                        $raw2Norm = preg_replace('/\s+/', ' ', trim((string)$raw2));
                        
                        // Comparação exata primeiro (mais precisa)
                        if ($raw1Norm === $raw2Norm) {
                            return true;
                        }
                        
                        // Comparação por regex - extrair data e hora inicial E FINAL EXATAS
                        // Formato esperado: "dd/mm/yyyy - HH:MM-HH:MM"
                        $regex = '/(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}:\d{2})-(\d{2}:\d{2})/';
                        $match1 = preg_match($regex, $raw1Norm, $m1);
                        $match2 = preg_match($regex, $raw2Norm, $m2);
                        
                        if ($match1 && $match2) {
                            // ✅ Comparar data, hora inicial E hora final EXATAS (não apenas data e hora inicial)
                            // Isso garante que apenas horários EXATOS sejam considerados iguais
                            return ($m1[1] === $m2[1] && $m1[2] === $m2[2] && $m1[3] === $m2[3]);
                        }
                        
                        // Se não conseguir comparar por regex, retornar false (não é match)
                        return false;
                    };
                    
                    // 2. Para cada horário selecionado (usar rawsUnicos para evitar processar duplicatas)
                    // ✅ Usar array temporário para evitar duplicatas
                    $confirmedTemp = [];
                    $rawsProcessados = []; // ✅ Rastrear quais raws já foram processados
                    
                    // ✅ Processar apenas os horários únicos selecionados
                    foreach ($rawsSelecionados as $rawSelecionado) {
                        $rawNorm = $normalizarHorario($rawSelecionado);
                        
                        // ✅ Verificar se já processamos este raw (segunda camada de proteção)
                        if (in_array($rawNorm, $rawsProcessados, true)) {
                            error_log("DEBUG atualizarDetalhes [ID:{$id}] - ⚠️ Raw já processado, pulando: {$rawNorm}");
                            continue;
                        }
                        $rawsProcessados[] = $rawNorm;
                        
                        // ✅ Verificar se já existe nos confirmados existentes (comparação precisa)
                        $horarioExistente = null;
                        foreach ($confirmedExistentes as $existente) {
                            $existenteRaw = trim($existente['raw'] ?? '');
                            if ($compararHorarios($rawNorm, $existenteRaw)) {
                                $horarioExistente = $existente;
                                break;
                            }
                        }
                        
                        // ✅ Verificar se já está em confirmedTemp (evitar duplicatas no mesmo processamento)
                        $jaExisteNoTemp = false;
                        foreach ($confirmedTemp as $temp) {
                            $tempRaw = trim($temp['raw'] ?? '');
                            if ($compararHorarios($rawNorm, $tempRaw)) {
                                $jaExisteNoTemp = true;
                                break;
                            }
                        }
                        
                        // Se já existe no temp, pular (evitar duplicata)
                        if ($jaExisteNoTemp) {
                            error_log("DEBUG atualizarDetalhes [ID:{$id}] - ⚠️ Raw já existe no confirmedTemp, pulando: {$rawNorm}");
                            continue;
                        }
                        
                        // ✅ Buscar dados completos do scheduleFromJson para este raw
                        $scheduleData = null;
                        foreach ($schedulesFromJson as $s) {
                            $sRaw = trim($s['raw'] ?? trim(($s['date'] ?? '') . ' ' . ($s['time'] ?? '')));
                            $sRawNorm = $normalizarHorario($sRaw);
                            if ($compararHorarios($rawNorm, $sRawNorm)) {
                                $scheduleData = $s;
                                break;
                            }
                        }
                        
                        // Se existe nos confirmados existentes, manter (preserva confirmed_at original)
                        if ($horarioExistente) {
                            $confirmedTemp[] = $horarioExistente;
                            error_log("DEBUG atualizarDetalhes [ID:{$id}] - ✅ Horário existente preservado: {$rawNorm}");
                        } else {
                            // Se não existe, criar novo confirmado
                            $confirmedTemp[] = [
                                'date' => $scheduleData['date'] ?? null,
                                'time' => $scheduleData['time'] ?? null,
                                'raw'  => $rawNorm,
                                'source' => 'operator',
                                'confirmed_at' => date('c')
                            ];
                            error_log("DEBUG atualizarDetalhes [ID:{$id}] - ✅ Novo horário confirmado criado: {$rawNorm}");
                        }
                    }
                    
                    // ✅ Usar confirmedTemp como confirmedFinal (já sem duplicatas)
                    $confirmedFinal = $confirmedFinalLimpo = $confirmedTemp;
                    
                    // ✅ DEBUG: Log final antes de salvar
                    error_log("DEBUG atualizarDetalhes [ID:{$id}] - confirmedFinal (antes de salvar): " . json_encode($confirmedFinal));
                    error_log("DEBUG atualizarDetalhes [ID:{$id}] - Total de horários confirmados: " . count($confirmedFinal));
                    
                    // ✅ Se não há mais nenhum confirmado, limpar agendamento
                    if (empty($confirmedFinalLimpo)) {
                        $dados['horario_confirmado'] = 0;
                        $dados['horario_confirmado_raw'] = null;
                        $dados['data_agendamento'] = null;
                        $dados['horario_agendamento'] = null;
                        $dados['confirmed_schedules'] = json_encode([]);
                        // Voltar status para "Nova Solicitação"
                        try {
                            $statusNova = $this->getStatusId('Nova Solicitação');
                            if ($statusNova) {
                                $dados['status_id'] = $statusNova;
                            }
                        } catch (\Exception $e) {
                            // Ignorar erro de status, manter status atual
                        }
                    } else {
                        // ✅ Último horário vira o agendamento principal
                        $last = end($confirmedFinalLimpo);
                        $dataAg = (!empty($last['date'])) ? date('Y-m-d', strtotime($last['date'])) : null;
                        $horaRaw = $last['time'] ?? '';
                        $horaAg = preg_match('/^\d{2}:\d{2}/', $horaRaw, $m) ? ($m[0] . ':00') : (!empty($horaRaw) ? $horaRaw : null);

                        $dados['data_agendamento'] = $dataAg;
                        $dados['horario_agendamento'] = $horaAg;
                        $dados['horario_confirmado'] = 1;
                        $dados['horario_confirmado_raw'] = $last['raw'];
                        $dados['confirmed_schedules'] = json_encode($confirmedFinalLimpo);
                        
                        // Mudar status para "Serviço Agendado"
                        $dados['status_id'] = $this->getStatusId('Serviço Agendado');
                        
                        // ✅ Enviar notificação WhatsApp quando horários são confirmados
                        try {
                            // Buscar dados atualizados da solicitação para garantir que temos o telefone correto
                            $solicitacaoAtual = $this->solicitacaoModel->find($id);
                            
                            // Verificar se tem telefone antes de enviar
                            $telefone = $solicitacaoAtual['locatario_telefone'] ?? null;
                            if (empty($telefone) && !empty($solicitacaoAtual['locatario_id'])) {
                                // Buscar telefone do locatário
                                $sqlLocatario = "SELECT telefone FROM locatarios WHERE id = ?";
                                $locatario = \App\Core\Database::fetch($sqlLocatario, [$solicitacaoAtual['locatario_id']]);
                                $telefone = $locatario['telefone'] ?? null;
                            }
                            
                            if (!empty($telefone)) {
                                // Formatar horário completo para exibição (usar o último horário confirmado)
                                $horarioCompleto = $last['raw'] ?? '';
                                
                                // Enviar WhatsApp para cada horário NOVO confirmado (não os que já existiam)
                                $horariosNovos = [];
                                foreach ($confirmedFinalLimpo as $confirmado) {
                                    $confirmadoRaw = $confirmado['raw'] ?? '';
                                    $jaExistia = false;
                                    
                                    // Verificar se este horário já estava confirmado antes
                                    foreach ($confirmedExistentes as $existente) {
                                        $existenteRaw = $existente['raw'] ?? '';
                                        if ($confirmadoRaw === $existenteRaw) {
                                            $jaExistia = true;
                                            break;
                                        }
                                    }
                                    
                                    if (!$jaExistia) {
                                        $horariosNovos[] = $confirmado;
                                    }
                                }
                                
                                // Se há horários novos confirmados, enviar WhatsApp
                                if (!empty($horariosNovos)) {
                                    // Formatar lista de horários para a mensagem
                                    $horariosLista = [];
                                    foreach ($horariosNovos as $horarioNovo) {
                                        $horariosLista[] = $horarioNovo['raw'] ?? '';
                                    }
                                    $horariosTexto = implode(', ', $horariosLista);
                                    
                                    $this->enviarNotificacaoWhatsApp($id, 'Horário Confirmado', [
                                        'data_agendamento' => date('d/m/Y', strtotime($dataAg)),
                                        'horario_agendamento' => $horaAg ? date('H:i', strtotime($horaAg)) : '',
                                        'horario_servico' => $horarioCompleto,
                                        'horario_confirmado_raw' => $horarioCompleto,
                                        'horarios_confirmados' => $horariosTexto
                                    ]);
                                    
                                    error_log("DEBUG atualizarDetalhes [ID:{$id}] - WhatsApp enviado para telefone: {$telefone}");
                                    error_log("DEBUG atualizarDetalhes [ID:{$id}] - Horários novos confirmados: " . json_encode($horariosNovos));
                                } else {
                                    error_log("DEBUG atualizarDetalhes [ID:{$id}] - Nenhum horário novo confirmado, WhatsApp NÃO enviado");
                                }
                            } else {
                                error_log("DEBUG atualizarDetalhes [ID:{$id}] - ⚠️ Telefone não encontrado, WhatsApp NÃO enviado");
                            }
                        } catch (\Exception $e) {
                            // Ignorar erro de WhatsApp, não bloquear a resposta
                            error_log('Erro ao enviar WhatsApp no atualizarDetalhes [ID:' . $id . ']: ' . $e->getMessage());
                            error_log('Stack trace: ' . $e->getTraceAsString());
                        }
                    }
                }
            }

            $resultado = $this->solicitacaoModel->update($id, $dados);
            
            if ($resultado) {
                $this->json([
                    'success' => true, 
                    'message' => 'Alterações salvas com sucesso',
                    'dados_salvos' => $dados
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'Falha ao atualizar no banco de dados'], 500);
            }
        } catch (\Exception $e) {
            error_log('Erro ao salvar: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            // ✅ Garantir que sempre retorne JSON válido
            $this->json([
                'success' => false,
                'error' => 'Erro ao salvar alterações: ' . $e->getMessage(),
                'message' => 'Ocorreu um erro ao processar sua solicitação. Tente novamente.'
            ], 500);
        } catch (\Throwable $e) {
            // ✅ Capturar qualquer erro PHP (fatal errors, etc.)
            error_log('Erro fatal ao salvar: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $this->json([
                'success' => false,
                'error' => 'Erro inesperado',
                'message' => 'Ocorreu um erro inesperado. Tente novamente.'
            ], 500);
        }
    }
    
    // ============================================================
    // SOLICITAÇÕES MANUAIS
    // ============================================================
    
    /**
     * Listar todas as solicitações manuais
     */
    public function solicitacoesManuais(): void
    {
        $this->requireAuth();
        
        $solicitacaoManualModel = new \App\Models\SolicitacaoManual();
        
        // Filtros
        $filtros = [
            'imobiliaria_id' => $this->input('imobiliaria_id'),
            'status_id' => $this->input('status_id'),
            'migrada' => $this->input('migrada') !== null ? (bool)$this->input('migrada') : null,
            'busca' => $this->input('busca')
        ];
        
        // Remover filtros vazios
        $filtros = array_filter($filtros, fn($value) => $value !== null && $value !== '');
        
        // Buscar solicitações
        $solicitacoes = $solicitacaoManualModel->getAll($filtros);
        
        // Buscar imobiliárias e status para os filtros
        $imobiliarias = $this->imobiliariaModel->getAll();
        $statusList = $this->statusModel->getAll();
        
        // Estatísticas
        $stats = [
            'total' => count($solicitacoes),
            'nao_migradas' => count(array_filter($solicitacoes, fn($s) => !$s['migrada'])),
            'migradas' => count(array_filter($solicitacoes, fn($s) => $s['migrada']))
        ];
        
        $this->view('solicitacoes.manuais', [
            'solicitacoes' => $solicitacoes,
            'imobiliarias' => $imobiliarias,
            'statusList' => $statusList,
            'stats' => $stats,
            'filtros' => $filtros
        ]);
    }
    
    /**
     * Ver detalhes de uma solicitação manual (JSON para modal)
     */
    public function verSolicitacaoManual(int $id): void
    {
        $this->requireAuth();
        
        try {
            $solicitacaoManualModel = new \App\Models\SolicitacaoManual();
            $solicitacao = $solicitacaoManualModel->getDetalhes($id);
            
            if (!$solicitacao) {
                $this->json(['success' => false, 'message' => 'Solicitação não encontrada'], 404);
                return;
            }
            
            // Decodificar JSONs
            if (!empty($solicitacao['horarios_preferenciais'])) {
                $solicitacao['horarios_preferenciais'] = is_string($solicitacao['horarios_preferenciais']) 
                    ? json_decode($solicitacao['horarios_preferenciais'], true) 
                    : $solicitacao['horarios_preferenciais'];
            }
            
            if (!empty($solicitacao['fotos'])) {
                $solicitacao['fotos'] = is_string($solicitacao['fotos']) 
                    ? json_decode($solicitacao['fotos'], true) 
                    : $solicitacao['fotos'];
            }
            
            // Buscar lista de status para o dropdown
            $statusList = $this->statusModel->getAll();
            
            $this->json([
                'success' => true,
                'solicitacao' => $solicitacao,
                'statusList' => $statusList
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao buscar solicitação manual: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Atualizar status de uma solicitação manual
     */
    public function atualizarStatusManual(int $id): void
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        try {
            $statusId = $this->input('status_id');
            
            if (empty($statusId)) {
                $this->json(['success' => false, 'message' => 'Status não informado'], 400);
                return;
            }
            
            $solicitacaoManualModel = new \App\Models\SolicitacaoManual();
            $resultado = $solicitacaoManualModel->update($id, [
                'status_id' => $statusId
            ]);
            
            if ($resultado) {
                $this->json([
                    'success' => true,
                    'message' => 'Status atualizado com sucesso'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar status'], 500);
            }
        } catch (\Exception $e) {
            error_log('Erro ao atualizar status: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Migrar solicitação manual para o sistema principal
     */
    public function migrarParaSistema(int $id): void
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        try {
            $usuarioId = $_SESSION['user_id'] ?? null;
            
            if (!$usuarioId) {
                $this->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
                return;
            }
            
            $solicitacaoManualModel = new \App\Models\SolicitacaoManual();
            $resultado = $solicitacaoManualModel->migrarParaSistema($id, $usuarioId);
            
            if ($resultado['success']) {
                $this->json([
                    'success' => true,
                    'message' => $resultado['message'],
                    'solicitacao_id' => $resultado['solicitacao_id']
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 400);
            }
        } catch (\Exception $e) {
            error_log('Erro ao migrar solicitação: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
