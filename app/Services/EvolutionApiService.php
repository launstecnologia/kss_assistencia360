<?php

namespace App\Services;

/**
 * Exceção customizada para erros da Evolution API
 */
class EvolutionApiException extends \Exception
{
    private int $httpStatusCode;

    public function __construct(string $message = "", int $httpStatusCode = 0, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}

/**
 * Serviço para interagir com a Evolution API
 * Gerencia criação de instâncias, QR codes, status, etc.
 */
class EvolutionApiService
{
    private string $apiUrl;
    private string $apiKey;
    private ?string $token;

    /**
     * Escreve log em arquivo whatsapp_evolution_api.log
     * 
     * @param array $data Dados do log
     * @return void
     */
    private function writeLog(array $data): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        
        // Criar diretório se não existir
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/whatsapp_evolution_api.log';
        
        // Formatar linha de log
        $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
        $status = $data['status'] ?? 'INFO';
        $operation = $data['operation'] ?? 'N/A';
        $instanceName = $data['instance_name'] ?? 'N/A';
        
        // Montar linha de log estruturada
        $logLine = sprintf(
            "[%s] [%s] Operação:%s | Instância:%s",
            $timestamp,
            strtoupper($status),
            $operation,
            $instanceName
        );
        
        // Adicionar informações adicionais
        if (isset($data['http_code'])) {
            $logLine .= " | HTTP:" . $data['http_code'];
        }
        
        if (isset($data['tempo_resposta'])) {
            $logLine .= " | Tempo:" . $data['tempo_resposta'];
        }
        
        if (isset($data['erro'])) {
            $logLine .= " | ERRO:" . $data['erro'];
        }
        
        if (isset($data['api_url'])) {
            $logLine .= " | API:" . $data['api_url'];
        }
        
        $logLine .= PHP_EOL;
        
        // Adicionar detalhes completos em formato JSON
        $logLine .= "  DETALHES: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        $logLine .= str_repeat('-', 100) . PHP_EOL;
        
        // Escrever no arquivo
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

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
     * 
     * @param string $instanceName Nome da instância
     * @param array $options Opções adicionais (token, number, webhook, etc.)
     */
    public function criarInstancia(string $instanceName, array $options = []): array
    {
        $startTime = microtime(true);
        $url = "{$this->apiUrl}/instance/create";
        
        // Na Evolution API, o campo 'token' no payload deve receber a API Key
        // O token UUID gerado é usado apenas para identificação interna
        $tokenToUse = $options['token'] ?? $this->apiKey;
        
        $payload = [
            'instanceName' => $instanceName,
            'token' => $tokenToUse, // API Key vai no campo token do payload
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
        ];
        
        // Adicionar webhook se fornecido
        if (isset($options['webhook']) && !empty($options['webhook'])) {
            $payload['webhook'] = $options['webhook'];
        }
        
        // Se foi fornecido um número, adicionar ao payload
        if (isset($options['number']) && !empty($options['number'])) {
            $payload['number'] = $options['number'];
        }
        
        // Mesclar outras opções (exceto as que já tratamos)
        $otherOptions = array_diff_key($options, ['token' => '', 'webhook' => '', 'number' => '']);
        $payload = array_merge($payload, $otherOptions);

        // Log da tentativa de criação
        $this->writeLog([
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'INICIADO',
            'operation' => 'Criar Instância',
            'instance_name' => $instanceName,
            'api_url' => $this->apiUrl,
            'payload' => $payload
        ]);

        try {
            $response = $this->fazerRequisicao('POST', $url, $payload);
            $endTime = microtime(true);
            $tempoResposta = round(($endTime - $startTime) * 1000, 2) . 'ms';

            // Log de sucesso
            $this->writeLog([
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'SUCESSO',
                'operation' => 'Criar Instância',
                'instance_name' => $instanceName,
                'api_url' => $this->apiUrl,
                'http_code' => 200,
                'tempo_resposta' => $tempoResposta,
                'api_response' => $response,
                'payload_enviado' => $payload
            ]);

            return $response;
        } catch (EvolutionApiException $e) {
            $endTime = microtime(true);
            $tempoResposta = round(($endTime - $startTime) * 1000, 2) . 'ms';
            $httpCode = $e->getHttpStatusCode();

            // Log de erro
            $this->writeLog([
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'ERRO',
                'operation' => 'Criar Instância',
                'instance_name' => $instanceName,
                'api_url' => $this->apiUrl,
                'http_code' => $httpCode,
                'tempo_resposta' => $tempoResposta,
                'erro' => $e->getMessage(),
                'erro_tipo' => 'EvolutionApiException',
                'payload_enviado' => $payload
            ]);

            throw $e;
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $tempoResposta = round(($endTime - $startTime) * 1000, 2) . 'ms';

            // Log de erro genérico
            $this->writeLog([
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'ERRO',
                'operation' => 'Criar Instância',
                'instance_name' => $instanceName,
                'api_url' => $this->apiUrl,
                'tempo_resposta' => $tempoResposta,
                'erro' => $e->getMessage(),
                'erro_tipo' => get_class($e),
                'erro_arquivo' => $e->getFile(),
                'erro_linha' => $e->getLine(),
                'payload_enviado' => $payload
            ]);

            throw $e;
        }
    }

