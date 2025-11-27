<?php
/**
 * View: Lista de Imobiliárias
 */
$title = 'Imobiliárias';
$currentPage = 'imobiliarias';
$pageTitle = 'Imobiliárias';
ob_start();
?>

<!-- Token CSRF (oculto) -->
<?= \App\Core\View::csrfField() ?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Imobiliárias</h2>
        <p class="text-sm text-gray-600">Gerencie as imobiliárias parceiras do sistema</p>
    </div>
    <button onclick="abrirOffcanvasNovaImobiliaria()" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <i class="fas fa-plus mr-2"></i>
        Nova Imobiliária
    </button>
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
            <button onclick="abrirOffcanvasNovaImobiliaria()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>
                Cadastrar Primeira Imobiliária
            </button>
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
                                         class="w-20 h-20 rounded-lg object-contain border border-gray-200 bg-white p-1">
                                <?php else: ?>
                                    <div class="w-20 h-20 rounded-lg flex items-center justify-center text-white text-lg font-bold overflow-hidden"
                                         style="background: linear-gradient(135deg, <?= $imobiliaria['cor_primaria'] ?? '#3B82F6' ?>, <?= $imobiliaria['cor_secundaria'] ?? '#1E40AF' ?>)">
                                        <span class="truncate px-2"><?= strtoupper(substr($imobiliaria['nome_fantasia'] ?? 'IM', 0, 2)) ?></span>
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
                            <button onclick="abrirModalListagemContratos(<?= $imobiliaria['id'] ?>)" 
                                    class="inline-flex items-center px-3 py-1 border border-purple-300 rounded-md text-sm font-medium text-purple-700 bg-white hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <i class="fas fa-list mr-1"></i>
                                Listagem
                            </button>
                            
                            <button onclick="abrirOffcanvasVer(<?= $imobiliaria['id'] ?>)" 
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-eye mr-1"></i>
                                Ver
                            </button>
                            
                            <button onclick="abrirOffcanvasEditar(<?= $imobiliaria['id'] ?>)" 
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-1"></i>
                                Editar
                            </button>
                            
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

<!-- Offcanvas para Ver/Editar/Criar Imobiliária -->
<div id="offcanvasImobiliaria" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" onclick="fecharOffcanvasImobiliaria()"></div>
    <div id="offcanvasImobiliariaPanel" class="fixed right-0 top-0 h-full w-full md:w-[90%] lg:w-[900px] bg-gray-50 shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-building text-gray-600"></i>
                    <h2 id="offcanvasImobiliariaTitle" class="text-xl font-bold text-gray-900">Imobiliária</h2>
                </div>
                <button onclick="fecharOffcanvasImobiliaria()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div id="offcanvasImobiliariaContent" class="p-6">
            <div id="loadingImobiliaria" class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                    <p class="text-gray-600">Carregando...</p>
                </div>
            </div>
            <div id="formImobiliaria" class="hidden"></div>
        </div>
    </div>
</div>

<!-- Modal de Upload Excel -->
<div id="upload-excel-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fas fa-file-excel text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4 text-center">Upload de Excel</h3>
            <p class="text-sm text-gray-500 mt-2 text-center">
                Envie um arquivo Excel (.xlsx ou .xls) ou CSV (.csv) com duas colunas:<br>
                <strong>CPF</strong> e <strong>Número do Contrato</strong>
            </p>
            <div class="mt-4">
                <form id="form-upload-excel" enctype="multipart/form-data">
                    <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" 
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    <p class="mt-2 text-xs text-gray-500">Primeira linha deve conter os cabeçalhos (CPF, Número do Contrato). CSV pode usar vírgula (,) ou ponto e vírgula (;) como separador.</p>
                </form>
            </div>
            <div class="items-center px-4 py-3 mt-4">
                <button id="upload-excel-button" 
                        class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i class="fas fa-upload mr-2"></i>
                    Enviar Arquivo
                </button>
                <button onclick="fecharModalUploadExcel()" 
                        class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
            <div id="upload-excel-result" class="mt-4 hidden"></div>
        </div>
    </div>
</div>

<!-- Modal de Listagem de Contratos -->
<div id="listagem-contratos-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white my-10">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="mx-auto flex items-center justify-center h-10 w-10 rounded-full bg-purple-100 mr-3">
                        <i class="fas fa-list text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Listagem de CPFs e Contratos</h3>
                </div>
                <button onclick="fecharModalListagemContratos()" 
                        class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="listagem-contratos-content" class="mt-4">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                    <p class="text-gray-500 mt-2">Carregando...</p>
                </div>
            </div>
        </div>
    </div>
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

// ========== FUNÇÕES DO OFFCANVAS ==========
let modoOffcanvas = null; // 'ver', 'editar', 'criar'
let imobiliariaEditandoId = null;

