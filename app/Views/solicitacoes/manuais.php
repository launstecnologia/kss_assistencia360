<?php
/**
 * View: Solicitações Manuais (Admin)
 * Listagem e gerenciamento de solicitações criadas por usuários não logados
 */
$title = 'Solicitações Manuais - Portal do Operador';
$currentPage = 'solicitacoes-manuais';
ob_start();
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-file-alt mr-2"></i>
                Solicitações Manuais
            </h1>
            <p class="text-gray-600 mt-1">
                Gerencie solicitações criadas por usuários não logados
            </p>
        </div>
        <div class="flex gap-3">
            <a href="<?= url('admin/solicitacoes-manuais/nova') ?>" 
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Nova Solicitação Manual
            </a>
            <a href="<?= url('admin/kanban') ?>" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-columns mr-2"></i>
                Ver Kanban
            </a>
            <a href="<?= url('admin/solicitacoes') ?>" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-clipboard-list mr-2"></i>
                Solicitações Normais
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="<?= url('admin/dashboard') ?>" class="text-gray-700 hover:text-blue-600 inline-flex items-center">
                <i class="fas fa-home mr-2"></i>
                Dashboard
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <span class="text-gray-500">Solicitações Manuais</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total de Solicitações</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['total'] ?? 0 ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-file-alt text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Aguardando Migração</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['nao_migradas'] ?? 0 ?></p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <i class="fas fa-clock text-2xl text-yellow-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Migradas</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['migradas'] ?? 0 ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="<?= url('admin/solicitacoes-manuais') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Busca -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input type="text" name="busca" 
                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                   placeholder="Nome, CPF..."
                   value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
        </div>
        
        <!-- Imobiliária -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Imobiliária</label>
            <select name="imobiliaria_id" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                <option value="">Todas</option>
                <?php foreach ($imobiliarias as $imob): ?>
                    <option value="<?= $imob['id'] ?>" <?= ($filtros['imobiliaria_id'] ?? '') == $imob['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($imob['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Status -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status_id" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                <option value="">Todos</option>
                <?php foreach ($statusList as $st): ?>
                    <option value="<?= $st['id'] ?>" <?= ($filtros['status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Situação -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Situação</label>
            <select name="migrada" 
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                <option value="">Todas</option>
                <option value="0" <?= isset($filtros['migrada']) && $filtros['migrada'] === false ? 'selected' : '' ?>>Aguardando Migração</option>
                <option value="1" <?= isset($filtros['migrada']) && $filtros['migrada'] === true ? 'selected' : '' ?>>Migradas</option>
            </select>
        </div>
        
        <!-- Botões -->
        <div class="md:col-span-4 flex justify-end gap-2">
            <a href="<?= url('admin/solicitacoes-manuais') ?>" 
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Limpar
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-search mr-2"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Lista de Solicitações -->
<div class="bg-white rounded-lg shadow-sm">
    <?php if (!empty($solicitacoes)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Serviço
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Endereço
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($solicitacoes as $solicitacao): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($solicitacao['nome_completo']) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    CPF: <?= htmlspecialchars($solicitacao['cpf']) ?><br>
                                    <?= htmlspecialchars($solicitacao['whatsapp']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?= htmlspecialchars($solicitacao['subcategoria_nome']) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?= htmlspecialchars($solicitacao['categoria_nome']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?= htmlspecialchars($solicitacao['endereco']) ?>, <?= htmlspecialchars($solicitacao['numero']) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?= htmlspecialchars($solicitacao['cidade']) ?> - <?= htmlspecialchars($solicitacao['estado']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                      style="background-color: <?= htmlspecialchars($solicitacao['status_cor']) ?>20; color: <?= htmlspecialchars($solicitacao['status_cor']) ?>;">
                                    <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                </span>
                                <?php if ($solicitacao['migrada']): ?>
                                    <span class="block mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i> Migrada
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($solicitacao['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <button onclick="verDetalhes(<?= $solicitacao['id'] ?>)" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </button>
                                <?php if (!$solicitacao['migrada']): ?>
                                    <a href="<?= url('admin/solicitacoes-manuais/' . $solicitacao['id'] . '/editar') ?>" 
                                       class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                    <button onclick="confirmarMigracao(<?= $solicitacao['id'] ?>)" 
                                            class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-arrow-right mr-1"></i> Migrar
                                    </button>
                                <?php else: ?>
                                    <a href="<?= url('admin/solicitacoes/show/' . $solicitacao['migrada_para_solicitacao_id']) ?>" 
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-external-link-alt mr-1"></i> Ver Original
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma solicitação encontrada</h3>
            <p class="text-gray-500">Não há solicitações manuais no momento.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Detalhes -->
<div id="modal-detalhes" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-900">
                <i class="fas fa-file-alt mr-2"></i>
                Detalhes da Solicitação Manual
            </h3>
            <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="modal-content" class="px-6 py-4">
            <!-- Conteúdo será preenchido via JavaScript -->
            <div class="flex items-center justify-center py-12">
                <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
            </div>
        </div>
    </div>
</div>

<script>
// Ver detalhes da solicitação
async function verDetalhes(id) {
    const modal = document.getElementById('modal-detalhes');
    const content = document.getElementById('modal-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="flex items-center justify-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i></div>';
    
    try {
        const response = await fetch(`<?= url('admin/solicitacoes-manuais') ?>/${id}`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao buscar detalhes');
        }
        
        const s = data.solicitacao;
        const horarios = s.horarios_preferenciais || [];
        const fotos = s.fotos || [];
        
        content.innerHTML = `
            <div class="space-y-6">
                <!-- Dados Pessoais -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        Dados Pessoais
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600">Nome:</span>
                            <p class="text-gray-900">${s.nome_completo}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">CPF:</span>
                            <p class="text-gray-900">${s.cpf}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">WhatsApp:</span>
                            <p class="text-gray-900">
                                <a href="https://wa.me/55${s.whatsapp.replace(/\D/g, '')}" target="_blank" class="text-green-600 hover:text-green-700">
                                    <i class="fab fa-whatsapp mr-1"></i>${s.whatsapp}
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Endereço -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-red-600"></i>
                        Endereço do Imóvel
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-600">Tipo:</span>
                            <p class="text-gray-900">${s.tipo_imovel}${s.subtipo_imovel ? ' - ' + s.subtipo_imovel : ''}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">CEP:</span>
                            <p class="text-gray-900">${s.cep}</p>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium text-gray-600">Endereço Completo:</span>
                            <p class="text-gray-900">
                                ${s.endereco}, ${s.numero}${s.complemento ? ' - ' + s.complemento : ''}<br>
                                ${s.bairro}, ${s.cidade} - ${s.estado}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Serviço -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-cog mr-2 text-purple-600"></i>
                        Serviço Solicitado
                    </h4>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="font-medium text-gray-600">Categoria:</span>
                            <p class="text-gray-900">${s.categoria_nome}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Subcategoria:</span>
                            <p class="text-gray-900">${s.subcategoria_nome}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Descrição do Problema:</span>
                            <p class="text-gray-900 whitespace-pre-wrap">${s.descricao_problema}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Horários -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-clock mr-2 text-orange-600"></i>
                        Horários Preferenciais
                    </h4>
                    ${horarios.length > 0 ? `
                        <ul class="text-sm text-gray-900 space-y-1">
                            ${horarios.map(h => `<li><i class="fas fa-calendar-alt mr-2 text-gray-400"></i>${h}</li>`).join('')}
                        </ul>
                    ` : '<p class="text-sm text-gray-500">Nenhum horário informado</p>'}
                </div>
                
                <!-- Fotos -->
                ${fotos.length > 0 ? `
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-images mr-2 text-green-600"></i>
                            Fotos Anexadas
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            ${fotos.map(foto => `
                                <a href="${foto}" target="_blank" class="block">
                                    <img src="${foto}" class="w-full h-24 object-cover rounded-lg border border-gray-200 hover:opacity-75 transition-opacity">
                                </a>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                <!-- Status e Ações -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Atual</label>
                            <select id="status-select-${s.id}" class="border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                ${data.statusList.map(st => `
                                    <option value="${st.id}" ${st.id == s.status_id ? 'selected' : ''}>${st.nome}</option>
                                `).join('')}
                            </select>
                        </div>
                        <button onclick="atualizarStatus(${s.id})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Salvar Status
                        </button>
                    </div>
                    
                    ${!s.migrada_para_solicitacao_id ? `
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="text-sm font-semibold text-yellow-900 mb-1">Migrar para o Sistema Principal</h5>
                                    <p class="text-sm text-yellow-700">Esta solicitação será convertida em uma solicitação normal e aparecerá no Kanban.</p>
                                </div>
                                <button onclick="migrarSolicitacao(${s.id})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                                    <i class="fas fa-arrow-right mr-2"></i>Migrar Agora
                                </button>
                            </div>
                        </div>
                    ` : `
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="text-sm font-semibold text-green-900 mb-1">
                                        <i class="fas fa-check-circle mr-1"></i>Solicitação Migrada
                                    </h5>
                                    <p class="text-sm text-green-700">
                                        Migrada em ${new Date(s.migrada_em).toLocaleString('pt-BR')} por ${s.migrada_por_nome || 'Desconhecido'}
                                    </p>
                                </div>
                                <a href="<?= url('admin/solicitacoes/show') ?>/${s.migrada_para_solicitacao_id}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                    <i class="fas fa-external-link-alt mr-2"></i>Ver Solicitação
                                </a>
                            </div>
                        </div>
                    `}
                </div>
                
                <!-- Informações Técnicas -->
                <div class="text-xs text-gray-500 border-t border-gray-200 pt-4">
                    <p><strong>ID:</strong> ${s.id}</p>
                    <p><strong>Criada em:</strong> ${new Date(s.created_at).toLocaleString('pt-BR')}</p>
                    <p><strong>Imobiliária:</strong> ${s.imobiliaria_nome}</p>
                    <p><strong>Termos Aceitos:</strong> ${s.termos_aceitos ? 'Sim' : 'Não'}</p>
                </div>
            </div>
        `;
    } catch (error) {
        content.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Erro ao carregar detalhes</h4>
                <p class="text-gray-600">${error.message}</p>
            </div>
        `;
    }
}

// Atualizar status
async function atualizarStatus(id) {
    const select = document.getElementById(`status-select-${id}`);
    const statusId = select.value;
    
    if (!confirm('Deseja realmente alterar o status desta solicitação?')) {
        return;
    }
    
    try {
        const response = await fetch(`<?= url('admin/solicitacoes-manuais') ?>/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status_id: statusId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Status atualizado com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        alert('Erro ao atualizar status: ' + error.message);
    }
}

// Migrar solicitação
async function migrarSolicitacao(id) {
    if (!confirm('Deseja migrar esta solicitação para o sistema principal? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    try {
        const response = await fetch(`<?= url('admin/solicitacoes-manuais') ?>/${id}/migrar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Solicitação migrada com sucesso! ID da nova solicitação: #' + data.solicitacao_id);
            location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (error) {
        alert('Erro ao migrar solicitação: ' + error.message);
    }
}

// Confirmar migração direto da lista
function confirmarMigracao(id) {
    migrarSolicitacao(id);
}

// Fechar modal
function fecharModal() {
    document.getElementById('modal-detalhes').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('modal-detalhes')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>

