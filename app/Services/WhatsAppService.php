<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ScheduleConfirmationToken;

/**
 * Serviço de WhatsApp - Integração com Evolution API (Envio Direto/Síncrono)
 * 
 * Este serviço gerencia o envio de mensagens WhatsApp através da Evolution API.
 * Modo: ENVIO DIRETO (síncrono) - mensagens enviadas imediatamente
 * 
 * Suporta:
 * - Envio direto de mensagens
 * - Templates customizáveis do banco de dados
 * - Substituição de variáveis dinâmicas
 * - Tokens de confirmação com expiração de 48h
 * - Formatação automática de números WhatsApp
 */
class WhatsAppService
{
    private string $apiUrl;
    private string $instance;
    private string $apiKey;
    private bool $enabled;
    
    /**
     * Construtor - Carrega configurações do WhatsApp
     */
    public function __construct()
    {
        $config = config('whatsapp');
        
        $this->enabled = $config['enabled'] ?? false;
        $this->apiUrl = rtrim($config['api_url'] ?? '', '/');
        $this->instance = $config['instance'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        
        // Validar configurações
        if ($this->enabled && (empty($this->apiUrl) || empty($this->instance) || empty($this->apiKey))) {
            error_log('WhatsApp: Configurações incompletas. Verifique WHATSAPP_API_URL, WHATSAPP_INSTANCE e WHATSAPP_API_KEY.');
        }
    }
    
    /**
     * Envia mensagem WhatsApp de forma síncrona/direta
     * 
     * @param int $solicitacaoId ID da solicitação
     * @param string $messageType Tipo de mensagem (ex: 'Nova Solicitação', 'Horário Confirmado')
     * @param array $extraData Dados adicionais para substituição de variáveis
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function sendMessage(int $solicitacaoId, string $messageType, array $extraData = []): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'WhatsApp está desabilitado',
                'data' => null
            ];
        }
        
        try {
            // Buscar dados da solicitação
            $solicitacao = $this->getSolicitacaoDetalhes($solicitacaoId);
            
            if (!$solicitacao) {
                throw new \Exception('Solicitação não encontrada');
            }
            
            // Buscar template
            $template = $this->getTemplate($messageType);
            
            if (!$template) {
                throw new \Exception("Template não encontrado para o tipo: {$messageType}");
            }
            
            // Criar token se necessário (para mensagens de confirmação/sugestão de horário)
            $token = $this->createTokenIfNeeded($solicitacaoId, $messageType, $solicitacao, $extraData);
            
            // Preparar variáveis (incluindo links com token)
            $variables = $this->prepareVariables($solicitacao, $extraData, $token);
            
            // Substituir variáveis no template
            $message = $this->replaceVariables($template['corpo'], $variables);
            
            // Formatar número WhatsApp
            $whatsappNumber = $this->formatWhatsAppNumber($solicitacao['cliente_telefone']);
            
            // Enviar para Evolution API
            $result = $this->sendToEvolutionAPI($whatsappNumber, $message);
            
            return [
                'success' => true,
                'message' => 'Mensagem enviada com sucesso',
                'data' => $result
            ];
            
        } catch (\Exception $e) {
            error_log('WhatsApp Service Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Gera token de confirmação para agendamento
     * 
     * @param int $solicitacaoId ID da solicitação
     * @param string $protocol Protocolo da solicitação
     * @param string|null $scheduledDate Data sugerida
     * @param string|null $scheduledTime Horário sugerido
     * @return string Token gerado
     */
    public function generateConfirmationToken(
        int $solicitacaoId, 
        string $protocol, 
        ?string $scheduledDate = null, 
        ?string $scheduledTime = null
    ): string {
        try {
            $token = bin2hex(random_bytes(32)); // 64 caracteres
            $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));
            
            $sql = "
                INSERT INTO schedule_confirmation_tokens 
                (token, solicitacao_id, protocol, scheduled_date, scheduled_time, expires_at, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ";
            
            Database::execute($sql, [
                $token,
                $solicitacaoId,
                $protocol,
                $scheduledDate,
                $scheduledTime,
                $expiresAt
            ]);
            
            return $token;
            
        } catch (\Exception $e) {
            error_log('Erro ao gerar token de confirmação: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca detalhes completos da solicitação com relacionamentos
     * 
     * @param int $id ID da solicitação
     * @return array|null Dados da solicitação
     */
    private function getSolicitacaoDetalhes(int $id): ?array
    {
        $sql = "
            SELECT 
                s.*,
                l.nome as cliente_nome,
                l.cpf as cliente_cpf,
                l.telefone as cliente_telefone,
                l.email as cliente_email,
                l.endereco as cliente_endereco,
                l.numero as cliente_numero,
                l.complemento as cliente_complemento,
                l.bairro as cliente_bairro,
                l.cidade as cliente_cidade,
                l.estado as cliente_estado,
                l.cep as cliente_cep,
                i.nome as imobiliaria_nome,
                i.telefone as imobiliaria_telefone,
                st.nome as status_nome,
                c.nome as categoria_nome
            FROM solicitacoes s
            LEFT JOIN locatarios l ON s.locatario_id = l.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            LEFT JOIN status st ON s.status_id = st.id
            LEFT JOIN categorias c ON s.categoria_id = c.id
            WHERE s.id = ?
        ";
        
        return Database::fetch($sql, [$id]);
    }
    
    /**
     * Busca template ativo por tipo de mensagem
     * 
     * @param string $messageType Tipo de mensagem
     * @return array|null Template encontrado
     */
    private function getTemplate(string $messageType): ?array
    {
        $sql = "
            SELECT * FROM whatsapp_templates 
            WHERE tipo = ? 
            AND ativo = 1 
            ORDER BY padrao DESC, created_at DESC 
            LIMIT 1
        ";
        
        return Database::fetch($sql, [$messageType]);
    }
    
    /**
     * Cria token de confirmação se necessário para o tipo de mensagem
     * 
     * @param int $solicitacaoId ID da solicitação
     * @param string $messageType Tipo de mensagem
     * @param array $solicitacao Dados da solicitação
     * @param array $extraData Dados extras
     * @return string|null Token gerado ou null
     */
    private function createTokenIfNeeded(int $solicitacaoId, string $messageType, array $solicitacao, array $extraData): ?string
    {
        // Tipos de mensagem que precisam de token
        $tokenTypes = ['Horário Confirmado', 'Horário Sugerido', 'Confirmação de Serviço'];
        
        if (!in_array($messageType, $tokenTypes)) {
            return null;
        }
        
        $protocol = $solicitacao['numero_solicitacao'] ?? ('KS' . $solicitacao['id']);
        $scheduledDate = null;
        $scheduledTime = null;
        $actionType = 'confirm';
        
        // Determinar data e horário agendados
        if (isset($extraData['data_agendamento']) && !empty($extraData['data_agendamento'])) {
            // Para 'Horário Confirmado' e 'Horário Sugerido'
            $scheduledDate = $extraData['data_agendamento'];
            
            // Converter formato se necessário (d/m/Y -> Y-m-d)
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $scheduledDate)) {
                $scheduledDate = \DateTime::createFromFormat('d/m/Y', $scheduledDate)->format('Y-m-d');
            }
        }
        
        if (isset($extraData['horario_agendamento']) && !empty($extraData['horario_agendamento'])) {
            $scheduledTime = $extraData['horario_agendamento'];
        }
        
        // Determinar tipo de ação baseado no tipo de mensagem
        if ($messageType === 'Horário Sugerido') {
            $actionType = 'reschedule';
        } elseif ($messageType === 'Confirmação de Serviço') {
            $actionType = 'service_status';
        }
        
        // Criar token
        $tokenModel = new ScheduleConfirmationToken();
        $token = $tokenModel->createToken($solicitacaoId, $protocol, $scheduledDate, $scheduledTime, $actionType);
        
        return $token;
    }
    
    /**
     * Prepara todas as variáveis disponíveis para substituição
     * 
     * @param array $solicitacao Dados da solicitação
     * @param array $extraData Dados adicionais
     * @param string|null $token Token de confirmação (se aplicável)
     * @return array Mapa de variáveis
     */
    private function prepareVariables(array $solicitacao, array $extraData = [], ?string $token = null): array
    {
        $baseUrl = rtrim(config('app.url'), '/');
        
        $variables = [
            // Cliente
            'cliente_nome' => $solicitacao['cliente_nome'] ?? 'Cliente',
            'cliente_cpf' => $solicitacao['cliente_cpf'] ?? '',
            'cliente_telefone' => $solicitacao['cliente_telefone'] ?? '',
            'cliente_email' => $solicitacao['cliente_email'] ?? '',
            
            // Solicitação
            'protocol' => $solicitacao['numero_solicitacao'] ?? ('KS' . $solicitacao['id']),
            'contrato_numero' => $solicitacao['contrato_numero'] ?? '',
            'protocolo_seguradora' => $solicitacao['protocolo_seguradora'] ?? '',
            'descricao_problema' => $solicitacao['descricao'] ?? '',
            'servico_tipo' => $solicitacao['categoria_nome'] ?? 'Serviço',
            
            // Status
            'status_atual' => $extraData['status_atual'] ?? $solicitacao['status_nome'] ?? '',
            
            // Endereço
            'endereco_completo' => $this->formatEndereco($solicitacao),
            'endereco_rua' => $solicitacao['cliente_endereco'] ?? '',
            'endereco_numero' => $solicitacao['cliente_numero'] ?? '',
            'endereco_bairro' => $solicitacao['cliente_bairro'] ?? '',
            'endereco_cidade' => $solicitacao['cliente_cidade'] ?? '',
            'endereco_estado' => $solicitacao['cliente_estado'] ?? '',
            'endereco_cep' => $solicitacao['cliente_cep'] ?? '',
            
            // Imobiliária
            'imobiliaria_nome' => $solicitacao['imobiliaria_nome'] ?? '',
            
            // Agendamento
            'data_agendamento' => $extraData['data_agendamento'] ?? 
                ($solicitacao['data_agendamento'] ? date('d/m/Y', strtotime($solicitacao['data_agendamento'])) : ''),
            'horario_agendamento' => $extraData['horario_agendamento'] ?? '',
            'horario_servico' => $extraData['horario_servico'] ?? '',
            
            // Prestador (se disponível)
            'prestador_nome' => $extraData['prestador_nome'] ?? '',
            'prestador_telefone' => $extraData['prestador_telefone'] ?? '',
            
            // Links
            'link_rastreamento' => $baseUrl . '/locatario/solicitacao/' . $solicitacao['id'],
            'link_confirmacao' => $token ? $baseUrl . '/confirmacao-horario?token=' . $token : '',
            'link_cancelamento' => $token ? $baseUrl . '/cancelamento-horario?token=' . $token : '',
            'link_status' => $token ? $baseUrl . '/status-servico?token=' . $token : $baseUrl . '/locatario/solicitacao/' . $solicitacao['id'],
        ];
        
        // Mesclar com dados extras (sobrescreve se existir)
        $variables = array_merge($variables, $extraData);
        
        return $variables;
    }
    
    /**
     * Substitui todas as variáveis {{variavel}} no template
     * 
     * @param string $template Texto do template
     * @param array $variables Mapa de variáveis
     * @return string Texto com variáveis substituídas
     */
    private function replaceVariables(string $template, array $variables): string
    {
        $text = $template;
        
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $text = str_replace($placeholder, $value, $text);
        }
        
        // Remover variáveis não preenchidas
        $text = preg_replace('/\{\{[^}]+\}\}/', '', $text);
        
        return $text;
    }
    
