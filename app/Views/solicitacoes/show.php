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
            
            <div class="space-y-3">
                <?php foreach ($horariosOpcoes as $index => $horario): 
                    // Verificar se este horário é o confirmado
                    $horarioConfirmado = false;
                    if (!empty($solicitacao['data_agendamento']) && !empty($solicitacao['horario_agendamento'])) {
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
                                <?= date('d/m/Y - H:i', strtotime($horario)) ?>
                            </span>
                            <?php if ($horarioConfirmado): ?>
                                <span class="block text-xs text-green-700 font-semibold mt-1">
                                    <i class="fas fa-calendar-check mr-1"></i>Horário Confirmado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($horarioConfirmado): ?>
                        <button onclick="desconfirmarHorario(<?= $solicitacao['id'] ?>)"
                                class="px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                            <i class="fas fa-times mr-1"></i>Desconfirmar
                        </button>
                    <?php else: ?>
                        <button onclick="confirmarHorario(<?= $solicitacao['id'] ?>, '<?= $horario ?>')"
                                class="px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700">
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
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro: ' + (data.error || 'Não foi possível confirmar'));
        }
    });
}

function desconfirmarHorario(solicitacaoId) {
    if (!confirm('Desconfirmar horário? O agendamento será removido.')) {
        return;
    }
    
    fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/desconfirmar-horario`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Horário desconfirmado com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.error || 'Não foi possível desconfirmar'));
        }
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
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Solicitação enviada! O locatário receberá notificação para informar novos horários.');
                location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Não foi possível solicitar'));
            }
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>
