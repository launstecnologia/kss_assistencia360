<?php
/**
 * Script para adicionar campos necessários na tabela solicitacoes
 * Execute acessando: http://localhost:8000/adicionar_campos_script.php
 * DEPOIS DE EXECUTAR, DELETE ESTE ARQUIVO POR SEGURANÇA!
 */

require_once __DIR__ . '/app/Config/config.php';

try {
    $config = require_once __DIR__ . '/app/Config/config.php';
    $dbConfig = $config['database'];
    
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['options']
    );
    
    echo "<h1>Adicionando campos à tabela solicitacoes...</h1>";
    
    // Adicionar campo locatario_cpf
    try {
        $pdo->exec("ALTER TABLE solicitacoes ADD COLUMN locatario_cpf VARCHAR(14) NULL AFTER locatario_email");
        echo "<p>✅ Campo 'locatario_cpf' adicionado com sucesso!</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Campo 'locatario_cpf' já existe.</p>";
        } else {
            throw $e;
        }
    }
    
    // Adicionar campo horarios_opcoes
    try {
        $pdo->exec("ALTER TABLE solicitacoes ADD COLUMN horarios_opcoes JSON NULL AFTER prioridade");
        echo "<p>✅ Campo 'horarios_opcoes' adicionado com sucesso!</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Campo 'horarios_opcoes' já existe.</p>";
        } else {
            throw $e;
        }
    }
    
    // Adicionar índice
    try {
        $pdo->exec("ALTER TABLE solicitacoes ADD INDEX idx_locatario_cpf (locatario_cpf)");
        echo "<p>✅ Índice 'idx_locatario_cpf' adicionado com sucesso!</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<p>ℹ️ Índice 'idx_locatario_cpf' já existe.</p>";
        } else {
            throw $e;
        }
    }
    
    echo "<div style='background: #d4edda; color: #155724; padding: 20px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h2>✅ Campos adicionados com sucesso!</h2>";
    echo "<p>Agora você pode:</p>";
    echo "<ol>";
    echo "<li><strong style='color: red;'>DELETE este arquivo (adicionar_campos_script.php) por segurança!</strong></li>";
    echo "<li>Testar a migração novamente em: <a href='http://localhost:8000/admin/solicitacoes-manuais'>Solicitações Manuais</a></li>";
    echo "</ol>";
    echo "</div>";
    
    // Mostrar estrutura atualizada
    $columns = $pdo->query("DESCRIBE solicitacoes")->fetchAll();
    echo "<h3>📊 Estrutura Atualizada da Tabela:</h3>";
    echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Campo</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Tipo</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Nulo</th>";
    echo "</tr>";
    
    foreach ($columns as $col) {
        $isNew = in_array($col['Field'], ['locatario_cpf', 'horarios_opcoes']);
        $rowStyle = $isNew ? 'background: #d4edda;' : '';
        echo "<tr style='{$rowStyle}'>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['Field']}</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['Type']}</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['Null']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h2>❌ Erro</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Campos - Solicitações</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
</body>
</html>

