<?php
/**
 * View: Detalhes da Solicitação
 */
$title = 'Solicitação #' . ($solicitacao['numero_solicitacao'] ?? $solicitacao['id']);
$currentPage = 'solicitacoes';
$pageTitle = 'Detalhes da Solicitação';
ob_start();

// Helper para valores seguros
function safe($value, $default = 'Não informado') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}
?>

<style>
.section-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.timeline-item {
    position: relative;
    padding-left: 2rem;
    padding-bottom: 1.5rem;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-dot {
    position: absolute;
    left: 0;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #3B82F6;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #3B82F6;
}
.timeline-line {
    position: absolute;
    left: 5px;
    top: 12px;
    bottom: -1.5rem;
    width: 2px;
    background: #E5E7EB;
}
</style>

<!-- Header -->
<div class="bg-gray-800 -mx-6 -mt-6 px-6 py-4 mb-6 rounded-t-lg">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-white">Detalhes da Solicitação</h1>
        <div class="flex gap-2">
            <button onclick="copiarInformacoes()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 text-sm">
                <i class="fas fa-copy mr-2"></i>
                Copiar Informações
            </button>
            <a href="<?= url('admin/solicitacoes') ?>" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </div>
</div>

<!-- Info Box -->
<div class="bg-gray-50 p-4 rounded-lg mb-6 flex items-start justify-between">
    <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
            <h2 class="text-xl font-bold text-gray-900">
                <?= safe($solicitacao['numero_solicitacao'] ?? '#'.$solicitacao['id'], '#'.$solicitacao['id']) ?>
            </h2>
            <span class="px-3 py-1 rounded-full text-sm font-medium" 
                  style="background-color: <?= $solicitacao['status_cor'] ?? '#3B82F6' ?>20; color: <?= $solicitacao['status_cor'] ?? '#3B82F6' ?>">
                <?= safe($solicitacao['status_nome'], 'Sem status') ?>
            </span>
        </div>
        <p class="text-lg font-medium text-gray-700">
            <?= safe($solicitacao['categoria_nome'], 'Sem categoria') ?>
            <?php if (!empty($solicitacao['subcategoria_nome'])): ?>
            - <?= safe($solicitacao['subcategoria_nome']) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="text-right text-sm text-gray-500">
        <i class="fas fa-calendar mr-1"></i>
        <?= date('d/m/Y', strtotime($solicitacao['created_at'])) ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Coluna Principal (2/3) -->
    <div class="lg:col-span-2 space-y-4">
        
        <!-- Informações do Cliente -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-user text-blue-600"></i>
                Informações do Cliente
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Nome</label>
                    <p class="text-sm font-medium text-gray-900"><?= safe($solicitacao['locatario_nome']) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">CPF</label>
                    <p class="text-sm font-medium text-gray-900"><?= safe($solicitacao['locatario_cpf']) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Telefone</label>
                    <p class="text-sm font-medium text-gray-900">
                        <?php if (!empty($solicitacao['locatario_telefone'])): ?>
                        <a href="https://wa.me/55<?= preg_replace('/[^0-9]/', '', $solicitacao['locatario_telefone']) ?>" 
                           target="_blank" class="text-green-600 hover:text-green-800">
                            <i class="fab fa-whatsapp mr-1"></i>
                            <?= safe($solicitacao['locatario_telefone']) ?>
                        </a>
                        <?php else: ?>
                        Não informado
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Imobiliária</label>
                    <p class="text-sm font-medium text-gray-900"><?= safe($solicitacao['imobiliaria_nome']) ?></p>
                </div>
            </div>
        </div>

        <!-- Descrição do Problema -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-clipboard-list text-blue-600"></i>
                Descrição do Problema
            </div>
            
            <?php 
            // Verificar se há horário confirmado
            $temHorarioConfirmado = !empty($solicitacao['data_agendamento']) && !empty($solicitacao['horario_agendamento']);
            ?>
            
            <div class="<?= $temHorarioConfirmado ? 'bg-green-50 border-2 border-green-500' : 'bg-gray-50 border border-gray-200' ?> p-4 rounded transition-all">
                <?php if ($temHorarioConfirmado): ?>
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-600 text-lg"></i>
                            <span class="text-xs text-green-700 font-semibold">
                                <i class="fas fa-calendar-check mr-1"></i>Serviço Agendado - Descrição Confirmada
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <p class="text-sm <?= $temHorarioConfirmado ? 'text-green-900' : 'text-gray-900' ?> whitespace-pre-wrap">
                    <?= safe($solicitacao['descricao_problema'], 'Nenhuma descrição fornecida.') ?>
                </p>
            </div>
        </div>

        <!-- Disponibilidade Informada pelo Locatário -->
        <?php 
        $horariosOpcoes = !empty($solicitacao['horarios_opcoes']) 
            ? json_decode($solicitacao['horarios_opcoes'], true) : [];
        if (!empty($horariosOpcoes)): 
        ?>
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-clock text-blue-600"></i>
                <div>
                    <div class="font-semibold">Disponibilidade Informada pelo Locatário</div>
                    <div class="text-xs font-normal text-gray-500 mt-0.5">Horários da Solicitação Inicial</div>
                </div>
            </div>
            
            <div class="space-y-3" id="lista-horarios">
                <?php foreach ($horariosOpcoes as $index => $horario): 
                    // ✅ Verificar se este horário é o confirmado (múltiplas fontes)
                    $horarioConfirmado = false;
                    
                    // Formatar horário atual para comparação (mesmo formato do offcanvas)
                    // Formato esperado: "dd/mm/yyyy - HH:00-HH:00"
                    $horarioFormatado = $horario;
                    
                    // DEBUG: Log para verificar formato original
                    error_log("DEBUG show.php [ID:{$solicitacao['id']}] - Horário original do array: " . var_export($horario, true));
                    error_log("DEBUG show.php [ID:{$solicitacao['id']}] - horario_confirmado_raw do banco: " . var_export($solicitacao['horario_confirmado_raw'] ?? null, true));
                    error_log("DEBUG show.php [ID:{$solicitacao['id']}] - confirmed_schedules do banco: " . var_export($solicitacao['confirmed_schedules'] ?? null, true));
                    
                    // Tentar diferentes formatos de entrada
                    $dt = null;
                    if (is_string($horario) && is_numeric(strtotime($horario))) {
                        // Formato ISO ou similar
                        try {
                            $dt = new \DateTime($horario);
                        } catch (\Exception $e) {
                            error_log("DEBUG show.php [ID:{$solicitacao['id']}] - Erro ao criar DateTime: " . $e->getMessage());
                        }
                    } elseif (is_string($horario) && preg_match('/(\d{4}-\d{2}-\d{2})[T ](\d{2}):(\d{2})/', $horario, $matches)) {
                        // Formato ISO com T ou espaço
                        try {
                            $dt = new \DateTime($matches[1] . ' ' . $matches[2] . ':' . $matches[3]);
                        } catch (\Exception $e) {
                            error_log("DEBUG show.php [ID:{$solicitacao['id']}] - Erro ao criar DateTime ISO: " . $e->getMessage());
                        }
                    } elseif (is_string($horario) && preg_match('/(\d{2})\/(\d{2})\/(\d{4})[ -](\d{2}):(\d{2})/', $horario, $matches)) {
                        // Formato dd/mm/yyyy HH:MM
                        try {
                            $dt = \DateTime::createFromFormat('d/m/Y H:i', $matches[1] . '/' . $matches[2] . '/' . $matches[3] . ' ' . $matches[4] . ':' . $matches[5]);
                        } catch (\Exception $e) {
                            error_log("DEBUG show.php [ID:{$solicitacao['id']}] - Erro ao criar DateTime dd/mm/yyyy: " . $e->getMessage());
                        }
                    }
                    
                    if ($dt && $dt !== false) {
                        $dia = str_pad($dt->format('d'), 2, '0', STR_PAD_LEFT);
                        $mes = str_pad($dt->format('m'), 2, '0', STR_PAD_LEFT);
                        $ano = $dt->format('Y');
                        $hora = str_pad($dt->format('H'), 2, '0', STR_PAD_LEFT);
                        $horaFim = str_pad((int)$hora + 3, 2, '0', STR_PAD_LEFT);
                        $horarioFormatado = "{$dia}/{$mes}/{$ano} - {$hora}:00-{$horaFim}:00";
                    } else {
                        // Se não conseguir formatar, usar o original
                        $horarioFormatado = $horario;
                    }
                    
                    // DEBUG: Log do formato final
                    error_log("DEBUG show.php [ID:{$solicitacao['id']}] - Horário formatado FINAL: '{$horarioFormatado}'");
                    
                    // 1. Verificar em confirmed_schedules (JSON) - prioridade
                    // ✅ confirmed_schedules já vem parseado do controller (pode ser array ou null)
                    if (!empty($solicitacao['confirmed_schedules']) && is_array($solicitacao['confirmed_schedules'])) {
                        foreach ($solicitacao['confirmed_schedules'] as $schedule) {
                            if (!isset($schedule) || !is_array($schedule)) continue;
                            
                            // Comparar por raw (prioridade) - formato "dd/mm/yyyy - HH:00-HH:00"
                            if (!empty($schedule['raw'])) {
                                $scheduleRaw = trim((string)$schedule['raw']);
                                $horarioAtual = trim((string)$horarioFormatado);
                                
                                // Normalizar espaços para comparação
                                $scheduleRawNorm = preg_replace('/\s+/', ' ', $scheduleRaw);
                                $horarioAtualNorm = preg_replace('/\s+/', ' ', $horarioAtual);
                                
                                // ✅ Comparação exata primeiro (mais precisa)
                                if ($scheduleRawNorm === $horarioAtualNorm) {
                                    $horarioConfirmado = true;
                                    break; // ✅ Break imediato para evitar verificar outros
                                }
                                
                                // ✅ Comparação por regex - extrair data e hora inicial E FINAL EXATAS
                                // Isso evita matches parciais incorretos
                                $regex = '/(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}:\d{2})-(\d{2}:\d{2})/';
                                $matchRaw = preg_match($regex, $scheduleRawNorm, $mRaw);
                                $matchAtual = preg_match($regex, $horarioAtualNorm, $mAtual);
                                
                                if ($matchRaw && $matchAtual) {
                                    // ✅ Comparar data, hora inicial E hora final EXATAS (não apenas data e hora inicial)
                                    // Isso garante que apenas horários EXATOS sejam marcados como confirmados
                                    if ($mRaw[1] === $mAtual[1] && $mRaw[2] === $mAtual[2] && $mRaw[3] === $mAtual[3]) {
                                        $horarioConfirmado = true;
                                        break; // ✅ Break imediato para evitar verificar outros
                                    }
                                }
                                
                                // ❌ REMOVIDO: Comparação por substring (muito flexível, causava matches incorretos)
                            }
                            
                            // Comparar por date + time se raw não funcionar (comparação EXATA)
                            if (!$horarioConfirmado && !empty($schedule['date']) && !empty($schedule['time'])) {
                                try {
                                    $scheduleDate = new \DateTime($schedule['date']);
                                    $scheduleTime = trim((string)$schedule['time']);
                                    
                                    // Comparar data
                                    if ($dt && $scheduleDate->format('Y-m-d') === $dt->format('Y-m-d')) {
                                        // ✅ Comparar hora inicial E FINAL EXATAS (não apenas hora inicial)
                                        $horaAtual = $dt->format('H:i');
                                        $horaFimAtual = date('H:i', strtotime('+3 hours', $dt->getTimestamp()));
                                        $timeEsperado = $horaAtual . '-' . $horaFimAtual;
                                        
                                        // ✅ Comparação EXATA do time (deve ser exatamente igual)
                                        if ($scheduleTime === $timeEsperado) {
                                            $horarioConfirmado = true;
                                            break;
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Ignorar erro de data
                                }
                            }
                        }
                    }
                    
                    // 2. Verificar em horario_confirmado_raw (se ainda não confirmado)
                    if (!$horarioConfirmado && !empty($solicitacao['horario_confirmado_raw'])) {
                        $horarioRaw = trim((string)$solicitacao['horario_confirmado_raw']);
                        $horarioAtual = trim((string)$horarioFormatado);
                        
                        // Normalizar espaços para comparação
                        $rawNorm = preg_replace('/\s+/', ' ', $horarioRaw);
                        $atualNorm = preg_replace('/\s+/', ' ', $horarioAtual);
                        
                        // ✅ Comparação exata primeiro (mais precisa)
                        if ($rawNorm === $atualNorm) {
                            $horarioConfirmado = true;
                        } else {
                            // ✅ Comparação por regex - extrair data e hora inicial E FINAL EXATAS
                            // Isso evita matches parciais incorretos (ex: "08:00" matchando com "08:00-11:00")
                            $regex = '/(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}:\d{2})-(\d{2}:\d{2})/';
                            $matchRaw = preg_match($regex, $rawNorm, $mRaw);
                            $matchAtual = preg_match($regex, $atualNorm, $mAtual);
                            
                            if ($matchRaw && $matchAtual) {
                                // ✅ Comparar data, hora inicial E hora final EXATAS (não apenas data e hora inicial)
                                // Isso garante que apenas horários EXATOS sejam marcados como confirmados
                                if ($mRaw[1] === $mAtual[1] && $mRaw[2] === $mAtual[2] && $mRaw[3] === $mAtual[3]) {
                                    $horarioConfirmado = true;
                                }
                            }
                            
                            // ❌ REMOVIDO: Comparação por substring (muito flexível, causava matches incorretos)
                        }
                    }
                    
                    // 3. Verificar em data_agendamento + horario_agendamento (se ainda não confirmado)
                    if (!$horarioConfirmado && !empty($solicitacao['data_agendamento']) && !empty($solicitacao['horario_agendamento'])) {
                        $dataHoraConfirmada = $solicitacao['data_agendamento'] . ' ' . $solicitacao['horario_agendamento'];
                        $dataHoraAtual = date('Y-m-d H:i:s', strtotime($horario));
                        $horarioConfirmado = (date('Y-m-d H:i', strtotime($dataHoraConfirmada)) === date('Y-m-d H:i', strtotime($dataHoraAtual)));
                    }
                ?>
                <div class="<?= $horarioConfirmado ? 'bg-green-50 border-2 border-green-500' : 'bg-blue-50 border border-blue-200' ?> rounded-lg p-4 flex items-center justify-between transition-all">
                    <div class="flex items-center gap-3">
                        <?php if ($horarioConfirmado): ?>
                            <i class="fas fa-check-circle text-green-600 text-lg"></i>
                        <?php else: ?>
                            <i class="fas fa-clock text-blue-600"></i>
                        <?php endif; ?>
                        <div>
                            <span class="text-sm font-medium <?= $horarioConfirmado ? 'text-green-900' : '' ?>">
                                <?= htmlspecialchars($horarioFormatado) ?>
                            </span>
                            <?php if ($horarioConfirmado): ?>
                                <span class="block text-xs text-green-700 font-semibold mt-1">
                                    <i class="fas fa-calendar-check mr-1"></i>Horário Confirmado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($horarioConfirmado): 
                        // ✅ Preparar horário para desconfirmação (usar formato normalizado)
                        $horarioParaDesconfirmar = $horarioFormatado;
                        if ($dt && $dt !== false) {
                            $dia = str_pad($dt->format('d'), 2, '0', STR_PAD_LEFT);
                            $mes = str_pad($dt->format('m'), 2, '0', STR_PAD_LEFT);
                            $ano = $dt->format('Y');
                            $hora = str_pad($dt->format('H'), 2, '0', STR_PAD_LEFT);
                            $horaFim = str_pad((int)$hora + 3, 2, '0', STR_PAD_LEFT);
                            $horarioParaDesconfirmar = "{$dia}/{$mes}/{$ano} - {$hora}:00-{$horaFim}:00";
                        }
                        $horarioEscapadoDesconfirmar = htmlspecialchars($horarioParaDesconfirmar, ENT_QUOTES, 'UTF-8');
                    ?>
                        <button onclick="desconfirmarHorario(<?= $solicitacao['id'] ?>, '<?= $horarioEscapadoDesconfirmar ?>')" class="px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                            <i class="fas fa-times mr-1"></i>Desconfirmar
                        </button>
                    <?php else: 
                        // ✅ Preparar horário para JavaScript (converter para formato ISO se possível)
                        $horarioParaJS = $horario;
                        if ($dt && $dt !== false) {
                            // Se temos um DateTime válido, usar formato ISO que strtotime() aceita
                            $horarioParaJS = $dt->format('Y-m-d H:i:s');
                        } elseif (is_string($horario) && preg_match('/(\d{2})\/(\d{2})\/(\d{4})[ -](\d{2}):(\d{2})/', $horario, $matches)) {
                            // Converter formato dd/mm/yyyy HH:MM para Y-m-d H:i:s
                            $horarioParaJS = sprintf('%s-%s-%s %s:%s:00', $matches[3], $matches[2], $matches[1], $matches[4], $matches[5]);
                        }
                        // Escapar para JavaScript (escapar aspas simples)
                        $horarioEscapado = htmlspecialchars($horarioParaJS, ENT_QUOTES, 'UTF-8');
                    ?>
                        <button onclick="confirmarHorario(<?= $solicitacao['id'] ?>, '<?= $horarioEscapado ?>')" class="px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                            <i class="fas fa-check mr-1"></i>Confirmar horário
                        </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            
            <!-- Checkbox: Horários Indisponíveis -->
            <div class="mt-4 border-t pt-4">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="horarios-indisponiveis" 
                           onchange="toggleSolicitarNovosHorarios(<?= $solicitacao['id'] ?>)"
                           class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">Horários Indisponíveis - Solicitar novos horários</span>
                </label>
            </div>
        </div>
        <?php endif; ?>

        <!-- Serviço -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-tools text-blue-600"></i>
                Informações do Serviço
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Categoria</label>
                    <p class="text-sm font-medium text-gray-900"><?= safe($solicitacao['categoria_nome']) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Subcategoria</label>
                    <p class="text-sm font-medium text-gray-900"><?= safe($solicitacao['subcategoria_nome']) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Prioridade</label>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        <?= ($solicitacao['prioridade'] ?? 'NORMAL') == 'ALTA' ? 'bg-red-100 text-red-800' : 
                           (($solicitacao['prioridade'] ?? 'NORMAL') == 'MEDIA' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') ?>">
                        <?= safe($solicitacao['prioridade'], 'NORMAL') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Agendamento -->
        <?php if (!empty($solicitacao['data_agendamento'])): ?>
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-calendar-check text-blue-600"></i>
                Disponibilidade Informada
            </div>
            <div class="bg-gray-50 p-3 rounded border border-gray-200 flex items-center">
                <i class="fas fa-clock text-gray-400 mr-3"></i>
                <span class="text-sm font-medium">
                    <?= date('d/m/Y', strtotime($solicitacao['data_agendamento'])) ?>
                    <?php if (!empty($solicitacao['horario_agendamento'])): ?>
                    - <?= safe($solicitacao['horario_agendamento']) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Coluna Lateral (1/3) -->
    <div class="space-y-4">
        
        <!-- Endereço -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-map-marker-alt text-blue-600"></i>
                Endereço
            </div>
            <div class="text-sm text-gray-900">
                <p class="font-medium">
                    <?= safe($solicitacao['imovel_endereco']) ?>, <?= safe($solicitacao['imovel_numero'], 's/n') ?>
                </p>
                <?php if (!empty($solicitacao['imovel_complemento'])): ?>
                <p class="text-gray-600"><?= safe($solicitacao['imovel_complemento']) ?></p>
                <?php endif; ?>
                <p class="text-gray-600 mt-1">
                    <?= safe($solicitacao['imovel_bairro'], '') ?><br>
                    <?= safe($solicitacao['imovel_cidade'], '') ?><?= !empty($solicitacao['imovel_estado']) ? '/' . safe($solicitacao['imovel_estado']) : '' ?>
                    <?php if (!empty($solicitacao['imovel_cep'])): ?>
                    <br>CEP: <?= safe($solicitacao['imovel_cep']) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Observações da Seguradora -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-sticky-note text-blue-600"></i>
                Observações da Seguradora
            </div>
            <form method="POST" action="<?= url("admin/solicitacoes/{$solicitacao['id']}/observacoes") ?>">
                <textarea name="observacoes" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                          placeholder="Adicione observações da seguradora..."><?= safe($solicitacao['observacoes'] ?? '', '') ?></textarea>
                <button type="submit" class="mt-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Observações
                </button>
            </form>
        </div>

        <!-- Status -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-exchange-alt text-blue-600"></i>
                Status da Solicitação
            </div>
            <button onclick="abrirModalStatus()" 
                    class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 text-sm text-gray-600 hover:text-blue-600">
                <i class="fas fa-edit mr-2"></i>
                Alterar Status
            </button>
        </div>

        <!-- Prestador -->
        <?php if (!empty($solicitacao['prestador_nome'])): ?>
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-user-tie text-blue-600"></i>
                Prestador de Serviço
            </div>
            <div class="text-sm">
                <p class="font-medium text-gray-900"><?= safe($solicitacao['prestador_nome']) ?></p>
                <?php if (!empty($solicitacao['prestador_telefone'])): ?>
                <p class="text-gray-600 mt-1">
                    <i class="fas fa-phone mr-1"></i>
                    <?= safe($solicitacao['prestador_telefone']) ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($solicitacao['valor_orcamento']) && $solicitacao['valor_orcamento'] > 0): ?>
                <p class="text-green-600 font-semibold mt-2">
                    R$ <?= number_format($solicitacao['valor_orcamento'], 2, ',', '.') ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Timeline -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-history text-blue-600"></i>
                Linha do Tempo
            </div>
            <?php if (!empty($historico)): ?>
            <div class="space-y-0 max-h-96 overflow-y-auto pr-2">
                <?php foreach ($historico as $index => $item): ?>
                <div class="timeline-item">
                    <?php if ($index < count($historico) - 1): ?>
                    <div class="timeline-line"></div>
                    <?php endif; ?>
                    <div class="timeline-dot" style="background-color: <?= $item['status_cor'] ?? '#3B82F6' ?>; box-shadow: 0 0 0 2px <?= $item['status_cor'] ?? '#3B82F6' ?>;"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?= safe($item['status_nome'] ?? '', 'Status') ?></p>
                        <?php if (!empty($item['observacao'])): ?>
                        <p class="text-xs text-gray-500 mt-1"><?= safe($item['observacao']) ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-400 mt-1">
                            <?= date('d/m/Y, H:i', strtotime($item['created_at'])) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-sm text-gray-500">Nenhum histórico disponível</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Status -->
<div id="modalStatus" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-8 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Alterar Status</h3>
            <button onclick="fecharModalStatus()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form onsubmit="salvarStatus(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Novo Status</label>
                <select id="novoStatus" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione um status</option>
                    <?php foreach ($statusDisponiveis as $status): ?>
                    <option value="<?= $status['id'] ?>" <?= $status['id'] == $solicitacao['status_id'] ? 'disabled' : '' ?>>
                        <?= safe($status['nome'] ?? '') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                <textarea id="observacaoStatus" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                          placeholder="Adicione uma observação..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="fecharModalStatus()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalStatus() {
    document.getElementById('modalStatus').classList.remove('hidden');
}

function fecharModalStatus() {
    document.getElementById('modalStatus').classList.add('hidden');
}

function salvarStatus(event) {
    event.preventDefault();
    
    const statusId = document.getElementById('novoStatus').value;
    const observacao = document.getElementById('observacaoStatus').value;
    
    if (!statusId) {
        alert('Por favor, selecione um status');
        return;
    }
    
    fetch(`<?= url("admin/solicitacoes/{$solicitacao['id']}/status") ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            status_id: statusId,
            observacao: observacao
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro: ' + (data.error || 'Não foi possível atualizar o status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao atualizar status');
    });
}

function copiarInformacoes() {
    const info = `SOLICITAÇÃO: <?= $solicitacao['numero_solicitacao'] ?? '#'.$solicitacao['id'] ?>
STATUS: <?= $solicitacao['status_nome'] ?? '' ?>
CATEGORIA: <?= $solicitacao['categoria_nome'] ?? '' ?>
CLIENTE: <?= $solicitacao['locatario_nome'] ?? '' ?>
TELEFONE: <?= $solicitacao['locatario_telefone'] ?? '' ?>
ENDEREÇO: <?= $solicitacao['imovel_endereco'] ?? '' ?>, <?= $solicitacao['imovel_numero'] ?? '' ?>
DESCRIÇÃO: <?= $solicitacao['descricao_problema'] ?? '' ?>
DATA: <?= date('d/m/Y H:i', strtotime($solicitacao['created_at'])) ?>`.trim();
    
    navigator.clipboard.writeText(info).then(() => {
        alert('Informações copiadas!');
    });
}

document.getElementById('modalStatus')?.addEventListener('click', function(e) {
    if (e.target === this) fecharModalStatus();
});

function confirmarHorario(solicitacaoId, horario) {
    if (!confirm('Confirmar este horário? O status será alterado para "Serviço Agendado".')) {
        return;
    }
    
    fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/confirmar-horario`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ horario: horario })
    })
    .then(async response => {
        // ✅ Verificar se a resposta é JSON válido antes de parsear
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            throw new Error('Resposta do servidor não é JSON válido. Verifique o console para mais detalhes.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Horário confirmado com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.error || 'Não foi possível confirmar'));
        }
    })
    .catch(error => {
        console.error('Erro ao confirmar horário:', error);
        alert('Erro ao confirmar horário: ' + error.message);
    });
}

// (interações extras removidas - confirmação/desconfirmação ocorre por botão de cada item ou via Kanban)

function desconfirmarHorario(solicitacaoId, horario) {
    if (!confirm('Desconfirmar este horário? O agendamento será removido.')) {
        return;
    }
    
    fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/desconfirmar-horario`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ horario: horario })
    })
    .then(async response => {
        // ✅ Verificar se a resposta é JSON válido antes de parsear
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            throw new Error('Resposta do servidor não é JSON válido. Verifique o console para mais detalhes.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Horário desconfirmado com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.error || 'Não foi possível desconfirmar'));
        }
    })
    .catch(error => {
        console.error('Erro ao desconfirmar horário:', error);
        alert('Erro ao desconfirmar horário: ' + error.message);
    });
}

function toggleSolicitarNovosHorarios(solicitacaoId) {
    const checked = document.getElementById('horarios-indisponiveis').checked;
    
    if (checked) {
        const obs = prompt('Por favor, informe o motivo dos horários estarem indisponíveis:');
        if (!obs) {
            document.getElementById('horarios-indisponiveis').checked = false;
            return;
        }
        
        fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/solicitar-novos-horarios`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ observacao: obs })
        })
        .then(async response => {
            // ✅ Verificar se a resposta é JSON válido antes de parsear
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Resposta não é JSON:', text);
                throw new Error('Resposta do servidor não é JSON válido. Verifique o console para mais detalhes.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Solicitação enviada! O locatário receberá notificação para informar novos horários.');
                location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Não foi possível solicitar'));
            }
        })
        .catch(error => {
            console.error('Erro ao solicitar novos horários:', error);
            alert('Erro ao solicitar novos horários: ' + error.message);
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>