function abrirOffcanvasVer(id) {
    modoOffcanvas = 'ver';
    imobiliariaEditandoId = id;
    
    const offcanvas = document.getElementById('offcanvasImobiliaria');
    const panel = document.getElementById('offcanvasImobiliariaPanel');
    const loading = document.getElementById('loadingImobiliaria');
    const form = document.getElementById('formImobiliaria');
    const title = document.getElementById('offcanvasImobiliariaTitle');
    
    title.textContent = 'Detalhes da Imobiliária';
    
    offcanvas.classList.remove('hidden');
    setTimeout(() => panel.classList.remove('translate-x-full'), 10);
    
    loading.classList.remove('hidden');
    form.classList.add('hidden');
    
    fetch(`<?= url('admin/imobiliarias/') ?>${id}/api`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarFormularioVer(data.imobiliaria);
            } else {
                form.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                        <p class="text-gray-600">${data.message || 'Erro ao carregar dados da imobiliária'}</p>
                    </div>
                `;
            }
            loading.classList.add('hidden');
            form.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Erro:', error);
            form.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                    <p class="text-gray-600">Erro ao carregar dados da imobiliária</p>
                </div>
            `;
            loading.classList.add('hidden');
            form.classList.remove('hidden');
        });
}

function abrirOffcanvasEditar(id) {
    modoOffcanvas = 'editar';
    imobiliariaEditandoId = id;
    
    const offcanvas = document.getElementById('offcanvasImobiliaria');
    const panel = document.getElementById('offcanvasImobiliariaPanel');
    const loading = document.getElementById('loadingImobiliaria');
    const form = document.getElementById('formImobiliaria');
    const title = document.getElementById('offcanvasImobiliariaTitle');
    
    title.textContent = 'Editar Imobiliária';
    
    offcanvas.classList.remove('hidden');
    setTimeout(() => panel.classList.remove('translate-x-full'), 10);
    
    loading.classList.remove('hidden');
    form.classList.add('hidden');
    
    fetch(`<?= url('admin/imobiliarias/') ?>${id}/api`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarFormularioEditar(data.imobiliaria);
            } else {
                form.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                        <p class="text-gray-600">${data.message || 'Erro ao carregar dados da imobiliária'}</p>
                    </div>
                `;
            }
            loading.classList.add('hidden');
            form.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Erro:', error);
            form.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                    <p class="text-gray-600">Erro ao carregar dados da imobiliária</p>
                </div>
            `;
            loading.classList.add('hidden');
            form.classList.remove('hidden');
        });
}

function abrirOffcanvasNovaImobiliaria() {
    modoOffcanvas = 'criar';
    imobiliariaEditandoId = null;
    
    const offcanvas = document.getElementById('offcanvasImobiliaria');
    const panel = document.getElementById('offcanvasImobiliariaPanel');
    const loading = document.getElementById('loadingImobiliaria');
    const form = document.getElementById('formImobiliaria');
    const title = document.getElementById('offcanvasImobiliariaTitle');
    
    title.textContent = 'Nova Imobiliária';
    
    offcanvas.classList.remove('hidden');
    setTimeout(() => panel.classList.remove('translate-x-full'), 10);
    
    loading.classList.add('hidden');
    form.classList.remove('hidden');
    
    renderizarFormularioCriar();
}

