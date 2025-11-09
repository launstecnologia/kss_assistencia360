<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class MaintenanceController extends Controller
{
    public function showMigrations(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_level'] ?? '') !== 'ADMINISTRADOR') {
            header('Location: /admin/dashboard');
            return;
        }

        $this->view('admin.migracoes', $this->getMigrationViewData());
    }

    public function runMigrations(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_level'] ?? '') !== 'ADMINISTRADOR') {
            header('Location: /admin/dashboard');
            return;
        }

        // CSRF básico
        $token = $this->input('csrf_token');
        if (!$token || $token !== \App\Core\View::csrfToken()) {
            $this->view('admin.migracoes', $this->getMigrationViewData([
                'error' => 'CSRF inválido'
            ]));
            return;
        }

        try {
            // DDL no MySQL faz autocommit; evite transações aqui
            // descricao_card
            Database::query("ALTER TABLE solicitacoes ADD COLUMN IF NOT EXISTS descricao_card TEXT NULL AFTER descricao_problema");
            Database::query("UPDATE solicitacoes SET descricao_card = descricao_problema WHERE descricao_card IS NULL");

            // horario_confirmado
            Database::query("ALTER TABLE solicitacoes ADD COLUMN IF NOT EXISTS horario_confirmado TINYINT(1) NOT NULL DEFAULT 0 AFTER horario_agendamento");
            Database::query("ALTER TABLE solicitacoes ADD COLUMN IF NOT EXISTS horario_confirmado_raw TEXT NULL AFTER horario_confirmado");

            // confirmed_schedules JSON (lista de confirmações)
            Database::query("ALTER TABLE solicitacoes ADD COLUMN IF NOT EXISTS confirmed_schedules JSON NULL AFTER horario_confirmado_raw");

            // datas_opcoes JSON (para preservar horários originais do locatário quando horarios_indisponiveis = 1)
            $checkColumn = function(string $column): bool {
                $sql = "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solicitacoes' AND COLUMN_NAME = ?";
                $row = Database::fetch($sql, [$column]);
                return (int)($row['c'] ?? 0) > 0;
            };
            
            if (!$checkColumn('datas_opcoes')) {
                Database::query("ALTER TABLE solicitacoes ADD COLUMN datas_opcoes JSON NULL AFTER horarios_opcoes");
            }

            // Campos de lembrete de peças
            Database::query("ALTER TABLE solicitacoes ADD COLUMN IF NOT EXISTS data_limite_peca DATE NULL AFTER horarios_opcoes");
            Database::query("ALTER TABLE solicitacoes ADD COLUMN IF NOT EXISTS data_ultimo_lembrete DATETIME NULL AFTER data_limite_peca");
            Database::query("ALTER TABLE solicitacoes ADD COLUMN IF NOT EXISTS lembretes_enviados INT NOT NULL DEFAULT 0 AFTER data_ultimo_lembrete");
            Database::query("UPDATE solicitacoes SET lembretes_enviados = 0 WHERE lembretes_enviados IS NULL");

            $this->view('admin.migracoes', $this->getMigrationViewData([
                'success' => 'Migrações executadas com sucesso.'
            ]));
        } catch (\Exception $e) {
            $this->view('admin.migracoes', $this->getMigrationViewData([
                'error' => 'Falha ao executar: ' . $e->getMessage()
            ]));
        }
    }

    public function redirectToMigrations(): void
    {
        $this->requireAuth();
        header('Location: /admin/migracoes');
    }

    public function purgeSolicitacoes(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_level'] ?? '') !== 'ADMINISTRADOR') {
            header('Location: /admin/dashboard');
            return;
        }

        $token = $this->input('csrf_token');
        $confirm = trim((string)$this->input('confirm_text'));
        if (!$token || $token !== \App\Core\View::csrfToken()) {
            $this->view('admin.migracoes', $this->getMigrationViewData([
                'error' => 'CSRF inválido'
            ]));
            return;
        }
        if (strtoupper($confirm) !== 'LIMPAR') {
            $this->view('admin.migracoes', $this->getMigrationViewData([
                'error' => 'Para confirmar, digite LIMPAR.'
            ]));
            return;
        }

        try {
            // Desativar FKs para garantir limpeza em cascata controlada
            Database::query('SET FOREIGN_KEY_CHECKS=0');

            // Tabelas relacionadas (algumas podem não existir em certas instalações)
            $tables = [
                'historico_status',
                'fotos',
                'solicitacoes',
            ];
            foreach ($tables as $t) {
                try { Database::query("DELETE FROM {$t}"); } catch (\Exception $e) { /* ignora */ }
            }

            // Limpar solicitações manuais se existir
            try { Database::query('DELETE FROM solicitacoes_manuais'); } catch (\Exception $e) { /* ignora */ }

            Database::query('SET FOREIGN_KEY_CHECKS=1');

            $this->view('admin.migracoes', $this->getMigrationViewData([
                'success' => 'Todas as solicitações foram limpas.'
            ]));
        } catch (\Exception $e) {
            $this->view('admin.migracoes', $this->getMigrationViewData([
                'error' => 'Falha ao limpar: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * Limpar "Disponibilidade:" das descrições existentes
     */
    public function limparDisponibilidadeDescricoes(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_level'] ?? '') !== 'ADMINISTRADOR') {
            header('Location: /admin/dashboard');
            return;
        }

        $token = $this->input('csrf_token');
        if (!$token || $token !== \App\Core\View::csrfToken()) {
            $this->json(['error' => 'CSRF inválido'], 403);
            return;
        }

        try {
            // Remover "Disponibilidade: ..." das descrições usando REPLACE (compatível com MySQL antigo)
            // Primeiro, buscar todas as solicitações com "Disponibilidade:"
            $sqlSelect = "
                SELECT id, descricao_problema, descricao_card 
                FROM solicitacoes 
                WHERE descricao_problema LIKE '%Disponibilidade:%' 
                   OR descricao_card LIKE '%Disponibilidade:%'
            ";
            
            $solicitacoes = Database::fetchAll($sqlSelect);
            $atualizadas = 0;
            
            foreach ($solicitacoes as $solicitacao) {
                $id = $solicitacao['id'];
                $descricaoProblema = $solicitacao['descricao_problema'] ?? '';
                $descricaoCard = $solicitacao['descricao_card'] ?? '';
                
                // Limpar descricao_problema usando preg_replace (PHP)
                $descricaoProblemaLimpa = preg_replace('/\n?Disponibilidade:.*$/m', '', $descricaoProblema);
                $descricaoProblemaLimpa = trim($descricaoProblemaLimpa);
                
                // Limpar descricao_card
                $descricaoCardLimpa = preg_replace('/\n?Disponibilidade:.*$/m', '', $descricaoCard);
                $descricaoCardLimpa = trim($descricaoCardLimpa);
                
                // Atualizar apenas se houve mudança
                if ($descricaoProblemaLimpa !== $descricaoProblema || $descricaoCardLimpa !== $descricaoCard) {
                    $sqlUpdate = "
                        UPDATE solicitacoes 
                        SET 
                            descricao_problema = ?,
                            descricao_card = ?
                        WHERE id = ?
                    ";
                    
                    Database::query($sqlUpdate, [
                        $descricaoProblemaLimpa ?: null,
                        $descricaoCardLimpa ?: null,
                        $id
                    ]);
                    
                    $atualizadas++;
                }
            }

            // Buscar quantas ainda têm "Disponibilidade:" (pode ter formatos diferentes)
            $sqlCount = "
                SELECT COUNT(*) as total 
                FROM solicitacoes 
                WHERE descricao_problema LIKE '%Disponibilidade:%' 
                   OR descricao_card LIKE '%Disponibilidade:%'
            ";
            $count = Database::fetch($sqlCount);

            $this->json([
                'success' => true,
                'message' => "Descrições limpas com sucesso! {$atualizadas} registro(s) atualizado(s).",
                'atualizadas' => $atualizadas,
                'restantes' => (int)($count['total'] ?? 0)
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao limpar disponibilidade: ' . $e->getMessage());
            $this->json(['error' => 'Falha ao limpar: ' . $e->getMessage()], 500);
        }
    }

    private function getMigrationStatus(): array
    {
        $check = function(string $column): bool {
            $sql = "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solicitacoes' AND COLUMN_NAME = ?";
            $row = Database::fetch($sql, [$column]);
            return (int)($row['c'] ?? 0) > 0;
        };

        return [
            'hasDescricaoCard' => $check('descricao_card'),
            'hasHorarioConfirmado' => $check('horario_confirmado'),
            'hasHorarioRaw' => $check('horario_confirmado_raw'),
            'hasConfirmedSchedules' => $check('confirmed_schedules'),
            'hasDatasOpcoes' => $check('datas_opcoes'),
            'hasDataLimitePeca' => $check('data_limite_peca'),
            'hasDataUltimoLembrete' => $check('data_ultimo_lembrete'),
            'hasLembretesEnviados' => $check('lembretes_enviados'),
        ];
    }

    private function getSqlScripts(): array
    {
        $basePath = dirname(__DIR__, 2);
        $directories = [
            'scripts' => $basePath . DIRECTORY_SEPARATOR . 'scripts',
            'scripts/migrations' => $basePath . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'migrations',
        ];

        $scripts = [];
        foreach ($directories as $relativeDir => $absoluteDir) {
            if (!is_dir($absoluteDir)) {
                continue;
            }
            $files = glob($absoluteDir . DIRECTORY_SEPARATOR . '*.sql');
            if (!$files) {
                continue;
            }
            sort($files);
            foreach ($files as $file) {
                $relativePath = $relativeDir . '/' . basename($file);
                $scripts[$relativePath] = $relativePath;
            }
        }

        ksort($scripts);
        return $scripts;
    }

    private function getMigrationViewData(array $data = []): array
    {
        return array_merge(
            ['title' => 'Migrações rápidas'],
            $this->getMigrationStatus(),
            ['sqlScripts' => $this->getSqlScripts()],
            $data
        );
    }

    public function runSqlScript(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_level'] ?? '') !== 'ADMINISTRADOR') {
            header('Location: /admin/dashboard');
            return;
        }

        $token = $this->input('csrf_token');
        if (!$token || $token !== \App\Core\View::csrfToken()) {
            $this->view('admin.migracoes', $this->getMigrationViewData([
                'error' => 'CSRF inválido'
            ]));
            return;
        }

        $scriptFile = trim((string)$this->input('script_file'));
        $sqlText = trim((string)$this->input('sql_text'));

        if ($scriptFile === '' && $sqlText === '') {
            $this->view('admin.migracoes', $this->getMigrationViewData([
                'error' => 'Selecione um arquivo SQL ou informe o conteúdo manualmente.',
                'previous_script_file' => $scriptFile,
                'previous_sql_text' => $sqlText,
            ]));
            return;
        }

        try {
            $executadas = 0;
            $origens = [];

            if ($scriptFile !== '') {
                $path = $this->resolveSqlScriptPath($scriptFile);
                if (!$path || !is_file($path)) {
                    throw new \RuntimeException('Arquivo SQL selecionado não encontrado.');
                }
                $conteudoArquivo = file_get_contents($path);
                if ($conteudoArquivo === false) {
                    throw new \RuntimeException('Não foi possível ler o arquivo selecionado.');
                }
                $executadas += $this->executeSqlBatch($conteudoArquivo);
                $origens[] = $scriptFile;
            }

            if ($sqlText !== '') {
                $executadas += $this->executeSqlBatch($sqlText);
                $origens[] = 'SQL manual';
            }

            $descricaoOrigem = implode(' + ', $origens);
            if ($descricaoOrigem === '') {
                $descricaoOrigem = 'Script';
            }

            $this->view('admin.migracoes', $this->getMigrationViewData([
                'success' => sprintf('%s executado com sucesso (%d instrução(ões)).', $descricaoOrigem, $executadas)
            ]));
        } catch (\Throwable $e) {
            $this->view('admin.migracoes', $this->getMigrationViewData([
                'error' => 'Falha ao executar script: ' . $e->getMessage(),
                'previous_script_file' => $scriptFile,
                'previous_sql_text' => $sqlText,
            ]));
        }
    }

    private function resolveSqlScriptPath(string $relativePath): ?string
    {
        $basePath = dirname(__DIR__, 2);
        $fullPath = realpath($basePath . DIRECTORY_SEPARATOR . $relativePath);
        if ($fullPath === false) {
            return null;
        }

        $allowedDirs = [
            realpath($basePath . DIRECTORY_SEPARATOR . 'scripts'),
            realpath($basePath . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'migrations'),
        ];

        foreach ($allowedDirs as $allowedDir) {
            if ($allowedDir && strncmp($fullPath, $allowedDir, strlen($allowedDir)) === 0) {
                return $fullPath;
            }
        }

        return null;
    }

    private function executeSqlBatch(string $sql): int
    {
        $pdo = Database::getInstance();

        $clean = preg_replace('/^\s*(--|#).*$\r?$/m', '', $sql);
        $clean = preg_replace('/\/\*.*?\*\//s', '', $clean);

        $parts = preg_split('/;\s*(?:\r?\n|$)/', $clean);
        $executadas = 0;

        foreach ($parts as $statement) {
            $stmt = trim($statement);
            if ($stmt === '') {
                continue;
            }
            $pdo->exec($stmt);
            $executadas++;
        }

        return $executadas;
    }
}


