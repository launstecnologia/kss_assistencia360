<?php
$title = 'Gerenciamento de Usuários';
$currentPage = 'usuarios';
$pageTitle = 'Usuários';
ob_start();
?>

<!-- Barra de Ações -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
    <div class="flex-1 w-full sm:w-auto">
        <form method="GET" action="<?= url('admin/usuarios') ?>" class="flex gap-2">
            <input 
                type="text" 
                name="busca" 
                placeholder="Buscar por nome, email, CPF ou código..." 
                value="<?= htmlspecialchars($busca ?? '') ?>"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    
    <a href="<?= url('admin/usuarios/create') ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <i class="fas fa-plus mr-2"></i>
        Novo Usuário
    </a>
</div>

<!-- Tabela de Usuários -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Cadastro</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível Acesso</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                        Nenhum usuário encontrado
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($usuarios as $usuario): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #<?= str_pad($usuario['id'], 4, '0', STR_PAD_LEFT) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($usuario['nome']) ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($usuario['telefone'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($usuario['email']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($usuario['cpf'] ?? '') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d/m/Y', strtotime($usuario['created_at'])) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            <?= $usuario['nivel_permissao'] === 'ADMINISTRADOR' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                            <?= htmlspecialchars($usuario['nivel_permissao']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                value="" 
                                class="sr-only peer" 
                                <?= $usuario['status'] === 'ATIVO' ? 'checked' : '' ?>
                                onchange="toggleStatus(<?= $usuario['id'] ?>)"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <a href="<?= url("admin/usuarios/{$usuario['id']}/edit") ?>" 
                               class="text-blue-600 hover:text-blue-900" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button 
                                onclick="mostrarModalResetarSenha(<?= $usuario['id'] ?>)" 
                                class="text-yellow-600 hover:text-yellow-900" 
                                title="Resetar Senha">
                                <i class="fas fa-key"></i>
                            </button>
                            <button 
                                onclick="excluirUsuario(<?= $usuario['id'] ?>)" 
                                class="text-red-600 hover:text-red-900" 
                                title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Resetar Senha -->
<div id="modalResetarSenha" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" onclick="fecharModalResetarSenha()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Resetar Senha</h3>
            <form onsubmit="resetarSenha(event)">
                <input type="hidden" id="resetarSenhaUsuarioId" name="usuario_id" value="">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nova Senha</label>
                    <input 
                        type="password" 
                        id="novaSenha" 
                        name="nova_senha" 
                        required 
                        minlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                <div class="flex justify-end gap-3">
                    <button 
                        type="button" 
                        onclick="fecharModalResetarSenha()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Resetar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>

<script>
function toggleStatus(usuarioId) {
    if (confirm('Deseja realmente alterar o status deste usuário?')) {
        fetch(`<?= url('admin/usuarios/') ?>${usuarioId}/toggle-status`, {
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
                alert(data.error || 'Erro ao atualizar status');
                location.reload();
            }
        })
        .catch(error => {
            alert('Erro ao atualizar status');
            console.error('Error:', error);
            location.reload();
        });
    } else {
        location.reload();
    }
}

function mostrarModalResetarSenha(usuarioId) {
    document.getElementById('resetarSenhaUsuarioId').value = usuarioId;
    document.getElementById('modalResetarSenha').classList.remove('hidden');
    document.getElementById('novaSenha').value = '';
    document.getElementById('novaSenha').focus();
}

function fecharModalResetarSenha() {
    document.getElementById('modalResetarSenha').classList.add('hidden');
}

function resetarSenha(event) {
    event.preventDefault();
    
    const usuarioId = document.getElementById('resetarSenhaUsuarioId').value;
    const novaSenha = document.getElementById('novaSenha').value;
    
    fetch(`<?= url('admin/usuarios/') ?>${usuarioId}/resetar-senha`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ nova_senha: novaSenha })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            fecharModalResetarSenha();
        } else {
            alert(data.error || 'Erro ao resetar senha');
        }
    })
    .catch(error => {
        alert('Erro ao resetar senha');
        console.error('Error:', error);
    });
}

function excluirUsuario(usuarioId) {
    if (confirm('Deseja realmente excluir este usuário? Esta ação não pode ser desfeita.')) {
        fetch(`<?= url('admin/usuarios/') ?>${usuarioId}/delete`, {
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
                alert(data.error || 'Erro ao excluir usuário');
            }
        })
        .catch(error => {
            alert('Erro ao excluir usuário');
            console.error('Error:', error);
        });
    }
}

// Fechar modal com ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        fecharModalResetarSenha();
    }
});
</script>