function fecharOffcanvasImobiliaria() {
    const offcanvas = document.getElementById('offcanvasImobiliaria');
    const panel = document.getElementById('offcanvasImobiliariaPanel');
    
    panel.classList.add('translate-x-full');
    setTimeout(() => {
        offcanvas.classList.add('hidden');
        modoOffcanvas = null;
        imobiliariaEditandoId = null;
        document.getElementById('formImobiliaria').innerHTML = '';
    }, 300);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function renderizarFormularioVer(imobiliaria) {
    const form = document.getElementById('formImobiliaria');
    
    const logoUrl = imobiliaria.logo ? `<?= url('Public/uploads/logos/') ?>${imobiliaria.logo}` : '';
    const logoHtml = logoUrl ? 
        `<img src="${logoUrl}" alt="Logo" class="w-24 h-24 rounded-lg object-contain border border-gray-200 bg-white p-2">` :
        `<div class="w-24 h-24 rounded-lg flex items-center justify-center text-white text-lg font-bold overflow-hidden" style="background: linear-gradient(135deg, ${imobiliaria.cor_primaria || '#3B82F6'}, ${imobiliaria.cor_secundaria || '#1E40AF'});"><span class="truncate px-2">${(imobiliaria.nome_fantasia || 'IM').substring(0, 2).toUpperCase()}</span></div>`;
    
    form.innerHTML = `
        <div class="space-y-6">
            <!-- Logo e Status -->
            <div class="flex items-center gap-4 pb-4 border-b">
                ${logoHtml}
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900">${escapeHtml(imobiliaria.nome_fantasia || imobiliaria.nome || 'Sem nome')}</h3>
                    <p class="text-sm text-gray-600 mt-1">${escapeHtml(imobiliaria.razao_social || 'Razão social não informada')}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 ${imobiliaria.status === 'ATIVA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${imobiliaria.status || 'N/A'}
                    </span>
                </div>
            </div>
            
            <!-- Dados Empresariais -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Dados Empresariais</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CNPJ</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.cnpj || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Razão Social</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.razao_social || 'Não informado')}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nome Fantasia</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.nome_fantasia || 'Não informado')}</p>
                    </div>
                </div>
            </div>
            
            <!-- Endereço -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Endereço</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Logradouro</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.endereco_logradouro || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Número</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.endereco_numero || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Complemento</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.endereco_complemento || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bairro</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.endereco_bairro || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cidade</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.endereco_cidade || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.endereco_estado || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CEP</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.endereco_cep || 'Não informado')}</p>
                    </div>
                </div>
            </div>
            
            <!-- Contato -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Contato</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefone</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.telefone || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.email || 'Não informado')}</p>
                    </div>
                </div>
            </div>
            
            <!-- Configurações da API -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Configurações da API</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">API ID</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.api_id || 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">URL Base</label>
                        <p class="mt-1 text-sm text-gray-900">
                            ${imobiliaria.url_base ? `<a href="${escapeHtml(imobiliaria.url_base)}" target="_blank" class="text-blue-600 hover:underline">${escapeHtml(imobiliaria.url_base)}</a>` : 'Não informado'}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Token</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">${escapeHtml(imobiliaria.token ? imobiliaria.token.substring(0, 20) + '...' : 'Não informado')}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Instância</label>
                        <p class="mt-1 text-sm text-gray-900">${escapeHtml(imobiliaria.instancia || 'Não informado')}</p>
                    </div>
                </div>
            </div>
            
            <!-- Personalização -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Personalização</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                        <div class="flex items-center justify-center">
                            ${logoHtml}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cor Primária</label>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-md border-2 border-gray-300" style="background-color: ${escapeHtml(imobiliaria.cor_primaria || '#3B82F6')};"></div>
                            <span class="text-sm text-gray-600 font-mono">${escapeHtml(imobiliaria.cor_primaria || '#3B82F6')}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cor Secundária</label>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-md border-2 border-gray-300" style="background-color: ${escapeHtml(imobiliaria.cor_secundaria || '#1E40AF')};"></div>
                            <span class="text-sm text-gray-600 font-mono">${escapeHtml(imobiliaria.cor_secundaria || '#1E40AF')}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Observações -->
            ${imobiliaria.observacoes ? `
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Observações</h4>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(imobiliaria.observacoes)}</p>
            </div>
            ` : ''}
            
            <!-- Botões de Ação -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button onclick="fecharOffcanvasImobiliaria()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Fechar
                </button>
                <button onclick="abrirOffcanvasEditar(${imobiliaria.id})" 
                        class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </button>
            </div>
        </div>
    `;
}

