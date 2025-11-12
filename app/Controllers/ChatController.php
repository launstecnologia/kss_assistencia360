<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SolicitacaoMensagem;
use App\Models\Solicitacao;
use App\Models\WhatsappInstance;
use App\Services\EvolutionApiService;

class ChatController extends Controller
{
    private SolicitacaoMensagem $mensagemModel;
    private Solicitacao $solicitacaoModel;
    private WhatsappInstance $whatsappInstanceModel;

    public function __construct()
    {
        $this->mensagemModel = new SolicitacaoMensagem();
        $this->solicitacaoModel = new Solicitacao();
        $this->whatsappInstanceModel = new WhatsappInstance();
    }

    /**
     * Busca mensagens de uma solicitação
     * GET /admin/chat/{solicitacao_id}/mensagens
     */
    public function getMensagens(int $solicitacaoId): void
    {
        $this->requireAdmin();

        $solicitacao = $this->solicitacaoModel->find($solicitacaoId);
        if (!$solicitacao) {
            $this->json([
                'success' => false,
                'message' => 'Solicitação não encontrada'
            ], 404);
            return;
        }

        $mensagens = $this->mensagemModel->getBySolicitacao($solicitacaoId);
        
        // Marcar mensagens recebidas como lidas
        $this->mensagemModel->marcarComoLidas($solicitacaoId);

        $this->json([
            'success' => true,
            'mensagens' => $mensagens,
            'solicitacao' => [
                'id' => $solicitacao['id'],
                'protocol' => $solicitacao['protocol'] ?? '',
                'cliente_nome' => $solicitacao['cliente_nome'] ?? '',
                'cliente_telefone' => $solicitacao['cliente_telefone'] ?? '',
                'cliente_whatsapp' => $solicitacao['cliente_whatsapp'] ?? $solicitacao['cliente_telefone'] ?? ''
            ]
        ]);
    }

