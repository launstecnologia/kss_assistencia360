<?php
/**
 * View: Dashboard do Locatário
 */
$title = 'Dashboard - Assistência 360°';
$currentPage = 'locatario-dashboard';
ob_start();
?>

<!-- Welcome Banner -->
<div class="locatario-gradient rounded-lg p-8 mb-8 text-white text-center">
    <div>
        <h1 class="text-3xl font-bold mb-2">
            Olá, <?= htmlspecialchars(explode(' ', $locatario['nome'])[0]) ?>.
        </h1>
        <p class="text-lg opacity-90 mb-6">
            Bem-vindo ao seu portal de assistência.
        </p>
    </div>
    
    <div class="flex justify-center">
        <a href="<?= url($locatario['instancia'] . '/nova-solicitacao') ?>" 
           class="inline-flex items-center px-6 py-3 bg-white text-green-600 font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nova Solicitação
        </a>
    </div>
</div>

<!-- Messages -->
<?php if (isset($_GET['error'])): ?>
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded alert-message">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded alert-message">
        <i class="fas fa-check-circle mr-2"></i>
        <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-2 gap-3 md:gap-6 mb-8">
    <!-- Solicitações Agendadas -->
    <div class="bg-white rounded-lg shadow-sm p-4 md:p-6 card-hover">
        <div class="flex flex-col md:flex-row items-center md:items-start text-center md:text-left">
            <div class="flex-shrink-0 mb-2 md:mb-0">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto md:mx-0">
                    <i class="fas fa-calendar-check text-blue-600 text-lg md:text-xl"></i>
                </div>
            </div>
            <div class="md:ml-4 flex-1">
                <p class="hidden md:block text-sm font-medium text-gray-500">Agendadas</p>
                <p class="text-xl md:text-2xl font-bold text-gray-900"><?= $stats['agendadas'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <!-- Solicitações Concluídas -->
    <div class="bg-white rounded-lg shadow-sm p-4 md:p-6 card-hover">
        <div class="flex flex-col md:flex-row items-center md:items-start text-center md:text-left">
            <div class="flex-shrink-0 mb-2 md:mb-0">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto md:mx-0">
                    <i class="fas fa-check-circle text-green-600 text-lg md:text-xl"></i>
                </div>
            </div>
            <div class="md:ml-4 flex-1">
                <p class="hidden md:block text-sm font-medium text-gray-500">Concluídas</p>
                <p class="text-xl md:text-2xl font-bold text-gray-900"><?= $stats['concluidas'] ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Solicitations -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-list mr-2"></i>
                Suas Solicitações
            </h2>
            <a href="<?= url($locatario['instancia'] . '/solicitacoes') ?>" 
               class="text-sm text-blue-600 hover:text-blue-500">
                Ver todas
            </a>
        </div>
    </div>
    
    <div class="p-6">
        <?php if (empty($solicitacoes)): ?>
            <div class="text-center py-12">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma solicitação encontrada</h3>
                <p class="text-gray-500 mb-6">Você ainda não possui solicitações de assistência.</p>
                <a href="<?= url($locatario['instancia'] . '/nova-solicitacao') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Primeira Solicitação
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($solicitacoes, 0, 5) as $solicitacao): 
                    $numeroSolicitacao = $solicitacao['numero_solicitacao'] ?? ('KSS' . $solicitacao['id']);
                    $protocolo = $solicitacao['protocolo_seguradora'] ?? '-';
                    
                    // Formatar data criada com horário em português
                    $dataCriada = date('d/m/Y \à\s H:i', strtotime($solicitacao['created_at']));
                    
                    // Formatar data agendada
                    if (!empty($solicitacao['data_agendamento'])) {
                        $dataAgendamento = $solicitacao['data_agendamento'];
                        $timestamp = strtotime($dataAgendamento);
                        if ($timestamp !== false) {
                            // Sempre mostrar data e horário quando tiver agendamento
                            $dataAgendada = date('d/m/Y \à\s H:i', $timestamp);
                        } else {
                            $dataAgendada = 'Data inválida';
                        }
                    } else {
                        $dataAgendada = 'Aguardando confirmação do prestador';
                    }
                ?>
                    <a href="<?= url($locatario['instancia'] . '/solicitacoes/' . $solicitacao['id']) ?>" 
                       class="block border border-gray-200 rounded-lg p-3 hover:bg-gray-50 hover:border-green-300 transition-all cursor-pointer">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                            <!-- Nº Solicitação -->
                            <div>
                                <span class="text-gray-500">Nº Solicitação:</span>
                                <span class="font-semibold text-gray-900 ml-1"><?= htmlspecialchars($numeroSolicitacao) ?></span>
                            </div>
                            
                            <!-- Nº Protocolo -->
                            <div>
                                <span class="text-gray-500">Nº Protocolo:</span>
                                <span class="font-semibold text-gray-900 ml-1"><?= htmlspecialchars($protocolo) ?></span>
                            </div>
                            
                            <!-- Status -->
                            <div class="col-span-2 md:col-span-1">
                                <span class="text-gray-500">Status:</span>
                                <span class="status-badge status-<?= strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $solicitacao['status_nome'])) ?> ml-1 text-xs">
                                    <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                </span>
                            </div>
                            
                            <!-- Categoria -->
                            <div>
                                <span class="text-gray-500">Categoria:</span>
                                <span class="font-medium text-gray-900 ml-1"><?= htmlspecialchars($solicitacao['categoria_nome'] ?? '-') ?></span>
                            </div>
                            
                            <!-- Data Criada -->
                            <div>
                                <span class="text-gray-500">Data Criada:</span>
                                <span class="font-medium text-gray-900 ml-1"><?= $dataCriada ?></span>
                            </div>
                            
                            <!-- Data Agendada -->
                            <div>
                                <span class="text-gray-500">Data Agendada:</span>
                                <span class="font-medium ml-1 <?= $dataAgendada === 'Aguardando confirmação do prestador' ? 'text-red-600' : 'text-gray-900' ?>"><?= $dataAgendada ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($solicitacoes) > 5): ?>
                <div class="mt-6 text-center">
                    <a href="<?= url($locatario['instancia'] . '/solicitacoes') ?>" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium text-gray-700 bg-white rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-list mr-2"></i>
                        Ver Todas as Solicitações (<?= count($solicitacoes) ?>)
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Seus Dados -->
<div class="mt-8 bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="fas fa-user mr-2"></i>
            Seus Dados
        </h2>
        <a href="<?= url($locatario['instancia'] . '/perfil') ?>" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
            <i class="fas fa-edit mr-2"></i>
            Editar
        </a>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Nome -->
        <div>
            <h3 class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                <i class="fas fa-user mr-2"></i>
                Nome Completo
            </h3>
            <p class="text-gray-900 font-medium"><?= htmlspecialchars($locatario['nome']) ?></p>
        </div>
        
        <!-- WhatsApp -->
        <div>
            <h3 class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                <i class="fab fa-whatsapp mr-2 text-green-500"></i>
                WhatsApp
            </h3>
            <p class="text-gray-900 font-medium">
                <?= !empty($locatario['whatsapp']) ? htmlspecialchars($locatario['whatsapp']) : 'Não cadastrado' ?>
            </p>
            <?php if (empty($locatario['whatsapp'])): ?>
            <p class="text-xs text-gray-500 mt-1">Usado para enviar notificações importantes sobre suas solicitações</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Seus Imóveis e Status da Conta -->
