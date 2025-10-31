<?php
$title = 'Gerenciar Status';
$currentPage = 'status';
$pageTitle = 'Configurações - Status do Kanban';
ob_start();
?>

<!-- Barra de Ações -->
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Configure os status do Kanban</p>
    <a href="<?= url('admin/status/create') ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-plus mr-2"></i>
        Novo Status
    </a>
</div>

<!-- Lista de Status -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Gerenciar Status</h3>
        
        <div class="space-y-3" id="statusList">
            <?php if (empty($status)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                Nenhum status cadastrado
            </div>
            <?php else: ?>
            <?php foreach ($status as $item): ?>
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50" data-id="<?= $item['id'] ?>">
                <div class="flex items-center">
                    <div class="mr-3 cursor-move">
                        <i class="fas fa-grip-vertical text-gray-400"></i>
                    </div>
                    <span class="status-badge" style="background-color: <?= $item['cor'] ?>20; color: <?= $item['cor'] ?>">
                        <?= htmlspecialchars($item['nome']) ?>
                    </span>
                    <?php if (isset($item['visivel_kanban']) && $item['visivel_kanban']): ?>
                    <span class="ml-2 text-xs text-gray-500">
                        <i class="fas fa-eye"></i> Visível no Kanban
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex gap-2">
                    <a href="<?= url("admin/status/{$item['id']}/edit") ?>" 
                       class="text-blue-600 hover:text-blue-900" 
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button 
                        onclick="excluirStatus(<?= $item['id'] ?>)" 
                        class="text-red-600 hover:text-red-900" 
                        title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Inicializar Sortable para reordenação
const statusList = document.getElementById('statusList');
if (statusList && statusList.children.length > 0) {
    new Sortable(statusList, {
        animation: 150,
        handle: '.fa-grip-vertical',
        onEnd: function(evt) {
            const ordens = [];
            statusList.querySelectorAll('[data-id]').forEach((item, index) => {
                ordens.push({
                    id: item.getAttribute('data-id'),
                    ordem: index + 1
                });
            });
            
            console.log('Enviando ordens:', ordens);
            const payload = { ordens: ordens };
            console.log('Payload:', JSON.stringify(payload));
            
            fetch('<?= url('admin/status/reordenar') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Response JSON:', data);
                    if (!data.success) {
                        alert(data.error || 'Erro ao atualizar ordem');
                        location.reload();
                    }
                } catch (e) {
                    console.error('Erro ao parsear JSON:', e);
                    console.error('Texto recebido:', text);
                    alert('Erro ao atualizar ordem: resposta inválida do servidor');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao atualizar ordem');
                location.reload();
            });
        }
    });
}

function excluirStatus(statusId) {
    if (confirm('Deseja realmente excluir este status? Esta ação não pode ser desfeita.')) {
        fetch(`<?= url('admin/status/') ?>${statusId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.error || 'Erro ao excluir status');
            }
        })
        .catch(error => {
            alert('Erro ao excluir status');
            console.error('Error:', error);
        });
    }
}
</script>

