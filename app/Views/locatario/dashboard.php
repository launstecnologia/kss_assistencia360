<?php
/**
 * View: Dashboard do Locatário
 */
$title = 'Dashboard - Assistência 360°';
$currentPage = 'locatario-dashboard';
ob_start();
?>

<!-- Welcome Banner -->
<div class="locatario-gradient rounded-lg p-8 mb-8 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold mb-2">
                Olá, <?= htmlspecialchars(explode(' ', $locatario['nome'])[0]) ?>! 👋
            </h1>
            <p class="text-lg opacity-90">
                Bem-vindo ao seu portal de assistência. Aqui você pode criar novas solicitações e acompanhar o andamento dos seus serviços.
            </p>
        </div>
        <div class="hidden md:block">
            <i class="fas fa-home text-6xl opacity-20"></i>
        </div>
    </div>
    
    <div class="mt-6">
        <a href="<?= url($locatario['instancia'] . '/nova-solicitacao') ?>" 
           class="inline-flex items-center px-6 py-3 bg-white text-green-600 font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nova Solicitação
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6 card-hover">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Solicitações Ativas</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['ativas'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 card-hover">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Serviços Concluídos</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['concluidas'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm p-6 card-hover">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list text-gray-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total de Solicitações</p>
                <p class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?></p>
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
            <div class="space-y-4">
                <?php foreach (array_slice($solicitacoes, 0, 5) as $solicitacao): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($solicitacao['categoria_nome']) ?>
                                    </h3>
                                    <span class="status-badge status-<?= strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $solicitacao['status_nome'])) ?>">
                                        <?= htmlspecialchars($solicitacao['status_nome']) ?>
                                    </span>
                                </div>
                                
                                <p class="text-xs text-gray-500 mb-2">
                                    Protocolo: <?= htmlspecialchars($solicitacao['numero_solicitacao'] ?? '#' . $solicitacao['id']) ?>
                                </p>
                                
                                <?php if (!empty($solicitacao['descricao_problema'])): ?>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <?= htmlspecialchars(substr($solicitacao['descricao_problema'], 0, 100)) ?>
                                        <?= strlen($solicitacao['descricao_problema']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="text-xs text-gray-400">
                                    Criado em: <?= date('d/m/Y \à\s H:i', strtotime($solicitacao['created_at'])) ?>
                                </p>
                            </div>
                            
                            <div class="flex-shrink-0 ml-4">
                                <a href="<?= url($locatario['instancia'] . '/solicitacoes/' . $solicitacao['id']) ?>" 
                                   class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-full hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
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
