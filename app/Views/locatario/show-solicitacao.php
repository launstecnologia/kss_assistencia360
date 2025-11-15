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
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex-1">
            <h2 class="text-lg font-medium text-gray-900">
                <?= htmlspecialchars($solicitacao['categoria_nome']) ?>
            </h2>
            </div>
            <span class="status-badge status-<?= strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $solicitacao['status_nome'])) ?> self-start sm:self-auto">
                <?= htmlspecialchars($solicitacao['status_nome']) ?>
            </span>
        </div>
    </div>
    
    <!-- Ações Disponíveis - Movido para o topo -->
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-sm font-medium text-gray-700 mb-3">Ações Disponíveis</h3>
        <div class="flex flex-wrap gap-2 sm:gap-3">
            <?php
            $statusNome = $solicitacao['status_nome'] ?? '';
            $condicaoNome = $solicitacao['condicao_nome'] ?? '';
            $dataAgendamento = $solicitacao['data_agendamento'] ?? null;
            
            // Verificar se pode cancelar (até 1 dia antes da data agendada)
            $podeCancelar = false;
            if ($dataAgendamento) {
                $dataAgendamentoObj = new \DateTime($dataAgendamento);
                $hoje = new \DateTime();
                $diferenca = $hoje->diff($dataAgendamentoObj);
                // Pode cancelar se a data agendada for pelo menos 1 dia no futuro
                $podeCancelar = $diferenca->days >= 1 && $dataAgendamentoObj > $hoje;
            }
            
            // Status: Nova Solicitação
            if ($statusNome === 'Nova Solicitação' || stripos($statusNome, 'Nova Solicitação') !== false) {
                // Botão Cancelar
                ?>
                <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'cancelado')" 
                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-times-circle mr-2"></i>
                    <span class="hidden sm:inline">Cancelar</span>
                    <span class="sm:hidden">Cancelar</span>
                </button>
                <?php
            }
            // Status: Aguardando Prestador / Buscando Prestador
            elseif (stripos($statusNome, 'Aguardando Prestador') !== false || 
                    stripos($statusNome, 'Buscando Prestador') !== false ||
                    stripos($statusNome, 'Aguardando prestador') !== false) {
                // Botão Cancelar
                ?>
                <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'cancelado')" 
                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-times-circle mr-2"></i>
                    <span class="hidden sm:inline">Cancelar</span>
                    <span class="sm:hidden">Cancelar</span>
                </button>
                <?php
                // Botão Reagendar (se condição permitir)
                if (stripos($condicaoNome, 'reagendar') !== false || 
                    stripos($condicaoNome, 'Reagendar') !== false) {
                    ?>
                    <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'reagendar')" 
                            class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span class="hidden sm:inline">Reagendar</span>
                        <span class="sm:hidden">Reagendar</span>
                    </button>
                    <?php
                }
            }
            // Status: Serviço Agendado
            elseif (stripos($statusNome, 'Serviço Agendado') !== false || 
                    stripos($statusNome, 'Servico Agendado') !== false) {
                // Botão Cancelar (até 1 dia antes)
                if ($podeCancelar) {
                    ?>
                    <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'cancelado')" 
                            class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-times-circle mr-2"></i>
                        <span class="hidden sm:inline">Cancelar</span>
                        <span class="sm:hidden">Cancelar</span>
                    </button>
                    <?php
                }
                // Botão Concluído
                ?>
                <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'concluido')" 
                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span class="hidden sm:inline">Concluído</span>
                    <span class="sm:hidden">Concluído</span>
                </button>
                
                <!-- Serviço não realizado -->
                <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'servico_nao_realizado')" 
                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span class="hidden sm:inline">Serviço não realizado</span>
                    <span class="sm:hidden">Não realizado</span>
                </button>
                
                <!-- Comprar peças -->
                <button onclick="executarAcao(<?= $solicitacao['id'] ?>, 'comprar_pecas')" 
                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    <span class="hidden sm:inline">Comprar peças</span>
                    <span class="sm:hidden">Peças</span>
                </button>
                
                <?php
            }
            ?>
        </div>
    </div>
</div>

