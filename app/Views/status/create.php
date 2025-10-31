<?php
$title = 'Novo Status';
$currentPage = 'status';
$pageTitle = 'Criar Novo Status';
ob_start();
?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="<?= url('admin/status') ?>" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar para lista
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="POST" action="<?= url('admin/status') ?>">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Status <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nome" 
                        value="<?= htmlspecialchars($data['nome'] ?? '') ?>"
                        required
                        placeholder="Ex: Em Análise"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cor <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input 
                            type="color" 
                            name="cor" 
                            value="<?= htmlspecialchars($data['cor'] ?? '#3B82F6') ?>"
                            required
                            class="h-10 w-20 border border-gray-300 rounded-md cursor-pointer"
                        >
                        <input 
                            type="text" 
                            id="corHex"
                            value="<?= htmlspecialchars($data['cor'] ?? '#3B82F6') ?>"
                            readonly
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                        >
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Selecione a cor que representará este status no sistema</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ordem
                    </label>
                    <input 
                        type="number" 
                        name="ordem" 
                        value="<?= htmlspecialchars($data['ordem'] ?? '') ?>"
                        min="1"
                        placeholder="A ordem será definida automaticamente se não informada"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <p class="text-xs text-gray-500 mt-1">A ordem em que o status aparecerá no Kanban</p>
                </div>

                <div>
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="visivel_kanban" 
                            value="1"
                            <?= ($data['visivel_kanban'] ?? true) ? 'checked' : '' ?>
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Visível no Kanban</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-6">Se marcado, este status aparecerá como uma coluna no Kanban</p>
                </div>

                <!-- Preview -->
                <div id="statusPreview" class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Preview:</p>
                    <span id="previewBadge" class="status-badge inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                        Status de Exemplo
                    </span>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                <a href="<?= url('admin/status') ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Criar Status
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>

<script>
// Atualizar preview ao mudar a cor
document.querySelector('input[name="cor"]').addEventListener('input', function(e) {
    const cor = e.target.value;
    document.getElementById('corHex').value = cor;
    updatePreview(cor);
});

document.querySelector('input[name="nome"]').addEventListener('input', function(e) {
    const nome = e.target.value || 'Status de Exemplo';
    document.getElementById('previewBadge').textContent = nome;
});

function updatePreview(cor) {
    const badge = document.getElementById('previewBadge');
    badge.style.backgroundColor = cor + '20';
    badge.style.color = cor;
}

// Inicializar preview
document.addEventListener('DOMContentLoaded', function() {
    const cor = document.querySelector('input[name="cor"]').value;
    updatePreview(cor);
});
</script>

