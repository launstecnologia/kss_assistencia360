<?php
/**
 * Script de Teste - Enviar WhatsApp para Solicitação Específica
 * 
 * Uso: php testar_whatsapp_solicitacao.php <numero_solicitacao>
 * Exemplo: php testar_whatsapp_solicitacao.php KS18
 */

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações
$config = require __DIR__ . '/app/Config/config.php';
\App\Core\Database::setConfig($config['database']);

// Verificar argumentos
if ($argc < 2) {
    echo "📱 Teste de Envio WhatsApp - Solicitação Específica\n\n";
    echo "Uso: php testar_whatsapp_solicitacao.php <numero_solicitacao>\n\n";
    echo "Exemplo:\n";
    echo "  php testar_whatsapp_solicitacao.php KS18\n";
    exit(1);
}

$numeroSolicitacao = $argv[1];

echo "🔍 Buscando solicitação: {$numeroSolicitacao}\n";
echo str_repeat("=", 60) . "\n\n";

// Buscar solicitação (pode ser por número ou ID)
$solicitacao = null;

// Tentar buscar por número_solicitacao primeiro
try {
    $sql = "
        SELECT s.*,
               l.nome as cliente_nome,
               l.telefone as cliente_telefone,
               l.email as cliente_email,
               i.nome as imobiliaria_nome
        FROM solicitacoes s
        LEFT JOIN locatarios l ON s.locatario_id = l.id
        LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
        WHERE s.numero_solicitacao = ?
    ";
    $solicitacao = \App\Core\Database::fetch($sql, [$numeroSolicitacao]);
} catch (\Exception $e) {
    // Se não tiver coluna numero_solicitacao, tentar por ID
    if (preg_match('/^KS(\d+)$/i', $numeroSolicitacao, $matches)) {
        $id = (int)$matches[1];
        $sql = "
            SELECT s.*,
                   l.nome as cliente_nome,
                   l.telefone as cliente_telefone,
                   l.email as cliente_email,
                   i.nome as imobiliaria_nome
            FROM solicitacoes s
            LEFT JOIN locatarios l ON s.locatario_id = l.id
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            WHERE s.id = ?
        ";
        $solicitacao = \App\Core\Database::fetch($sql, [$id]);
    }
}

if (!$solicitacao) {
    echo "❌ Solicitação não encontrada: {$numeroSolicitacao}\n";
    exit(1);
}

echo "✅ Solicitação encontrada!\n\n";
echo "📋 Dados da Solicitação:\n";
echo "   ID: {$solicitacao['id']}\n";
echo "   Número: {$solicitacao['numero_solicitacao']}\n";
echo "   Cliente: " . ($solicitacao['cliente_nome'] ?? $solicitacao['locatario_nome'] ?? 'N/A') . "\n";
echo "   Telefone: " . ($solicitacao['cliente_telefone'] ?? $solicitacao['locatario_telefone'] ?? 'NÃO ENCONTRADO') . "\n";
echo "   Email: " . ($solicitacao['cliente_email'] ?? $solicitacao['locatario_email'] ?? 'N/A') . "\n";
echo "   Imobiliária: " . ($solicitacao['imobiliaria_nome'] ?? 'N/A') . "\n";
echo "\n";

// Verificar se tem telefone
$telefone = $solicitacao['cliente_telefone'] ?? $solicitacao['locatario_telefone'] ?? '';

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

try {
    $whatsappService = new \App\Services\WhatsAppService();
    $result = $whatsappService->sendMessage($solicitacao['id'], 'Nova Solicitação');
    
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
            echo "   - Verifique no banco: SELECT * FROM whatsapp_templates WHERE tipo = 'Nova Solicitação'\n";
        }
        
        if (strpos($result['message'], 'Telefone') !== false) {
            echo "   - Telefone não encontrado na solicitação\n";
        }
        
        if (strpos($result['message'], 'Connection Closed') !== false) {
            echo "   - Instância do WhatsApp não está conectada\n";
            echo "   - Acesse: https://evolutionapi.launs.com.br/instance/login/notification_launs_01\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Erro fatal: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo str_repeat("=", 60) . "\n";