    /**
     * Envia mensagem via WhatsApp
     * POST /admin/chat/{solicitacao_id}/enviar
     */
    public function enviarMensagem(int $solicitacaoId): void
    {
        $this->requireAdmin();

        $solicitacao = $this->solicitacaoModel->find($solicitacaoId);
        if (!$solicitacao) {
            $this->json([
                'success' => false,
                'message' => 'Solicitação não encontrada'
            ], 404);
            return;
        }

        $mensagem = $_POST['mensagem'] ?? '';
        $instanceId = $_POST['whatsapp_instance_id'] ?? null;

        if (empty($mensagem)) {
            $this->json([
                'success' => false,
                'message' => 'Mensagem não pode estar vazia'
            ], 400);
            return;
        }

        // Buscar instância WhatsApp
        $whatsappInstance = null;
        if ($instanceId) {
            $whatsappInstance = $this->whatsappInstanceModel->find($instanceId);
        } else {
            // Usar instância padrão
            $whatsappInstance = $this->whatsappInstanceModel->getPadrao();
        }

        if (!$whatsappInstance || $whatsappInstance['status'] !== 'CONECTADO') {
            $this->json([
                'success' => false,
                'message' => 'Instância WhatsApp não disponível ou desconectada'
            ], 400);
            return;
        }

        // Obter número do destinatário (locatário)
        // Número padrão para testes: 16997360690
        $numeroDestinatario = $solicitacao['locatario_telefone'] ?? 
                             $solicitacao['cliente_telefone'] ?? 
                             $solicitacao['cliente_whatsapp'] ?? 
                             '16997360690'; // Número padrão registrado
        
        // Se ainda estiver vazio, usar o número padrão
        if (empty($numeroDestinatario)) {
            $numeroDestinatario = '16997360690';
        }

        // Formatar número para Evolution API
        $numeroFormatado = $this->formatWhatsAppNumber($numeroDestinatario);

        // Criar registro de mensagem (status: ENVIANDO)
        $mensagemData = [
            'solicitacao_id' => $solicitacaoId,
            'whatsapp_instance_id' => $whatsappInstance['id'],
            'instance_name' => $whatsappInstance['instance_name'],
            'numero_remetente' => $whatsappInstance['numero_whatsapp'] ?? 'ADMIN',
            'numero_destinatario' => $numeroDestinatario,
            'mensagem' => $mensagem,
            'tipo' => 'ENVIADA',
            'status' => 'ENVIANDO',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $mensagemId = $this->mensagemModel->create($mensagemData);

        // Enviar via Evolution API
        try {
            $evolutionService = new EvolutionApiService(
                $whatsappInstance['api_url'],
                $whatsappInstance['api_key'],
                $whatsappInstance['token']
            );

            $response = $evolutionService->sendMessage(
                $whatsappInstance['instance_name'],
                $numeroFormatado,
                $mensagem
            );

            // Atualizar mensagem com sucesso
            $updateData = [
                'status' => 'ENVIADA',
                'message_id' => $response['key']['id'] ?? null,
                'metadata' => json_encode($response),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->mensagemModel->update($mensagemId, $updateData);

            $this->json([
                'success' => true,
                'message' => 'Mensagem enviada com sucesso',
                'data' => [
                    'id' => $mensagemId,
                    'message_id' => $response['key']['id'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            // Atualizar mensagem com erro
            $this->mensagemModel->update($mensagemId, [
                'status' => 'ERRO',
                'erro' => $e->getMessage(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook para receber mensagens da Evolution API
     * POST /webhook/whatsapp
     */
    public function webhook(): void
    {
        // Log do webhook recebido
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'headers' => getallheaders(),
            'body' => file_get_contents('php://input')
        ];
        error_log('WEBHOOK RECEBIDO: ' . json_encode($logData, JSON_PRETTY_PRINT));

        $payload = json_decode(file_get_contents('php://input'), true);

        if (!$payload) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payload']);
            return;
        }

        // Evolution API envia eventos diferentes
        // Verificar se é evento de mensagem recebida
        if (isset($payload['event']) && $payload['event'] === 'messages.upsert') {
            $this->processarMensagemRecebida($payload);
        } elseif (isset($payload['event']) && $payload['event'] === 'messages.update') {
            $this->processarAtualizacaoMensagem($payload);
        }

        // Sempre retornar 200 para Evolution API
        http_response_code(200);
        echo json_encode(['success' => true]);
    }

    /**
     * Processa mensagem recebida do webhook
     */
    private function processarMensagemRecebida(array $payload): void
    {
        try {
            $messages = $payload['data']['messages'] ?? [];
            
            foreach ($messages as $message) {
                // Ignorar mensagens enviadas por nós
                if (isset($message['key']['fromMe']) && $message['key']['fromMe'] === true) {
                    continue;
                }

                $numeroRemetente = $message['key']['remoteJid'] ?? '';
                $mensagemTexto = $message['message']['conversation'] ?? 
                                $message['message']['extendedTextMessage']['text'] ?? '';

                if (empty($mensagemTexto) || empty($numeroRemetente)) {
                    continue;
                }

                // Remover @c.us do número
                $numeroLimpo = str_replace('@c.us', '', $numeroRemetente);
                $numeroFormatado = $this->formatarNumeroBrasil($numeroLimpo);

                // Buscar solicitação pelo número do cliente
                $solicitacao = $this->solicitacaoModel->findByWhatsApp($numeroFormatado);

                if (!$solicitacao) {
                    error_log("Solicitação não encontrada para número: $numeroFormatado");
                    continue;
                }

                // Buscar instância pelo instance_name do webhook
                $instanceName = $payload['instance'] ?? null;
                $whatsappInstance = null;
                if ($instanceName) {
                    $whatsappInstance = $this->whatsappInstanceModel->findByInstanceName($instanceName);
                }

                // Criar registro de mensagem recebida
                $mensagemData = [
                    'solicitacao_id' => $solicitacao['id'],
                    'whatsapp_instance_id' => $whatsappInstance['id'] ?? null,
                    'instance_name' => $instanceName,
                    'numero_remetente' => $numeroFormatado,
                    'numero_destinatario' => $whatsappInstance['numero_whatsapp'] ?? 'ADMIN',
                    'mensagem' => $mensagemTexto,
                    'tipo' => 'RECEBIDA',
                    'status' => 'ENTREGUE',
                    'message_id' => $message['key']['id'] ?? null,
                    'metadata' => json_encode($message),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $this->mensagemModel->create($mensagemData);
            }
        } catch (\Exception $e) {
            error_log("Erro ao processar mensagem recebida: " . $e->getMessage());
        }
    }

    /**
     * Processa atualização de status de mensagem (entregue, lida, etc)
     */
    private function processarAtualizacaoMensagem(array $payload): void
    {
        try {
            $updates = $payload['data']['update'] ?? [];
            
            foreach ($updates as $update) {
                $messageId = $update['key']['id'] ?? null;
                $status = $update['update']['status'] ?? null;

                if (!$messageId || !$status) {
                    continue;
                }

                // Buscar mensagem pelo message_id
                $sql = "SELECT * FROM solicitacao_mensagens WHERE message_id = ? LIMIT 1";
                $mensagem = \App\Core\Database::fetch($sql, [$messageId]);

                if ($mensagem) {
                    $novoStatus = $mensagem['status'];
                    if ($status === 'DELIVERY_ACK') {
                        $novoStatus = 'ENTREGUE';
                    } elseif ($status === 'READ') {
                        $novoStatus = 'LIDA';
                    }

                    if ($novoStatus !== $mensagem['status']) {
                        $this->mensagemModel->update($mensagem['id'], [
                            'status' => $novoStatus,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Erro ao processar atualização de mensagem: " . $e->getMessage());
        }
    }

    /**
     * Formata número para formato Evolution API
     */
    private function formatWhatsAppNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }
        return $phone . '@c.us';
    }

    /**
     * Formata número do Brasil para formato legível
     */
    private function formatarNumeroBrasil(string $numero): string
    {
        $numero = preg_replace('/[^0-9]/', '', $numero);
        // Remover DDI 55 se presente
        if (substr($numero, 0, 2) === '55') {
            $numero = substr($numero, 2);
        }
        return $numero;
    }

    /**
     * Busca instâncias WhatsApp disponíveis
     * GET /admin/chat/instancias
     */
    public function getInstancias(): void
    {
        $this->requireAdmin();

        $instancias = $this->whatsappInstanceModel->getAtivas();
        
        // Debug: log para verificar se está retornando instâncias
        error_log('ChatController::getInstancias - Total de instâncias encontradas: ' . count($instancias));
        if (!empty($instancias)) {
            error_log('ChatController::getInstancias - Primeira instância: ' . json_encode($instancias[0]));
        }

        $this->json([
            'success' => true,
            'instancias' => $instancias
        ]);
    }
}

