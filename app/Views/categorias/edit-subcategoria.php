<?php
/**
 * View: Editar Subcategoria
 */
$title = 'Editar ' . $subcategoria['nome'];
$currentPage = 'categorias';
$pageTitle = 'Editar ' . $subcategoria['nome'];
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
                <span class="text-sm font-medium text-gray-500">Editar Subcategoria</span>
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
        <h2 class="text-2xl font-bold text-gray-900">Editar Subcategoria</h2>
        <p class="text-sm text-gray-600">Categoria: <?= htmlspecialchars($categoria['nome']) ?></p>
    </div>
</div>

<!-- Formulário -->
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Editar Subcategoria</h3>
        <p class="text-sm text-gray-500">Atualize os dados da subcategoria de assistência</p>
    </div>
    
    <form method="POST" action="<?= url('admin/categorias/' . $categoria['id'] . '/subcategorias/' . $subcategoria['id']) ?>" class="p-6 space-y-6">
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
                    Nome da Subcategoria <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       id="nome" 
                       value="<?= htmlspecialchars($data['nome'] ?? $subcategoria['nome']) ?>"
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
                          placeholder="Descreva brevemente esta subcategoria de assistência"><?= htmlspecialchars($data['descricao'] ?? $subcategoria['descricao']) ?></textarea>
                <?php if (isset($errors['descricao'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['descricao']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Prazo Mínimo -->
            <div>
                <label for="prazo_minimo" class="block text-sm font-medium text-gray-700">
                    Prazo Mínimo (dias) <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="prazo_minimo" 
                       id="prazo_minimo" 
                       value="<?= htmlspecialchars($data['prazo_minimo'] ?? $subcategoria['prazo_minimo'] ?? '1') ?>"
                       min="0"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm <?= isset($errors['prazo_minimo']) ? 'border-red-300' : '' ?>"
                       placeholder="1"
                       required>
                <p class="mt-1 text-xs text-gray-500">
                    Dias mínimos a partir de hoje para poder agendar. Ex: 0 = hoje, 1 = amanhã, 2 = depois de amanhã. Sábados e domingos são automaticamente excluídos.
                </p>
                <?php if (isset($errors['prazo_minimo'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['prazo_minimo']) ?></p>
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
                    <option value="ATIVA" <?= ($data['status'] ?? $subcategoria['status']) === 'ATIVA' ? 'selected' : '' ?>>Ativa</option>
                    <option value="INATIVA" <?= ($data['status'] ?? $subcategoria['status']) === 'INATIVA' ? 'selected' : '' ?>>Inativa</option>
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
                       value="<?= htmlspecialchars($data['ordem'] ?? $subcategoria['ordem']) ?>"
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
            
            <!-- É Emergencial -->
            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_emergencial" 
                           id="is_emergencial" 
                           value="1"
                           <?= ($data['is_emergencial'] ?? $subcategoria['is_emergencial'] ?? 0) ? 'checked' : '' ?>
                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <label for="is_emergencial" class="ml-2 block text-sm font-medium text-gray-700">
                        É Emergencial
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500 ml-6">
                    Marque esta opção se esta subcategoria representa uma emergência. Solicitações emergenciais serão processadas imediatamente sem agendamento.
                </p>
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
                            <?= htmlspecialchars($data['nome'] ?? $subcategoria['nome']) ?>
                        </h6>
                        <span id="preview-status" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            <?= ($data['status'] ?? $subcategoria['status']) === 'ATIVA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= ($data['status'] ?? $subcategoria['status']) === 'ATIVA' ? 'ATIVA' : 'INATIVA' ?>
                        </span>
                    </div>
                    <p id="preview-descricao" class="text-xs text-gray-500 mt-1">
                        <?= htmlspecialchars($data['descricao'] ?? $subcategoria['descricao'] ?? 'Descrição da subcategoria') ?>
                    </p>
                    <p id="preview-prazo" class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-calendar-day mr-1"></i>
                        Prazo mínimo: <span id="preview-prazo-value"><?= htmlspecialchars($data['prazo_minimo'] ?? $subcategoria['prazo_minimo'] ?? '1') ?></span> dia(s)
                    </p>
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
                Salvar Alterações
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

document.getElementById('prazo_minimo').addEventListener('input', function() {
    const prazo = this.value;
    const previewPrazoValue = document.getElementById('preview-prazo-value');
    if (previewPrazoValue) {
        previewPrazoValue.textContent = prazo || '1';
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