function renderizarFormularioEditar(imobiliaria) {
    const form = document.getElementById('formImobiliaria');
    
    // Estados brasileiros
    const estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
    const estadosNomes = {
        'AC': 'Acre', 'AL': 'Alagoas', 'AP': 'Amapá', 'AM': 'Amazonas', 'BA': 'Bahia',
        'CE': 'Ceará', 'DF': 'Distrito Federal', 'ES': 'Espírito Santo', 'GO': 'Goiás',
        'MA': 'Maranhão', 'MT': 'Mato Grosso', 'MS': 'Mato Grosso do Sul', 'MG': 'Minas Gerais',
        'PA': 'Pará', 'PB': 'Paraíba', 'PR': 'Paraná', 'PE': 'Pernambuco', 'PI': 'Piauí',
        'RJ': 'Rio de Janeiro', 'RN': 'Rio Grande do Norte', 'RS': 'Rio Grande do Sul',
        'RO': 'Rondônia', 'RR': 'Roraima', 'SC': 'Santa Catarina', 'SP': 'São Paulo',
        'SE': 'Sergipe', 'TO': 'Tocantins'
    };
    
    const logoUrl = imobiliaria.logo ? `<?= url('Public/uploads/logos/') ?>${imobiliaria.logo}` : '';
    const logoPreview = logoUrl ? 
        `<div class="w-24 h-24 rounded-lg border-2 border-gray-300 bg-white p-2 flex items-center justify-center overflow-hidden" id="logo-preview-container">
            <img src="${logoUrl}" alt="Logo atual" class="max-w-full max-h-full object-contain" id="logo-preview-img">
        </div>` :
        `<div class="w-24 h-24 rounded-lg flex items-center justify-center text-white text-lg font-bold overflow-hidden border-2 border-gray-300" id="logo-preview-placeholder" style="background: linear-gradient(135deg, ${imobiliaria.cor_primaria || '#3B82F6'}, ${imobiliaria.cor_secundaria || '#1E40AF'});"><span class="truncate px-2">${(imobiliaria.nome_fantasia || 'IM').substring(0, 2).toUpperCase()}</span></div>`;
    
    const estadosOptions = estados.map(uf => 
        `<option value="${uf}" ${(imobiliaria.endereco_estado || '') === uf ? 'selected' : ''}>${estadosNomes[uf]}</option>`
    ).join('');
    
    form.innerHTML = `
        <form id="formEditarImobiliaria" onsubmit="salvarEdicao(event)" enctype="multipart/form-data">
            ${document.querySelector('input[name="_token"]') ? `<input type="hidden" name="_token" value="${document.querySelector('input[name="_token"]').value}">` : ''}
            <input type="hidden" name="_method" value="PUT">
            
            <div class="space-y-8">
                <!-- Dados Empresariais -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Dados Empresariais</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                CNPJ <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="cnpj" id="cnpj-edit" value="${escapeHtml(imobiliaria.cnpj || '')}" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
                                       placeholder="00.000.000/0000-00" required>
                                <button type="button" onclick="buscarCnpjEdit()" 
                                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Razão Social <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="razao_social" value="${escapeHtml(imobiliaria.razao_social || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Fantasia <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nome_fantasia" value="${escapeHtml(imobiliaria.nome_fantasia || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                </div>
                
                <!-- Endereço -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Endereço</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                            <input type="text" name="endereco_cep" value="${escapeHtml(imobiliaria.endereco_cep || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
                                   placeholder="00000-000">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logradouro</label>
                            <input type="text" name="endereco_logradouro" value="${escapeHtml(imobiliaria.endereco_logradouro || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                            <input type="text" name="endereco_numero" value="${escapeHtml(imobiliaria.endereco_numero || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
                            <input type="text" name="endereco_complemento" value="${escapeHtml(imobiliaria.endereco_complemento || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                            <input type="text" name="endereco_bairro" value="${escapeHtml(imobiliaria.endereco_bairro || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                            <input type="text" name="endereco_cidade" value="${escapeHtml(imobiliaria.endereco_cidade || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select name="endereco_estado" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                ${estadosOptions}
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Contato -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Contato</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                            <input type="text" name="telefone" value="${escapeHtml(imobiliaria.telefone || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
                                   placeholder="(00) 00000-0000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" value="${escapeHtml(imobiliaria.email || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <!-- Configurações da API -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Configurações da API</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                API ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="api_id" value="${escapeHtml(imobiliaria.api_id || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                URL Base <span class="text-red-500">*</span>
                            </label>
                            <input type="url" name="url_base" value="${escapeHtml(imobiliaria.url_base || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Token <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="token" value="${escapeHtml(imobiliaria.token || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Instância <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="instancia" value="${escapeHtml(imobiliaria.instancia || '')}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                </div>
                
                <!-- Personalização -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Personalização</h4>
                    <div class="space-y-6">
                        <!-- Logo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Logo</label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                ${logoPreview}
                                <div class="flex-1">
                                    <input type="file" name="logo" id="logo-edit" accept="image/*" 
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                           onchange="previewLogoEdit(event)">
                                    <p class="mt-2 text-xs text-gray-500">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cores -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Cor Primária</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="cor_primaria" value="${escapeHtml(imobiliaria.cor_primaria || '#3B82F6')}" 
                                           class="w-16 h-12 border-2 border-gray-300 rounded-md cursor-pointer">
                                    <input type="text" id="cor_primaria_text_edit" value="${escapeHtml(imobiliaria.cor_primaria || '#3B82F6')}" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                                           placeholder="#3B82F6">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Cor Secundária</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="cor_secundaria" value="${escapeHtml(imobiliaria.cor_secundaria || '#1E40AF')}" 
                                           class="w-16 h-12 border-2 border-gray-300 rounded-md cursor-pointer">
                                    <input type="text" id="cor_secundaria_text_edit" value="${escapeHtml(imobiliaria.cor_secundaria || '#1E40AF')}" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                                           placeholder="#1E40AF">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Observações -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <textarea name="observacoes" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">${escapeHtml(imobiliaria.observacoes || '')}</textarea>
                </div>
                
                <!-- Botões -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="fecharOffcanvasImobiliaria()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    `;
    
    // Aplicar máscaras
    aplicarMascaras();
    sincronizarCores();
}