    /**
     * Obtém o QR code de uma instância
     * Se a instância não existir, tenta criá-la automaticamente
     * 
     * @param string $instanceName Nome da instância
     * @param bool $criarSeNaoExistir Se true, tenta criar a instância se não existir
     * @param string|null $number Número de telefone (opcional) para gerar QR code específico
     */
    public function obterQrcode(string $instanceName, bool $criarSeNaoExistir = true, ?string $number = null): array
    {
        // Primeiro, verificar se a instância existe
        $instanciaExiste = false;
        try {
            $infoUrl = "{$this->apiUrl}/instance/fetchInstance/{$instanceName}";
            $infoResponse = $this->fazerRequisicao('GET', $infoUrl);
            
            // Se a instância não existe, a API pode retornar erro ou objeto vazio
            if (!empty($infoResponse) && !isset($infoResponse['error'])) {
                $instanciaExiste = true;
            }
        } catch (\Exception $e) {
            // Verificar se é erro 404 ou "not found"
            $httpCode = 0;
            if ($e instanceof EvolutionApiException) {
                $httpCode = $e->getHttpStatusCode();
            } else {
                $httpCode = $e->getCode() ?? 0;
            }
            
            $errorMsg = strtolower($e->getMessage());
            
            if ($httpCode === 404 || 
                strpos($errorMsg, 'not found') !== false || 
                strpos($errorMsg, '404') !== false ||
                strpos($errorMsg, 'não encontrada') !== false ||
                strpos($errorMsg, 'não foi encontrada') !== false) {
                $instanciaExiste = false;
            } else {
                // Para outros erros, apenas logar e continuar
                error_log("Aviso ao verificar se instância existe: " . $e->getMessage());
                // Se não for 404, assumir que existe para continuar o fluxo
                $instanciaExiste = true;
            }
        }
        
        // Se a instância não existe e podemos criar, tentar criar
        if (!$instanciaExiste && $criarSeNaoExistir) {
            try {
                error_log("Instância '{$instanceName}' não encontrada. Tentando criar automaticamente...");
                
                // Preparar opções para criação (incluindo número se fornecido)
                $createOptions = [];
                if ($number !== null && !empty($number)) {
                    $createOptions['number'] = $number;
                }
                
                $createResponse = $this->criarInstancia($instanceName, $createOptions);
                
                // Verificar se a criação retornou QR code diretamente
                if (isset($createResponse['qrcode'])) {
                    if (is_array($createResponse['qrcode']) && isset($createResponse['qrcode']['base64'])) {
                        return [
                            'qrcode' => $createResponse['qrcode']['base64'],
                            'base64' => $createResponse['qrcode']['base64']
                        ];
                    }
                    if (is_string($createResponse['qrcode'])) {
                        if (strpos($createResponse['qrcode'], 'data:image') === 0) {
                            $base64 = explode(',', $createResponse['qrcode'])[1] ?? $createResponse['qrcode'];
                        } else {
                            $base64 = $createResponse['qrcode'];
                        }
                        return [
                            'qrcode' => $base64,
                            'base64' => $base64
                        ];
                    }
                }
                
                if (isset($createResponse['base64'])) {
                    return [
                        'qrcode' => $createResponse['base64'],
                        'base64' => $createResponse['base64']
                    ];
                }
                
                $instanciaExiste = true;
            } catch (EvolutionApiException $e) {
                error_log("Erro ao criar instância automaticamente: " . $e->getMessage());
                $httpCode = $e->getHttpStatusCode();
                
                // Mensagens mais específicas baseadas no código HTTP
                if ($httpCode === 403) {
                    throw new \Exception("A instância '{$instanceName}' não foi encontrada e não foi possível criá-la automaticamente. Erro de permissão (403 Forbidden). Verifique se a API Key está correta e tem permissão para criar instâncias.");
                } elseif ($httpCode === 401) {
                    throw new \Exception("A instância '{$instanceName}' não foi encontrada e não foi possível criá-la automaticamente. Erro de autenticação (401 Unauthorized). Verifique se a API Key está correta.");
                } else {
                    throw new \Exception("A instância '{$instanceName}' não foi encontrada na Evolution API e não foi possível criá-la automaticamente. Erro: " . $e->getMessage());
                }
            } catch (\Exception $e) {
                error_log("Erro ao criar instância automaticamente: " . $e->getMessage());
                throw new \Exception("A instância '{$instanceName}' não foi encontrada na Evolution API e não foi possível criá-la automaticamente. Erro: " . $e->getMessage());
            }
        } elseif (!$instanciaExiste) {
            throw new \Exception("A instância '{$instanceName}' não foi encontrada na Evolution API. Verifique se o nome da instância está correto e se a instância foi criada.");
        }
        
        // Verificar o status da conexão
        try {
            $statusUrl = "{$this->apiUrl}/instance/connectionState/{$instanceName}";
            $statusResponse = $this->fazerRequisicao('GET', $statusUrl);
            
            // Se já estiver conectado, não há QR code
            $state = strtoupper($statusResponse['state'] ?? $statusResponse['status'] ?? '');
            if ($state === 'OPEN' || $state === 'CONNECTED') {
                return [
                    'connected' => true,
                    'state' => $state,
                    'qrcode' => null
                ];
            }
        } catch (\Exception $e) {
            // Continuar tentando obter QR code mesmo se verificação de status falhar
            error_log("Aviso ao verificar status antes de obter QR code: " . $e->getMessage());
        }
        
        // Tentar conectar a instância (isso pode gerar um novo QR code)
        try {
            $connectUrl = "{$this->apiUrl}/instance/connect/{$instanceName}";
            
            // Se foi fornecido um número, adicionar como query parameter
            if ($number !== null && !empty($number)) {
                $connectUrl .= "?number=" . urlencode($number);
            }
            
            $connectResponse = $this->fazerRequisicao('GET', $connectUrl);
            
            // Log da resposta para debug
            $this->writeLog([
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'INFO',
                'operation' => 'Connect Response',
                'instance_name' => $instanceName,
                'api_url' => $connectUrl,
                'response_keys' => array_keys($connectResponse),
                'has_base64' => isset($connectResponse['base64']),
                'has_qrcode' => isset($connectResponse['qrcode'])
            ]);
            
            // Verificar diferentes formatos de resposta
            if (isset($connectResponse['qrcode'])) {
                // Formato: { "qrcode": { "base64": "..." } }
                if (is_array($connectResponse['qrcode']) && isset($connectResponse['qrcode']['base64'])) {
                    return [
                        'qrcode' => $connectResponse['qrcode']['base64'],
                        'base64' => $connectResponse['qrcode']['base64']
                    ];
                }
                // Formato: { "qrcode": "data:image/png;base64,..." }
                if (is_string($connectResponse['qrcode'])) {
                    // Extrair base64 se vier com prefixo data:image
                    if (strpos($connectResponse['qrcode'], 'data:image') === 0) {
                        $base64 = explode(',', $connectResponse['qrcode'])[1] ?? $connectResponse['qrcode'];
                    } else {
                        $base64 = $connectResponse['qrcode'];
                    }
                    return [
                        'qrcode' => $base64,
                        'base64' => $base64
                    ];
                }
            }
            
            // Verificar se retornou base64 diretamente (formato mais comum)
            if (isset($connectResponse['base64'])) {
                $base64Value = $connectResponse['base64'];
                // Se vier com prefixo data:image, extrair só o base64
                if (strpos($base64Value, 'data:image') === 0) {
                    $base64Value = explode(',', $base64Value)[1] ?? $base64Value;
                }
                return [
                    'qrcode' => $base64Value,
                    'base64' => $base64Value
                ];
            }
        } catch (\Exception $e) {
            error_log("Erro ao conectar instância para obter QR code: " . $e->getMessage());
        }
        
        // Se não retornou QR code na conexão, buscar diretamente
        try {
            $fetchUrl = "{$this->apiUrl}/instance/fetchQrCode/{$instanceName}";
            
            // Se foi fornecido um número, adicionar como query parameter
            if ($number !== null && !empty($number)) {
                $fetchUrl .= "?number=" . urlencode($number);
            }
            
            $fetchResponse = $this->fazerRequisicao('GET', $fetchUrl);
            
            // Verificar diferentes formatos de resposta
            if (isset($fetchResponse['qrcode'])) {
                if (is_array($fetchResponse['qrcode']) && isset($fetchResponse['qrcode']['base64'])) {
                    return [
                        'qrcode' => $fetchResponse['qrcode']['base64'],
                        'base64' => $fetchResponse['qrcode']['base64']
                    ];
                }
                if (is_string($fetchResponse['qrcode'])) {
                    if (strpos($fetchResponse['qrcode'], 'data:image') === 0) {
                        $base64 = explode(',', $fetchResponse['qrcode'])[1] ?? $fetchResponse['qrcode'];
                    } else {
                        $base64 = $fetchResponse['qrcode'];
                    }
                    return [
                        'qrcode' => $base64,
                        'base64' => $base64
                    ];
                }
            }
            
            if (isset($fetchResponse['base64'])) {
                $base64Value = $fetchResponse['base64'];
                // Se vier com prefixo data:image, extrair só o base64
                if (strpos($base64Value, 'data:image') === 0) {
                    $base64Value = explode(',', $base64Value)[1] ?? $base64Value;
                }
                return [
                    'qrcode' => $base64Value,
                    'base64' => $base64Value
                ];
            }
            
            // Se retornou a resposta completa mas sem qrcode/base64, retornar como está
            return $fetchResponse;
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar QR code diretamente: " . $e->getMessage());
            throw new \Exception("Não foi possível obter o QR code. Verifique se a instância existe na Evolution API. Erro: " . $e->getMessage());
        }
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
            throw new \Exception("Erro cURL: {$curlError}", 0);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = $responseData['message'] ?? $responseData['error'] ?? "HTTP {$httpCode}";
            
            // Para erros específicos, melhorar a mensagem
            if ($httpCode === 403) {
                $errorMsg = "Acesso negado (Forbidden). Verifique se a API Key está correta e tem permissão para esta operação.";
            } elseif ($httpCode === 401) {
                $errorMsg = "Não autorizado. Verifique se a API Key está correta.";
            } elseif ($httpCode === 404) {
                $errorMsg = "Recurso não encontrado.";
            }
            
            throw new EvolutionApiException($errorMsg, $httpCode);
        }
        
        return $responseData ?? [];
    }
}