    /**
     * Formata endereço completo
     * 
     * @param array $solicitacao Dados da solicitação
     * @return string Endereço formatado
     */
    private function formatEndereco(array $solicitacao): string
    {
        $parts = [];
        
        if (!empty($solicitacao['cliente_endereco'])) {
            $parts[] = $solicitacao['cliente_endereco'];
        }
        
        if (!empty($solicitacao['cliente_numero'])) {
            $parts[] = 'nº ' . $solicitacao['cliente_numero'];
        }
        
        if (!empty($solicitacao['cliente_complemento'])) {
            $parts[] = $solicitacao['cliente_complemento'];
        }
        
        if (!empty($solicitacao['cliente_bairro'])) {
            $parts[] = $solicitacao['cliente_bairro'];
        }
        
        $cidadeEstado = [];
        if (!empty($solicitacao['cliente_cidade'])) {
            $cidadeEstado[] = $solicitacao['cliente_cidade'];
        }
        if (!empty($solicitacao['cliente_estado'])) {
            $cidadeEstado[] = $solicitacao['cliente_estado'];
        }
        if (!empty($cidadeEstado)) {
            $parts[] = implode('/', $cidadeEstado);
        }
        
        if (!empty($solicitacao['cliente_cep'])) {
            $parts[] = 'CEP: ' . $solicitacao['cliente_cep'];
        }
        
        return implode(', ', $parts);
    }
    