function renderizarFormularioCriar() {
    const form = document.getElementById('formImobiliaria');
    
    // Estados brasileiros
    const estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
    const estadosNomes = {
        'AC': 'Acre', 'AL': 'Alagoas', 'AP': 'Amapá', 'AM': 'Amazonas', 'BA': 'Bahia',
        'CE': 'Ceará', 'DF': 'Distrito Federal', 'ES': 'Espírito Santo', 'GO': 'Goiás',
        'MA': 'Maranhão', 'MT': 'Mato Grosso', 'MS': 'Mato Grosso do Sul', 'MG': 'Minas Gerais',
        'PA': 'Pará', 'PB': 'Paraíba', 'PR': 'Paraná', 'PE': 'Pernambuco', 'PI': 'Piauí',
        'RJ': 'Rio de Janeiro', 'RN': 'Rio Grande do Norte', 'RS': 'Rio Grande do Sul',
        'RO': 'Rondônia', 'RR': 'Roraima', 'SC': 'Santa Catarina', 'SP': 'São Paulo',
        'SE': 'Sergipe', 'TO': 'Tocantins'
    };
    
    const estadosOptions = estados.map(uf => 
        `<option value="${uf}">${estadosNomes[uf]}</option>`
    ).join('');
    
    form.innerHTML = `
        <form id="formCriarImobiliaria" onsubmit="salvarCriacao(event)" enctype="multipart/form-data">
            ${document.querySelector('input[name="_token"]') ? `<input type="hidden" name="_token" value="${document.querySelector('input[name="_token"]').value}">` : ''}
            
            <div class="space-y-8">
                <!-- Dados Empresariais -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Dados Empresariais</h4>
                    <div id="receita-info-edit" class="hidden bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                        <div class="text-sm text-blue-800" id="receita-details-edit"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                CNPJ <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="cnpj" id="cnpj-create" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
                                       placeholder="00.000.000/0000-00" required>
                                <button type="button" onclick="buscarCnpjCreate()" 
                                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Razão Social <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="razao_social" id="razao_social-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Fantasia <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nome_fantasia" id="nome_fantasia-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                </div>
                
                <!-- Endereço -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Endereço</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                            <input type="text" name="endereco_cep" id="endereco_cep-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
                                   placeholder="00000-000">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logradouro</label>
                            <input type="text" name="endereco_logradouro" id="endereco_logradouro-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                            <input type="text" name="endereco_numero" id="endereco_numero-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
                            <input type="text" name="endereco_complemento" id="endereco_complemento-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                            <input type="text" name="endereco_bairro" id="endereco_bairro-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                            <input type="text" name="endereco_cidade" id="endereco_cidade-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select name="endereco_estado" id="endereco_estado-create" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione</option>
                                ${estadosOptions}
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Contato -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Contato</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                            <input type="text" name="telefone" id="telefone-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
                                   placeholder="(00) 00000-0000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="email-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <!-- Configurações da API -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Configurações da API</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                API ID
                            </label>
                            <input type="text" name="api_id" id="api_id-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                URL Base <span class="text-red-500">*</span>
                            </label>
                            <input type="url" name="url_base" id="url_base-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Token <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="token" id="token-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Instância <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="instancia" id="instancia-create" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                </div>
                
                <!-- Personalização -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Personalização</h4>
                    <div class="space-y-6">
                        <!-- Logo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Logo</label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50" id="logo-preview-create">
                                    <span class="text-gray-400 text-xs text-center px-2">Sem logo</span>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="logo" id="logo-create" accept="image/*" 
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                           onchange="previewLogoCreate(event)">
                                    <p class="mt-2 text-xs text-gray-500">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cores -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Cor Primária</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="cor_primaria" id="cor_primaria-create" value="#3B82F6" 
                                           class="w-16 h-12 border-2 border-gray-300 rounded-md cursor-pointer">
                                    <input type="text" id="cor_primaria_text_create" value="#3B82F6" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                                           placeholder="#3B82F6">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Cor Secundária</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="cor_secundaria" id="cor_secundaria-create" value="#1E40AF" 
                                           class="w-16 h-12 border-2 border-gray-300 rounded-md cursor-pointer">
                                    <input type="text" id="cor_secundaria_text_create" value="#1E40AF" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                                           placeholder="#1E40AF">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Observações -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <textarea name="observacoes" id="observacoes-create" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <!-- Botões -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="fecharOffcanvasImobiliaria()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>
                        Cadastrar Imobiliária
                    </button>
                </div>
            </div>
        </form>
    `;
    
    // Aplicar máscaras
    aplicarMascaras();
    sincronizarCores();
}

function aplicarMascaras() {
    // Máscara CNPJ
    const cnpjInputs = document.querySelectorAll('#cnpj-edit, #cnpj-create');
    cnpjInputs.forEach(input => {
        if (input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                e.target.value = value;
            });
        }
    });
    
    // Máscara CEP
    const cepInputs = document.querySelectorAll('#endereco_cep-create, [name="endereco_cep"]');
    cepInputs.forEach(input => {
        if (input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            });
        }
    });
    
    // Máscara Telefone
    const telefoneInputs = document.querySelectorAll('#telefone-create, [name="telefone"]');
    telefoneInputs.forEach(input => {
        if (input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                e.target.value = value;
            });
        }
    });
}

