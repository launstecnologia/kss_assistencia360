<?php
/**
 * Script para verificar e criar templates WhatsApp faltantes
 */

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/app/Config/config.php';
\App\Core\Database::setConfig($config['database']);

echo "📋 Verificando templates WhatsApp...\n";
echo str_repeat("=", 60) . "\n\n";

// Buscar todos os templates
$templates = \App\Core\Database::fetchAll('SELECT tipo, nome, ativo, padrao FROM whatsapp_templates ORDER BY tipo');

echo "✅ Templates existentes:\n";
$tiposExistentes = [];
foreach ($templates as $t) {
    echo "   - {$t['tipo']}: {$t['nome']} (" . ($t['ativo'] ? 'ATIVO' : 'INATIVO') . ")" . ($t['padrao'] ? ' [PADRÃO]' : '') . "\n";
    $tiposExistentes[] = $t['tipo'];
}

echo "\n";

// Templates necessários conforme documentação
$templatesNecessarios = [
    'Nova Solicitação',
    'Horário Confirmado',
    'Horário Sugerido',
    'Confirmação de Serviço',
    'Atualização de Status',
    'agendado',  // Para confirmarDatas()
    'concluido', // Para confirmarAtendimento()
    'lembrete_peca' // Para enviarLembretes()
];

echo "📝 Templates necessários:\n";
foreach ($templatesNecessarios as $tipo) {
    $existe = in_array($tipo, $tiposExistentes);
    $status = $existe ? '✅' : '❌';
    echo "   {$status} {$tipo}\n";
}

echo "\n";

// Verificar quais estão faltando
$templatesFaltando = array_diff($templatesNecessarios, $tiposExistentes);

if (empty($templatesFaltando)) {
    echo "✅ Todos os templates necessários existem!\n";
} else {
    echo "⚠️ Templates faltando:\n";
    foreach ($templatesFaltando as $tipo) {
        echo "   - {$tipo}\n";
    }
    
    echo "\n";
    echo "🔧 Criando templates faltantes...\n";
    
    foreach ($templatesFaltando as $tipo) {
        $nome = ucfirst(str_replace('_', ' ', $tipo));
        $corpo = getCorpoTemplate($tipo);
        
        $sql = "INSERT INTO whatsapp_templates (nome, tipo, corpo, variaveis, ativo, padrao, created_at) 
                VALUES (?, ?, ?, ?, 1, 1, NOW())";
        
        $variaveis = json_encode(getVariaveisTemplate($tipo));
        
        try {
            \App\Core\Database::query($sql, [$nome, $tipo, $corpo, $variaveis]);
            echo "   ✅ Template '{$tipo}' criado com sucesso!\n";
        } catch (\Exception $e) {
            echo "   ❌ Erro ao criar template '{$tipo}': " . $e->getMessage() . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";

function getCorpoTemplate($tipo): string
{
    $templates = [
        'agendado' => "🏠 *Serviço Agendado*\n\nOlá {{cliente_nome}},\n\nSua solicitação {{protocol}} foi agendada com sucesso!\n\n📅 Data: {{data_agendamento}}\n🕐 Horário: {{horario_agendamento}}\n\n📍 Endereço: {{endereco_completo}}\n\n🔗 Acompanhe sua solicitação em:\n{{link_rastreamento}}\n\n---\nSolicitação agendada automaticamente pelo sistema",
        
        'concluido' => "✅ *Atendimento Confirmado*\n\nOlá {{cliente_nome}},\n\nObrigado por confirmar o atendimento da solicitação {{protocol}}!\n\nSua confirmação é muito importante para nós.\n\n🔗 Acompanhe outras solicitações em:\n{{link_rastreamento}}\n\n---\nConfirmação automática do sistema",
        
        'lembrete_peca' => "🔔 *Lembrete - Aguardando Peça*\n\nOlá {{cliente_nome}},\n\nEste é um lembrete sobre sua solicitação {{protocol}}.\n\n⏳ Status: Aguardando compra de peça\n\nEstamos trabalhando para resolver sua solicitação o mais breve possível.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\n🔗 Acompanhe sua solicitação em:\n{{link_rastreamento}}\n\n---\nLembrete automático do sistema"
    ];
    
    return $templates[$tipo] ?? "Template para {$tipo}";
}

function getVariaveisTemplate($tipo): array
{
    $variaveis = [
        'agendado' => ['cliente_nome', 'protocol', 'data_agendamento', 'horario_agendamento', 'endereco_completo', 'link_rastreamento'],
        'concluido' => ['cliente_nome', 'protocol', 'link_rastreamento'],
        'lembrete_peca' => ['cliente_nome', 'protocol', 'link_rastreamento']
    ];
    
    return $variaveis[$tipo] ?? [];
}

