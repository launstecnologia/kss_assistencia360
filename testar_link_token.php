<?php
/**
 * Script de teste para verificar se os links estão sendo gerados corretamente
 */

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/app/Config/config.php';
\App\Core\Database::setConfig($config['database']);

echo "🧪 Teste de Geração de Links\n";
echo str_repeat("=", 60) . "\n\n";

// Simular preparação de variáveis (igual ao WhatsAppService)
$whatsappConfig = $config['whatsapp'] ?? [];
$baseUrl = $whatsappConfig['links_base_url'] ?? null;

if (!$baseUrl) {
    $baseUrl = $config['app']['url'] ?? null;
}

if (!$baseUrl) {
    $baseUrl = 'http://localhost';
}

// Limpar: remover barras invertidas, espaços, e barras finais
$baseUrl = str_replace('\\', '', $baseUrl); // Remover todas as barras invertidas
$baseUrl = trim($baseUrl); // Remover espaços
$baseUrl = rtrim($baseUrl, '/'); // Remover barra final

// Garantir que tem protocolo
if (empty($baseUrl) || !preg_match('/^https?:\/\//', $baseUrl)) {
    $baseUrl = 'http://localhost';
}

echo "📌 URL Base configurada: {$baseUrl}\n\n";

$token = '919a8c93e6218f25b9af3337fb7d7d69b0819129e1c309ca15526859e314aa8e';
$solicitacaoId = 19;

$links = [
    'link_confirmacao' => $baseUrl . '/confirmacao-horario?token=' . $token,
    'link_cancelamento' => $baseUrl . '/cancelamento-horario?token=' . $token,
    'link_status' => $baseUrl . '/status-servico?token=' . $token,
    'link_rastreamento' => $baseUrl . '/locatario/solicitacao/' . $solicitacaoId,
];

echo "📋 URLs geradas:\n\n";
foreach ($links as $key => $url) {
    echo "  {$key}:\n";
    echo "    {$url}\n";
    echo "    ✅ URL válida: " . (filter_var($url, FILTER_VALIDATE_URL) ? 'SIM' : 'NÃO') . "\n\n";
}

// Testar template com barras invertidas
echo "🔍 Testando substituição de variáveis com barras invertidas:\n\n";

$templateComBarras = "\\http://localhost/kss\\/{{link_cancelamento}}";
$templateNormal = "http://localhost/kss/{{link_cancelamento}}";

$whatsappService = new \App\Services\WhatsAppService();
$reflection = new ReflectionClass($whatsappService);
$method = $reflection->getMethod('replaceVariables');
$method->setAccessible(true);

$variables = ['link_cancelamento' => $links['link_cancelamento']];

echo "Template com barras: {$templateComBarras}\n";
$resultado1 = $method->invoke($whatsappService, $templateComBarras, $variables);
echo "Resultado: {$resultado1}\n";
echo "✅ Sem barras invertidas: " . (strpos($resultado1, '\\http') === false ? 'SIM' : 'NÃO') . "\n\n";

echo "Template normal: {$templateNormal}\n";
$resultado2 = $method->invoke($whatsappService, $templateNormal, $variables);
echo "Resultado: {$resultado2}\n";
echo "✅ Correto: " . ($resultado2 === $links['link_cancelamento'] ? 'SIM' : 'NÃO') . "\n\n";

echo str_repeat("=", 60) . "\n";
echo "✅ Teste concluído!\n";