    /**
     * Formata número de telefone para formato WhatsApp
     * 
     * @param string $phone Número de telefone
     * @return string Número formatado (ex: 5511999998888@c.us)
     */
    private function formatWhatsAppNumber(string $phone): string
    {
        // Remove tudo que não é número
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Adiciona DDI 55 se não tiver
        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }
        
        // Formato Evolution API: 5511999998888@c.us
        return $phone . '@c.us';
    }
    
    /**
     * Envia mensagem para Evolution API
     * 
     * @param string $number Número formatado
     * @param string $message Texto da mensagem
     * @return array Resposta da API
     * @throws \Exception Se falhar ao enviar
     */
    private function sendToEvolutionAPI(string $number, string $message): array
    {
        $url = "{$this->apiUrl}/message/sendText/{$this->instance}";
        
        $payload = [
            'number' => $number,
            'text' => $message
        ];
        
        $ch = curl_init($url);
        
        // Headers de autenticação (Evolution API usa 2 níveis)
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $this->apiKey  // API Key global
        ];
        
        // Adicionar token da instância se disponível
        $token = config('whatsapp.token');
        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('Erro cURL: ' . $error);
        }
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new \Exception("Evolution API retornou código {$httpCode}: {$response}");
        }
        
        return json_decode($response, true) ?? [];
    }
}