<div class="mt-8 space-y-6">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            <i class="fas fa-home mr-2 text-blue-600"></i>
            Seus Imóveis
        </h3>
        <p class="text-sm text-gray-500 mb-4">Imóveis vinculados ao seu contrato</p>
        
        <?php if (!empty($locatario['imoveis'])): ?>
            <?php foreach ($locatario['imoveis'] as $imovel): ?>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-home mr-2 text-gray-600"></i>
                                <span class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($imovel['endereco']) ?>, <?= htmlspecialchars($imovel['numero']) ?>
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-1">
                                <?= htmlspecialchars($imovel['bairro']) ?>, <?= htmlspecialchars($imovel['cidade']) ?> - <?= htmlspecialchars($imovel['uf']) ?>
                            </div>
                            <div class="text-sm text-gray-600">
                                CEP: <?= htmlspecialchars($imovel['cep']) ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <?php if (!empty($imovel['contratos'])): ?>
                                <?php 
                                $contratoAtivo = null;
                                foreach ($imovel['contratos'] as $contrato) {
                                    if ($contrato['CtrStatus'] !== 'RESCINDIDO') {
                                        $contratoAtivo = $contrato;
                                        break;
                                    }
                                }
                                if (!$contratoAtivo && !empty($imovel['contratos'])) {
                                    $contratoAtivo = $imovel['contratos'][0]; // Pega o primeiro se não houver ativo
                                }
                                ?>
                                <?php if ($contratoAtivo): ?>
                                    <div class="text-xs text-gray-500 mb-1">
                                        Contrato: <?= htmlspecialchars($contratoAtivo['CtrCod']) ?>-<?= htmlspecialchars($contratoAtivo['CtrDV']) ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="text-xs text-gray-400">
                                Cód: <?= htmlspecialchars($imovel['codigo']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-home text-3xl mb-2"></i>
                <p>Nenhum imóvel encontrado</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Status da Conta -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
            <i class="fas fa-user-check mr-2 text-green-600"></i>
            Status da Conta
        </h3>
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i>
                    Ativo
                </span>
            </div>
            <div class="text-right text-sm text-gray-500">
                <div>Última sincronização: <?= date('d/m/Y') ?></div>
                <div>Dados sincronizados em: <?= date('d/m/Y, H:i:s') ?></div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/locatario.php';
?>
