<?php
$title = 'Gerenciar Condições';
$currentPage = 'condicoes';
$pageTitle = 'Configurações - Condições';
ob_start();
?>

<!-- Barra de Ações -->
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Configure as condições das solicitações</p>
    <a href="<?= url('admin/condicoes/create') ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-plus mr-2"></i>
        Nova Condição
    </a>
</div>

<!-- Lista de Condições -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Gerenciar Condições</h3>
        
        <div class="space-y-3" id="condicoesList">
            <?php if (empty($condicoes)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                Nenhuma condição cadastrada
            </div>
            <?php else: ?>
            <?php foreach ($condicoes as $item): ?>
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50" data-id="<?= $item['id'] ?>">
                <div class="flex items-center">
                    <div class="mr-3 cursor-move">
                        <i class="fas fa-grip-vertical text-gray-400"></i>
                    </div>
                    <span class="condicao-badge" style="background-color: <?= $item['cor'] ?>20; color: <?= $item['cor'] ?>">
                        <?= htmlspecialchars($item['nome']) ?>
                    </span>
                    <?php if (isset($item['status']) && $item['status'] === 'ATIVO'): ?>
                    <span class="ml-2 text-xs text-gray-500">
                        <i class="fas fa-check-circle"></i> Ativo
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex gap-2">
                    <a href="<?= url("admin/condicoes/{$item['id']}/edit") ?>" 
                       class="text-blue-600 hover:text-blue-900" 
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button 
                        onclick="excluirCondicao(<?= $item['id'] ?>)" 
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
const condicoesList = document.getElementById('condicoesList');
if (condicoesList && condicoesList.children.length > 0) {
    new Sortable(condicoesList, {
        animation: 150,
        handle: '.fa-grip-vertical',
        onEnd: function(evt) {
            const ordens = [];
            condicoesList.querySelectorAll('[data-id]').forEach((item, index) => {
                ordens.push({
                    id: item.getAttribute('data-id'),
                    ordem: index + 1
                });
            });
            
            fetch('<?= url('admin/condicoes/reordenar') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ordens: ordens })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Erro ao atualizar ordem');
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

function excluirCondicao(condicaoId) {
    if (confirm('Deseja realmente excluir esta condição? Esta ação não pode ser desfeita.')) {
        fetch(`<?= url('admin/condicoes/') ?>${condicaoId}/delete`, {
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
                alert(data.error || 'Erro ao excluir condição');
            }
        })
        .catch(error => {
            alert('Erro ao excluir condição');
            console.error('Error:', error);
        });
    }
}
</script>

