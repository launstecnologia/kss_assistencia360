<?php

namespace App\Services;

/**
 * Serviço para interagir com a Evolution API
 * Gerencia criação de instâncias, QR codes, status, etc.
 */
class EvolutionApiService
{
    private string $apiUrl;
    private string $apiKey;
    private ?string $token;

    public function __construct(?string $apiUrl = null, ?string $apiKey = null, ?string $token = null)
    {
        // Se não fornecidos, buscar do config
        if ($apiUrl === null || $apiKey === null) {
            $configFile = __DIR__ . '/../Config/config.php';
            $config = file_exists($configFile) ? require $configFile : [];
            $whatsappConfig = $config['whatsapp'] ?? [];
            
            $this->apiUrl = rtrim($apiUrl ?? $whatsappConfig['api_url'] ?? '', '/');
            $this->apiKey = $apiKey ?? $whatsappConfig['api_key'] ?? '';
            $this->token = $token ?? $whatsappConfig['token'] ?? null;
        } else {
            $this->apiUrl = rtrim($apiUrl, '/');
            $this->apiKey = $apiKey;
            $this->token = $token;
        }
    }

    /**
     * Cria uma nova instância na Evolution API
     */
    public function criarInstancia(string $instanceName, array $options = []): array
    {
        $url = "{$this->apiUrl}/instance/create";
        
        $payload = array_merge([
            'instanceName' => $instanceName,
            'token' => $options['token'] ?? $this->token,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
            'webhook' => $options['webhook'] ?? null,
        ], $options);

        return $this->fazerRequisicao('POST', $url, $payload);
    }

    /**
     * Obtém o QR code de uma instância
     */
    public function obterQrcode(string $instanceName): array
    {
        // Primeiro, tentar conectar a instância
        $url = "{$this->apiUrl}/instance/connect/{$instanceName}";
        $response = $this->fazerRequisicao('GET', $url);
        
        // Se retornou QR code, retornar
        if (isset($response['qrcode'])) {
            return $response;
        }
        
        // Se não, buscar QR code diretamente
        $url = "{$this->apiUrl}/instance/fetchQrCode/{$instanceName}";
        return $this->fazerRequisicao('GET', $url);
    }

    /**
     * Verifica o status de uma instância
     */
    public function verificarStatus(string $instanceName): array
    {
        // Buscar status de conexão diretamente
        $url = "{$this->apiUrl}/instance/connectionState/{$instanceName}";
        $response = $this->fazerRequisicao('GET', $url);
        
        // Se não retornou dados, buscar informações da instância
        if (empty($response)) {
            $url = "{$this->apiUrl}/instance/fetchInstance/{$instanceName}";
            $response = $this->fazerRequisicao('GET', $url);
        }
        
        return $response;
    }

    /**
     * Desconecta uma instância
     */
    public function desconectarInstancia(string $instanceName): array
    {
        $url = "{$this->apiUrl}/instance/logout/{$instanceName}";
        return $this->fazerRequisicao('DELETE', $url);
    }

    /**
     * Deleta uma instância
     */
    public function deletarInstancia(string $instanceName): array
    {
        $url = "{$this->apiUrl}/instance/delete/{$instanceName}";
        return $this->fazerRequisicao('DELETE', $url);
    }

    /**
     * Obtém informações de uma instância
     */
    public function obterInfoInstancia(string $instanceName): array
    {
        $url = "{$this->apiUrl}/instance/fetchInstance/{$instanceName}";
        return $this->fazerRequisicao('GET', $url);
    }

    /**
     * Faz uma requisição HTTP para a Evolution API
     */
    private function fazerRequisicao(string $method, string $url, array $payload = []): array
    {
        $ch = curl_init($url);
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $this->apiKey
        ];
        
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new \Exception("Erro cURL: {$curlError}");
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = $responseData['message'] ?? $responseData['error'] ?? "HTTP {$httpCode}";
            throw new \Exception($errorMsg);
        }
        
        return $responseData ?? [];
    }
}

