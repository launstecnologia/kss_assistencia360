<?php
/**
 * Script temporário para criar a tabela solicitacoes_manuais
 * Execute acessando: http://localhost:8000/criar_tabela_manual.php
 * DEPOIS DE EXECUTAR, DELETE ESTE ARQUIVO POR SEGURANÇA!
 */

// Carregar configurações do banco
$config = require_once __DIR__ . '/app/Config/config.php';

try {
    // Extrair configurações do banco
    $dbConfig = $config['database'];
    
    // Conectar ao banco
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['options']
    );
    
    echo "<h1>Criando tabela solicitacoes_manuais...</h1>";
    
    // SQL para criar a tabela
    $sql = "
    CREATE TABLE IF NOT EXISTS solicitacoes_manuais (
        id INT AUTO_INCREMENT PRIMARY KEY,
        
        -- Relacionamento
        imobiliaria_id INT NOT NULL,
        
        -- Dados Pessoais
        nome_completo VARCHAR(255) NOT NULL,
        cpf VARCHAR(14) NOT NULL,
        whatsapp VARCHAR(20) NOT NULL,
        
        -- Endereço
        tipo_imovel ENUM('RESIDENCIAL', 'COMERCIAL') NOT NULL,
        subtipo_imovel ENUM('CASA', 'APARTAMENTO') NULL,
        cep VARCHAR(10) NOT NULL,
        endereco VARCHAR(255) NOT NULL,
        numero VARCHAR(20) NOT NULL,
        complemento VARCHAR(100) NULL,
        bairro VARCHAR(100) NOT NULL,
        cidade VARCHAR(100) NOT NULL,
        estado VARCHAR(2) NOT NULL,
        
        -- Serviço
        categoria_id INT NOT NULL,
        subcategoria_id INT NOT NULL,
        descricao_problema TEXT NOT NULL,
        
        -- Horários e Fotos
        horarios_preferenciais JSON NULL,
        fotos JSON NULL,
        
        -- Termos e Controle
        termos_aceitos BOOLEAN DEFAULT FALSE,
        status_id INT NOT NULL DEFAULT 1,
        
        -- Migração
        migrada_para_solicitacao_id INT NULL,
        migrada_em DATETIME NULL,
        migrada_por_usuario_id INT NULL,
        
        -- Timestamps
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- Índices
        INDEX idx_imobiliaria (imobiliaria_id),
        INDEX idx_cpf (cpf),
        INDEX idx_status (status_id),
        INDEX idx_categoria (categoria_id),
        INDEX idx_subcategoria (subcategoria_id),
        INDEX idx_migrada (migrada_para_solicitacao_id),
        INDEX idx_created (created_at),
        
        -- Foreign Keys
        FOREIGN KEY (imobiliaria_id) REFERENCES imobiliarias(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
        FOREIGN KEY (subcategoria_id) REFERENCES subcategorias(id) ON DELETE RESTRICT,
        FOREIGN KEY (status_id) REFERENCES status(id) ON DELETE RESTRICT,
        FOREIGN KEY (migrada_para_solicitacao_id) REFERENCES solicitacoes(id) ON DELETE SET NULL,
        FOREIGN KEY (migrada_por_usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    
    echo "<div style='background: #d4edda; color: #155724; padding: 20px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h2>✅ Sucesso!</h2>";
    echo "<p><strong>A tabela 'solicitacoes_manuais' foi criada com sucesso no banco de dados '{$dbConfig['database']}'!</strong></p>";
    echo "<p>Agora você pode:</p>";
    echo "<ol>";
    echo "<li>Testar a solicitação manual em: <a href='http://localhost:8000/demo/solicitacao-manual' target='_blank'>http://localhost:8000/demo/solicitacao-manual</a></li>";
    echo "<li>Ver solicitações no admin: <a href='http://localhost:8000/admin/solicitacoes-manuais' target='_blank'>http://localhost:8000/admin/solicitacoes-manuais</a></li>";
    echo "<li><strong style='color: red;'>IMPORTANTE: Delete este arquivo (criar_tabela_manual.php) por segurança!</strong></li>";
    echo "</ol>";
    echo "</div>";
    
    // Verificar se a tabela foi criada
    $check = $pdo->query("SHOW TABLES LIKE 'solicitacoes_manuais'")->fetch();
    if ($check) {
        echo "<div style='background: #cce5ff; padding: 15px; border-radius: 5px; margin: 20px;'>";
        echo "<h3>📊 Estrutura da Tabela Criada:</h3>";
        
        $columns = $pdo->query("DESCRIBE solicitacoes_manuais")->fetchAll();
        echo "<table style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th style='padding: 10px; border: 1px solid #ddd;'>Campo</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd;'>Tipo</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd;'>Nulo</th>";
        echo "<th style='padding: 10px; border: 1px solid #ddd;'>Chave</th>";
        echo "</tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['Field']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['Type']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['Null']}</td>";
            echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$col['Key']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    $dbName = isset($dbConfig) ? $dbConfig['database'] : 'launs_kss';
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h2>❌ Erro ao criar tabela</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    echo "<hr>";
    echo "<h3>Possíveis soluções:</h3>";
    echo "<ol>";
    echo "<li>Verifique se o banco de dados '{$dbName}' existe</li>";
    echo "<li>Verifique se as tabelas referenciadas (imobiliarias, categorias, subcategorias, status, solicitacoes, usuarios) existem</li>";
    echo "<li>Verifique as permissões do usuário do banco de dados</li>";
    echo "<li>Verifique se o arquivo .env está configurado corretamente</li>";
    echo "</ol>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h2>❌ Erro Geral</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Tabela - Solicitações Manuais</title>
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

