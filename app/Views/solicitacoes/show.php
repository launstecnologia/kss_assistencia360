<?php
/**
 * View: Detalhes da Solicitação
 */
$title = 'Solicitação #' . $solicitacao['id'];
$currentPage = 'solicitacoes';
$pageTitle = 'Solicitação #' . $solicitacao['id'];
ob_start();
?>

<!-- Breadcrumb -->
<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-4">
        <li>
            <div>
                <a href="<?= url('solicitacoes') ?>" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-list"></i>
                    <span class="sr-only">Solicitações</span>
                </a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <span class="text-sm font-medium text-gray-500">Solicitação #<?= $solicitacao['id'] ?></span>
            </div>
        </li>
    </ol>
</nav>

<!-- Ações -->
<div class="flex justify-end mb-6 space-x-3">
    <a href="<?= url('solicitacoes/' . $solicitacao['id'] . '/edit') ?>" 
       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <i class="fas fa-edit mr-2"></i>
        Editar
    </a>
    <button type="button" onclick="updateStatus()" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <i class="fas fa-sync-alt mr-2"></i>
        Atualizar Status
    </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Informações Principais -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Status e Prioridade -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Status e Prioridade</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status Atual</label>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?= htmlspecialchars($solicitacao['status_nome']) ?>
                        </span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Prioridade</label>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            <?= $solicitacao['prioridade'] == 'ALTA' ? 'bg-red-100 text-red-800' : 
                               ($solicitacao['prioridade'] == 'MEDIA' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') ?>">
                            <?= $solicitacao['prioridade'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Locatário -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Locatário</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['locatario_nome']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <a href="tel:<?= $solicitacao['locatario_telefone'] ?>" class="text-blue-600 hover:text-blue-500">
                            <?= htmlspecialchars($solicitacao['locatario_telefone']) ?>
                        </a>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?php if ($solicitacao['locatario_email']): ?>
                            <a href="mailto:<?= $solicitacao['locatario_email'] ?>" class="text-blue-600 hover:text-blue-500">
                                <?= htmlspecialchars($solicitacao['locatario_email']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-400">Não informado</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Informações do Imóvel -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Imóvel</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Endereço</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?= htmlspecialchars($solicitacao['imovel_endereco'] . ', ' . $solicitacao['imovel_numero']) ?>
                        <?php if ($solicitacao['imovel_complemento']): ?>
                            - <?= htmlspecialchars($solicitacao['imovel_complemento']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bairro</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['imovel_bairro']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cidade</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['imovel_cidade']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['imovel_estado']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">CEP</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['imovel_cep']) ?></p>
                </div>
            </div>
        </div>

        <!-- Descrição do Problema -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Descrição do Problema</h3>
            <p class="text-sm text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($solicitacao['descricao_problema']) ?></p>
        </div>

        <!-- Observações -->
        <?php if ($solicitacao['observacoes']): ?>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Observações</h3>
            <p class="text-sm text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($solicitacao['observacoes']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Fotos -->
        <?php if (!empty($fotos)): ?>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Fotos</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($fotos as $foto): ?>
                    <div class="relative">
                        <img src="<?= asset('uploads/' . $foto['arquivo']) ?>" 
                             alt="Foto da solicitação" 
                             class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-75"
                             onclick="openImageModal('<?= asset('uploads/' . $foto['arquivo']) ?>')">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Informações da Imobiliária -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Imobiliária</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['imobiliaria_nome']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <a href="tel:<?= $solicitacao['imobiliaria_telefone'] ?>" class="text-blue-600 hover:text-blue-500">
                            <?= htmlspecialchars($solicitacao['imobiliaria_telefone']) ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Agendamento -->
        <?php if ($solicitacao['data_agendamento']): ?>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Agendamento</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?= date('d/m/Y', strtotime($solicitacao['data_agendamento'])) ?>
                    </p>
                </div>
                <?php if ($solicitacao['horario_agendamento']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Horário</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['horario_agendamento']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Prestador -->
        <?php if ($solicitacao['prestador_nome']): ?>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Prestador</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['prestador_nome']) ?></p>
                </div>
                <?php if ($solicitacao['prestador_telefone']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <a href="tel:<?= $solicitacao['prestador_telefone'] ?>" class="text-blue-600 hover:text-blue-500">
                            <?= htmlspecialchars($solicitacao['prestador_telefone']) ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                <?php if ($solicitacao['valor_orcamento']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Valor do Orçamento</label>
                    <p class="mt-1 text-sm text-gray-900">R$ <?= number_format($solicitacao['valor_orcamento'], 2, ',', '.') ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Informações do Sistema -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Sistema</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data de Criação</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?= date('d/m/Y H:i', strtotime($solicitacao['created_at'])) ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Última Atualização</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?= date('d/m/Y H:i', strtotime($solicitacao['updated_at'])) ?>
                    </p>
                </div>
                <?php if ($solicitacao['numero_ncp']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Número NCP</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($solicitacao['numero_ncp']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Histórico de Status -->
        <?php if (!empty($historico)): ?>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Histórico de Status</h3>
            <div class="space-y-3">
                <?php foreach ($historico as $item): ?>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-2 h-2 bg-blue-400 rounded-full mt-2"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['status_nome']) ?></p>
                            <p class="text-xs text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                                <?php if ($item['observacoes']): ?>
                                    - <?= htmlspecialchars($item['observacoes']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Visualizar Imagem -->
<div id="image-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Foto da Solicitação</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="text-center">
                <img id="modal-image" src="" alt="Foto da solicitação" class="max-w-full max-h-96 mx-auto rounded-lg">
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus() {
    // Implementar modal para atualizar status
    console.log('Atualizar status');
}

function openImageModal(imageSrc) {
    document.getElementById('modal-image').src = imageSrc;
    document.getElementById('image-modal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('image-modal').classList.add('hidden');
}
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>