<!-- Bloco 1: Informações do Cliente e Endereço -->
<div class="bg-white rounded-lg p-5 shadow-sm mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-user mr-2 text-blue-600"></i>
        Informações do Cliente e Endereço
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informações do Cliente -->
        <div>
            <h4 class="text-sm font-medium text-gray-700 mb-3">Informações do Cliente</h4>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-500">Nome:</span>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['locatario_nome']) ?></p>
                </div>
                <?php if (!empty($solicitacao['locatario_cpf'])): ?>
                <div>
                    <span class="text-sm text-gray-500">CPF:</span>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['locatario_cpf']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($solicitacao['locatario_telefone'])): ?>
                <div>
                    <span class="text-sm text-gray-500">Telefone:</span>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['locatario_telefone']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($solicitacao['imobiliaria_nome'])): ?>
                <div>
                    <span class="text-sm text-gray-500">Imobiliária:</span>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['imobiliaria_nome']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Endereço -->
        <?php if (!empty($solicitacao['imovel_endereco'])): ?>
        <div>
            <h4 class="text-sm font-medium text-gray-700 mb-3">
                <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                Endereço do Imóvel
            </h4>
            <div class="space-y-2">
                <p class="text-sm text-gray-900">
                    <?= htmlspecialchars($solicitacao['imovel_endereco']) ?>
                    <?php if (!empty($solicitacao['imovel_numero'])): ?>
                        , <?= htmlspecialchars($solicitacao['imovel_numero']) ?>
                    <?php endif; ?>
                    <?php if (!empty($solicitacao['imovel_complemento'])): ?>
                        - <?= htmlspecialchars($solicitacao['imovel_complemento']) ?>
                    <?php endif; ?>
                </p>
                <p class="text-sm text-gray-600">
                    <?= htmlspecialchars($solicitacao['imovel_bairro']) ?> - 
                    <?= htmlspecialchars($solicitacao['imovel_cidade']) ?>/<?= htmlspecialchars($solicitacao['imovel_estado']) ?>
                </p>
                <?php if (!empty($solicitacao['imovel_cep'])): ?>
                <p class="text-sm text-gray-600">CEP: <?= htmlspecialchars($solicitacao['imovel_cep']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bloco 2: Descrição do Problema, Informação do Serviço, Obs do Segurado e Fotos -->
<div class="bg-white rounded-lg p-5 shadow-sm mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
        Informações do Serviço
    </h3>
    
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Descrição do Problema -->
        <?php if (!empty($solicitacao['descricao_problema'])): ?>
        <div>
            <h4 class="text-sm font-medium text-gray-700 mb-3">
                <i class="fas fa-file-alt mr-2 text-gray-400"></i>
                Descrição do Problema
            </h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($solicitacao['descricao_problema'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Informação do Serviço -->
            <div>
            <h4 class="text-sm font-medium text-gray-700 mb-3">Informações do Serviço</h4>
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
    </div>
    
    <!-- Observações do Segurado -->
    <?php if (!empty($solicitacao['observacoes'])): ?>
    <div class="mt-6">
        <h4 class="text-sm font-medium text-gray-700 mb-3">
            <i class="fas fa-comment-dots mr-2 text-gray-400"></i>
            Observações do Segurado
        </h4>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($solicitacao['observacoes'])) ?></p>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Fotos Enviadas -->
    <?php if (!empty($fotos) && count($fotos) > 0): ?>
    <div class="mt-6">
        <h4 class="text-sm font-medium text-gray-700 mb-3">
            <i class="fas fa-camera mr-2 text-gray-400"></i>
            Fotos Enviadas (<?= count($fotos) ?>)
        </h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($fotos as $foto): ?>
            <div class="relative">
                <img src="<?= url('Public/uploads/fotos/' . $foto['arquivo']) ?>" 
                     alt="Foto da solicitação" 
                     class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                     onclick="abrirModalFoto('<?= url('Public/uploads/fotos/' . $foto['arquivo']) ?>')">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="mt-6">
        <h4 class="text-sm font-medium text-gray-700 mb-3">
            <i class="fas fa-camera mr-2 text-gray-400"></i>
            Fotos Enviadas (0)
        </h4>
        <p class="text-sm text-gray-500">Nenhuma foto foi enviada</p>
    </div>
    <?php endif; ?>
</div>

<!-- Bloco 3: Disponibilidade de Data, Status da Solicitação, Condições, Protocolo da Seguradora -->
<div class="bg-white rounded-lg p-5 shadow-sm mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
        Status e Agendamento
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Status da Solicitação -->
        <div>
            <h4 class="text-sm font-medium text-gray-700 mb-3">Status da Solicitação</h4>
            <div class="space-y-2">
                <div>
                    <span class="text-sm text-gray-500">Status Atual:</span>
                    <p class="text-sm font-medium text-gray-900">
                        <span class="status-badge status-<?= strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $solicitacao['status_nome'])) ?>">
                            <?= htmlspecialchars($solicitacao['status_nome']) ?>
                        </span>
                    </p>
                </div>
                <?php if (!empty($solicitacao['condicao_nome'])): ?>
                <div>
                    <span class="text-sm text-gray-500">Condição:</span>
                    <p class="text-sm font-medium text-gray-900">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" 
                              style="background-color: <?= htmlspecialchars($solicitacao['condicao_cor'] ?? '#6B7280') ?>20; color: <?= htmlspecialchars($solicitacao['condicao_cor'] ?? '#6B7280') ?>;">
                            <?= htmlspecialchars($solicitacao['condicao_nome']) ?>
                        </span>
                    </p>
                </div>
                <?php endif; ?>
                </div>
            </div>
            
        <!-- Datas -->
            <div>
            <h4 class="text-sm font-medium text-gray-700 mb-3">Datas</h4>
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
        
    <!-- Disponibilidade de Data -->
    <?php 
    $horariosOpcoes = [];
    if (!empty($solicitacao['horarios_opcoes'])) {
        $horariosOpcoes = is_string($solicitacao['horarios_opcoes']) ? json_decode($solicitacao['horarios_opcoes'], true) : $solicitacao['horarios_opcoes'];
    }
    if (!empty($solicitacao['datas_opcoes'])) {
        $datasOpcoes = is_string($solicitacao['datas_opcoes']) ? json_decode($solicitacao['datas_opcoes'], true) : $solicitacao['datas_opcoes'];
    }
    ?>
    <?php if (!empty($horariosOpcoes) || !empty($datasOpcoes)): ?>
        <div class="mt-6">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Disponibilidade de Data</h4>
            <div class="bg-gray-50 rounded-lg p-4">
            <?php if (!empty($datasOpcoes) && is_array($datasOpcoes)): ?>
                <?php foreach ($datasOpcoes as $data): ?>
                <p class="text-sm text-gray-900"><?= htmlspecialchars($data) ?></p>
                <?php endforeach; ?>
            <?php elseif (!empty($horariosOpcoes) && is_array($horariosOpcoes)): ?>
                <?php foreach ($horariosOpcoes as $horario): ?>
                <p class="text-sm text-gray-900"><?= htmlspecialchars($horario) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
    <!-- Protocolo da Seguradora -->
    <?php if (!empty($solicitacao['protocolo_seguradora'])): ?>
        <div class="mt-6">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Protocolo da Seguradora</h4>
            <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['protocolo_seguradora']) ?></p>
        </div>
    </div>
                <?php endif; ?>
            </div>

<!-- Bloco 4: Anexar Documentos com Campo de Obs -->
<div class="bg-white rounded-lg p-5 shadow-sm mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-paperclip mr-2 text-blue-600"></i>
        Anexar Documentos
    </h3>
    
    <form id="formAnexarDocumentos" onsubmit="processarAnexarDocumentos(event)" enctype="multipart/form-data">
        <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['id'] ?>">
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Anexos</label>
            <input type="file" name="anexos[]" multiple accept="image/*,.pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Você pode selecionar múltiplos arquivos (imagens, PDF, Word)</p>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-upload mr-2"></i>
                Enviar Documentos
            </button>
        </div>
    </form>
                </div>

<!-- Bloco 6: Linha do Tempo -->
<div class="bg-white rounded-lg p-5 shadow-sm mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-history mr-2 text-blue-600"></i>
        Linha do Tempo
    </h3>
    
    <?php if (!empty($historicoStatus) && count($historicoStatus) > 0): ?>
    <div class="space-y-4">
        <?php foreach ($historicoStatus as $index => $item): ?>
        <div class="flex items-start gap-4 relative">
            <?php if ($index < count($historicoStatus) - 1): ?>
            <div class="absolute left-3 top-8 w-0.5 h-full bg-gray-200"></div>
            <?php endif; ?>
            <div class="relative z-10 flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center" 
                 style="background-color: <?= htmlspecialchars($item['status_cor'] ?? '#3B82F6') ?>; box-shadow: 0 0 0 2px <?= htmlspecialchars($item['status_cor'] ?? '#3B82F6') ?>;">
                </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['status_nome'] ?? 'Status') ?></p>
                <?php if (!empty($item['observacao'])): ?>
                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($item['observacao']) ?></p>
                <?php endif; ?>
                <p class="text-xs text-gray-400 mt-1">
                    <?= date('d/m/Y, H:i', strtotime($item['created_at'])) ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-sm text-gray-500">Nenhum histórico disponível</p>
    <?php endif; ?>
            </div>
            
