<?php
/**
 * View: Perfil do Locatário
 */
$title = 'Meu Perfil - Assistência 360°';
$currentPage = 'locatario-perfil';
ob_start();
?>

<!-- Header -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">
        <i class="fas fa-user mr-2"></i>
        Meu Perfil
    </h1>
    <p class="text-gray-600 mt-1">
        Visualize e gerencie suas informações pessoais
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-user mr-2"></i>
                        Seus Dados
                    </h2>
                    <button onclick="editarDados()" 
                            class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <i class="fas fa-edit mr-1"></i>
                        Editar
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Info -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Informações Pessoais</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-user text-gray-400 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($locatario['nome']) ?></p>
                                    <p class="text-xs text-gray-500">Nome completo</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-id-card text-gray-400 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($locatario['cpf']) ?></p>
                                    <p class="text-xs text-gray-500">CPF/CNPJ</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Contato</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fab fa-whatsapp text-green-500 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">(16) 99242-2354</p>
                                    <p class="text-xs text-gray-500">WhatsApp</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-gray-400 mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">lucas@email.com</p>
                                    <p class="text-xs text-gray-500">E-mail</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-green-50 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-green-600 mr-2 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-green-800 font-medium">WhatsApp</p>
                            <p class="text-xs text-green-700">Usado para enviar notificações importantes sobre suas solicitações</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Properties -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-home mr-2"></i>
                    Seus Imóveis
                </h2>
                <p class="text-sm text-gray-500 mt-1">Imóveis vinculados ao seu contrato</p>
            </div>
            
            <div class="p-6">
                <?php if (!empty($locatario['imoveis'])): ?>
                    <div class="space-y-4">
                        <?php foreach ($locatario['imoveis'] as $imovel): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-home text-gray-400 mr-2"></i>
                                            <h3 class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($imovel['endereco'] ?? 'Endereço não informado') ?>
                                            </h3>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-1">
                                            <?= htmlspecialchars($imovel['bairro'] ?? '') ?> - 
                                            <?= htmlspecialchars($imovel['cidade'] ?? '') ?>/<?= htmlspecialchars($imovel['estado'] ?? '') ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            CEP: <?= htmlspecialchars($imovel['cep'] ?? '') ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500">Contrato: <?= htmlspecialchars($imovel['contrato'] ?? '') ?></p>
                                        <p class="text-xs text-gray-500">Cód: <?= htmlspecialchars($imovel['codigo'] ?? '') ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-home text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum imóvel encontrado</h3>
                        <p class="text-gray-500">Não foram encontrados imóveis vinculados ao seu contrato.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Account Status -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Status da Conta</h2>
            </div>
            
            <div class="p-6">
                <div class="text-center">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 mb-4">
                        <i class="fas fa-check-circle mr-1"></i>
                        Ativo
                    </div>
                    
                    <div class="space-y-3 text-sm text-gray-600">
                        <div>
                            <p class="font-medium text-gray-900">Última sincronização</p>
                            <p><?= date('d/m/Y') ?></p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Dados sincronizados em</p>
                            <p><?= date('d/m/Y, H:i:s') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Ações Rápidas</h2>
            </div>
            
            <div class="p-6">
        <div class="space-y-3">
            <a href="<?= url($locatario['instancia'] . '/nova-solicitacao') ?>" 
               class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="fas fa-plus-circle mr-3 text-green-600"></i>
                <span>Nova Solicitação</span>
            </a>
            <a href="<?= url($locatario['instancia'] . '/solicitacoes') ?>" 
               class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="fas fa-list mr-3 text-blue-600"></i>
                <span>Minhas Solicitações</span>
            </a>
            <a href="<?= url($locatario['instancia'] . '/dashboard') ?>" 
               class="flex items-center p-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="fas fa-home mr-3 text-purple-600"></i>
                <span>Dashboard</span>
            </a>
        </div>
            </div>
        </div>
        
        <!-- Real Estate Info -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Imobiliária</h2>
            </div>
            
            <div class="p-6">
                <div class="text-center">
                    <?php if (!empty($locatario['imobiliaria_logo'])): ?>
                        <img src="<?= asset('uploads/' . $locatario['imobiliaria_logo']) ?>" 
                             alt="<?= htmlspecialchars($locatario['imobiliaria_nome']) ?>" 
                             class="mx-auto h-12 w-auto mb-3">
                    <?php else: ?>
                        <div class="mx-auto h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center mb-3">
                            <i class="fas fa-building text-white"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="text-sm font-medium text-gray-900">
                        <?= htmlspecialchars($locatario['imobiliaria_nome']) ?>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        <?= htmlspecialchars($locatario['imobiliaria_nome_fantasia'] ?? '') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Editar Dados</h3>
                <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="edit-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($locatario['nome']) ?>" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">WhatsApp</label>
                    <input type="text" name="whatsapp" value="(16) 99242-2354" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="email" value="lucas@email.com" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarDados() {
    document.getElementById('edit-modal').classList.remove('hidden');
}

function fecharModal() {
    document.getElementById('edit-modal').classList.add('hidden');
}

document.getElementById('edit-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Implementar salvamento dos dados
    const formData = new FormData(this);
    
    fetch('<?= url($locatario['instancia'] . '/atualizar-perfil') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao atualizar dados: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar dados');
    });
});

// Máscara para WhatsApp
document.querySelector('input[name="whatsapp"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '($1) $2');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/locatario.php';
?>
