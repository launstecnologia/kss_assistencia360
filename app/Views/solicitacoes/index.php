<?php
/**
 * View: Lista de Solicitações com Kanban
 */
$title = 'Solicitações';
$currentPage = 'solicitacoes';
$pageTitle = 'Solicitações';
ob_start();
?>

<!-- Filtros -->
<div class="bg-white p-6 rounded-lg shadow-sm mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
        <div class="flex space-x-2">
            <button type="button" onclick="toggleFilters()" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-filter mr-2"></i>
                Filtros
            </button>
            <button type="button" onclick="refreshKanban()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-sync-alt mr-2"></i>
                Atualizar
            </button>
        </div>
    </div>
    
    <div id="filters-panel" class="hidden">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Imobiliária</label>
                <select name="imobiliaria_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas</option>
                    <?php foreach ($imobiliarias as $imobiliaria): ?>
                        <option value="<?= $imobiliaria['id'] ?>" <?= ($filtros['imobiliaria_id'] ?? '') == $imobiliaria['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($imobiliaria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <?php foreach ($status as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($filtros['status_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Categoria</label>
                <select name="categoria_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>" <?= ($filtros['categoria_id'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Data Início</label>
                <input type="date" name="data_inicio" value="<?= $filtros['data_inicio'] ?? '' ?>" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Data Fim</label>
                <input type="date" name="data_fim" value="<?= $filtros['data_fim'] ?? '' ?>" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="md:col-span-2 lg:col-span-5 flex justify-end space-x-2">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-search mr-2"></i>
                    Aplicar Filtros
                </button>
                <a href="<?= url('solicitacoes') ?>" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-times mr-2"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Kanban Board -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach ($status as $statusItem): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Header da Coluna -->
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900">
                        <?= htmlspecialchars($statusItem['nome']) ?>
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <?php 
                        if ($statusItem['nome'] === 'Pendências') {
                            $count = count(array_filter($solicitacoes, function($s) {
                                return in_array($s['status_nome'], [
                                    'Pendências', 
                                    'Aguardando Peça', 
                                    'Aguardando Confirmação Mawdy', 
                                    'Aguardando Confirmação Locatário'
                                ]);
                            }));
                        } else {
                            $count = count(array_filter($solicitacoes, fn($s) => $s['status_id'] == $statusItem['id']));
                        }
                        echo $count;
                        ?>
                    </span>
                </div>
            </div>
            
            <!-- Cards das Solicitações -->
            <div class="p-4 space-y-3 min-h-[400px]">
                <?php 
                // Para a coluna "Pendências", agrupar todos os status técnicos
                if ($statusItem['nome'] === 'Pendências') {
                    $solicitacoesStatus = array_filter($solicitacoes, function($s) {
                        return in_array($s['status_nome'], [
                            'Pendências', 
                            'Aguardando Peça', 
                            'Aguardando Confirmação Mawdy', 
                            'Aguardando Confirmação Locatário'
                        ]);
                    });
                } else {
                    $solicitacoesStatus = array_filter($solicitacoes, fn($s) => $s['status_id'] == $statusItem['id']);
                }
                
                foreach ($solicitacoesStatus as $solicitacao): 
                ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer" 
                         onclick="showSolicitacao(<?= $solicitacao['id'] ?>)">
                        <!-- Header do Card -->
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 truncate">
                                    #<?= $solicitacao['id'] ?> - <?= htmlspecialchars($solicitacao['categoria_nome']) ?>
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?= htmlspecialchars($solicitacao['subcategoria_nome']) ?>
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                <?= $solicitacao['prioridade'] == 'ALTA' ? 'bg-red-100 text-red-800' : 
                                   ($solicitacao['prioridade'] == 'MEDIA' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') ?>">
                                <?= $solicitacao['prioridade'] ?>
                            </span>
                        </div>
                        
                        <!-- Informações do Locatário -->
                        <div class="mb-2">
                            <p class="text-sm text-gray-900 font-medium">
                                <?= htmlspecialchars($solicitacao['locatario_nome']) ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?= htmlspecialchars($solicitacao['locatario_telefone']) ?>
                            </p>
                        </div>
                        
                        <!-- Endereço -->
                        <div class="mb-2">
                            <p class="text-xs text-gray-600 truncate">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <?= htmlspecialchars($solicitacao['imovel_endereco'] . ', ' . $solicitacao['imovel_numero']) ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?= htmlspecialchars($solicitacao['imovel_bairro'] . ' - ' . $solicitacao['imovel_cidade']) ?>
                            </p>
                        </div>
                        
                        <!-- Imobiliária -->
                        <div class="mb-2">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-building mr-1"></i>
                                <?= htmlspecialchars($solicitacao['imobiliaria_nome']) ?>
                            </p>
                        </div>
                        
                        <!-- Data -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>
                                <i class="fas fa-calendar mr-1"></i>
                                <?= date('d/m/Y', strtotime($solicitacao['created_at'])) ?>
                            </span>
                            <span>
                                <i class="fas fa-clock mr-1"></i>
                                <?= date('H:i', strtotime($solicitacao['created_at'])) ?>
                            </span>
                        </div>
                        
                        <!-- Ações Rápidas -->
                        <div class="mt-3 flex space-x-2">
                            <button onclick="event.stopPropagation(); updateStatus(<?= $solicitacao['id'] ?>, <?= $statusItem['id'] ?>)" 
                                    class="flex-1 bg-blue-50 text-blue-700 text-xs py-1 px-2 rounded hover:bg-blue-100 transition-colors">
                                <i class="fas fa-edit mr-1"></i>
                                Editar
                            </button>
                            <button onclick="event.stopPropagation(); viewDetails(<?= $solicitacao['id'] ?>)" 
                                    class="flex-1 bg-gray-50 text-gray-700 text-xs py-1 px-2 rounded hover:bg-gray-100 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                Ver
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($solicitacoesStatus)): ?>
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p class="text-sm">Nenhuma solicitação</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function toggleFilters() {
    const panel = document.getElementById('filters-panel');
    panel.classList.toggle('hidden');
}

function refreshKanban() {
    window.location.reload();
}

function showSolicitacao(id) {
    window.location.href = '<?= url('solicitacoes') ?>/' + id;
}

function viewDetails(id) {
    // Implementar modal com detalhes
    console.log('Ver detalhes:', id);
}

function updateStatus(id, currentStatusId) {
    // Implementar atualização de status
    console.log('Atualizar status:', id, currentStatusId);
}

// Auto-refresh a cada 30 segundos
setInterval(function() {
    // Opcional: implementar refresh automático
}, 30000);
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>
