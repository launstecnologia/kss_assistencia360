<?php
/**
 * View: Criar Nova Subcategoria
 */
$title = 'Nova Subcategoria - ' . $categoria['nome'];
$currentPage = 'categorias';
$pageTitle = 'Nova Subcategoria';
ob_start();
?>

<!-- Breadcrumb -->
<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-4">
        <li>
            <div>
                <a href="<?= url('admin/categorias') ?>" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-tags"></i>
                    <span class="sr-only">Categorias</span>
                </a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <a href="<?= url('admin/categorias/' . $categoria['id']) ?>" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                    <?= htmlspecialchars($categoria['nome']) ?>
                </a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <span class="text-sm font-medium text-gray-500">Nova Subcategoria</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="flex items-center space-x-4 mb-6">
    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl"
         style="background-color: <?= $categoria['cor'] ?>">
        <i class="<?= $categoria['icone'] ?>"></i>
    </div>
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Nova Subcategoria</h2>
        <p class="text-sm text-gray-600">Categoria: <?= htmlspecialchars($categoria['nome']) ?></p>
    </div>
</div>

<!-- Formulário -->
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Criar Nova Subcategoria</h3>
        <p class="text-sm text-gray-500">Preencha os dados da nova subcategoria de assistência</p>
    </div>
    
    <form method="POST" action="<?= url('admin/categorias/' . $categoria['id'] . '/subcategorias') ?>" class="p-6 space-y-6">
        <?= \App\Core\View::csrfField() ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Erro</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nome -->
            <div class="md:col-span-2">
                <label for="nome" class="block text-sm font-medium text-gray-700">
                    Nome da Subcategoria <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       id="nome" 
                       value="<?= htmlspecialchars($data['nome'] ?? '') ?>"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['nome']) ? 'border-red-300' : '' ?>"
                       placeholder="Ex: Troca de lâmpada, Vazamento no banheiro"
                       required>
                <?php if (isset($errors['nome'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['nome']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Descrição -->
            <div class="md:col-span-2">
                <label for="descricao" class="block text-sm font-medium text-gray-700">
                    Descrição
                </label>
                <textarea name="descricao" 
                          id="descricao" 
                          rows="3"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['descricao']) ? 'border-red-300' : '' ?>"
                          placeholder="Descreva brevemente esta subcategoria de assistência"><?= htmlspecialchars($data['descricao'] ?? '') ?></textarea>
                <?php if (isset($errors['descricao'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['descricao']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Tempo Estimado -->
            <div>
                <label for="tempo_estimado" class="block text-sm font-medium text-gray-700">
                    Tempo Estimado (horas)
                </label>
                <input type="number" 
                       name="tempo_estimado" 
                       id="tempo_estimado" 
                       value="<?= htmlspecialchars($data['tempo_estimado'] ?? '') ?>"
                       min="0"
                       step="0.5"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['tempo_estimado']) ? 'border-red-300' : '' ?>"
                       placeholder="2.5">
                <p class="mt-1 text-xs text-gray-500">
                    Tempo estimado para resolver esta subcategoria
                </p>
                <?php if (isset($errors['tempo_estimado'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['tempo_estimado']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">
                    Status <span class="text-red-500">*</span>
                </label>
                <select name="status" 
                        id="status"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['status']) ? 'border-red-300' : '' ?>"
                        required>
                    <option value="ATIVA" <?= ($data['status'] ?? 'ATIVA') === 'ATIVA' ? 'selected' : '' ?>>Ativa</option>
                    <option value="INATIVA" <?= ($data['status'] ?? '') === 'INATIVA' ? 'selected' : '' ?>>Inativa</option>
                </select>
                <?php if (isset($errors['status'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['status']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Ordem -->
            <div>
                <label for="ordem" class="block text-sm font-medium text-gray-700">
                    Ordem de Exibição
                </label>
                <input type="number" 
                       name="ordem" 
                       id="ordem" 
                       value="<?= htmlspecialchars($data['ordem'] ?? '0') ?>"
                       min="0"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['ordem']) ? 'border-red-300' : '' ?>"
                       placeholder="0">
                <p class="mt-1 text-xs text-gray-500">
                    Subcategorias com menor número aparecem primeiro
                </p>
                <?php if (isset($errors['ordem'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['ordem']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Preview -->
        <div class="border-t pt-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Preview</h4>
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-4 mb-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-lg"
                         style="background-color: <?= $categoria['cor'] ?>">
                        <i class="<?= $categoria['icone'] ?>"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-medium text-gray-900"><?= htmlspecialchars($categoria['nome']) ?></h5>
                        <p class="text-xs text-gray-500">Categoria</p>
                    </div>
                </div>
                
                <div class="ml-14">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                        <h6 id="preview-nome" class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($data['nome'] ?? 'Nome da Subcategoria') ?>
                        </h6>
                        <span id="preview-status" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <?= ($data['status'] ?? 'ATIVA') === 'ATIVA' ? 'ATIVA' : 'INATIVA' ?>
                        </span>
                    </div>
                    <p id="preview-descricao" class="text-xs text-gray-500 mt-1">
                        <?= htmlspecialchars($data['descricao'] ?? 'Descrição da subcategoria') ?>
                    </p>
                    <?php if ($data['tempo_estimado'] ?? ''): ?>
                        <p id="preview-tempo" class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-clock mr-1"></i>
                            Tempo estimado: <span id="preview-tempo-value"><?= htmlspecialchars($data['tempo_estimado'] ?? '') ?></span> horas
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Botões -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
            <a href="<?= url('admin/categorias/' . $categoria['id']) ?>" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-times mr-2"></i>
                Cancelar
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Criar Subcategoria
            </button>
        </div>
    </form>
</div>

<script>
// Atualizar preview em tempo real
document.getElementById('nome').addEventListener('input', function() {
    document.getElementById('preview-nome').textContent = this.value || 'Nome da Subcategoria';
});

document.getElementById('descricao').addEventListener('input', function() {
    document.getElementById('preview-descricao').textContent = this.value || 'Descrição da subcategoria';
});

document.getElementById('tempo_estimado').addEventListener('input', function() {
    const tempo = this.value;
    const previewTempo = document.getElementById('preview-tempo');
    const previewTempoValue = document.getElementById('preview-tempo-value');
    
    if (tempo) {
        previewTempoValue.textContent = tempo;
        if (!previewTempo.style.display || previewTempo.style.display === 'none') {
            previewTempo.style.display = 'block';
        }
    } else {
        previewTempo.style.display = 'none';
    }
});

document.getElementById('status').addEventListener('change', function() {
    const status = this.value;
    const previewStatus = document.getElementById('preview-status');
    
    previewStatus.textContent = status;
    previewStatus.className = `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
        status === 'ATIVA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
    }`;
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>
