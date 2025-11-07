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
                <div class="kanban-card bg-white rounded-lg shadow-sm p-4 cursor-pointer hover:shadow-md transition-shadow border-l-4" 
                     style="border-color: <?= $status['cor'] ?>"
                     data-solicitacao-id="<?= $solicitacao['id'] ?>"
                     data-status-id="<?= $solicitacao['status_id'] ?>">
                    
                    <!-- Header do Card -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-semibold text-gray-900 text-sm">
                                    <?= htmlspecialchars($solicitacao['numero_solicitacao'] ?? 'KSI' . $solicitacao['id']) ?>
                                </h4>
                                <?php if (!empty($solicitacao['numero_contrato'])): ?>
                                    <span class="text-xs text-gray-500">
                                        Contrato: <?= htmlspecialchars($solicitacao['numero_contrato']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center text-xs text-gray-600 mt-1">
                                <i class="fas fa-wrench w-3 mr-1 text-gray-400"></i>
                                <span class="truncate"><?= htmlspecialchars($solicitacao['categoria_nome'] ?? 'Sem categoria') ?></span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <?php if (!empty($solicitacao['imobiliaria_logo'])): ?>
                                <img src="<?= url('Public/uploads/logos/' . $solicitacao['imobiliaria_logo']) ?>" 
                                     alt="<?= htmlspecialchars($solicitacao['imobiliaria_nome'] ?? 'Imobiliária') ?>" 
                                     class="h-7 w-auto"
                                     onerror="this.style.display='none';">
                            <?php endif; ?>
                            <?php if (!empty($solicitacao['condicao_nome'])): ?>
                                <span class="inline-block px-2 py-0.5 rounded-md text-xs font-medium" 
                                      style="background-color: <?= htmlspecialchars($solicitacao['condicao_cor'] ?? '#6B7280') ?>20; color: <?= htmlspecialchars($solicitacao['condicao_cor'] ?? '#6B7280') ?>">
                                    <?= htmlspecialchars($solicitacao['condicao_nome']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Informações do Card -->
                    <div class="space-y-1 text-xs text-gray-600">
                        
                        <?php if (!empty($solicitacao['subcategoria_nome'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-list w-4 mr-1 text-gray-400"></i>
                            <span class="truncate"><?= htmlspecialchars($solicitacao['subcategoria_nome']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Protocolo da Seguradora -->
                        <?php if (!empty($solicitacao['protocolo_seguradora'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-hashtag w-4 mr-1 text-gray-400"></i>
                            <span class="truncate text-xs text-gray-500">
                                Protocolo: <?= htmlspecialchars($solicitacao['protocolo_seguradora']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
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

<script>
// Permitir drag visual mas fazer voltar e mostrar mensagem
document.querySelectorAll('.kanban-card').forEach(card => {
    // Adicionar cursor pointer para indicar que é clicável
    card.style.cursor = 'pointer';
    
    // Permitir drag visual
    card.setAttribute('draggable', 'true');
    
    let originalColumn = null;
    let originalIndex = -1;
    let isDragging = false;
    
    // Quando começar a arrastar
    card.addEventListener('dragstart', function(e) {
        isDragging = true;
        originalColumn = this.parentElement;
        
        // Guardar a posição original (índice entre os irmãos)
        const cards = Array.from(originalColumn.children);
        originalIndex = cards.indexOf(this);
        
        // Adicionar classe visual de arrastando
        this.style.opacity = '0.5';
        this.style.cursor = 'grabbing';
        
        // Armazenar dados do drag
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.outerHTML);
    });
    
    // Quando terminar de arrastar
    card.addEventListener('dragend', function(e) {
        isDragging = false;
        this.style.opacity = '1';
        this.style.cursor = 'pointer';
        
        // Sempre fazer o card voltar para a posição original com animação
        if (originalColumn && originalIndex >= 0) {
            const currentColumn = this.parentElement;
            const currentCards = Array.from(currentColumn.children);
            const originalCards = Array.from(originalColumn.children);
            
            // Verificar se o card foi movido
            const wasMoved = currentColumn !== originalColumn || 
                           (currentColumn === originalColumn && currentCards.indexOf(this) !== originalIndex);
            
            if (wasMoved) {
                this.style.transition = 'all 0.3s ease-in-out';
                
                // Voltar para a posição original
                // Remover o card de onde está agora (se estiver em outra coluna)
                if (currentColumn !== originalColumn && currentColumn.contains(this)) {
                    this.remove();
                }
                
                // Obter lista atualizada de cards da coluna original (sem o card que está sendo movido)
                const cardsInOriginal = Array.from(originalColumn.children).filter(c => c !== this);
                
                // Inserir na posição original
                if (originalIndex < cardsInOriginal.length) {
                    // Inserir antes do card que está na posição original
                    originalColumn.insertBefore(this, cardsInOriginal[originalIndex]);
                } else {
                    // Se o índice é maior que o número de cards, adicionar no final
                    originalColumn.appendChild(this);
                }
                
                // Remover transição após animação
                setTimeout(() => {
                    this.style.transition = '';
                }, 300);
            }
        }
        
        // Mostrar mensagem informativa
        mostrarNotificacao('Para alterar o status, clique no card e altere pelo modal de detalhes', 'info');
    });
});

// Permitir visualmente o drag sobre as colunas, mas não fazer nada
document.querySelectorAll('.kanban-cards').forEach(column => {
    column.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        // Adicionar feedback visual de que está sobre a coluna
        this.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
        return false;
    });
    
    column.addEventListener('dragleave', function(e) {
        // Remover feedback visual
        this.style.backgroundColor = '';
    });
    
    column.addEventListener('drop', function(e) {
        e.preventDefault();
        
        // Remover feedback visual
        this.style.backgroundColor = '';
        
        // Não fazer nada - o dragend já vai fazer o card voltar
        // Mas garantir que o card não seja inserido aqui
        return false;
    });
    
    // Prevenir que o card seja inserido durante o drag
    column.addEventListener('dragenter', function(e) {
        e.preventDefault();
        return false;
    });
});

// Clique para abrir detalhes (mas não se foi um drag)
document.querySelectorAll('.kanban-card').forEach(card => {
    let wasDragging = false;
    
    card.addEventListener('dragstart', function() {
        wasDragging = true;
    });
    
    card.addEventListener('dragend', function() {
        // Resetar flag após um pequeno delay
        setTimeout(() => {
            wasDragging = false;
        }, 100);
    });
    
    card.addEventListener('click', function(e) {
        // Se acabou de arrastar, não abrir detalhes
        if (wasDragging) {
            return;
        }
        
        const solicitacaoId = this.getAttribute('data-solicitacao-id');
        if (solicitacaoId) {
            abrirDetalhes(parseInt(solicitacaoId));
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

// ✅ Variáveis globais para rastrear mudanças não salvas
let hasUnsavedChanges = false;
let offcanvasSolicitacaoId = null;
let whatsappHistoricoGlobal = [];

// Funções do Offcanvas (reutilizadas do Dashboard)
function abrirDetalhes(solicitacaoId) {
    const offcanvas = document.getElementById('detalhesOffcanvas');
    const panel = document.getElementById('offcanvasPanel');
    const loadingContent = document.getElementById('loadingContent');
    const detalhesContent = document.getElementById('detalhesContent');
    
    // Resetar flag de mudanças não salvas
    hasUnsavedChanges = false;
    offcanvasSolicitacaoId = solicitacaoId;
    
    offcanvas.classList.remove('hidden');
    setTimeout(() => panel.classList.remove('translate-x-full'), 10);
    
    loadingContent.classList.remove('hidden');
    detalhesContent.classList.add('hidden');
    
    fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/api`)
        .then(response => response.json())
        .then(data => {
            console.log('📡 Resposta da API:', data);
            if (data.success) {
                console.log('📸 Fotos recebidas da API:', data.solicitacao.fotos);
                console.log('📸 Quantidade de fotos:', data.solicitacao.fotos ? data.solicitacao.fotos.length : 0);
                // Armazenar histórico de WhatsApp globalmente
                whatsappHistoricoGlobal = data.solicitacao.whatsapp_historico || [];
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
    // ✅ Verificar se há mudanças não salvas
    if (hasUnsavedChanges) {
        const confirm = window.confirm(
            'Você tem alterações não salvas. Deseja realmente fechar?\n\n' +
            'As alterações serão perdidas se você não salvar.'
        );
        if (!confirm) {
            return;
        }
    }
    
    // Limpar flags
    hasUnsavedChanges = false;
    offcanvasSolicitacaoId = null;
    
    const offcanvas = document.getElementById('detalhesOffcanvas');
    const panel = document.getElementById('offcanvasPanel');
    
    panel.classList.add('translate-x-full');
    setTimeout(() => offcanvas.classList.add('hidden'), 300);
}

// ✅ Prevenir navegação se houver mudanças não salvas
window.addEventListener('beforeunload', (e) => {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = 'Você tem alterações não salvas. Deseja realmente sair?';
        return e.returnValue;
    }
});

// Variável global com todos os status
const todosStatus = <?= json_encode($todosStatus ?? []) ?>;
const todasCondicoes = <?= json_encode($todasCondicoes ?? []) ?>;

function renderizarDetalhes(solicitacao) {
    const content = document.getElementById('detalhesContent');
    
    // ✅ Resetar flag de mudanças ao renderizar
    hasUnsavedChanges = false;
    
    console.log('🔍 renderizarDetalhes - horarios_indisponiveis:', solicitacao.horarios_indisponiveis);
    console.log('📸 renderizarDetalhes - fotos:', solicitacao.fotos);
    console.log('📸 renderizarDetalhes - quantidade de fotos:', solicitacao.fotos ? solicitacao.fotos.length : 0);
    
    // Parse TODOS os horários (locatário + prestador)
    // IMPORTANTE: Combinar horários do locatário e do prestador em uma única lista
    let horariosLocatario = [];
    let horariosPrestador = [];
    let horariosOpcoes = [];
    
    try {
        // Buscar horários do locatário
        if (solicitacao.horarios_indisponiveis) {
            // Quando horarios_indisponiveis = 1, horários originais do locatário estão em datas_opcoes
            horariosLocatario = solicitacao.datas_opcoes ? JSON.parse(solicitacao.datas_opcoes) : [];
        } else {
            // Quando horarios_indisponiveis = 0, horários do locatário estão em horarios_opcoes
            horariosLocatario = solicitacao.horarios_opcoes ? JSON.parse(solicitacao.horarios_opcoes) : [];
        }
        
        // Buscar horários do prestador (quando horarios_indisponiveis = 1)
        if (solicitacao.horarios_indisponiveis) {
            horariosPrestador = solicitacao.horarios_opcoes ? JSON.parse(solicitacao.horarios_opcoes) : [];
        }
        
        // Verificar se condição é "Data Aceita pelo Prestador" ou "Data Aceita pelo Locatário" - mostrar apenas essa data
        const condicaoAtual = todasCondicoes.find(c => c.id === solicitacao.condicao_id);
        if (condicaoAtual && (condicaoAtual.nome === 'Data Aceita pelo Prestador' || condicaoAtual.nome === 'Data Aceita pelo Locatário')) {
            // Quando prestador ou locatário aceitou uma data, mostrar apenas essa data de confirmed_schedules
            if (solicitacao.confirmed_schedules) {
                try {
                    const confirmed = JSON.parse(solicitacao.confirmed_schedules);
                    if (Array.isArray(confirmed) && confirmed.length > 0) {
                        // Buscar horário com source correspondente à condição
                        const source = condicaoAtual.nome === 'Data Aceita pelo Prestador' ? 'prestador' : 'tenant';
                        const horarioAceito = confirmed.find(s => s && s.source === source && s.raw);
                        if (horarioAceito && horarioAceito.raw) {
                            horariosOpcoes = [horarioAceito.raw];
                        } else {
                            // Se não encontrou pelo source, usar o último confirmado
                            const ultimoConfirmado = confirmed[confirmed.length - 1];
                            if (ultimoConfirmado && ultimoConfirmado.raw) {
                                horariosOpcoes = [ultimoConfirmado.raw];
                            }
                        }
                    }
                } catch (e) {
                    // Se não conseguir parsear, usar horario_confirmado_raw
                    if (solicitacao.horario_confirmado_raw) {
                        horariosOpcoes = [solicitacao.horario_confirmado_raw];
                    }
                }
            } else if (solicitacao.horario_confirmado_raw) {
                // Se não há confirmed_schedules mas há horario_confirmado_raw, usar ele
                horariosOpcoes = [solicitacao.horario_confirmado_raw];
            }
        } else {
            // Combinar todos os horários (locatário + prestador)
            horariosOpcoes = [...horariosLocatario, ...horariosPrestador];
            // Remover duplicatas
            horariosOpcoes = [...new Set(horariosOpcoes)];
        }
    } catch (e) {
        horariosOpcoes = [];
    }
    
    content.innerHTML = `
        <!-- Cabeçalho com ID e Data -->
        <div class="bg-white rounded-lg p-5 mb-4">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="text-3xl font-bold text-gray-900">${solicitacao.numero_solicitacao || 'KS' + solicitacao.id}</div>
                        ${solicitacao.numero_contrato ? `
                            <span class="text-sm text-gray-500 mt-2">
                                Contrato: ${solicitacao.numero_contrato}
                            </span>
                        ` : ''}
                    </div>
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
                            <h3 class="font-semibold text-gray-900">Disponibilidade Informada</h3>
                            <p class="text-xs text-gray-500">Horários informados pelo locatário e prestador</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        ${horariosOpcoes.map((horario, index) => {
                            try {
                                let dt, textoHorario;
                                
                                // Verificar se horario é uma string no formato "dd/mm/yyyy - HH:00-HH:00"
                                if (typeof horario === 'string' && horario.includes(' - ')) {
                                    // Já está no formato correto, usar diretamente
                                    textoHorario = horario;
                                    // Extrair data para comparação
                                    const match = horario.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                                    if (match) {
                                        dt = new Date(`${match[3]}-${match[2]}-${match[1]}`);
                                    } else {
                                        dt = new Date();
                                    }
                                } else {
                                    // É uma data ISO, converter para o formato esperado
                                    dt = new Date(horario);
                                    if (isNaN(dt.getTime())) {
                                        // Se não for uma data válida, pular
                                        return '';
                                    }
                                    const dia = String(dt.getDate()).padStart(2, '0');
                                    const mes = String(dt.getMonth() + 1).padStart(2, '0');
                                    const ano = dt.getFullYear();
                                    const hora = String(dt.getHours()).padStart(2, '0');
                                    const faixaHora = hora + ':00-' + (parseInt(hora) + 3) + ':00';
                                    textoHorario = `${dia}/${mes}/${ano} - ${faixaHora}`;
                                }
                                
                                // ✅ Verificar se este horário está confirmado
                                let isConfirmed = false;
                                
                                // DEBUG: Log para verificar dados
                                console.log('🔍 DEBUG Horário atual:', textoHorario);
                                console.log('🔍 confirmed_schedules:', solicitacao.confirmed_schedules);
                                console.log('🔍 horario_confirmado_raw:', solicitacao.horario_confirmado_raw);
                                
                                // ✅ Função auxiliar para comparar horários de forma precisa
                                const compararHorarios = (raw1, raw2) => {
                                    const raw1Norm = String(raw1).trim().replace(/\s+/g, ' ');
                                    const raw2Norm = String(raw2).trim().replace(/\s+/g, ' ');
                                    
                                    // Comparação exata primeiro (mais precisa)
                                    if (raw1Norm === raw2Norm) {
                                        return true;
                                    }
                                    
                                    // Comparação por regex - extrair data e hora inicial E FINAL EXATAS
                                    // Formato esperado: "dd/mm/yyyy - HH:MM-HH:MM"
                                    const regex = /(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}:\d{2})-(\d{2}:\d{2})/;
                                    const match1 = raw1Norm.match(regex);
                                    const match2 = raw2Norm.match(regex);
                                    
                                    if (match1 && match2) {
                                        // ✅ Comparar data, hora inicial E hora final EXATAS (não apenas data e hora inicial)
                                        // Isso garante que apenas horários EXATOS sejam considerados iguais
                                        return (match1[1] === match2[1] && match1[2] === match2[2] && match1[3] === match2[3]);
                                    }
                                    
                                    // Se não conseguir comparar por regex, retornar false (não é match)
                                    return false;
                                };
                                
                                // 1. Verificar em confirmed_schedules (JSON) - PRIORIDADE
                                if (solicitacao.confirmed_schedules) {
                                    try {
                                        // Pode ser string JSON ou já um objeto
                                        const confirmed = typeof solicitacao.confirmed_schedules === 'string' 
                                            ? JSON.parse(solicitacao.confirmed_schedules) 
                                            : solicitacao.confirmed_schedules;
                                        
                                        if (Array.isArray(confirmed) && confirmed.length > 0) {
                                            // Comparar raw de cada horário confirmado
                                            isConfirmed = confirmed.some(s => {
                                                if (!s || !s.raw) return false;
                                                
                                                // ✅ Usar função de comparação precisa
                                                return compararHorarios(String(s.raw), textoHorario);
                                            });
                                        }
                                    } catch (e) {
                                        console.error('Erro ao parsear confirmed_schedules:', e);
                                    }
                                }
                                
                                // 2. Verificar em horario_confirmado_raw (texto direto) - IMPORTANTE se confirmed_schedules está null
                                if (!isConfirmed && solicitacao.horario_confirmado_raw) {
                                    // ✅ Usar função de comparação precisa
                                    isConfirmed = compararHorarios(solicitacao.horario_confirmado_raw, textoHorario);
                                }
                                
                                // 3. Verificar por data_agendamento + horario_agendamento (compatibilidade)
                                if (!isConfirmed && solicitacao.data_agendamento && solicitacao.horario_agendamento) {
                                    try {
                                        const dataAg = new Date(solicitacao.data_agendamento);
                                        const horaAg = String(solicitacao.horario_agendamento).trim();
                                        
                                        // Comparar data
                                        if (dataAg.getDate() === dt.getDate() &&
                                            dataAg.getMonth() === dt.getMonth() &&
                                            dataAg.getFullYear() === dt.getFullYear()) {
                                            // Comparar hora (primeira hora do horário atual)
                                            const hora = String(dt.getHours()).padStart(2, '0');
                                            if (horaAg.includes(hora)) {
                                                isConfirmed = true;
                                            }
                                        }
                                    } catch (e) {
                                        // Ignorar erro de data
                                    }
                                }
                                
                                console.log('🔍 Resultado final isConfirmed para', textoHorario, ':', isConfirmed);
                                
                                return `
                                <div class="flex items-center gap-3 py-2 ${isConfirmed ? 'bg-green-50 rounded px-2' : ''}">
                                    <input type="checkbox" 
                                           class="w-4 h-4 text-blue-600 rounded horario-offcanvas" 
                                           data-raw="${textoHorario}" 
                                           id="horario-${index}"
                                           ${isConfirmed ? 'checked' : ''}>
                                    <label for="horario-${index}" class="text-sm text-gray-700 flex items-center gap-2">
                                        ${isConfirmed ? '<i class="fas fa-check-circle text-green-600"></i>' : ''}
                                        <span>${textoHorario}</span>
                                        ${isConfirmed ? '<span class="text-xs text-green-700 font-semibold">(Confirmado)</span>' : ''}
                                    </label>
                                </div>
                                `;
                            } catch (e) {
                                return '';
                            }
                        }).join('')}
                    </div>
                    <div class="mt-4 pt-3 border-t">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" 
                                   id="horarios-indisponiveis-kanban" 
                                   class="w-4 h-4 text-blue-600 rounded"
                                   ${solicitacao.horarios_indisponiveis ? 'checked' : ''}
                                   onchange="toggleAdicionarHorariosSeguradoraKanban(${solicitacao.id}, this.checked)">
                            <span class="text-sm text-gray-700">Nenhum horário está disponível</span>
                        </label>
                    </div>
                    
                    <!-- Seção: Adicionar Horários da Seguradora (aparece quando checkbox está marcado) -->
                    <div id="secao-adicionar-horarios-seguradora-kanban" 
                         class="mt-4 ${solicitacao.horarios_indisponiveis ? '' : 'hidden'}"
                         style="${solicitacao.horarios_indisponiveis ? 'display: block;' : 'display: none;'}">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-blue-900 mb-2">
                                <i class="fas fa-clock mr-2"></i>Adicionar Horário da Seguradora
                            </h3>
                            <p class="text-xs text-blue-700 mb-4">
                                Adicione horários alternativos que a seguradora pode oferecer
                            </p>
                            
                            <!-- Formulário para adicionar horário -->
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Data</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-calendar-alt text-gray-400"></i>
                                        </div>
                                        <input type="date" 
                                               id="data-seguradora-kanban" 
                                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                               min="${new Date(Date.now() + 86400000).toISOString().split('T')[0]}"
                                               max="${new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0]}">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-2">Horário</label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="horario-seguradora-kanban" value="08:00-11:00" class="sr-only">
                                            <div class="border-2 border-gray-200 rounded-lg p-2 text-center hover:border-blue-300 transition-colors horario-seguradora-card-kanban">
                                                <div class="text-xs font-medium text-gray-900">08h00 às 11h00</div>
                                            </div>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="horario-seguradora-kanban" value="11:00-14:00" class="sr-only">
                                            <div class="border-2 border-gray-200 rounded-lg p-2 text-center hover:border-blue-300 transition-colors horario-seguradora-card-kanban">
                                                <div class="text-xs font-medium text-gray-900">11h00 às 14h00</div>
                                            </div>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="horario-seguradora-kanban" value="14:00-17:00" class="sr-only">
                                            <div class="border-2 border-gray-200 rounded-lg p-2 text-center hover:border-blue-300 transition-colors horario-seguradora-card-kanban">
                                                <div class="text-xs font-medium text-gray-900">14h00 às 17h00</div>
                                            </div>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="horario-seguradora-kanban" value="17:00-20:00" class="sr-only">
                                            <div class="border-2 border-gray-200 rounded-lg p-2 text-center hover:border-blue-300 transition-colors horario-seguradora-card-kanban">
                                                <div class="text-xs font-medium text-gray-900">17h00 às 20h00</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="button" 
                                        onclick="adicionarHorarioSeguradoraKanban(${solicitacao.id})" 
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                    <i class="fas fa-plus mr-2"></i>Salvar Horário
                                </button>
                            </div>
                        </div>
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
                
                <!-- Histórico de WhatsApp -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fab fa-whatsapp text-green-600"></i>
                        <h3 class="font-semibold text-gray-900">Histórico WhatsApp</h3>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        ${solicitacao.whatsapp_historico && solicitacao.whatsapp_historico.length > 0 ? 
                            solicitacao.whatsapp_historico.map((envio, index) => {
                                const statusIcon = envio.status === 'sucesso' ? 'fa-check-circle text-green-600' : 
                                                  envio.status === 'erro' ? 'fa-times-circle text-red-600' : 
                                                  'fa-clock text-yellow-600';
                                const statusText = envio.status === 'sucesso' ? 'Enviado' : 
                                                  envio.status === 'erro' ? 'Erro' : 
                                                  'Pendente';
                                return `
                                    <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer transition-colors" 
                                         onclick="verMensagemWhatsApp(${index})">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <i class="fas ${statusIcon} text-sm"></i>
                                                <span class="text-xs font-medium text-gray-700">${envio.tipo}</span>
                                                <span class="text-xs px-2 py-0.5 rounded ${envio.status === 'sucesso' ? 'bg-green-100 text-green-700' : envio.status === 'erro' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'}">${statusText}</span>
                                            </div>
                                            <span class="text-xs text-gray-500">${formatarDataHora(envio.timestamp)}</span>
                                        </div>
                                        ${envio.telefone ? `
                                            <div class="text-xs text-gray-600 mb-1">
                                                <i class="fas fa-phone mr-1"></i>
                                                ${envio.telefone}
                                            </div>
                                        ` : ''}
                                        ${envio.erro ? `
                                            <div class="text-xs text-red-600 mt-1">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                ${envio.erro}
                                            </div>
                                        ` : ''}
                                        <div class="text-xs text-blue-600 mt-2 flex items-center">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver mensagem
                                        </div>
                                    </div>
                                `;
                            }).join('') : 
                            `
                            <div class="text-center py-6 text-gray-400">
                                <i class="fab fa-whatsapp text-2xl mb-2 block"></i>
                                <p class="text-sm">Nenhum envio de WhatsApp registrado</p>
                            </div>
                            `
                        }
                    </div>
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
                
                <!-- Fotos -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-camera text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Fotos Enviadas</h3>
                        <span class="text-xs text-gray-500" id="fotos-count">(${solicitacao.fotos && Array.isArray(solicitacao.fotos) ? solicitacao.fotos.length : 0})</span>
                    </div>
                    ${solicitacao.fotos && Array.isArray(solicitacao.fotos) && solicitacao.fotos.length > 0 ? `
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        ${solicitacao.fotos.map((foto, index) => {
                            // Construir URL correta da foto
                            let urlFoto = '';
                            
                            // Obter nome do arquivo (priorizar nome_arquivo)
                            const nomeArquivo = foto.nome_arquivo || (foto.url_arquivo ? foto.url_arquivo.split('/').pop() : '');
                            
                            if (nomeArquivo) {
                                // Construir URL usando a função url() do PHP
                                urlFoto = '<?= url("Public/uploads/solicitacoes/") ?>' + nomeArquivo;
                                
                                // Log para debug
                                console.log('📸 Construindo URL da foto:', {
                                    foto: foto,
                                    nomeArquivo: nomeArquivo,
                                    urlFinal: urlFoto
                                });
                            } else {
                                console.error('❌ Erro: Nome do arquivo não encontrado', foto);
                                return '';
                            }
                            
                            // Escapar aspas para evitar problemas no JavaScript
                            const urlFotoEscapada = urlFoto.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            
                            return `
                                <div class="relative group">
                                    <img src="${urlFotoEscapada}" 
                                         alt="Foto ${index + 1} da solicitação" 
                                         class="w-full h-32 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity shadow-sm"
                                         onclick="abrirFotoModal('${urlFotoEscapada}')"
                                         onerror="console.error('Erro ao carregar foto:', '${urlFotoEscapada}'); this.parentElement.innerHTML='<div class=\\'w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs\\'><i class=\\'fas fa-image mr-2\\'></i>Erro ao carregar</div>';">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-all flex items-center justify-center">
                                        <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity text-2xl"></i>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                    ` : `
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-camera text-3xl mb-2 block"></i>
                        <p class="text-sm">Nenhuma foto enviada</p>
                    </div>
                    `}
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
                    <select id="statusSelectKanban" 
                            onchange="marcarMudancaStatus()"
                            class="w-full bg-gray-50 border border-gray-200 rounded px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        ${todosStatus.map(status => `
                            <option value="${status.id}" ${status.id == solicitacao.status_id ? 'selected' : ''}>
                                ${status.nome}
                            </option>
                        `).join('')}
                    </select>
                </div>
                
                <!-- Condições -->
                <div class="bg-white rounded-lg p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-tag text-gray-600"></i>
                        <h3 class="font-semibold text-gray-900">Condições</h3>
                    </div>
                    <select id="condicaoSelectKanban" 
                            onchange="marcarMudancaCondicao()"
                            class="w-full bg-gray-50 border border-gray-200 rounded px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Selecione uma condição</option>
                        ${todasCondicoes.map(condicao => `
                            <option value="${condicao.id}" ${condicao.id == solicitacao.condicao_id ? 'selected' : ''}>
                                ${condicao.nome}
                            </option>
                        `).join('')}
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
            <button id="btnSalvarAlteracoes" onclick="salvarAlteracoes(${solicitacao.id})" 
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
    
    // ✅ Monitorar mudanças em todos os campos após renderizar
    setTimeout(() => {
        monitorarMudancas();
        
        // Verificar se a seção foi criada
        const secao = document.getElementById('secao-adicionar-horarios-seguradora-kanban');
        const checkbox = document.getElementById('horarios-indisponiveis-kanban');
        console.log('🔍 Após renderizar - Seção encontrada:', secao);
        console.log('🔍 Após renderizar - Checkbox encontrado:', checkbox);
        console.log('🔍 Após renderizar - Checkbox checked:', checkbox?.checked);
        console.log('🔍 Após renderizar - Seção display:', secao?.style.display);
        
        // Ajustar visibilidade inicial baseado no estado do checkbox
        if (checkbox && secao) {
            if (checkbox.checked) {
                secao.style.display = 'block';
                secao.classList.remove('hidden');
            } else {
                secao.style.display = 'none';
                secao.classList.add('hidden');
            }
        }
    }, 100);
}

// ✅ Função para monitorar mudanças nos campos
function monitorarMudancas() {
    // Monitorar textarea de observações
    const textarea = document.querySelector('textarea[placeholder*="Descreva qualquer situação"]');
    if (textarea) {
        textarea.addEventListener('input', () => {
            hasUnsavedChanges = true;
        });
        textarea.addEventListener('change', () => {
            hasUnsavedChanges = true;
        });
    }
    
    // Monitorar inputs de texto
    const inputs = document.querySelectorAll('#protocoloSeguradora, #valorReembolso');
    inputs.forEach(input => {
        if (input) {
            input.addEventListener('input', () => {
                hasUnsavedChanges = true;
            });
            input.addEventListener('change', () => {
                hasUnsavedChanges = true;
            });
        }
    });
    
    // Monitorar checkbox de reembolso
    const checkboxReembolso = document.getElementById('checkboxReembolso');
    if (checkboxReembolso) {
        checkboxReembolso.addEventListener('change', () => {
            hasUnsavedChanges = true;
        });
    }
    
    // Monitorar checkboxes de horários
    const checkboxes = document.querySelectorAll('.horario-offcanvas');
    checkboxes.forEach(chk => {
        chk.addEventListener('change', () => {
            hasUnsavedChanges = true;
        });
    });
    
    // Monitorar select de status
    const statusSelect = document.getElementById('statusSelectKanban');
    if (statusSelect) {
        statusSelect.addEventListener('change', () => {
            hasUnsavedChanges = true;
        });
    }
    
    // Monitorar select de condição
    const condicaoSelect = document.getElementById('condicaoSelectKanban');
    if (condicaoSelect) {
        condicaoSelect.addEventListener('change', () => {
            hasUnsavedChanges = true;
        });
    }
}

// Funções para marcar mudanças (não salvar automaticamente)
function marcarMudancaStatus() {
    hasUnsavedChanges = true;
}

function marcarMudancaCondicao() {
    hasUnsavedChanges = true;
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
    // ✅ Loading state granular: Desabilitar botão e mostrar feedback
    const btnSalvar = document.getElementById('btnSalvarAlteracoes');
    const originalText = btnSalvar.innerHTML;
    const originalDisabled = btnSalvar.disabled;
    
    btnSalvar.disabled = true;
    btnSalvar.innerHTML = `
        <i class="fas fa-spinner fa-spin mr-2"></i>
        Salvando...
    `;
    btnSalvar.classList.remove('hover:bg-blue-700');
    
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
    
    // ✅ Coletar status e condição
    const statusId = document.getElementById('statusSelectKanban')?.value || '';
    const condicaoId = document.getElementById('condicaoSelectKanban')?.value || '';
    
    // ✅ Validação: Verificar se está tentando mudar para "Serviço Agendado" sem protocolo
    if (statusId) {
        const statusSelect = document.getElementById('statusSelectKanban');
        const statusNome = statusSelect.options[statusSelect.selectedIndex]?.text || '';
        
        if (statusNome === 'Serviço Agendado' && !protocoloSeguradora.trim()) {
            // Restaurar botão
            btnSalvar.innerHTML = originalText;
            btnSalvar.disabled = originalDisabled;
            btnSalvar.classList.add('hover:bg-blue-700');
            
            mostrarNotificacao('É obrigatório preencher o protocolo da seguradora para mudar para "Serviço Agendado"', 'error');
            
            // Destacar o campo de protocolo
            const protocoloInput = document.getElementById('protocoloSeguradora');
            if (protocoloInput) {
                protocoloInput.focus();
                protocoloInput.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                setTimeout(() => {
                    protocoloInput.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                }, 3000);
            }
            
            return;
        }
    }
    
    // ✅ Coletar horários da seguradora da lista visual
    const horariosSeguradora = coletarHorariosSeguradoraVisual();
    
    // Criar objeto com os dados
    // ✅ Coletar horários selecionados
    // IMPORTANTE: Só enviar schedules se houver horários selecionados
    // Se não houver horários selecionados, não enviar schedules para não limpar os existentes
    const schedules = coletarSchedulesOffcanvas();
    const dados = {
        observacoes: observacoes,
        precisa_reembolso: precisaReembolso,
        valor_reembolso: valorReembolso,
        protocolo_seguradora: protocoloSeguradora
    };
    
    // ✅ Adicionar status_id e condicao_id se foram alterados
    if (statusId) {
        dados.status_id = statusId;
    }
    if (condicaoId) {
        dados.condicao_id = condicaoId;
    }
    
    // ✅ Só adicionar schedules se houver horários selecionados
    // Se o array estiver vazio, não enviar schedules para preservar os horários existentes
    if (schedules.length > 0) {
        dados.schedules = schedules;
    }
    
    // ✅ Adicionar horários da seguradora se houver
    if (horariosSeguradora.length > 0) {
        dados.horarios_seguradora = horariosSeguradora;
    }
    
    console.log('Dados a serem salvos:', dados); // Debug
    
    // Enviar para o servidor
    fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/atualizar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(async response => {
        // ✅ Verificar se a resposta é JSON válido antes de parsear
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            throw new Error('Resposta do servidor não é JSON válido. ' + text.substring(0, 200));
        }
        return response.json();
    })
    .then(data => {
        console.log('Resposta do servidor:', data); // Debug
        if (data.success) {
            // ✅ Feedback específico de sucesso
            btnSalvar.innerHTML = `
                <i class="fas fa-check mr-2"></i>
                Salvo!
            `;
            btnSalvar.classList.remove('bg-blue-600');
            btnSalvar.classList.add('bg-green-600');
            
            // Limpar flag de mudanças não salvas
            hasUnsavedChanges = false;
            
            // Aguardar um momento antes de fechar para mostrar feedback
            setTimeout(() => {
                fecharDetalhes();
                // Recarregar a página para atualizar os dados
                window.location.reload();
            }, 1000);
        } else {
            // ✅ Restaurar botão em caso de erro
            btnSalvar.innerHTML = originalText;
            btnSalvar.disabled = originalDisabled;
            btnSalvar.classList.add('hover:bg-blue-700');
            mostrarNotificacao('Erro ao salvar: ' + (data.error || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        // ✅ Restaurar botão em caso de erro
        btnSalvar.innerHTML = originalText;
        btnSalvar.disabled = originalDisabled;
        btnSalvar.classList.add('hover:bg-blue-700');
        mostrarNotificacao('Erro ao salvar alterações. Tente novamente.', 'error');
    });
}

// ======== Horários (offcanvas) ========
function parseScheduleRawOffcanvas(raw) {
    const out = { date: null, time: null, raw };
    if (!raw) return out;
    const dBR = raw.match(/(\d{2})\/(\d{2})\/(\d{4})/);
    if (dBR) out.date = `${dBR[3]}-${dBR[2]}-${dBR[1]}`;
    const range = raw.match(/(\d{2}:\d{2})\s?-\s?(\d{2}:\d{2})/);
    if (range) out.time = `${range[1]}-${range[2]}`; else {
        const single = raw.match(/\b(\d{2}:\d{2})\b/);
        if (single) out.time = single[1];
    }
    return out;
}

function coletarSchedulesOffcanvas() {
    const checkboxes = Array.from(document.querySelectorAll('.horario-offcanvas:checked'));
    
    // ✅ DEBUG: Log dos checkboxes encontrados
    console.log('🔍 coletarSchedulesOffcanvas - Total de checkboxes marcados:', checkboxes.length);
    checkboxes.forEach((chk, idx) => {
        console.log(`  [${idx}] data-raw:`, chk.getAttribute('data-raw'));
    });
    
    // ✅ Mapear e processar
    const schedules = checkboxes
        .map(chk => {
            const raw = chk.getAttribute('data-raw');
            // ✅ Validar se raw é válido (não contém NaN ou formato inválido)
            if (!raw || raw.includes('NaN') || raw.trim() === '') {
                console.warn('⚠️ Horário inválido ignorado:', raw);
                return null;
            }
            const parsed = parseScheduleRawOffcanvas(raw);
            // ✅ Validar se o parse foi bem-sucedido
            if (!parsed.date && !parsed.time) {
                console.warn('⚠️ Horário não parseado corretamente:', raw);
                return null;
            }
            return parsed;
        })
        .filter(s => s !== null && (s.date || s.time));
    
    // ✅ Remover duplicatas baseado no raw (comparação precisa)
    const schedulesUnicos = [];
    const rawsProcessados = [];
    
    schedules.forEach(s => {
        const rawNorm = String(s.raw || '').trim().replace(/\s+/g, ' ');
        
        // ✅ Verificar se já processamos este raw
        const jaExiste = rawsProcessados.some(rp => {
            // Comparação exata primeiro
            if (rp === rawNorm) return true;
            
            // Comparação por regex (data e hora inicial)
            const regex = /(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}:\d{2})-(\d{2}:\d{2})/;
            const match1 = rp.match(regex);
            const match2 = rawNorm.match(regex);
            
            if (match1 && match2) {
                return (match1[1] === match2[1] && match1[2] === match2[2]);
            }
            
            return false;
        });
        
        if (!jaExiste) {
            rawsProcessados.push(rawNorm);
            schedulesUnicos.push(s);
        } else {
            console.log('⚠️ coletarSchedulesOffcanvas - Duplicata removida:', rawNorm);
        }
    });
    
    console.log('🔍 coletarSchedulesOffcanvas - Schedules únicos finais:', schedulesUnicos.length);
    console.log('🔍 coletarSchedulesOffcanvas - Schedules:', schedulesUnicos);
    
    return schedulesUnicos;
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

// Função para exibir mensagem completa do WhatsApp
function verMensagemWhatsApp(index) {
    if (!whatsappHistoricoGlobal || !whatsappHistoricoGlobal[index]) {
        mostrarNotificacao('Mensagem não encontrada', 'error');
        return;
    }
    
    const envio = whatsappHistoricoGlobal[index];
    const mensagem = envio.mensagem || 'Mensagem não disponível';
    
    // Criar modal para exibir a mensagem
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <div class="bg-green-600 text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fab fa-whatsapp text-2xl"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Mensagem WhatsApp</h3>
                        <p class="text-sm text-green-100">${envio.tipo} - ${formatarDataHora(envio.timestamp)}</p>
                    </div>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <div class="space-y-4">
                    ${envio.telefone ? `
                        <div class="flex items-center gap-2 text-gray-700">
                            <i class="fas fa-phone text-gray-500"></i>
                            <span class="font-medium">Telefone:</span>
                            <span>${envio.telefone}</span>
                        </div>
                    ` : ''}
                    ${envio.status === 'erro' && envio.erro ? `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-exclamation-triangle text-red-600 mt-1"></i>
                                <div>
                                    <p class="font-medium text-red-800 mb-1">Erro no envio:</p>
                                    <p class="text-sm text-red-700">${envio.erro}</p>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-sm font-medium text-gray-700 mb-2">Mensagem enviada:</p>
                        <div class="bg-white rounded p-4 border border-gray-200 whitespace-pre-wrap text-sm text-gray-800">
                            ${mensagem.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                    ${envio.detalhes ? `
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm text-gray-600 hover:text-gray-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                Ver detalhes técnicos
                            </summary>
                            <div class="mt-2 bg-gray-50 rounded p-3 border border-gray-200">
                                <pre class="text-xs text-gray-700 overflow-x-auto">${JSON.stringify(envio.detalhes, null, 2)}</pre>
                            </div>
                        </details>
                    ` : ''}
                </div>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 flex justify-end">
                <button onclick="this.closest('.fixed').remove()" 
                        class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    // Fechar ao clicar fora
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    // Fechar com ESC
    const escHandler = function(e) {
        if (e.key === 'Escape') {
            modal.remove();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
    
    document.body.appendChild(modal);
}

// ======== Horários da Seguradora (Kanban) ========
function toggleAdicionarHorariosSeguradoraKanban(solicitacaoId, checked) {
    console.log('🔍 toggleAdicionarHorariosSeguradoraKanban chamado, solicitacaoId:', solicitacaoId, 'checked:', checked);
    
    const secao = document.getElementById('secao-adicionar-horarios-seguradora-kanban');
    
    if (!secao) {
        console.error('❌ Seção não encontrada!');
        return;
    }
    
    if (checked) {
        console.log('🔍 Mostrando seção...');
        secao.classList.remove('hidden');
        secao.style.display = 'block';
        // Atualizar status no banco
        atualizarHorariosIndisponiveisKanban(solicitacaoId, true);
    } else {
        console.log('🔍 Ocultando seção...');
        secao.classList.add('hidden');
        secao.style.display = 'none';
        // Atualizar status no banco
        atualizarHorariosIndisponiveisKanban(solicitacaoId, false);
    }
}

// Atualizar status de horários indisponíveis (Kanban) - APENAS VISUAL, não salva no banco
// O salvamento será feito quando clicar em "Salvar Alterações"
function atualizarHorariosIndisponiveisKanban(solicitacaoId, indisponivel) {
    // Apenas marcar que houve mudança, não salvar automaticamente
    hasUnsavedChanges = true;
    console.log('Horários indisponíveis alterados (será salvo ao clicar em "Salvar Alterações")');
}

// Confirmar horário selecionado pelo locatário
// Função removida - confirmação agora é feita diretamente pelos checkboxes na seção "Disponibilidade Informada"

// Adicionar horário da seguradora (Kanban) - APENAS VISUAL, não salva no banco
function adicionarHorarioSeguradoraKanban(solicitacaoId) {
    const data = document.getElementById('data-seguradora-kanban').value;
    const horarioRadio = document.querySelector('input[name="horario-seguradora-kanban"]:checked');
    
    if (!data) {
        mostrarNotificacao('Por favor, selecione uma data', 'error');
        return;
    }
    
    if (!horarioRadio) {
        mostrarNotificacao('Por favor, selecione um horário', 'error');
        return;
    }
    
    const horario = horarioRadio.value;
    const [horaInicio, horaFim] = horario.split('-');
    
    // Formatar horário: "dd/mm/yyyy - HH:00-HH:00"
    const dataObj = new Date(data + 'T' + horaInicio + ':00');
    const dia = String(dataObj.getDate()).padStart(2, '0');
    const mes = String(dataObj.getMonth() + 1).padStart(2, '0');
    const ano = dataObj.getFullYear();
    const horarioFormatado = `${dia}/${mes}/${ano} - ${horaInicio}:00-${horaFim}:00`;
    
    // Adicionar horário apenas visualmente (não salva no banco ainda)
    let listaHorarios = document.getElementById('lista-horarios-seguradora-kanban');
    let secaoHorarios = document.getElementById('secao-horarios-seguradora-kanban');
    
    // Se a seção não existe, criar ela
    if (!secaoHorarios) {
        // Buscar onde inserir (antes de "Anexar Documento")
        const anexarDocumento = Array.from(document.querySelectorAll('.bg-white.rounded-lg.p-5')).find(el => {
            return el.querySelector('.fa-paperclip');
        });
        if (anexarDocumento && anexarDocumento.parentNode) {
            const novaSecao = document.createElement('div');
            novaSecao.className = 'bg-white rounded-lg p-5';
            novaSecao.id = 'secao-horarios-seguradora-kanban';
            novaSecao.innerHTML = `
                <div class="flex items-center gap-2 mb-3">
                    <i class="fas fa-building text-blue-600"></i>
                    <h3 class="font-semibold text-gray-900">Disponibilidade Informada pela Seguradora</h3>
                </div>
                <div class="space-y-3" id="lista-horarios-seguradora-kanban">
                    <!-- Horários serão adicionados aqui dinamicamente -->
                </div>
            `;
            anexarDocumento.parentNode.insertBefore(novaSecao, anexarDocumento);
            listaHorarios = document.getElementById('lista-horarios-seguradora-kanban');
            secaoHorarios = document.getElementById('secao-horarios-seguradora-kanban');
        }
    }
    
    if (listaHorarios) {
        // Verificar se o horário já existe na lista
        const horariosExistentes = Array.from(listaHorarios.querySelectorAll('.text-sm.font-medium.text-blue-900'));
        const jaExiste = horariosExistentes.some(el => el.textContent.trim() === horarioFormatado);
        
        if (jaExiste) {
            mostrarNotificacao('Este horário já foi adicionado', 'error');
            return;
        }
        
        // Criar elemento do novo horário
        const horarioEscapado = horarioFormatado.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const novoHorario = document.createElement('div');
        novoHorario.className = 'bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center justify-between';
        novoHorario.setAttribute('data-horario-seguradora', horarioFormatado);
        novoHorario.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas fa-clock text-blue-600"></i>
                <span class="text-sm font-medium text-blue-900">${horarioFormatado}</span>
            </div>
            <button onclick="removerHorarioSeguradoraKanban(${solicitacaoId}, '${horarioEscapado}')" 
                    class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        `;
        listaHorarios.appendChild(novoHorario);
        
        // Mostrar a seção se estava oculta
        if (secaoHorarios) {
            secaoHorarios.classList.remove('hidden');
        }
        
        // Marcar que há mudanças não salvas
        hasUnsavedChanges = true;
        
        // Limpar formulário
        document.getElementById('data-seguradora-kanban').value = '';
        document.querySelectorAll('input[name="horario-seguradora-kanban"]').forEach(radio => radio.checked = false);
        
        mostrarNotificacao('Horário adicionado (será salvo ao clicar em "Salvar Alterações")', 'info');
    } else {
        mostrarNotificacao('Erro: Seção não encontrada', 'error');
    }
}

// Coletar horários da seguradora da lista visual
function coletarHorariosSeguradoraVisual() {
    const listaHorarios = document.getElementById('lista-horarios-seguradora-kanban');
    if (!listaHorarios) {
        return [];
    }
    
    const horarios = [];
    const elementos = listaHorarios.querySelectorAll('[data-horario-seguradora]');
    elementos.forEach(el => {
        const horario = el.getAttribute('data-horario-seguradora');
        if (horario) {
            horarios.push(horario);
        }
    });
    
    return horarios;
}

// Remover horário da seguradora (Kanban) - APENAS VISUAL, não salva no banco
function removerHorarioSeguradoraKanban(solicitacaoId, horario) {
    const listaHorarios = document.getElementById('lista-horarios-seguradora-kanban');
    if (!listaHorarios) {
        return;
    }
    
    // Encontrar e remover o elemento visual
    const elementos = listaHorarios.querySelectorAll('[data-horario-seguradora]');
    elementos.forEach(el => {
        const horarioAtual = el.getAttribute('data-horario-seguradora');
        if (horarioAtual === horario) {
            el.remove();
            hasUnsavedChanges = true;
            mostrarNotificacao('Horário removido (será salvo ao clicar em "Salvar Alterações")', 'info');
        }
    });
    
    // Se não há mais horários, ocultar a seção
    if (listaHorarios.children.length === 0) {
        const secaoHorarios = document.getElementById('secao-horarios-seguradora-kanban');
        if (secaoHorarios) {
            secaoHorarios.classList.add('hidden');
        }
    }
}

// Função para salvar condição no Kanban
function salvarCondicaoKanban(solicitacaoId, condicaoId) {
    if (!solicitacaoId) {
        mostrarNotificacao('Erro: Dados inválidos', 'error');
        return;
    }
    
    // Mostrar loading
    const select = document.getElementById('condicaoSelectKanban');
    if (select) {
        select.disabled = true;
    }
    
    fetch('<?= url('admin/kanban/atualizar-condicao') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            solicitacao_id: solicitacaoId,
            condicao_id: condicaoId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (select) {
            select.disabled = false;
        }
        
        if (data.success) {
            mostrarNotificacao('Condição atualizada com sucesso!', 'success');
        } else {
            mostrarNotificacao('Erro: ' + (data.error || 'Não foi possível atualizar a condição'), 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        if (select) {
            select.disabled = false;
        }
        mostrarNotificacao('Erro ao atualizar condição', 'error');
    });
}

// Função para salvar status no Kanban
function salvarStatusKanban(solicitacaoId, novoStatusId) {
    if (!solicitacaoId || !novoStatusId) {
        mostrarNotificacao('Erro: Dados inválidos', 'error');
        return;
    }
    
    // Mostrar loading
    const select = document.getElementById('statusSelectKanban');
    if (select) {
        select.disabled = true;
    }
    
    fetch(`<?= url('admin/solicitacoes/') ?>${solicitacaoId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            status_id: novoStatusId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacao('Status atualizado com sucesso!', 'success');
            
            // Fechar o modal
            fecharDetalhes();
            
            // Recarregar a página para atualizar o Kanban
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            mostrarNotificacao('Erro: ' + (data.error || 'Não foi possível atualizar o status'), 'error');
            if (select) {
                select.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarNotificacao('Erro ao atualizar status', 'error');
        if (select) {
            select.disabled = false;
        }
    });
}

// Função para abrir foto em modal
function abrirFotoModal(urlFoto) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="relative max-w-6xl max-h-full w-full">
            <button onclick="this.closest('.fixed').remove()" 
                    class="absolute -top-12 right-0 text-white hover:text-gray-300 text-3xl z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
            <img src="${urlFoto}" 
                 alt="Foto ampliada" 
                 class="max-w-full max-h-[90vh] rounded-lg mx-auto block object-contain"
                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'400\\' height=\\'300\\'%3E%3Crect fill=\\'%23ddd\\' width=\\'400\\' height=\\'300\\'/%3E%3Ctext fill=\\'%23999\\' font-family=\\'sans-serif\\' font-size=\\'18\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dominant-baseline=\\'middle\\'%3EErro ao carregar imagem%3C/text%3E%3C/svg%3E';">
        </div>
    `;
    modal.onclick = function(e) {
        if (e.target === modal || e.target.tagName === 'BUTTON') {
            modal.remove();
        }
    };
    document.body.appendChild(modal);
    
    // Fechar com ESC
    const escHandler = function(e) {
        if (e.key === 'Escape') {
            modal.remove();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}

// Estilização dos cards de horário da seguradora (Kanban)
document.addEventListener('change', function(e) {
    if (e.target.name === 'horario-seguradora-kanban') {
        document.querySelectorAll('.horario-seguradora-card-kanban').forEach(card => {
            card.classList.remove('border-blue-500', 'bg-blue-100');
            card.classList.add('border-gray-200');
        });
        
        const selectedCard = e.target.closest('label').querySelector('.horario-seguradora-card-kanban');
        if (selectedCard) {
            selectedCard.classList.remove('border-gray-200');
            selectedCard.classList.add('border-blue-500', 'bg-blue-100');
        }
    }
});
</script>


