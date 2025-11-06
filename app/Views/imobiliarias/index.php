<?php
/**
 * View: Lista de Imobiliárias
 */
$title = 'Imobiliárias';
$currentPage = 'imobiliarias';
$pageTitle = 'Imobiliárias';
ob_start();
?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Imobiliárias</h2>
        <p class="text-sm text-gray-600">Gerencie as imobiliárias parceiras do sistema</p>
    </div>
    <a href="<?= url('admin/imobiliarias/create') ?>" 
       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <i class="fas fa-plus mr-2"></i>
        Nova Imobiliária
    </a>
</div>

<!-- Lista de Imobiliárias -->
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Todas as Imobiliárias</h3>
    </div>
    
    <?php if (empty($imobiliarias)): ?>
        <div class="text-center py-12">
            <i class="fas fa-building text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma imobiliária encontrada</h3>
            <p class="text-gray-500 mb-4">Comece cadastrando sua primeira imobiliária parceira.</p>
            <a href="<?= url('admin/imobiliarias/create') ?>" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>
                Cadastrar Primeira Imobiliária
            </a>
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($imobiliarias as $imobiliaria): ?>
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Logo -->
                            <div class="flex-shrink-0">
                                <?php if ($imobiliaria['logo']): ?>
                                    <img src="<?= url('Public/uploads/logos/' . $imobiliaria['logo']) ?>" 
                                         alt="Logo <?= htmlspecialchars($imobiliaria['nome_fantasia'] ?? 'Imobiliária') ?>"
                                         class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-lg flex items-center justify-center text-white text-xl font-bold"
                                         style="background: linear-gradient(135deg, <?= $imobiliaria['cor_primaria'] ?? '#3B82F6' ?>, <?= $imobiliaria['cor_secundaria'] ?? '#1E40AF' ?>)">
                                        <?= strtoupper(substr($imobiliaria['nome_fantasia'] ?? 'IM', 0, 2)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Informações -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-lg font-medium text-gray-900 truncate">
                                        <?= htmlspecialchars($imobiliaria['nome_fantasia'] ?? $imobiliaria['nome'] ?? 'Sem nome') ?>
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $imobiliaria['status'] === 'ATIVA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $imobiliaria['status'] ?>
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600 mt-1">
                                    <?= htmlspecialchars($imobiliaria['razao_social'] ?? 'Razão social não informada') ?>
                                </p>
                                
                                <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                    <span>
                                        <i class="fas fa-id-card mr-1"></i>
                                        CNPJ: <?= htmlspecialchars($imobiliaria['cnpj'] ?? 'Não informado') ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?= htmlspecialchars($imobiliaria['endereco_cidade'] ?? 'Cidade') ?> - <?= htmlspecialchars($imobiliaria['endereco_estado'] ?? 'Estado') ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        Cadastrada em <?= date('d/m/Y', strtotime($imobiliaria['created_at'])) ?>
                                    </span>
                                </div>
                                
                                <!-- Informações da API -->
                                <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                    <span>
                                        <i class="fas fa-link mr-1"></i>
                                        URL: <?= htmlspecialchars($imobiliaria['url_base'] ?? 'Não configurada') ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-key mr-1"></i>
                                        Instância: <?= htmlspecialchars($imobiliaria['instancia'] ?? 'Não definida') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ações -->
                        <div class="flex items-center space-x-2">
                            <a href="<?= url('admin/imobiliarias/' . $imobiliaria['id']) ?>" 
                               class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-eye mr-1"></i>
                                Ver
                            </a>
                            
                            <a href="<?= url('admin/imobiliarias/' . $imobiliaria['id'] . '/edit') ?>" 
                               class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-1"></i>
                                Editar
                            </a>
                            
                            <button onclick="toggleStatus(<?= $imobiliaria['id'] ?>, '<?= $imobiliaria['status'] ?>')" 
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-toggle-<?= $imobiliaria['status'] === 'ATIVA' ? 'on' : 'off' ?> mr-1"></i>
                                <?= $imobiliaria['status'] === 'ATIVA' ? 'Desativar' : 'Ativar' ?>
                            </button>
                            
                            <button onclick="testConnection(<?= $imobiliaria['id'] ?>)" 
                                    class="inline-flex items-center px-3 py-1 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-wifi mr-1"></i>
                                Testar
                            </button>
                            
                            <button onclick="deleteImobiliaria(<?= $imobiliaria['id'] ?>, '<?= htmlspecialchars($imobiliaria['nome_fantasia'] ?? $imobiliaria['nome'] ?? 'Imobiliária') ?>')" 
                                    class="inline-flex items-center px-3 py-1 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-trash mr-1"></i>
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Confirmação -->
<div id="confirm-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2" id="modal-title">Confirmar Exclusão</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modal-message">
                    Tem certeza que deseja excluir esta imobiliária?
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirm-button" 
                        class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Confirmar
                </button>
                <button onclick="closeModal()" 
                        class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus === 'ATIVA' ? 'INATIVA' : 'ATIVA';
    const action = newStatus === 'ATIVA' ? 'ativar' : 'desativar';
    
    if (confirm(`Tem certeza que deseja ${action} esta imobiliária?`)) {
        fetch(`<?= url('admin/imobiliarias') ?>/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao atualizar status');
        });
    }
}

function testConnection(id) {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Testando...';
    button.disabled = true;
    
    fetch(`<?= url('admin/imobiliarias') ?>/${id}/test-connection`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Conexão OK!\nTempo de resposta: ${data.response_time}\nStatus: ${data.status_code}`);
        } else {
            alert('❌ Erro na conexão: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao testar conexão');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function deleteImobiliaria(id, nome) {
    document.getElementById('modal-title').textContent = 'Confirmar Exclusão';
    document.getElementById('modal-message').textContent = `Tem certeza que deseja excluir a imobiliária "${nome}"? Esta ação não pode ser desfeita.`;
    
    document.getElementById('confirm-button').onclick = function() {
        fetch(`<?= url('admin/imobiliarias') ?>/${id}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.error);
                closeModal();
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir imobiliária');
            closeModal();
        });
    };
    
    document.getElementById('confirm-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
}
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>
