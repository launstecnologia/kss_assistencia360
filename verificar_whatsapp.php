<?php
/**
 * Script de Diagnóstico - Sistema de Mensagens WhatsApp
 * 
 * Verifica se o sistema está configurado corretamente e funcionando
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações
$config = require __DIR__ . '/app/Config/config.php';

echo "🔍 DIAGNÓSTICO DO SISTEMA WHATSAPP\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Verificar configurações do .env
echo "1️⃣ CONFIGURAÇÕES DO WHATSAPP\n";
echo str_repeat("-", 60) . "\n";

$whatsappConfig = $config['whatsapp'] ?? [];
$whatsappEnabled = $whatsappConfig['enabled'] ?? false;
$apiUrl = $whatsappConfig['api_url'] ?? '';
$instance = $whatsappConfig['instance'] ?? '';
$apiKey = $whatsappConfig['api_key'] ?? '';
$token = $whatsappConfig['token'] ?? '';

echo "WhatsApp Habilitado: " . ($whatsappEnabled ? "✅ SIM" : "❌ NÃO") . "\n";
echo "API URL: " . ($apiUrl ? "✅ {$apiUrl}" : "❌ VAZIA") . "\n";
echo "Instância: " . ($instance ? "✅ {$instance}" : "❌ VAZIA") . "\n";
echo "API Key: " . ($apiKey ? "✅ CONFIGURADA" : "❌ VAZIA") . "\n";
echo "Token: " . ($token ? "✅ CONFIGURADO" : "⚠️  OPCIONAL (não obrigatório)") . "\n";

if (!$whatsappEnabled || empty($apiUrl) || empty($instance) || empty($apiKey)) {
    echo "\n⚠️  ATENÇÃO: Configurações incompletas!\n";
    echo "   O WhatsApp não funcionará até corrigir essas configurações.\n";
}

echo "\n";

// 2. Verificar conexão com banco de dados
echo "2️⃣ CONEXÃO COM BANCO DE DADOS\n";
echo str_repeat("-", 60) . "\n";

try {
    \App\Core\Database::setConfig($config['database']);
    $pdo = \App\Core\Database::getInstance();
    echo "✅ Conexão com banco de dados OK\n";
    
    // Verificar se a tabela whatsapp_templates existe
    $sql = "SHOW TABLES LIKE 'whatsapp_templates'";
    $result = $pdo->query($sql);
    
    if ($result->rowCount() > 0) {
        echo "✅ Tabela 'whatsapp_templates' existe\n";
        
        // Contar templates
        $sql = "SELECT COUNT(*) as total FROM whatsapp_templates";
        $count = \App\Core\Database::fetch($sql);
        echo "   Total de templates: " . ($count['total'] ?? 0) . "\n";
        
        // Listar templates ativos
        $sql = "SELECT tipo, nome, ativo, padrao FROM whatsapp_templates WHERE ativo = 1 ORDER BY tipo";
        $templates = \App\Core\Database::fetchAll($sql);
        
        if (count($templates) > 0) {
            echo "\n   📋 Templates Ativos:\n";
            foreach ($templates as $template) {
                $padrao = $template['padrao'] ? "⭐ Padrão" : "";
                echo "   - {$template['tipo']}: {$template['nome']} {$padrao}\n";
            }
        } else {
            echo "   ⚠️  Nenhum template ativo encontrado!\n";
        }
        
        // Verificar templates por tipo
        $tiposNecessarios = ['Nova Solicitação', 'Horário Confirmado', 'Horário Sugerido', 'Confirmação de Serviço', 'Atualização de Status'];
        echo "\n   📊 Verificação de Templates Necessários:\n";
        
        foreach ($tiposNecessarios as $tipo) {
            $sql = "SELECT COUNT(*) as total FROM whatsapp_templates WHERE tipo = ? AND ativo = 1";
            $result = \App\Core\Database::fetch($sql, [$tipo]);
            
            if (($result['total'] ?? 0) > 0) {
                echo "   ✅ {$tipo}: Template encontrado\n";
            } else {
                echo "   ❌ {$tipo}: Template NÃO encontrado ou inativo\n";
            }
        }
        
        // Templates faltando
        $sql = "SELECT DISTINCT tipo FROM whatsapp_templates WHERE ativo = 1";
        $tiposExistentes = \App\Core\Database::fetchAll($sql);
        $tiposExistentesArray = array_column($tiposExistentes, 'tipo');
        
        $tiposFaltando = array_diff($tiposNecessarios, $tiposExistentesArray);
        if (count($tiposFaltando) > 0) {
            echo "\n   ⚠️  Templates Faltando:\n";
            foreach ($tiposFaltando as $tipo) {
                echo "   - {$tipo}\n";
            }
        }
        
    } else {
        echo "❌ Tabela 'whatsapp_templates' NÃO existe!\n";
        echo "   Execute o script: database_whatsapp_infrastructure.sql\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro ao conectar com banco de dados: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Verificar se a instância está conectada
echo "3️⃣ STATUS DA INSTÂNCIA WHATSAPP\n";
echo str_repeat("-", 60) . "\n";

if ($whatsappEnabled && !empty($apiUrl) && !empty($instance) && !empty($apiKey)) {
    try {
        $statusUrl = rtrim($apiUrl, '/') . "/instance/fetchInstances";
        
        $ch = curl_init($statusUrl);
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ];
        
        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $instances = json_decode($response, true);
            $foundInstance = false;
            
            if (is_array($instances)) {
                foreach ($instances as $inst) {
                    if (isset($inst['instance']['instanceName']) && $inst['instance']['instanceName'] === $instance) {
                        $foundInstance = true;
                        $state = $inst['instance']['state'] ?? 'unknown';
                        
                        if ($state === 'open') {
                            echo "✅ Instância '{$instance}' encontrada e CONECTADA (status: {$state})\n";
                        } else {
                            echo "⚠️  Instância '{$instance}' encontrada mas NÃO CONECTADA (status: {$state})\n";
                            echo "   Acesse para conectar: {$apiUrl}/instance/login/{$instance}\n";
                        }
                        break;
                    }
                }
                
                if (!$foundInstance) {
                    echo "❌ Instância '{$instance}' NÃO encontrada na Evolution API\n";
                }
            }
        } else {
            echo "⚠️  Não foi possível verificar status da instância (HTTP {$httpCode})\n";
            echo "   Resposta: " . substr($response, 0, 200) . "\n";
        }
        
    } catch (\Exception $e) {
        echo "⚠️  Erro ao verificar instância: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️  Não é possível verificar: configurações incompletas\n";
}

echo "\n";

// 4. Testar WhatsAppService
echo "4️⃣ TESTE DO WHATSAPPSERVICE\n";
echo str_repeat("-", 60) . "\n";

try {
    $whatsappService = new \App\Services\WhatsAppService();
    
    // Verificar se o serviço está habilitado
    $reflection = new ReflectionClass($whatsappService);
    $enabledProp = $reflection->getProperty('enabled');
    $enabledProp->setAccessible(true);
    $serviceEnabled = $enabledProp->getValue($whatsappService);
    
    if ($serviceEnabled) {
        echo "✅ WhatsAppService está habilitado\n";
    } else {
        echo "❌ WhatsAppService está desabilitado\n";
        echo "   Verifique as configurações no .env\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro ao criar WhatsAppService: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. Resumo final
echo "📊 RESUMO DO DIAGNÓSTICO\n";
echo str_repeat("=", 60) . "\n";

$problemas = [];

if (!$whatsappEnabled) {
    $problemas[] = "WhatsApp está desabilitado no .env";
}

if (empty($apiUrl)) {
    $problemas[] = "WHATSAPP_API_URL não configurado";
}

if (empty($instance)) {
    $problemas[] = "WHATSAPP_INSTANCE não configurado";
}

if (empty($apiKey)) {
    $problemas[] = "WHATSAPP_API_KEY não configurado";
}

try {
    $sql = "SELECT COUNT(*) as total FROM whatsapp_templates WHERE ativo = 1";
    $count = \App\Core\Database::fetch($sql);
    if (($count['total'] ?? 0) === 0) {
        $problemas[] = "Nenhum template ativo no banco de dados";
    }
} catch (\Exception $e) {
    $problemas[] = "Não foi possível verificar templates no banco";
}

if (count($problemas) === 0) {
    echo "✅ Sistema configurado corretamente!\n";
    echo "   O WhatsApp deve estar funcionando.\n";
} else {
    echo "❌ Problemas encontrados:\n";
    foreach ($problemas as $problema) {
        echo "   - {$problema}\n";
    }
    echo "\n⚠️  Corrija os problemas acima para o WhatsApp funcionar.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";



