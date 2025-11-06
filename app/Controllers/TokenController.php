<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ScheduleConfirmationToken;
use App\Models\Solicitacao;

/**
 * Controller para processar tokens de confirmação/cancelamento de horários
 * 
 * Rotas públicas que não requerem autenticação
 */
class TokenController extends Controller
{
    private ScheduleConfirmationToken $tokenModel;
    private Solicitacao $solicitacaoModel;

    public function __construct()
    {
        // Não requer autenticação - rotas públicas
        $this->tokenModel = new ScheduleConfirmationToken();
        $this->solicitacaoModel = new Solicitacao();
    }

    /**
     * Exibe página de confirmação de horário
     * GET /confirmacao-horario?token=xxx
     */
    public function confirmacaoHorario(): void
    {
        $token = $this->input('token');

        if (!$token) {
            $this->view('token.error', [
                'title' => 'Token Inválido',
                'message' => 'Token não fornecido. Por favor, use o link completo enviado no WhatsApp.',
                'error_type' => 'missing_token'
            ]);
            return;
        }

        // Validar token
        $tokenData = $this->tokenModel->validateToken($token);

        if (!$tokenData) {
            $this->view('token.error', [
                'title' => 'Token Inválido ou Expirado',
                'message' => 'Este link de confirmação é inválido ou expirou. Por favor, entre em contato conosco.',
                'error_type' => 'invalid_token'
            ]);
            return;
        }

        // Buscar dados da solicitação
        $solicitacao = $this->solicitacaoModel->find($tokenData['solicitacao_id']);

        if (!$solicitacao) {
            $this->view('token.error', [
                'title' => 'Solicitação Não Encontrada',
                'message' => 'A solicitação associada a este token não foi encontrada.',
                'error_type' => 'solicitacao_not_found'
            ]);
            return;
        }

        // Se já foi processado (POST), processar confirmação
        if ($this->isPost()) {
            $this->processarConfirmacao($token, $tokenData, $solicitacao);
            return;
        }

        // Exibir formulário de confirmação
        $this->view('token.confirmacao', [
            'token' => $token,
            'tokenData' => $tokenData,
            'solicitacao' => $solicitacao,
            'title' => 'Confirmar Horário de Atendimento'
        ]);
    }

