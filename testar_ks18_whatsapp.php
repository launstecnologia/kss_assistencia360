<?php
/**
 * Script de Teste - Enviar WhatsApp para Solicitação KS18
 * 
 * Este script testa o envio de mensagem WhatsApp para a solicitação KS18
 * e verifica se o sistema de log está funcionando corretamente.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações
$config = require __DIR__ . '/app/Config/config.php';
\App\Core\Database::setConfig($config['database']);

echo "📱 Teste de Envio WhatsApp - Solicitação KS18\n";
echo str_repeat("=", 60) . "\n\n";

// Buscar solicitação KS18 (ID 18)
$solicitacaoId = 18;
$numeroSolicitacao = 'KS18';

echo "🔍 Buscando solicitação: {$numeroSolicitacao} (ID: {$solicitacaoId})\n";
echo str_repeat("-", 60) . "\n";

try {
    $sql = "
        SELECT s.*,
               COALESCE(l.nome, s.locatario_nome) as cliente_nome,
               l.cpf as cliente_cpf,
               COALESCE(l.telefone, s.locatario_telefone) as cliente_telefone,
               COALESCE(l.email, s.locatario_email) as cliente_email,
               i.nome as imobiliaria_nome
        FROM solicitacoes s
        LEFT JOIN locatarios l ON s.locatario_id = l.id
        LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
        WHERE s.id = ?
    ";
    $solicitacao = \App\Core\Database::fetch($sql, [$solicitacaoId]);
    
    if (!$solicitacao) {
        echo "❌ Solicitação não encontrada: {$numeroSolicitacao}\n";
        exit(1);
    }
    
    echo "✅ Solicitação encontrada!\n\n";
    echo "📋 Dados da Solicitação:\n";
    echo "   ID: {$solicitacao['id']}\n";
    echo "   Número: " . ($solicitacao['numero_solicitacao'] ?? 'KS' . $solicitacao['id']) . "\n";
    echo "   Cliente: " . ($solicitacao['cliente_nome'] ?? 'N/A') . "\n";
    echo "   Telefone: " . ($solicitacao['cliente_telefone'] ?? 'NÃO ENCONTRADO') . "\n";
    echo "   Email: " . ($solicitacao['cliente_email'] ?? 'N/A') . "\n";
    echo "   Imobiliária: " . ($solicitacao['imobiliaria_nome'] ?? 'N/A') . "\n";
    echo "\n";
    
    // Verificar se tem telefone
    $telefone = $solicitacao['cliente_telefone'] ?? '';
    
    if (empty($telefone)) {
        echo "❌ ERRO: Telefone não encontrado para esta solicitação!\n";
        echo "   O WhatsApp não pode ser enviado sem um telefone.\n";
        exit(1);
    }
    
    // Verificar template
    echo "🔍 Verificando template 'Nova Solicitação'...\n";
    $sql = "SELECT * FROM whatsapp_templates WHERE tipo = 'Nova Solicitação' AND ativo = 1 ORDER BY padrao DESC LIMIT 1";
    $template = \App\Core\Database::fetch($sql);
    
    if (!$template) {
        echo "❌ ERRO: Template 'Nova Solicitação' não encontrado ou inativo!\n";
        exit(1);
    }
    
    echo "✅ Template encontrado: {$template['nome']}\n";
    echo "\n";
    
    // Tentar enviar WhatsApp
    echo "📱 Tentando enviar WhatsApp...\n";
    echo str_repeat("-", 60) . "\n";
    
    $whatsappService = new \App\Services\WhatsAppService();
    $result = $whatsappService->sendMessage($solicitacaoId, 'Nova Solicitação');
    
    echo "\n" . str_repeat("=", 60) . "\n";
    
    if ($result['success']) {
        echo "✅ WhatsApp enviado com sucesso!\n";
        if (isset($result['data'])) {
            echo "📊 Resposta da API:\n";
            echo json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "❌ Erro ao enviar WhatsApp:\n";
        echo "   Mensagem: {$result['message']}\n";
        
        // Verificar logs
        echo "\n💡 Verificando possíveis causas:\n";
        
        if (strpos($result['message'], 'Template não encontrado') !== false) {
            echo "   - Template 'Nova Solicitação' não existe ou está inativo\n";
        }
        
        if (strpos($result['message'], 'Telefone') !== false) {
            echo "   - Telefone não encontrado na solicitação\n";
        }
        
        if (strpos($result['message'], 'Connection Closed') !== false) {
            echo "   - Instância do WhatsApp não está conectada\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📝 Verificando arquivo de log...\n";
    
    $logFile = __DIR__ . '/storage/logs/whatsapp_evolution_api.log';
    if (file_exists($logFile)) {
        echo "✅ Arquivo de log encontrado: {$logFile}\n";
        echo "📄 Últimas 20 linhas do log:\n";
        echo str_repeat("-", 60) . "\n";
        
        $lines = file($logFile);
        $lastLines = array_slice($lines, -20);
        echo implode('', $lastLines);
    } else {
        echo "⚠️ Arquivo de log ainda não foi criado (será criado na primeira execução)\n";
    }
    
    echo str_repeat("=", 60) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Erro fatal: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . "\n";
    echo "   Linha: " . $e->getLine() . "\n";
    echo "\n   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