<!-- Bloco 7: Histórico do WhatsApp -->
<div class="bg-white rounded-lg p-5 shadow-sm mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fab fa-whatsapp mr-2 text-green-600"></i>
        Histórico do WhatsApp
    </h3>
    
    <?php if (!empty($whatsappHistorico) && count($whatsappHistorico) > 0): ?>
    <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php foreach ($whatsappHistorico as $envio): ?>
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-700">
                    <?= date('d/m/Y H:i', strtotime($envio['created_at'] ?? 'now')) ?>
                </span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                    <?= ($envio['status'] ?? '') === 'sucesso' ? 'bg-green-100 text-green-800' : 
                       (($envio['status'] ?? '') === 'erro' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                    <?= ($envio['status'] ?? '') === 'sucesso' ? 'Enviado' : 
                       (($envio['status'] ?? '') === 'erro' ? 'Erro' : 'Pendente') ?>
                </span>
            </div>
            <?php if (!empty($envio['mensagem'])): ?>
            <p class="text-sm text-gray-900"><?= htmlspecialchars($envio['mensagem']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-sm text-gray-500">Nenhum histórico de WhatsApp disponível</p>
    <?php endif; ?>
</div>

<!-- Modal para visualizar foto em tamanho maior -->
<div id="modalFoto" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="fecharModalFoto()" class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75">
            <i class="fas fa-times"></i>
        </button>
        <img id="fotoModal" src="" alt="Foto" class="max-w-full max-h-[90vh] rounded-lg">
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

<!-- Modal Reagendar -->
<div id="modalReagendar" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reagendar Serviço</h3>
        <p class="text-sm text-gray-600 mb-4">
            Você será redirecionado para a página de agendamento para selecionar uma nova data e horário.
        </p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="fecharModal('modalReagendar')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancelar
            </button>
            <button onclick="confirmarReagendar(<?= $solicitacao['id'] ?>)" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                Confirmar Reagendamento
            </button>
        </div>
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

function abrirModalFoto(url) {
    document.getElementById('fotoModal').src = url;
    document.getElementById('modalFoto').classList.remove('hidden');
}

function fecharModalFoto() {
    document.getElementById('modalFoto').classList.add('hidden');
}

function executarAcao(solicitacaoId, acao) {
    solicitacaoIdAtual = solicitacaoId;
    
    const modais = {
        'concluido': 'modalConcluido',
        'cancelado': 'modalCancelando',
        'servico_nao_realizado': 'modalServicoNaoRealizado',
        'comprar_pecas': 'modalComprarPecas',
        'reembolso': 'modalReembolso',
        'reagendar': 'modalReagendar'
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

function processarAnexarDocumentos(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('acao', 'anexar_documentos');
    
    fetch('<?= url($locatario['instancia']) ?>/solicitacoes/' + solicitacaoIdAtual + '/acao', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Documentos anexados com sucesso!');
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

function processarReembolsoBloco(event) {
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
            alert(data.message || 'Reembolso registrado com sucesso!');
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

function confirmarReagendar(solicitacaoId) {
    // Redirecionar para a página de nova solicitação com os dados pré-preenchidos
    // ou para uma página específica de reagendamento
    window.location.href = '<?= url($locatario['instancia']) ?>/nova-solicitacao?reagendar=' + solicitacaoId;
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
