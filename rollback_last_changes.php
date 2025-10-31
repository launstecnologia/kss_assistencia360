<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Load app config
     = require __DIR__ . '/app/Config/config.php';
     = ['database'];

    // Build DSN
     = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', ['host'], ['port'], ['database'], ['charset']);

    // Connect
     = new PDO(, ['username'], ['password'], ['options']);

    // Helpers
     = ['database'];

    ->beginTransaction();

    // Drop columns if they exist
     = ['locatario_cpf', 'horarios_opcoes', 'horarios_sugestoes'];

    foreach ( as ) {
         = ->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'solicitacoes' AND COLUMN_NAME = ?");
        ->execute([, ]);
         = (int)->fetchColumn() > 0;
        if () {
             = "ALTER TABLE solicitacoes DROP COLUMN {}";
            ->exec();
        }
    }

    // Drop table solicitacoes_manuais if exists
     = ->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = " . ->quote() . " AND TABLE_NAME = 'solicitacoes_manuais'");
     = (int)->fetchColumn() > 0;
    if () {
        ->exec("DROP TABLE solicitacoes_manuais");
    }

    ->commit();

    echo "Rollback concluido com sucesso.\n";
} catch (Throwable ) {
    if (isset() && ->inTransaction()) {
        ->rollBack();
    }
    echo "Erro no rollback: " . ->getMessage() . "\n";
    exit(1);
}
