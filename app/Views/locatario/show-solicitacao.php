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
    <!-- Botão Voltar -->
    <a href="<?= url($locatario['instancia'] . '/dashboard') ?>" 
       class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        <span class="text-sm font-medium">Voltar para Dashboard</span>
    </a>
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-file-alt mr-2"></i>
                Detalhes da Solicitação
            </h1>
            <p class="text-gray-600 mt-1">
                Protocolo: <?= htmlspecialchars($solicitacao['protocolo_seguradora'] ?? '-') ?>
                <?php if (!empty($solicitacao['numero_contrato'])): ?>
                    | Contrato: <?= htmlspecialchars($solicitacao['numero_contrato']) ?>
                <?php endif; ?>
            </p>
        </div>
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
        <div class="mt-8">
            <div class="flex flex-wrap justify-end gap-3 mb-4">
                <a href="<?= url($locatario['instancia'] . '/solicitacoes') ?>" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar para Lista
                </a>
            </div>
            
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Ações Disponíveis</h3>
                <div class="flex flex-wrap gap-3">
                    <!-- Concluído -->
                    <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'concluido')" 
                            class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>
                        Concluído
                    </button>
                    
                    <!-- Cancelado -->
                    <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'cancelado')" 
                            class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-times-circle mr-2"></i>
                        Cancelando
                    </button>
                    
                    <!-- Serviço não realizado -->
                    <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'servico_nao_realizado')" 
                            class="px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Serviço não realizado
                    </button>
                    
                    <!-- Comprar peças -->
                    <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'comprar_pecas')" 
                            class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Comprar peças
                    </button>
                    
                    <!-- Reembolso -->
                    <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'reembolso')" 
                            class="px-4 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Reembolso
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Concluído -->
<div id="modalConcluido" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirmar Conclusão</h3>
        <form id="formConcluido" onsubmit="processarConcluido(event)">
            <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['id'] ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                <textarea name="observacao" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Adicione uma observação sobre a conclusão do serviço..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="fecharModal('modalConcluido')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    OK
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Cancelando -->
<div id="modalCancelando" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancelar Solicitação</h3>
        <form id="formCancelando" onsubmit="processarCancelando(event)">
            <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['id'] ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observação <span class="text-red-500">*</span></label>
                <textarea name="observacao" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Informe o motivo do cancelamento..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="fecharModal('modalCancelando')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Confirmar Cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Serviço não realizado -->
<div id="modalServicoNaoRealizado" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Serviço não realizado</h3>
        <form id="formServicoNaoRealizado" onsubmit="processarServicoNaoRealizado(event)" enctype="multipart/form-data">
            <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['id'] ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observação</label>
                <textarea name="observacao" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Informe o motivo pelo qual o serviço não foi realizado..."></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Anexos</label>
                <input type="file" name="anexos[]" multiple accept="image/*,.pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Você pode selecionar múltiplos arquivos (imagens, PDF, Word)</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="fecharModal('modalServicoNaoRealizado')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Comprar peças -->
<div id="modalComprarPecas" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Comprar peças</h3>
        <form id="formComprarPecas" onsubmit="processarComprarPecas(event)" enctype="multipart/form-data">
            <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['id'] ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observação</label>
                <textarea name="observacao" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Informe quais peças são necessárias..."></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Anexos</label>
                <input type="file" name="anexos[]" multiple accept="image/*,.pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Você pode selecionar múltiplos arquivos (imagens, PDF, Word)</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="fecharModal('modalComprarPecas')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reembolso -->
<div id="modalReembolso" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitar Reembolso</h3>
        <form id="formReembolso" onsubmit="processarReembolso(event)" enctype="multipart/form-data">
            <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['id'] ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Justificativa <span class="text-red-500">*</span></label>
                <textarea name="observacao" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Justifique o motivo do reembolso..."></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Valor do Reembolso <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">R$</span>
                    <input type="number" name="valor_reembolso" step="0.01" min="0" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="0,00">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Anexos</label>
                <input type="file" name="anexos[]" multiple accept="image/*,.pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Você pode selecionar múltiplos arquivos (imagens, PDF, Word)</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="fecharModal('modalReembolso')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Solicitar Reembolso
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let solicitacaoIdAtual = <?= $solicitacao['id'] ?>;

function abrirModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function fecharModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    // Limpar formulários
    const form = document.querySelector('#' + modalId + ' form');
    if (form) {
        form.reset();
    }
}

function executarAcao(solicitacaoId, acao) {
    solicitacaoIdAtual = solicitacaoId;
    
    const modais = {
        'concluido': 'modalConcluido',
        'cancelado': 'modalCancelando',
        'servico_nao_realizado': 'modalServicoNaoRealizado',
        'comprar_pecas': 'modalComprarPecas',
        'reembolso': 'modalReembolso'
    };
    
    const modalId = modais[acao];
    if (modalId) {
        abrirModal(modalId);
    } else {
        alert('Ação não reconhecida');
    }
}

function processarConcluido(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('acao', 'concluido');
    
    fetch('<?= url($locatario['instancia']) ?>/solicitacoes/' + solicitacaoIdAtual + '/acao', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharModal('modalConcluido');
            // Redirecionar para NPS
            window.location.href = '<?= url($locatario['instancia']) ?>/solicitacoes/' + solicitacaoIdAtual + '/avaliacao';
        } else {
            alert('Erro: ' + (data.message || 'Erro ao processar'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar. Tente novamente.');
    });
}

function processarCancelando(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('acao', 'cancelado');
    
    fetch('<?= url($locatario['instancia']) ?>/solicitacoes/' + solicitacaoIdAtual + '/acao', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharModal('modalCancelando');
            alert(data.message || 'Solicitação cancelada com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao processar'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar. Tente novamente.');
    });
}

function processarServicoNaoRealizado(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('acao', 'servico_nao_realizado');
    
    fetch('<?= url($locatario['instancia']) ?>/solicitacoes/' + solicitacaoIdAtual + '/acao', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharModal('modalServicoNaoRealizado');
            alert(data.message || 'Informação registrada com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao processar'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar. Tente novamente.');
    });
}

function processarComprarPecas(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('acao', 'comprar_pecas');
    
    fetch('<?= url($locatario['instancia']) ?>/solicitacoes/' + solicitacaoIdAtual + '/acao', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharModal('modalComprarPecas');
            alert(data.message || 'Informação registrada com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao processar'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar. Tente novamente.');
    });
}

function processarReembolso(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('acao', 'reembolso');
    
    fetch('<?= url($locatario['instancia']) ?>/solicitacoes/' + solicitacaoIdAtual + '/acao', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharModal('modalReembolso');
            alert(data.message || 'Solicitação de reembolso registrada com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao processar'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar. Tente novamente.');
    });
}

// Fechar modal ao clicar fora
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('bg-opacity-50')) {
        event.target.classList.add('hidden');
    }
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/locatario.php';
?>
