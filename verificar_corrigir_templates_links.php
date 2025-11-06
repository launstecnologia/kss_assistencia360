<?php
/**
 * Script para verificar e corrigir templates com barras invertidas nos links
 */

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/app/Config/config.php';
\App\Core\Database::setConfig($config['database']);

echo "🔍 Verificando templates WhatsApp com links...\n";
echo str_repeat("=", 60) . "\n\n";

// Buscar todos os templates que têm links
$templates = \App\Core\Database::fetchAll('SELECT id, tipo, nome, corpo FROM whatsapp_templates WHERE ativo = 1 ORDER BY tipo');

$corrigidos = 0;

foreach ($templates as $template) {
    $corpoOriginal = $template['corpo'];
    $corpoCorrigido = $corpoOriginal;
    $teveAlteracao = false;
    
    // Verificar se tem barras invertidas antes de links
    // Padrões a procurar: \http://, \\http://, \{{link_}}, \\{{link_}}
    $padroes = [
        '/\\\\+(http[s]?:\/\/[^\s]+)/' => '', // Remover \ antes de http://
        '/\\\\+\\{\\{link_([^}]+)\\}\\}\\\\*/' => '{{link_$1}}', // Remover \ antes e depois de {{link_*}}
        '/\\\\+(\/)/' => '$1', // Remover \ antes de /
    ];
    
    foreach ($padroes as $padrao => $substituicao) {
        $novoCorpo = preg_replace($padrao, $substituicao, $corpoCorrigido);
        if ($novoCorpo !== $corpoCorrigido) {
            $corpoCorrigido = $novoCorpo;
            $teveAlteracao = true;
        }
    }
    
    if ($teveAlteracao) {
        echo "⚠️  Template '{$template['tipo']}' (ID: {$template['id']}) tem barras invertidas!\n";
        echo "   Antes: " . substr($corpoOriginal, 0, 100) . "...\n";
        echo "   Depois: " . substr($corpoCorrigido, 0, 100) . "...\n";
        
        // Atualizar no banco
        try {
            $sql = "UPDATE whatsapp_templates SET corpo = ? WHERE id = ?";
            \App\Core\Database::query($sql, [$corpoCorrigido, $template['id']]);
            echo "   ✅ Corrigido!\n\n";
            $corrigidos++;
        } catch (\Exception $e) {
            echo "   ❌ Erro ao corrigir: " . $e->getMessage() . "\n\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Verificação concluída!\n";
echo "📊 Templates corrigidos: {$corrigidos}\n";

