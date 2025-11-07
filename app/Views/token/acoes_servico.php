<?php
$title = $title ?? 'Ações do Serviço';
ob_start();
?>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-tools text-green-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Como foi o serviço?</h2>
                <p class="text-gray-600 mt-2">Selecione uma das opções abaixo</p>
            </div>

            <!-- Informações da Solicitação -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nº de Atendimento:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($solicitacao['numero_solicitacao'] ?? 'N/A') ?></span>
                    </div>
                    <?php if ($solicitacao['data_agendamento']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Data:</span>
                        <span class="font-semibold text-gray-900"><?= date('d/m/Y', strtotime($solicitacao['data_agendamento'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($solicitacao['horario_agendamento']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Horário:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($solicitacao['horario_agendamento']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Opções de Ação -->
            <div id="opcoesAcoes">
                <form id="formAcoes" class="space-y-4">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <!-- 1. Serviço Realizado -->
                    <button type="button" 
                            onclick="processarAcao('servico_realizado')"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors flex items-center justify-center gap-3">
                        <i class="fas fa-check-circle text-xl"></i>
                        <span>Serviço realizado com sucesso</span>
                    </button>

                    <!-- 2. Prestador não compareceu -->
                    <button type="button" 
                            onclick="processarAcao('nao_compareceu')"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors flex items-center justify-center gap-3">
                        <i class="fas fa-times-circle text-xl"></i>
                        <span>Prestador não compareceu no serviço agendado</span>
                    </button>

                    <!-- 3. Precisa comprar peças -->
                    <button type="button" 
                            onclick="processarAcao('precisa_pecas')"
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors flex items-center justify-center gap-3">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span>Precisa comprar peças</span>
                    </button>

                    <!-- 4. Precisei me ausentar -->
                    <button type="button" 
                            onclick="processarAcao('ausente')"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors flex items-center justify-center gap-3">
                        <i class="fas fa-user-times text-xl"></i>
                        <span>Precisei me ausentar</span>
                    </button>

                    <!-- 5. Outros -->
                    <div class="space-y-2">
                        <button type="button" 
                                onclick="mostrarCampoOutros()"
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-4 px-6 rounded-lg transition-colors flex items-center justify-center gap-3">
                            <i class="fas fa-ellipsis-h text-xl"></i>
                            <span>Outros</span>
                        </button>
                        
                        <div id="campoOutros" class="hidden">
                            <textarea name="descricao" 
                                      id="descricaoOutros"
                                      placeholder="Descreva o motivo..."
                                      class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500"
                                      rows="4"></textarea>
                            <button type="button" 
                                    onclick="processarAcao('outros')"
                                    class="w-full mt-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                Enviar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Mensagem de Feedback -->
            <div id="mensagemFeedback" class="hidden mt-6"></div>
        </div>
    </div>
</div>

<script>
function mostrarCampoOutros() {
    document.getElementById('campoOutros').classList.remove('hidden');
    document.getElementById('descricaoOutros').focus();
}

function processarAcao(acao) {
    const form = document.getElementById('formAcoes');
    const formData = new FormData(form);
    formData.append('acao', acao);
    
    // Se for "outros", verificar se tem descrição
    if (acao === 'outros') {
        const descricao = document.getElementById('descricaoOutros').value.trim();
        if (!descricao) {
            alert('Por favor, descreva o motivo');
            return;
        }
        formData.set('descricao', descricao);
    }
    
    // Esconder opções de ação
    const opcoesDiv = document.getElementById('opcoesAcoes');
    opcoesDiv.style.display = 'none';
    
    // Mostrar loading
    const mensagemDiv = document.getElementById('mensagemFeedback');
    mensagemDiv.className = 'p-8 rounded-lg bg-blue-50 border-2 border-blue-300';
    mensagemDiv.innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin text-blue-600 text-4xl mb-4"></i>
            <p class="text-blue-800 text-lg font-semibold">Processando...</p>
        </div>
    `;
    mensagemDiv.classList.remove('hidden');
    
    fetch('/acoes-servico', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mensagemDiv.className = 'p-12 rounded-lg bg-green-50 border-2 border-green-400';
            mensagemDiv.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-check-circle text-green-600 text-5xl mb-6"></i>
                    <p class="text-green-800 text-2xl font-bold mb-2">${data.message}</p>
                </div>
            `;
            
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 3000);
            }
        } else {
            mensagemDiv.className = 'p-12 rounded-lg bg-red-50 border-2 border-red-400';
            mensagemDiv.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-5xl mb-6"></i>
                    <p class="text-red-800 text-2xl font-bold mb-2">${data.message}</p>
                    <button onclick="location.reload()" class="mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Tentar Novamente
                    </button>
                </div>
            `;
            
            // Mostrar opções novamente em caso de erro
            opcoesDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mensagemDiv.className = 'p-12 rounded-lg bg-red-50 border-2 border-red-400';
        mensagemDiv.innerHTML = `
            <div class="text-center">
                <i class="fas fa-exclamation-circle text-red-600 text-5xl mb-6"></i>
                <p class="text-red-800 text-2xl font-bold mb-2">Erro ao processar ação. Tente novamente.</p>
                <button onclick="location.reload()" class="mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Tentar Novamente
                </button>
            </div>
        `;
        
        // Mostrar opções novamente em caso de erro
        opcoesDiv.style.display = 'block';
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/public.php';
?>

