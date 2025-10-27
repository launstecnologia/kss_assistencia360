<?php
/**
 * View: Detalhes da Solicitação do Locatário
 */
$title = 'Detalhes da Solicitação - Assistência 360°';
$currentPage = 'locatario-solicitacao-detalhes';
ob_start();
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-file-alt mr-2"></i>
                Detalhes da Solicitação
            </h1>
            <p class="text-gray-600 mt-1">
                Protocolo: <?= htmlspecialchars($solicitacao['numero_solicitacao'] ?? '#' . $solicitacao['id']) ?>
            </p>
        </div>
        <a href="<?= url($locatario['instancia'] . '/solicitacoes') ?>" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Solicitation Details -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">
                <?= htmlspecialchars($solicitacao['categoria_nome']) ?>
            </h2>
            <span class="status-badge status-<?= strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $solicitacao['status_nome'])) ?>">
                <?= htmlspecialchars($solicitacao['status_nome']) ?>
            </span>
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Service Information -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3">Informações do Serviço</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm text-gray-500">Categoria:</span>
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['categoria_nome']) ?></p>
                    </div>
                    <?php if (!empty($solicitacao['subcategoria_nome'])): ?>
                    <div>
                        <span class="text-sm text-gray-500">Tipo:</span>
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['subcategoria_nome']) ?></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <span class="text-sm text-gray-500">Prioridade:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            <?= $solicitacao['prioridade'] === 'ALTA' ? 'bg-red-100 text-red-800' : 
                               ($solicitacao['prioridade'] === 'MEDIA' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') ?>">
                            <?= $solicitacao['prioridade'] ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Dates -->
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3">Datas</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm text-gray-500">Criado em:</span>
                        <p class="text-sm font-medium text-gray-900">
                            <?= date('d/m/Y \à\s H:i', strtotime($solicitacao['created_at'])) ?>
                        </p>
                    </div>
                    <?php if (!empty($solicitacao['data_agendamento'])): ?>
                    <div>
                        <span class="text-sm text-gray-500">Agendado para:</span>
                        <p class="text-sm font-medium text-gray-900">
                            <?= date('d/m/Y', strtotime($solicitacao['data_agendamento'])) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Description -->
        <?php if (!empty($solicitacao['descricao_problema'])): ?>
        <div class="mt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Descrição do Problema</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($solicitacao['descricao_problema'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Address -->
        <?php if (!empty($solicitacao['imovel_endereco'])): ?>
        <div class="mt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Endereço do Imóvel</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-900">
                    <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                    <?= htmlspecialchars($solicitacao['imovel_endereco']) ?>
                    <?php if (!empty($solicitacao['imovel_numero'])): ?>
                        , <?= htmlspecialchars($solicitacao['imovel_numero']) ?>
                    <?php endif; ?>
                    <?php if (!empty($solicitacao['imovel_complemento'])): ?>
                        - <?= htmlspecialchars($solicitacao['imovel_complemento']) ?>
                    <?php endif; ?>
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    <?= htmlspecialchars($solicitacao['imovel_bairro']) ?> - 
                    <?= htmlspecialchars($solicitacao['imovel_cidade']) ?>/<?= htmlspecialchars($solicitacao['imovel_estado']) ?>
                </p>
                <?php if (!empty($solicitacao['imovel_cep'])): ?>
                <p class="text-sm text-gray-600">CEP: <?= htmlspecialchars($solicitacao['imovel_cep']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Contact Information -->
        <div class="mt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Informações de Contato</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Nome</p>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['locatario_nome']) ?></p>
                </div>
                <?php if (!empty($solicitacao['locatario_telefone'])): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Telefone</p>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['locatario_telefone']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="mt-8 flex justify-end space-x-4">
            <a href="<?= url($locatario['instancia'] . '/solicitacoes') ?>" 
               class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Voltar para Lista
            </a>
            
            <?php if (in_array($solicitacao['status_nome'], ['Nova Solicitação', 'Buscando Prestador'])): ?>
                <button onclick="cancelarSolicitacao(<?= $solicitacao['id'] ?>)" 
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar Solicitação
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function cancelarSolicitacao(id) {
    if (confirm('Tem certeza que deseja cancelar esta solicitação?')) {
        // Implementar cancelamento via AJAX
        fetch('<?= url('solicitacoes/cancelar') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id: id,
                motivo: 'Cancelado pelo locatário'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao cancelar solicitação: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao cancelar solicitação');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/locatario.php';
?>
