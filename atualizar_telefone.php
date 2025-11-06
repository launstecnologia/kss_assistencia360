<?php
/**
 * Script para atualizar telefone de uma solicitação
 */

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/app/Config/config.php';
\App\Core\Database::setConfig($config['database']);

$solicitacaoId = 18;
$novoTelefone = '+55 16 99242-2354';

echo "📱 Atualizando telefone da solicitação KS18\n";
echo str_repeat("=", 60) . "\n\n";

// Buscar telefone atual
$sql = "SELECT id, locatario_telefone FROM solicitacoes WHERE id = ?";
$solicitacao = \App\Core\Database::fetch($sql, [$solicitacaoId]);

if (!$solicitacao) {
    echo "❌ Solicitação não encontrada!\n";
    exit(1);
}

echo "📋 Telefone atual: " . ($solicitacao['locatario_telefone'] ?? 'N/A') . "\n";
echo "📱 Novo telefone: {$novoTelefone}\n\n";

// Atualizar telefone
$sql = "UPDATE solicitacoes SET locatario_telefone = ? WHERE id = ?";
\App\Core\Database::query($sql, [$novoTelefone, $solicitacaoId]);

echo "✅ Telefone atualizado com sucesso!\n\n";

// Verificar atualização
$sql = "SELECT id, locatario_telefone FROM solicitacoes WHERE id = ?";
$resultado = \App\Core\Database::fetch($sql, [$solicitacaoId]);

echo "📋 Verificação:\n";
echo "   ID: {$resultado['id']}\n";
echo "   Telefone: {$resultado['locatario_telefone']}\n";