    /**
     * Processa confirmação de horário
     */
    private function processarConfirmacao(string $token, array $tokenData, array $solicitacao): void
    {
        try {
            // Marcar token como usado
            $this->tokenModel->markAsUsed($token, 'confirmed');

            // Atualizar status da solicitação para "Serviço Agendado" se ainda não estiver
            $statusModel = new \App\Models\Status();
            $statusAgendado = $statusModel->findByNome('Serviço Agendado');

            if ($statusAgendado && $solicitacao['status_id'] != $statusAgendado['id']) {
                $this->solicitacaoModel->update($solicitacao['id'], [
                    'status_id' => $statusAgendado['id'],
                    'horario_confirmado' => 1
                ]);
            } else {
                // Apenas marcar como confirmado
                $this->solicitacaoModel->update($solicitacao['id'], [
                    'horario_confirmado' => 1
                ]);
            }

            // Registrar no histórico diretamente
            $sql = "
                INSERT INTO historico_status (solicitacao_id, status_id, usuario_id, observacoes, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ";
            \App\Core\Database::query($sql, [
                $solicitacao['id'],
                $statusAgendado['id'] ?? $solicitacao['status_id'],
                null, // Usuário sistema (null = cliente)
                'Horário confirmado pelo cliente via link de confirmação'
            ]);

            // Exibir página de sucesso
            $this->view('token.sucesso', [
                'title' => 'Horário Confirmado',
                'message' => 'Seu horário foi confirmado com sucesso!',
                'solicitacao' => $solicitacao,
                'tokenData' => $tokenData,
                'action' => 'confirmacao'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao processar confirmação de horário: ' . $e->getMessage());
            $this->view('token.error', [
                'title' => 'Erro ao Confirmar',
                'message' => 'Ocorreu um erro ao processar sua confirmação. Por favor, tente novamente ou entre em contato conosco.',
                'error_type' => 'processing_error'
            ]);
        }
    }

    /**
     * Exibe página de cancelamento de horário
     * GET /cancelamento-horario?token=xxx
     */
    public function cancelamentoHorario(): void
    {
        $token = $this->input('token');

        if (!$token) {
            $this->view('token.error', [
                'title' => 'Token Inválido',
                'message' => 'Token não fornecido. Por favor, use o link completo enviado no WhatsApp.',
                'error_type' => 'missing_token'
            ]);
            return;
        }

        // Validar token
        $tokenData = $this->tokenModel->validateToken($token);

        if (!$tokenData) {
            $this->view('token.error', [
                'title' => 'Token Inválido ou Expirado',
                'message' => 'Este link de cancelamento é inválido ou expirou. Por favor, entre em contato conosco.',
                'error_type' => 'invalid_token'
            ]);
            return;
        }

        // Buscar dados da solicitação
        $solicitacao = $this->solicitacaoModel->find($tokenData['solicitacao_id']);

        if (!$solicitacao) {
            $this->view('token.error', [
                'title' => 'Solicitação Não Encontrada',
                'message' => 'A solicitação associada a este token não foi encontrada.',
                'error_type' => 'solicitacao_not_found'
            ]);
            return;
        }

        // Se já foi processado (POST), processar cancelamento
        if ($this->isPost()) {
            $this->processarCancelamento($token, $tokenData, $solicitacao);
            return;
        }

        // Exibir formulário de cancelamento
        $this->view('token.cancelamento', [
            'token' => $token,
            'tokenData' => $tokenData,
            'solicitacao' => $solicitacao,
            'title' => 'Cancelar Horário de Atendimento'
        ]);
    }

    /**
     * Processa cancelamento de horário
     */
    private function processarCancelamento(string $token, array $tokenData, array $solicitacao): void
    {
        try {
            $motivo = $this->input('motivo', 'Cancelado pelo cliente via link de cancelamento');

            // Marcar token como usado
            $this->tokenModel->markAsUsed($token, 'cancelled');

            // Atualizar status da solicitação para "Cancelado"
            $statusModel = new \App\Models\Status();
            $statusCancelado = $statusModel->findByNome('Cancelado');

            if ($statusCancelado) {
                $this->solicitacaoModel->update($solicitacao['id'], [
                    'status_id' => $statusCancelado['id'],
                    'observacoes' => $motivo
                ]);

                // Registrar no histórico diretamente
                $sql = "
                    INSERT INTO historico_status (solicitacao_id, status_id, usuario_id, observacoes, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ";
                \App\Core\Database::query($sql, [
                    $solicitacao['id'],
                    $statusCancelado['id'],
                    null, // Usuário sistema (null = cliente)
                    $motivo
                ]);
            }

            // Exibir página de sucesso
            $this->view('token.sucesso', [
                'title' => 'Horário Cancelado',
                'message' => 'Seu horário foi cancelado com sucesso. Entraremos em contato para reagendar.',
                'solicitacao' => $solicitacao,
                'tokenData' => $tokenData,
                'action' => 'cancelamento'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao processar cancelamento de horário: ' . $e->getMessage());
            $this->view('token.error', [
                'title' => 'Erro ao Cancelar',
                'message' => 'Ocorreu um erro ao processar seu cancelamento. Por favor, tente novamente ou entre em contato conosco.',
                'error_type' => 'processing_error'
            ]);
        }
    }

    /**
     * Exibe status do serviço
     * GET /status-servico?token=xxx
     */
    public function statusServico(): void
    {
        $token = $this->input('token');

        if (!$token) {
            $this->view('token.error', [
                'title' => 'Token Inválido',
                'message' => 'Token não fornecido. Por favor, use o link completo enviado no WhatsApp.',
                'error_type' => 'missing_token'
            ]);
            return;
        }

        // Validar token
        $tokenData = $this->tokenModel->validateToken($token);

        if (!$tokenData) {
            $this->view('token.error', [
                'title' => 'Token Inválido ou Expirado',
                'message' => 'Este link de status é inválido ou expirou. Por favor, entre em contato conosco.',
                'error_type' => 'invalid_token'
            ]);
            return;
        }

        // Buscar dados da solicitação com relacionamentos
        $sql = "
            SELECT 
                s.*,
                st.nome as status_nome,
                st.cor as status_cor,
                c.nome as categoria_nome,
                i.nome as imobiliaria_nome
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            WHERE s.id = ?
        ";
        
        $solicitacao = \App\Core\Database::fetch($sql, [$tokenData['solicitacao_id']]);

        if (!$solicitacao) {
            $this->view('token.error', [
                'title' => 'Solicitação Não Encontrada',
                'message' => 'A solicitação associada a este token não foi encontrada.',
                'error_type' => 'solicitacao_not_found'
            ]);
            return;
        }

        // Buscar histórico de status
        $historico = $this->solicitacaoModel->getHistoricoStatus($solicitacao['id']);

        // Exibir página de status
        $this->view('token.status', [
            'token' => $token,
            'tokenData' => $tokenData,
            'solicitacao' => $solicitacao,
            'historico' => $historico,
            'title' => 'Status do Serviço'
        ]);
    }
}

