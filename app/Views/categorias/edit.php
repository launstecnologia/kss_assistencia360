<?php
/**
 * View: Editar Categoria
 */
$title = 'Editar ' . $categoria['nome'];
$currentPage = 'categorias';
$pageTitle = 'Editar ' . $categoria['nome'];
ob_start();
?>

<!-- Breadcrumb -->
<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-4">
        <li>
            <div>
                <a href="<?= url('categorias') ?>" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-tags"></i>
                    <span class="sr-only">Categorias</span>
                </a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <a href="<?= url('categorias/' . $categoria['id']) ?>" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                    <?= htmlspecialchars($categoria['nome']) ?>
                </a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <span class="text-sm font-medium text-gray-500">Editar</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Formulário -->
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Editar Categoria</h3>
        <p class="text-sm text-gray-500">Atualize os dados da categoria de assistência</p>
    </div>
    
    <form method="POST" action="<?= url('categorias/' . $categoria['id']) ?>" class="p-6 space-y-6">
        <?= \App\Core\View::csrfField() ?>
        <input type="hidden" name="_method" value="PUT">
        
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
                    Nome da Categoria <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       id="nome" 
                       value="<?= htmlspecialchars($data['nome'] ?? $categoria['nome']) ?>"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['nome']) ? 'border-red-300' : '' ?>"
                       placeholder="Ex: Elétrica, Hidráulica, Estrutural"
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
                          placeholder="Descreva brevemente o que esta categoria engloba"><?= htmlspecialchars($data['descricao'] ?? $categoria['descricao']) ?></textarea>
                <?php if (isset($errors['descricao'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['descricao']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Ícone -->
            <div>
                <label for="icone" class="block text-sm font-medium text-gray-700">
                    Ícone
                </label>
                <div class="mt-1 relative">
                    <input type="text" 
                           name="icone" 
                           id="icone" 
                           value="<?= htmlspecialchars($data['icone'] ?? $categoria['icone']) ?>"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['icone']) ? 'border-red-300' : '' ?>"
                           placeholder="fas fa-tools">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i id="icon-preview" class="<?= htmlspecialchars($data['icone'] ?? $categoria['icone']) ?> text-gray-400"></i>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Use classes do Font Awesome (ex: fas fa-tools, fas fa-bolt, fas fa-wrench)
                </p>
                <?php if (isset($errors['icone'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['icone']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Cor -->
            <div>
                <label for="cor" class="block text-sm font-medium text-gray-700">
                    Cor
                </label>
                <div class="mt-1 flex items-center space-x-2">
                    <input type="color" 
                           name="cor" 
                           id="cor" 
                           value="<?= htmlspecialchars($data['cor'] ?? $categoria['cor']) ?>"
                           class="h-10 w-16 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <input type="text" 
                           id="cor-text" 
                           value="<?= htmlspecialchars($data['cor'] ?? $categoria['cor']) ?>"
                           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="#3B82F6">
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Escolha uma cor para identificar visualmente esta categoria
                </p>
                <?php if (isset($errors['cor'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['cor']) ?></p>
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
                    <option value="ATIVA" <?= ($data['status'] ?? $categoria['status']) === 'ATIVA' ? 'selected' : '' ?>>Ativa</option>
                    <option value="INATIVA" <?= ($data['status'] ?? $categoria['status']) === 'INATIVA' ? 'selected' : '' ?>>Inativa</option>
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
                       value="<?= htmlspecialchars($data['ordem'] ?? $categoria['ordem']) ?>"
                       min="0"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['ordem']) ? 'border-red-300' : '' ?>"
                       placeholder="0">
                <p class="mt-1 text-xs text-gray-500">
                    Categorias com menor número aparecem primeiro
                </p>
                <?php if (isset($errors['ordem'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['ordem']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Preview -->
        <div class="border-t pt-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Preview</h4>
            <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                <div id="preview-icon" class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl"
                     style="background-color: <?= htmlspecialchars($data['cor'] ?? $categoria['cor']) ?>">
                    <i id="preview-icon-class" class="<?= htmlspecialchars($data['icone'] ?? $categoria['icone']) ?>"></i>
                </div>
                <div>
                    <h5 id="preview-nome" class="text-lg font-medium text-gray-900">
                        <?= htmlspecialchars($data['nome'] ?? $categoria['nome']) ?>
                    </h5>
                    <p id="preview-descricao" class="text-sm text-gray-500">
                        <?= htmlspecialchars($data['descricao'] ?? $categoria['descricao'] ?? 'Descrição da categoria') ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Botões -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
            <a href="<?= url('categorias/' . $categoria['id']) ?>" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-times mr-2"></i>
                Cancelar
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
// Atualizar preview em tempo real
document.getElementById('nome').addEventListener('input', function() {
    document.getElementById('preview-nome').textContent = this.value || 'Nome da Categoria';
});

document.getElementById('descricao').addEventListener('input', function() {
    document.getElementById('preview-descricao').textContent = this.value || 'Descrição da categoria';
});

document.getElementById('icone').addEventListener('input', function() {
    const iconClass = this.value || 'fas fa-tools';
    document.getElementById('preview-icon-class').className = iconClass;
    document.getElementById('icon-preview').className = iconClass + ' text-gray-400';
});

document.getElementById('cor').addEventListener('input', function() {
    const color = this.value;
    document.getElementById('preview-icon').style.backgroundColor = color;
    document.getElementById('cor-text').value = color;
});

document.getElementById('cor-text').addEventListener('input', function() {
    const color = this.value;
    if (/^#[0-9A-F]{6}$/i.test(color)) {
        document.getElementById('cor').value = color;
        document.getElementById('preview-icon').style.backgroundColor = color;
    }
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>
