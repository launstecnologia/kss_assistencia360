<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\WhatsappInstance;
use App\Services\EvolutionApiService;

class WhatsappInstancesController extends Controller
{
    private WhatsappInstance $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->model = new WhatsappInstance();
    }

    /**
     * Lista todas as instâncias
     */
    public function index(): void
    {
        $instances = $this->model->findAll([], 'created_at DESC');
        
        $this->view('whatsapp.instances', [
            'pageTitle' => 'Instâncias WhatsApp',
            'currentPage' => 'whatsapp-instances',
            'user' => $_SESSION['user'] ?? null,
            'instances' => $instances
        ]);
    }

    /**
     * Exibe formulário para criar nova instância
     */
    public function create(): void
    {
        $config = require __DIR__ . '/../Config/config.php';
        $whatsappConfig = $config['whatsapp'] ?? [];
        
        $this->view('whatsapp.instances-create', [
            'pageTitle' => 'Nova Instância WhatsApp',
            'currentPage' => 'whatsapp-instances',
            'user' => $_SESSION['user'] ?? null,
            'apiUrl' => $whatsappConfig['api_url'] ?? '',
            'apiKey' => $whatsappConfig['api_key'] ?? '',
        ]);
    }

    /**
     * Cria uma nova instância
     */
    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect(url('admin/whatsapp-instances'));
            return;
        }

        $nome = trim($this->input('nome'));
        $instanceName = trim($this->input('instance_name'));
        $apiUrl = trim($this->input('api_url'));
        $apiKey = trim($this->input('api_key'));
        $token = trim($this->input('token', ''));

        $errors = [];
        if (empty($nome)) $errors[] = 'Nome é obrigatório';
        if (empty($instanceName)) $errors[] = 'Nome da instância é obrigatório';
        if (empty($apiUrl)) $errors[] = 'URL da API é obrigatória';
        if (empty($apiKey)) $errors[] = 'API Key é obrigatória';

        if (!empty($errors)) {
            $_SESSION['flash_message'] = implode('<br>', $errors);
            $_SESSION['flash_type'] = 'error';
            $this->redirect(url('admin/whatsapp-instances/create'));
            return;
        }

        try {
            // Criar instância na Evolution API
            $evolutionService = new EvolutionApiService($apiUrl, $apiKey, $token);
            $result = $evolutionService->criarInstancia($instanceName);

            // Salvar no banco
            $instanceId = $this->model->create([
                'nome' => $nome,
                'instance_name' => $instanceName,
                'api_url' => $apiUrl,
                'api_key' => $apiKey,
                'token' => $token ?: null,
                'status' => 'CONECTANDO',
                'is_ativo' => 1,
                'is_padrao' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Obter QR code
            $qrcodeData = $evolutionService->obterQrcode($instanceName);
            $qrcode = $qrcodeData['qrcode']['base64'] ?? $qrcodeData['base64'] ?? null;

            if ($qrcode) {
                $this->model->atualizarQrcode($instanceId, $qrcode);
            }

            $_SESSION['flash_message'] = 'Instância criada com sucesso! Escaneie o QR code para conectar.';
            $_SESSION['flash_type'] = 'success';
            $this->redirect(url('admin/whatsapp-instances/' . $instanceId . '/qrcode'));

        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Erro ao criar instância: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
            $this->redirect(url('admin/whatsapp-instances/create'));
        }
    }

    /**
     * Exibe QR code para conectar instância
     */
    public function qrcode(int $id): void
    {
        $instance = $this->model->find($id);
        
        if (!$instance) {
            $_SESSION['flash_message'] = 'Instância não encontrada';
            $_SESSION['flash_type'] = 'error';
            $this->redirect(url('admin/whatsapp-instances'));
            return;
        }

        // Atualizar QR code se necessário
        if (empty($instance['qrcode']) || $instance['status'] === 'DESCONECTADO') {
            try {
                $evolutionService = new EvolutionApiService(
                    $instance['api_url'],
                    $instance['api_key'],
                    $instance['token']
                );
                
                $qrcodeData = $evolutionService->obterQrcode($instance['instance_name']);
                $qrcode = $qrcodeData['qrcode']['base64'] ?? $qrcodeData['base64'] ?? null;
                
                if ($qrcode) {
                    $this->model->atualizarQrcode($id, $qrcode);
                    $this->model->atualizarStatus($id, 'CONECTANDO');
                    $instance['qrcode'] = $qrcode;
                }
            } catch (\Exception $e) {
                error_log("Erro ao obter QR code: " . $e->getMessage());
            }
        }

        $this->view('whatsapp.instances-qrcode', [
            'pageTitle' => 'Conectar Instância WhatsApp',
            'currentPage' => 'whatsapp-instances',
            'user' => $_SESSION['user'] ?? null,
            'instance' => $instance
        ]);
    }

    /**
     * Verifica status da instância (AJAX)
     */
    public function verificarStatus(int $id): void
    {
        $instance = $this->model->find($id);
        
        if (!$instance) {
            $this->json(['error' => 'Instância não encontrada'], 404);
            return;
        }

        try {
            $evolutionService = new EvolutionApiService(
                $instance['api_url'],
                $instance['api_key'],
                $instance['token']
            );
            
            $statusData = $evolutionService->verificarStatus($instance['instance_name']);
            
            // Atualizar status no banco
            $status = 'DESCONECTADO';
            $numeroWhatsapp = null;
            
            // Interpretar resposta da Evolution API
            $state = strtoupper($statusData['state'] ?? $statusData['status'] ?? '');
            
            if ($state === 'OPEN' || $state === 'CONNECTED') {
                $status = 'CONECTADO';
                $numeroWhatsapp = $statusData['instance']['owner'] ?? $statusData['owner'] ?? $statusData['phone'] ?? null;
            } elseif ($state === 'CLOSE' || $state === 'DISCONNECTED') {
                $status = 'DESCONECTADO';
            } elseif ($state === 'CONNECTING' || $state === 'QRCODE') {
                $status = 'CONECTANDO';
            }
            
            $this->model->atualizarStatus($id, $status, $numeroWhatsapp);
            
            // Buscar instância atualizada
            $instance = $this->model->find($id);
            
            $this->json([
                'success' => true,
                'status' => $instance['status'],
                'numero_whatsapp' => $instance['numero_whatsapp'],
                'qrcode' => $instance['qrcode']
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Define instância como padrão
     */
    public function setPadrao(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $instance = $this->model->find($id);
        
        if (!$instance) {
            $this->json(['error' => 'Instância não encontrada'], 404);
            return;
        }

        if ($instance['status'] !== 'CONECTADO') {
            $this->json(['error' => 'A instância deve estar conectada para ser definida como padrão'], 400);
            return;
        }

        $this->model->setPadrao($id);
        
        $this->json(['success' => true, 'message' => 'Instância definida como padrão']);
    }

    /**
     * Desconecta uma instância
     */
    public function desconectar(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $instance = $this->model->find($id);
        
        if (!$instance) {
            $this->json(['error' => 'Instância não encontrada'], 404);
            return;
        }

        try {
            $evolutionService = new EvolutionApiService(
                $instance['api_url'],
                $instance['api_key'],
                $instance['token']
            );
            
            $evolutionService->desconectarInstancia($instance['instance_name']);
            $this->model->atualizarStatus($id, 'DESCONECTADO');
            
            $this->json(['success' => true, 'message' => 'Instância desconectada']);
            
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Deleta uma instância
     */
    public function destroy(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $instance = $this->model->find($id);
        
        if (!$instance) {
            $this->json(['error' => 'Instância não encontrada'], 404);
            return;
        }

        try {
            // Deletar da Evolution API
            $evolutionService = new EvolutionApiService(
                $instance['api_url'],
                $instance['api_key'],
                $instance['token']
            );
            
            $evolutionService->deletarInstancia($instance['instance_name']);
            
            // Deletar do banco
            $this->model->delete($id);
            
            $_SESSION['flash_message'] = 'Instância deletada com sucesso';
            $_SESSION['flash_type'] = 'success';
            $this->redirect(url('admin/whatsapp-instances'));
            
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Erro ao deletar instância: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
            $this->redirect(url('admin/whatsapp-instances'));
        }
    }
}