function sincronizarCores() {
    // Sincronizar cores primária
    const corPrimaria = document.querySelector('#cor_primaria-create, [name="cor_primaria"]');
    const corPrimariaText = document.querySelector('#cor_primaria_text_create, #cor_primaria_text_edit');
    
    if (corPrimaria && corPrimariaText) {
        corPrimaria.addEventListener('input', function() {
            corPrimariaText.value = this.value;
        });
        corPrimariaText.addEventListener('input', function() {
            if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                corPrimaria.value = this.value;
            }
        });
    }
    
    // Sincronizar cores secundária
    const corSecundaria = document.querySelector('#cor_secundaria-create, [name="cor_secundaria"]');
    const corSecundariaText = document.querySelector('#cor_secundaria_text_create, #cor_secundaria_text_edit');
    
    if (corSecundaria && corSecundariaText) {
        corSecundaria.addEventListener('input', function() {
            corSecundariaText.value = this.value;
        });
        corSecundariaText.addEventListener('input', function() {
            if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                corSecundaria.value = this.value;
            }
        });
    }
}

function previewLogoEdit(event) {
    const file = event.target.files[0];
    const container = document.getElementById('logo-preview-container');
    const placeholder = document.getElementById('logo-preview-placeholder');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (placeholder) {
                // Substituir placeholder por container com imagem
                placeholder.outerHTML = `<div class="w-24 h-24 rounded-lg border-2 border-gray-300 bg-white p-2 flex items-center justify-center overflow-hidden" id="logo-preview-container">
                    <img src="${e.target.result}" alt="Preview" class="max-w-full max-h-full object-contain" id="logo-preview-img">
                </div>`;
            } else if (container) {
                // Atualizar imagem existente
                const img = container.querySelector('#logo-preview-img');
                if (img) {
                    img.src = e.target.result;
                } else {
                    container.innerHTML = `<img src="${e.target.result}" alt="Preview" class="max-w-full max-h-full object-contain" id="logo-preview-img">`;
                }
            }
        };
        reader.readAsDataURL(file);
    }
}

