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
            
            // Enviar notificação WhatsApp
            $this->enviarNotificacaoWhatsApp($solicitacaoId, 'agendado');
            
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
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $horario = $this->input('horario');
        $user = $this->getUser();

        if (!$horario) {
            $this->json(['error' => 'Horário é obrigatório'], 400);
            return;
        }

        try {
            // Buscar status "Serviço Agendado"
            $sql = "SELECT id FROM status WHERE nome = 'Serviço Agendado' LIMIT 1";
            $statusAgendado = \App\Core\Database::fetch($sql);
            
            $this->solicitacaoModel->update($id, [
                'data_agendamento' => date('Y-m-d', strtotime($horario)),
                'horario_agendamento' => date('H:i:s', strtotime($horario)),
                'status_id' => $statusAgendado['id'] ?? 3
            ]);
            
            // Registrar histórico
            $this->solicitacaoModel->updateStatus($id, $statusAgendado['id'] ?? 3, $user['id'], 
                'Horário confirmado: ' . date('d/m/Y H:i', strtotime($horario)));
            
            // Enviar notificação WhatsApp
            $this->enviarNotificacaoWhatsApp($id, 'Horário Confirmado', [
                'data_agendamento' => date('d/m/Y', strtotime($horario)),
                'horario_agendamento' => date('H:i', strtotime($horario))
            ]);
            
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function desconfirmarHorario(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $user = $this->getUser();

        try {
            // Buscar status "Pendente"
            $sql = "SELECT id FROM status WHERE nome = 'Pendente' LIMIT 1";
            $statusPendente = \App\Core\Database::fetch($sql);
            
            // Limpar agendamento
            $this->solicitacaoModel->update($id, [
                'data_agendamento' => null,
                'horario_agendamento' => null,
                'status_id' => $statusPendente['id'] ?? null
            ]);
            
            // Registrar histórico
            if ($statusPendente) {
                $this->solicitacaoModel->updateStatus($id, $statusPendente['id'], $user['id'], 
                    'Horário desconfirmado pelo operador');
            }
            
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function solicitarNovosHorarios(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $observacao = $this->input('observacao');
        $user = $this->getUser();

        try {
            // Limpar horários atuais
            $this->solicitacaoModel->update($id, [
                'horarios_opcoes' => null
            ]);
            
            // Registrar no histórico
            $solicitacao = $this->solicitacaoModel->find($id);
            $this->solicitacaoModel->updateStatus($id, 
                $solicitacao['status_id'], 
                $user['id'], 
                'Horários indisponíveis. Motivo: ' . $observacao);
            
            // Enviar notificação WhatsApp solicitando novos horários
            $this->enviarNotificacaoWhatsApp($id, 'Horário Sugerido', [
                'data_agendamento' => 'A definir',
                'horario_agendamento' => 'Aguardando novas opções'
            ]);
            
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
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

            // Debug log
            error_log('Dados recebidos: ' . json_encode([
                'id' => $id,
                'precisa_reembolso' => $precisaReembolso,
                'valor_reembolso_raw' => $valorReembolso,
                'valor_reembolso_convertido' => isset($dados['valor_reembolso']) ? $dados['valor_reembolso'] : 'null'
            ]));

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
            $this->json(['error' => $e->getMessage()], 500);
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
