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

        // Verificar status das colunas
        $check = function(string $column): bool {
            $sql = "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solicitacoes' AND COLUMN_NAME = ?";
            $row = Database::fetch($sql, [$column]);
            return (int)($row['c'] ?? 0) > 0;
        };

        $hasDescricaoCard = $check('descricao_card');
        $hasHorarioConfirmado = $check('horario_confirmado');
        $hasHorarioRaw = $check('horario_confirmado_raw');
        $hasConfirmedSchedules = $check('confirmed_schedules');
        $hasDatasOpcoes = $check('datas_opcoes');

        $this->view('admin.migracoes', [
            'title' => 'Migrações rápidas',
            'hasDescricaoCard' => $hasDescricaoCard,
            'hasHorarioConfirmado' => $hasHorarioConfirmado,
            'hasHorarioRaw' => $hasHorarioRaw,
            'hasConfirmedSchedules' => $hasConfirmedSchedules,
            'hasDatasOpcoes' => $hasDatasOpcoes,
        ]);
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
            $this->view('admin.migracoes', [ 'error' => 'CSRF inválido' ]);
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

            $this->view('admin.migracoes', [ 'success' => 'Migrações executadas com sucesso.' ]);
        } catch (\Exception $e) {
            $this->view('admin.migracoes', [ 'error' => 'Falha ao executar: ' . $e->getMessage() ]);
        }
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
            $this->view('admin.migracoes', [ 'error' => 'CSRF inválido' ]);
            return;
        }
        if (strtoupper($confirm) !== 'LIMPAR') {
            $this->view('admin.migracoes', [ 'error' => 'Para confirmar, digite LIMPAR.' ]);
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

            $this->view('admin.migracoes', [ 'success' => 'Todas as solicitações foram limpas.' ]);
        } catch (\Exception $e) {
            $this->view('admin.migracoes', [ 'error' => 'Falha ao limpar: ' . $e->getMessage() ]);
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
}