function previewLogoCreate(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('logo-preview-create');
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="max-w-full max-h-full object-contain rounded-lg">`;
            preview.classList.remove('border-dashed', 'bg-gray-50');
            preview.classList.add('border-2', 'border-gray-300', 'bg-white', 'p-2');
        };
        reader.readAsDataURL(file);
    }
}

function buscarCnpjEdit() {
    const cnpjInput = document.getElementById('cnpj-edit');
    if (!cnpjInput) return;
    
    const cnpj = cnpjInput.value.replace(/\D/g, '');
    if (cnpj.length !== 14) {
        alert('CNPJ deve ter 14 dígitos');
        return;
    }
    
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    fetch('<?= url('admin/imobiliarias/buscar-cnpj') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({ cnpj: cnpj })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherCamposEdit(data.data);
            mostrarInformacoesReceitaEdit(data.data);
        } else {
            alert('Erro: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao buscar dados do CNPJ');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function buscarCnpjCreate() {
    const cnpjInput = document.getElementById('cnpj-create');
    if (!cnpjInput) return;
    
    const cnpj = cnpjInput.value.replace(/\D/g, '');
    if (cnpj.length !== 14) {
        alert('CNPJ deve ter 14 dígitos');
        return;
    }
    
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    fetch('<?= url('admin/imobiliarias/buscar-cnpj') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({ cnpj: cnpj })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherCamposCreate(data.data);
            mostrarInformacoesReceitaCreate(data.data);
        } else {
            alert('Erro: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao buscar dados do CNPJ');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function preencherCamposEdit(dados) {
    const campos = {
        'razao_social': dados.razao_social || '',
        'nome_fantasia': dados.nome_fantasia || '',
        'endereco_logradouro': dados.endereco_logradouro || '',
        'endereco_numero': dados.endereco_numero || '',
        'endereco_complemento': dados.endereco_complemento || '',
        'endereco_bairro': dados.endereco_bairro || '',
        'endereco_cidade': dados.endereco_cidade || '',
        'endereco_estado': dados.endereco_estado || '',
        'endereco_cep': dados.endereco_cep || '',
        'telefone': dados.telefone || '',
        'email': dados.email || ''
    };
    
    Object.keys(campos).forEach(campo => {
        const elemento = document.querySelector(`[name="${campo}"]`);
        if (elemento) {
            elemento.value = campos[campo];
        }
    });
}

function preencherCamposCreate(dados) {
    const campos = {
        'razao_social': dados.razao_social || '',
        'nome_fantasia': dados.nome_fantasia || '',
        'endereco_logradouro': dados.endereco_logradouro || '',
        'endereco_numero': dados.endereco_numero || '',
        'endereco_complemento': dados.endereco_complemento || '',
        'endereco_bairro': dados.endereco_bairro || '',
        'endereco_cidade': dados.endereco_cidade || '',
        'endereco_estado': dados.endereco_estado || '',
        'endereco_cep': dados.endereco_cep || '',
        'telefone': dados.telefone || '',
        'email': dados.email || ''
    };
    
    Object.keys(campos).forEach(campo => {
        const elemento = document.getElementById(`${campo}-create`);
        if (elemento) {
            elemento.value = campos[campo];
        }
    });
}

function mostrarInformacoesReceitaEdit(dados) {
    const infoDiv = document.getElementById('receita-info-edit');
    const detailsDiv = document.getElementById('receita-details-edit');
    
    if (infoDiv && detailsDiv) {
        let html = '';
        if (dados.situacao) html += `<div><strong>Situação:</strong> ${escapeHtml(dados.situacao)}</div>`;
        if (dados.porte) html += `<div><strong>Porte:</strong> ${escapeHtml(dados.porte)}</div>`;
        if (dados.natureza_juridica) html += `<div><strong>Natureza Jurídica:</strong> ${escapeHtml(dados.natureza_juridica)}</div>`;
        if (dados.capital_social) html += `<div><strong>Capital Social:</strong> ${escapeHtml(dados.capital_social)}</div>`;
        if (dados.atividade_principal) html += `<div><strong>Atividade Principal:</strong> ${escapeHtml(dados.atividade_principal)}</div>`;
        
        detailsDiv.innerHTML = html;
        infoDiv.classList.remove('hidden');
    }
}

function mostrarInformacoesReceitaCreate(dados) {
    const infoDiv = document.getElementById('receita-info-edit');
    const detailsDiv = document.getElementById('receita-details-edit');
    
    if (infoDiv && detailsDiv) {
        let html = '';
        if (dados.situacao) html += `<div><strong>Situação:</strong> ${escapeHtml(dados.situacao)}</div>`;
        if (dados.porte) html += `<div><strong>Porte:</strong> ${escapeHtml(dados.porte)}</div>`;
        if (dados.natureza_juridica) html += `<div><strong>Natureza Jurídica:</strong> ${escapeHtml(dados.natureza_juridica)}</div>`;
        if (dados.capital_social) html += `<div><strong>Capital Social:</strong> ${escapeHtml(dados.capital_social)}</div>`;
        if (dados.atividade_principal) html += `<div><strong>Atividade Principal:</strong> ${escapeHtml(dados.atividade_principal)}</div>`;
        
        detailsDiv.innerHTML = html;
        infoDiv.classList.remove('hidden');
    }
}

function salvarEdicao(event) {
    event.preventDefault();
    
    const form = document.getElementById('formEditarImobiliaria');
    const formData = new FormData(form);
    
    const button = form.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    button.disabled = true;
    
    fetch(`<?= url('admin/imobiliarias/') ?>${imobiliariaEditandoId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (response.headers.get('content-type')?.includes('application/json')) {
            return response.json();
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch {
                return { success: false, message: 'Resposta inválida do servidor' };
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert('Imobiliária atualizada com sucesso!');
            location.reload();
        } else {
            let errorMsg = data.message || data.error || 'Erro ao atualizar imobiliária';
            if (data.errors) {
                errorMsg += '\n\n' + Object.values(data.errors).join('\n');
            }
            alert('Erro: ' + errorMsg);
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar alterações');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function salvarCriacao(event) {
    event.preventDefault();
    
    const form = document.getElementById('formCriarImobiliaria');
    const formData = new FormData(form);
    
    const button = form.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Cadastrando...';
    button.disabled = true;
    
    fetch('<?= url('admin/imobiliarias') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (response.headers.get('content-type')?.includes('application/json')) {
            return response.json();
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch {
                return { success: false, message: 'Resposta inválida do servidor' };
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert('Imobiliária cadastrada com sucesso!');
            location.reload();
        } else {
            let errorMsg = data.message || data.error || 'Erro ao cadastrar imobiliária';
            if (data.errors) {
                errorMsg += '\n\n' + Object.values(data.errors).join('\n');
            }
            alert('Erro: ' + errorMsg);
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao cadastrar imobiliária');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Fechar offcanvas com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('offcanvasImobiliaria').classList.contains('hidden')) {
        fecharOffcanvasImobiliaria();
    }
});

// ========== FUNÇÕES DE UPLOAD EXCEL ==========
let imobiliariaUploadId = null;

function abrirModalUploadExcel(id) {
    imobiliariaUploadId = id;
    const modal = document.getElementById('upload-excel-modal');
    const form = document.getElementById('form-upload-excel');
    const result = document.getElementById('upload-excel-result');
    
    // Resetar formulário
    form.reset();
    result.classList.add('hidden');
    result.innerHTML = '';
    
    modal.classList.remove('hidden');
}

function fecharModalUploadExcel() {
    const modal = document.getElementById('upload-excel-modal');
    modal.classList.add('hidden');
    imobiliariaUploadId = null;
}

let imobiliariaListagemId = null;

function abrirModalListagemContratos(id) {
    imobiliariaListagemId = id;
    const modal = document.getElementById('listagem-contratos-modal');
    const content = document.getElementById('listagem-contratos-content');
    
    // Mostrar loading
    content.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
            <p class="text-gray-500 mt-2">Carregando...</p>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Carregar dados
    fetch(`<?= url('admin/imobiliarias') ?>/${id}/listagem-contratos`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarListagemContratos(data.contratos, data.total);
            } else {
                content.innerHTML = `
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        ${data.error || 'Erro ao carregar listagem'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            content.innerHTML = `
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Erro ao carregar listagem. Tente novamente.
                </div>
            `;
        });
}

function fecharModalListagemContratos() {
    const modal = document.getElementById('listagem-contratos-modal');
    modal.classList.add('hidden');
    imobiliariaListagemId = null;
}

function renderizarListagemContratos(contratos, total) {
    const content = document.getElementById('listagem-contratos-content');
    
    if (!contratos || contratos.length === 0) {
        content.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Nenhum registro encontrado.</p>
                <p class="text-sm text-gray-400 mt-2">Faça upload de um arquivo Excel/CSV para começar.</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                <strong>Total:</strong> ${total} registro(s)
            </p>
            <button onclick="exportarListagemContratos()" 
                    class="inline-flex items-center px-3 py-1 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-white hover:bg-green-50">
                <i class="fas fa-download mr-1"></i>
                Exportar CSV
            </button>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contrato</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo Imóvel</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bairro</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endereço</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complemento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidade</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CEP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa Fiscal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Cadastro</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    contratos.forEach(contrato => {
        const cpfFormatado = contrato.cpf ? contrato.cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') : '';
        const dataCadastro = contrato.created_at ? new Date(contrato.created_at).toLocaleString('pt-BR') : '';
        
        html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${cpfFormatado || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${contrato.inquilino_nome || '-'}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${contrato.numero_contrato || '-'}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${contrato.tipo_imovel || '-'}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${contrato.cidade || '-'}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${contrato.estado || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${contrato.bairro || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${contrato.endereco || '-'}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${contrato.numero || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${contrato.complemento || '-'}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${contrato.unidade || '-'}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${contrato.cep || '-'}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${contrato.empresa_fiscal || '-'}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${dataCadastro}</td>
                    </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    content.innerHTML = html;
}

function exportarListagemContratos() {
    if (!imobiliariaListagemId) return;
    
    window.location.href = `<?= url('admin/imobiliarias') ?>/${imobiliariaListagemId}/exportar-contratos`;
}

document.getElementById('upload-excel-button').addEventListener('click', function() {
    const form = document.getElementById('form-upload-excel');
    const fileInput = document.getElementById('excel_file');
    const button = this;
    const result = document.getElementById('upload-excel-result');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Por favor, selecione um arquivo Excel');
        return;
    }
    
    const formData = new FormData();
    formData.append('excel_file', fileInput.files[0]);
    
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
    button.disabled = true;
    
    fetch(`<?= url('admin/imobiliarias') ?>/${imobiliariaUploadId}/upload-excel`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            result.classList.remove('hidden');
            let html = `<div class="p-4 rounded-md ${data.erros > 0 ? 'bg-yellow-50 border border-yellow-200' : 'bg-green-50 border border-green-200'}">`;
            html += `<p class="font-medium ${data.erros > 0 ? 'text-yellow-800' : 'text-green-800'}">${data.message}</p>`;
            
            if (data.detalhes_erros && data.detalhes_erros.length > 0) {
                html += `<div class="mt-2 text-sm text-yellow-700">`;
                html += `<p class="font-medium mb-2">Detalhes dos erros:</p>`;
                html += `<ul class="list-disc list-inside space-y-1 max-h-40 overflow-y-auto">`;
                data.detalhes_erros.forEach(erro => {
                    html += `<li>${erro}</li>`;
                });
                html += `</ul></div>`;
            }
            
            html += `</div>`;
            result.innerHTML = html;
            
            // Se não houver erros, recarregar a página após 2 segundos
            if (data.erros === 0) {
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        } else {
            result.classList.remove('hidden');
            result.innerHTML = `<div class="p-4 rounded-md bg-red-50 border border-red-200">
                <p class="font-medium text-red-800">Erro: ${data.error || 'Erro desconhecido'}</p>
            </div>`;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        result.classList.remove('hidden');
        result.innerHTML = `<div class="p-4 rounded-md bg-red-50 border border-red-200">
            <p class="font-medium text-red-800">Erro ao enviar arquivo</p>
        </div>`;
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>
