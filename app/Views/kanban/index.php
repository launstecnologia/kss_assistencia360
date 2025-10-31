<?php
$title = 'Kanban Board';
$currentPage = 'kanban';
$pageTitle = 'Kanban - Gerenciamento de Solicitações';
ob_start();
?>

<!-- Filtros -->
<div class="mb-6 bg-white rounded-lg shadow-sm p-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
        <form method="GET" action="<?= url('admin/kanban') ?>" class="flex gap-3">
            <select name="imobiliaria_id" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="this.form.submit()">
                <option value="">Todas as Imobiliárias</option>
                <?php foreach ($imobiliarias as $imob): ?>
                <option value="<?= $imob['id'] ?>" <?= $imobiliariaId == $imob['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($imob['nome']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($imobiliariaId): ?>
            <a href="<?= url('admin/kanban') ?>" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                <i class="fas fa-times mr-1"></i> Limpar Filtros
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Kanban Board -->
<div class="flex gap-4 overflow-x-auto pb-4">
    <?php foreach ($statusKanban as $status): ?>
    <div class="kanban-column flex-shrink-0 w-80 bg-gray-50 rounded-lg p-4">
        <!-- Header da Coluna -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-3 h-3 rounded-full mr-2" style="background-color: <?= $status['cor'] ?>"></div>
                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($status['nome']) ?></h3>
            </div>
            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">
                <?= count($solicitacoesPorStatus[$status['id']] ?? []) ?>
            </span>
        </div>
        
        <!-- Cards da Coluna -->
        <div class="kanban-cards space-y-3 min-h-32" data-status-id="<?= $status['id'] ?>">
            <?php 
            $solicitacoes = $solicitacoesPorStatus[$status['id']] ?? [];
            if (empty($solicitacoes)): 
            ?>
            <div class="text-center py-8 text-gray-400 text-sm">
                <i class="fas fa-inbox text-2xl mb-2 block"></i>
                Nenhuma solicitação
            </div>
            <?php else: ?>
                <?php foreach ($solicitacoes as $solicitacao): ?>
                <div class="kanban-card bg-white rounded-lg shadow-sm p-4 cursor-move hover:shadow-md transition-shadow border-l-4" 
                     style="border-color: <?= $status['cor'] ?>"
                     data-solicitacao-id="<?= $solicitacao['id'] ?>"
                     data-status-id="<?= $solicitacao['status_id'] ?>">
                    
                    <!-- Header do Card -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 text-sm">
                                <?= htmlspecialchars($solicitacao['numero_solicitacao'] ?? 'KSI' . $solicitacao['id']) ?>
                            </h4>
                        </div>
                        <button onclick="abrirDetalhes(<?= $solicitacao['id'] ?>)" 
                                class="text-gray-400 hover:text-gray-600 text-sm">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                    
                    <!-- Informações do Card -->
                    <div class="space-y-1 text-xs text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-wrench w-4 mr-1 text-gray-400"></i>
                            <span class="truncate"><?= htmlspecialchars($solicitacao['categoria_nome'] ?? 'Sem categoria') ?></span>
                        </div>
                        
                        <?php if (!empty($solicitacao['subcategoria_nome'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-list w-4 mr-1 text-gray-400"></i>
                            <span class="truncate"><?= htmlspecialchars($solicitacao['subcategoria_nome']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Tag Residencial/Comercial -->
                        <?php if (!empty($solicitacao['observacoes']) && strpos($solicitacao['observacoes'], 'Finalidade:') !== false): 
                            preg_match('/Finalidade:\s*(RESIDENCIAL|COMERCIAL)/i', $solicitacao['observacoes'], $matches);
                            if (!empty($matches[1])): ?>
                        <div class="my-2">
                            <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 rounded-md text-xs font-medium">
                                <?= htmlspecialchars($matches[1]) ?>
                            </span>
                        </div>
                        <?php endif; endif; ?>
                        
                        <div class="flex items-center">
                            <i class="fas fa-user w-4 mr-1 text-gray-400"></i>
                            <span class="truncate"><?= htmlspecialchars($solicitacao['locatario_nome'] ?? 'Não informado') ?></span>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt w-4 mr-1 text-gray-400"></i>
                            <span class="truncate">
                                <?php 
                                $endereco = '';
                                if (!empty($solicitacao['imovel_endereco'])) {
                                    $endereco = $solicitacao['imovel_endereco'];
                                    if (!empty($solicitacao['imovel_numero'])) {
                                        $endereco .= ', ' . $solicitacao['imovel_numero'];
                                    }
                                }
                                echo htmlspecialchars($endereco ?: 'Endereço não informado');
                                ?>
                            </span>
                        </div>
                        
                        <div class="flex items-center">
                            <i class="fas fa-calendar w-4 mr-1 text-gray-400"></i>
                            <span><?= date('d/m/Y', strtotime($solicitacao['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <!-- Prioridade -->
                    <?php if (isset($solicitacao['prioridade']) && $solicitacao['prioridade'] !== 'NORMAL'): ?>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                            <?= $solicitacao['prioridade'] === 'ALTA' ? 'bg-red-100 text-red-800' : 
                                ($solicitacao['prioridade'] === 'MEDIA' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') ?>">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <?= htmlspecialchars($solicitacao['prioridade']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Offcanvas para Detalhes -->
<div id="detalhesOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" onclick="fecharDetalhes()"></div>
    <div id="offcanvasPanel" class="fixed right-0 top-0 h-full w-full md:w-[90%] lg:w-[900px] bg-gray-50 shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-alt text-gray-600"></i>
                    <h2 class="text-xl font-bold text-gray-900">Detalhes da Solicitação</h2>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="copiarInformacoes()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-copy mr-2"></i>
                        Copiar Informações
                    </button>
                    <button onclick="fecharDetalhes()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="offcanvasContent" class="p-6">
            <div id="loadingContent" class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                    <p class="text-gray-600">Carregando detalhes...</p>
                </div>
            </div>
            <div id="detalhesContent" class="hidden"></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
// Inicializar Sortable em todas as colunas do Kanban
document.querySelectorAll('.kanban-cards').forEach(column => {
    new Sortable(column, {
        group: 'kanban',
        animation: 150,
        ghostClass: 'bg-blue-100',
        dragClass: 'opacity-50',
        handle: '.kanban-card',
        onEnd: function(evt) {
            const solicitacaoId = evt.item.getAttribute('data-solicitacao-id');
            const novoStatusId = evt.to.getAttribute('data-status-id');
            const antigoStatusId = evt.from.getAttribute('data-status-id');
            
            // Se moveu para a mesma coluna, não fazer nada
            if (novoStatusId === antigoStatusId) {
                return;
            }
            
            // Atualizar no servidor
            fetch('<?= url('admin/kanban/mover') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    solicitacao_id: solicitacaoId,
                    novo_status_id: novoStatusId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar o data-status-id do card
                    evt.item.setAttribute('data-status-id', novoStatusId);
                    
                    // Atualizar contadores
                    atualizarContadores();
                    
                    // Mostrar notificação
                    mostrarNotificacao('Status atualizado com sucesso!', 'success');
                } else {
                    // Reverter a movimentação
                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                    mostrarNotificacao('Erro: ' + (data.error || 'Não foi possível atualizar o status'), 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                // Reverter a movimentação
                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                mostrarNotificacao('Erro ao atualizar status', 'error');
            });
        }
    });
});

function atualizarContadores() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const cardsContainer = column.querySelector('.kanban-cards');
        const contador = column.querySelector('.bg-gray-200');
        const numCards = cardsContainer.querySelectorAll('.kanban-card').length;
        contador.textContent = numCards;
    });
}

function mostrarNotificacao(mensagem, tipo = 'info') {
    const cor = tipo === 'success' ? 'green' : tipo === 'error' ? 'red' : 'blue';
    const notificacao = document.createElement('div');
    notificacao.className = `fixed top-4 right-4 bg-${cor}-50 border border-${cor}-200 text-${cor}-700 px-4 py-3 rounded-lg shadow-lg z-50 transition-all`;
    notificacao.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
            <span>${mensagem}</span>
        </div>
    `;
    document.body.appendChild(notificacao);
    
    setTimeout(() => {
        notificacao.style.opacity = '0';
        setTimeout(() => notificacao.remove(), 300);
    }, 3000);
}

// Funções do Offcanvas (reutilizadas do Dashboard)
function abrirDetalhes(solicitacaoId) {
    const offcanvas = document.getElementById('detalhesOffcanvas');
    const panel = document.getElementById('offcanvasPanel');
    const loadingContent = document.getElementById('loadingContent');
    const detalhesContent = document.getElementById('detalhesContent');
    
    offcanvas.classList.remove('hidden');
    setTimeout(() => panel.classList.remove('translate-x-full'), 10);
    
    loadingContent.classList.remove('hidden');
    detalhesContent.classList.add('hidden');
    
    fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/api`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarDetalhes(data.solicitacao);
            } else {
                detalhesContent.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                        <p class="text-gray-600">${data.message || 'Erro ao carregar detalhes'}</p>
                    </div>
                `;
            }
            loadingContent.classList.add('hidden');
            detalhesContent.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Erro:', error);
            detalhesContent.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                    <p class="text-gray-600">Erro ao carregar detalhes da solicitação</p>
                </div>
            `;
            loadingContent.classList.add('hidden');
            detalhesContent.classList.remove('hidden');
        });
}

function fecharDetalhes() {
    const offcanvas = document.getElementById('detalhesOffcanvas');
    const panel = document.getElementById('offcanvasPanel');
    
    panel.classList.add('translate-x-full');
    setTimeout(() => offcanvas.classList.add('hidden'), 300);
}

function renderizarDetalhes(solicitacao) {
    const content = document.getElementById('detalhesContent');
    
    // Parse horários se existirem
    let horariosOpcoes = [];
    try {
        horariosOpcoes = solicitacao.horarios_opcoes ? JSON.parse(solicitacao.horarios_opcoes) : [];
    } catch (e) {
        horariosOpcoes = [];
    }
    
    content.innerHTML = `
        <!-- Cabeçalho com ID e Data -->
        <div class="bg-white rounded-lg p-5 mb-4">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">${solicitacao.numero_solicitacao || 'KS' + solicitacao.id}</div>
                    <div class="text-lg font-semibold text-gray-800">${solicitacao.categoria_nome}</div>
                    ${solicitacao.subcategoria_nome ? `<div class="text-sm text-gray-600 mt-1">${solicitacao.subcategoria_nome}</div>` : ''}
                </div>
                <div class="text-right text-sm text-gray-500">
                    ${formatarData(solicitacao.created_at)}
                </div>
            </div>
        </div>
        
        <!-- Layout 2 Colunas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- COLUNA ESQUERDA -->
            <div class="space-y-4">
                
                <!-- Informações do Cliente -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-user text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Informações do Cliente</h3>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Nome:</p>
                            <p class="font-medium text-gray-900">${solicitacao.locatario_nome}</p>
                        </div>
                        ${solicitacao.locatario_cpf ? `
                        <div>
                            <p class="text-gray-500 mb-1">CPF:</p>
                            <p class="font-medium text-gray-900">${solicitacao.locatario_cpf}</p>
                        </div>
                        ` : ''}
                        ${solicitacao.imobiliaria_nome ? `
                        <div>
                            <p class="text-gray-500 mb-1">Imobiliária:</p>
                            <p class="font-medium text-gray-900">${solicitacao.imobiliaria_nome}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <!-- Descrição do Problema -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-clipboard-list text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Descrição do Problema</h3>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded p-3 text-sm text-gray-900 min-h-[80px]">
                        ${solicitacao.descricao_problema || 'Nenhuma descrição fornecida.'}
                    </div>
                </div>
                
                <!-- Serviço -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-tools text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Informações do Serviço</h3>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Categoria:</p>
                            <p class="font-medium text-gray-900">${solicitacao.categoria_nome}</p>
                        </div>
                        ${solicitacao.subcategoria_nome ? `
                        <div>
                            <p class="text-gray-500 mb-1">Subcategoria:</p>
                            <p class="font-medium text-gray-900">${solicitacao.subcategoria_nome}</p>
                        </div>
                        ` : ''}
                        <div>
                            <p class="text-gray-500 mb-1">Prioridade:</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ${solicitacao.prioridade || 'NORMAL'}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Disponibilidade Informada -->
                ${horariosOpcoes.length > 0 ? `
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-clock text-gray-600"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">Disponibilidade Informada pelo Segurado</h3>
                            <p class="text-xs text-gray-500">Horários da Solicitação Inicial</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        ${horariosOpcoes.map((horario, index) => {
                            try {
                                const dt = new Date(horario);
                                const dia = String(dt.getDate()).padStart(2, '0');
                                const mes = String(dt.getMonth() + 1).padStart(2, '0');
                                const ano = dt.getFullYear();
                                const hora = String(dt.getHours()).padStart(2, '0');
                                const min = String(dt.getMinutes()).padStart(2, '0');
                                const faixaHora = hora + ':00-' + (parseInt(hora) + 3) + ':00';
                                return `
                                <div class="flex items-center gap-3 py-2">
                                    <input type="checkbox" class="w-4 h-4 text-blue-600 rounded" id="horario-${index}">
                                    <label for="horario-${index}" class="text-sm text-gray-700">${dia}/${mes}/${ano} - ${faixaHora}</label>
                                </div>
                                `;
                            } catch (e) {
                                return '';
                            }
                        }).join('')}
                    </div>
                    <div class="mt-4 pt-3 border-t">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm text-gray-700">Nenhum horário está disponível</span>
                        </label>
                    </div>
                </div>
                ` : ''}
                
                <!-- Anexar Documento -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-paperclip text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Anexar Documento</h3>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">(PDF, DOC, DOCX, JPG, PNG - máx 5MB)</p>
                    <button class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">
                        Anexar Documento
                    </button>
                    <p class="text-xs text-gray-500 mt-2">0/3 documentos</p>
                </div>
                
            </div>
            
            <!-- COLUNA DIREITA -->
            <div class="space-y-4">
                
                <!-- Endereço -->
                ${solicitacao.imovel_endereco ? `
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-map-marker-alt text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Endereço</h3>
                    </div>
                    <div class="text-sm text-gray-900">
                        <p class="font-medium">${solicitacao.imovel_endereco}${solicitacao.imovel_numero ? ', ' + solicitacao.imovel_numero : ''}</p>
                    </div>
                </div>
                ` : ''}
                
                <!-- Observações do Segurado -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-comment-dots text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Observações do Segurado</h3>
                    </div>
                    <textarea class="w-full bg-gray-50 border border-gray-200 rounded p-3 text-sm text-gray-700 min-h-[120px] resize-none" 
                              placeholder="Descreva qualquer situação adicional (ex: prestador não compareceu, precisa comprar peças, etc.)">${solicitacao.observacoes || ''}</textarea>
                </div>
                
                <!-- Precisa de Reembolso -->
                <div class="bg-white rounded-lg p-5">
                    <label class="flex items-center gap-2 mb-3">
                        <input type="checkbox" 
                               id="checkboxReembolso" 
                               class="w-4 h-4 text-blue-600 rounded" 
                               onchange="toggleCampoReembolso()"
                               ${solicitacao.precisa_reembolso ? 'checked' : ''}>
                        <span class="text-sm font-medium text-gray-900">Precisa de Reembolso?</span>
                    </label>
                    <div id="campoValorReembolso" class="${solicitacao.precisa_reembolso ? '' : 'hidden'} mt-3">
                        <label class="text-xs text-gray-600 mb-1 block">Valor do Reembolso (R$)</label>
                        <input type="text" 
                               id="valorReembolso"
                               placeholder="R$ 0,00" 
                               value="${solicitacao.valor_reembolso ? formatarValorMoeda(solicitacao.valor_reembolso) : ''}"
                               class="w-full bg-gray-50 border border-gray-200 rounded px-3 py-2 text-sm text-gray-900"
                               onkeyup="formatarMoeda(this)">
                    </div>
                </div>
                
                <!-- Status da Solicitação -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-info-circle text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Status da Solicitação</h3>
                    </div>
                    <select class="w-full bg-gray-50 border border-gray-200 rounded px-3 py-2 text-sm text-gray-900">
                        <option selected>${solicitacao.status_nome}</option>
                    </select>
                </div>
                
                <!-- Protocolo da Seguradora -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-hashtag text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Protocolo da Seguradora</h3>
                    </div>
                    <input type="text" 
                           id="protocoloSeguradora"
                           placeholder="Ex.: 123456/2025" 
                           value="${solicitacao.protocolo_seguradora || ''}"
                           class="w-full bg-gray-50 border border-gray-200 rounded px-3 py-2 text-sm text-gray-900">
                </div>
                
                <!-- Linha do Tempo -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-history text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Linha do Tempo</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-0.5 h-full bg-gray-200 mt-1"></div>
                            </div>
                            <div class="flex-1 pb-4">
                                <p class="font-medium text-sm text-gray-900">${solicitacao.status_nome}</p>
                                <p class="text-xs text-gray-500 mt-1">Por Sistema</p>
                                <p class="text-xs text-gray-500">Solicitação criada</p>
                                <p class="text-xs text-gray-400 mt-2">${formatarDataHora(solicitacao.created_at)}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Botões de Ação -->
        <div class="mt-6 flex gap-3">
            <button onclick="salvarAlteracoes(${solicitacao.id})" 
                    class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium transition-colors">
                <i class="fas fa-save"></i>
                Salvar Alterações
            </button>
            <a href="<?= url('admin/solicitacoes/') ?>${solicitacao.id}" 
               class="px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition-colors border border-gray-300">
                <i class="fas fa-external-link-square-alt mr-2"></i>
                Ver Página Completa
            </a>
            <button onclick="fecharDetalhes()" 
                    class="px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition-colors border border-gray-300">
                Fechar
            </button>
        </div>
    `;
}

function copiarInformacoes() {
    // TODO: Implementar função de copiar informações
    alert('Funcionalidade de copiar informações será implementada');
}

function toggleCampoReembolso() {
    const checkbox = document.getElementById('checkboxReembolso');
    const campo = document.getElementById('campoValorReembolso');
    
    if (checkbox.checked) {
        campo.classList.remove('hidden');
    } else {
        campo.classList.add('hidden');
        document.getElementById('valorReembolso').value = '';
    }
}

function formatarMoeda(input) {
    let valor = input.value.replace(/\D/g, '');
    valor = (parseFloat(valor) / 100).toFixed(2);
    
    if (isNaN(valor) || valor === '0.00') {
        input.value = '';
        return;
    }
    
    valor = valor.replace('.', ',');
    valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = 'R$ ' + valor;
}

function formatarValorMoeda(valor) {
    if (!valor || valor === 0) return '';
    
    let valorFormatado = parseFloat(valor).toFixed(2);
    valorFormatado = valorFormatado.replace('.', ',');
    valorFormatado = valorFormatado.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    return 'R$ ' + valorFormatado;
}

function salvarAlteracoes(solicitacaoId) {
    // Coletar dados do formulário
    const observacoes = document.querySelector('textarea[placeholder*="Descreva qualquer situação"]')?.value || '';
    const precisaReembolso = document.getElementById('checkboxReembolso')?.checked || false;
    
    // Pegar o valor do reembolso e converter corretamente
    let valorReembolso = '0';
    const inputValor = document.getElementById('valorReembolso')?.value || '';
    if (inputValor && inputValor.trim() !== '') {
        // Remove "R$", pontos (separador de milhar) e troca vírgula por ponto
        valorReembolso = inputValor.replace('R$', '').replace(/\s/g, '').replace(/\./g, '').replace(',', '.');
    }
    
    const protocoloSeguradora = document.getElementById('protocoloSeguradora')?.value || '';
    
    // Criar objeto com os dados
    const dados = {
        observacoes: observacoes,
        precisa_reembolso: precisaReembolso,
        valor_reembolso: valorReembolso,
        protocolo_seguradora: protocoloSeguradora
    };
    
    console.log('Dados a serem salvos:', dados); // Debug
    
    // Enviar para o servidor
    fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/atualizar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta do servidor:', data); // Debug
        if (data.success) {
            alert('Alterações salvas com sucesso!');
            fecharDetalhes();
            // Recarregar a página para atualizar os dados
            window.location.reload();
        } else {
            alert('Erro ao salvar: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar alterações. Tente novamente.');
    });
}

function formatarData(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function formatarDataHora(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}
</script>


