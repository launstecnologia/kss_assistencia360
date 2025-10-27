<?php
$title = 'Dashboard';
$currentPage = 'dashboard';
$pageTitle = 'Dashboard';
ob_start();
?>

<!-- Estatísticas Gerais -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Solicitações</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $estatisticas['total'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Concluídas</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $estatisticas['concluidas'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Tempo Médio</p>
                <p class="text-2xl font-semibold text-gray-900"><?= round($estatisticas['tempo_medio_resolucao'] ?? 0) ?>h</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-purple-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Satisfação</p>
                <p class="text-2xl font-semibold text-gray-900"><?= round($estatisticas['satisfacao_media'] ?? 0, 1) ?>/5</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Período -->
<div class="bg-white p-6 rounded-lg shadow-sm mb-8">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">Período de Análise</h3>
        <div class="flex space-x-2">
            <button onclick="updatePeriod('7')" class="px-3 py-1 text-sm rounded-md <?= $periodo === '7' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">7 dias</button>
            <button onclick="updatePeriod('30')" class="px-3 py-1 text-sm rounded-md <?= $periodo === '30' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">30 dias</button>
            <button onclick="updatePeriod('90')" class="px-3 py-1 text-sm rounded-md <?= $periodo === '90' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">90 dias</button>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Gráfico de Solicitações por Status -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Solicitações por Status</h3>
        <canvas id="statusChart" width="400" height="200"></canvas>
    </div>
    
    <!-- Gráfico de Solicitações por Imobiliária -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Solicitações por Imobiliária</h3>
        <canvas id="imobiliariaChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Kanban de Solicitações -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Solicitações Recentes</h3>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($kanbanData as $status): ?>
            <div class="kanban-column">
                <div class="flex items-center mb-4">
                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: <?= $status['status_cor'] ?>"></div>
                    <h4 class="font-medium text-gray-900"><?= $status['status_nome'] ?></h4>
                    <span class="ml-auto bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">
                        <?= count(array_filter($kanbanData, fn($s) => $s['status_id'] === $status['status_id'])) ?>
                    </span>
                </div>
                
                <div class="space-y-3">
                    <?php 
                    $solicitacoesStatus = array_filter($kanbanData, fn($s) => $s['status_id'] === $status['status_id']);
                    foreach (array_slice($solicitacoesStatus, 0, 5) as $solicitacao): 
                    ?>
                    <div class="drag-item bg-gray-50 p-4 rounded-lg border border-gray-200 cursor-pointer hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h5 class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['locatario_nome']) ?></h5>
                                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($solicitacao['categoria_nome']) ?></p>
                                <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y', strtotime($solicitacao['created_at'])) ?></p>
                            </div>
                            <a href="<?= url('solicitacoes/' . $solicitacao['id']) ?>" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Solicitações Pendentes -->
<?php if (!empty($solicitacoesPendentes)): ?>
<div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
        <h3 class="text-lg font-medium text-yellow-800">Solicitações Pendentes</h3>
    </div>
    <p class="mt-2 text-yellow-700">
        <?= count($solicitacoesPendentes) ?> solicitações estão aguardando há mais de 10 dias e precisam de atenção.
    </p>
    <div class="mt-4">
        <a href="<?= url('solicitacoes?status=pendente') ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200">
            Ver Solicitações Pendentes
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Dados Administrativos (apenas para admins) -->
<?php if ($user['nivel_permissao'] === 'ADMINISTRADOR'): ?>
<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Imobiliárias Ativas</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $total_imobiliarias ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-green-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Usuários Ativos</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $total_usuarios ?? 0 ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tags text-purple-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Categorias Ativas</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $total_categorias ?? 0 ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function updatePeriod(periodo) {
    window.location.href = '<?= url('dashboard') ?>?periodo=' + periodo;
}

// Aguardar o DOM estar pronto
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Status
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusChart = new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Concluídas', 'Novas', 'Aguardando Peça', 'Outros'],
                datasets: [{
                    data: [
                        <?= $estatisticas['concluidas'] ?? 0 ?>,
                        <?= $estatisticas['novas'] ?? 0 ?>,
                        <?= $estatisticas['aguardando_peca'] ?? 0 ?>,
                        <?= ($estatisticas['total'] ?? 0) - ($estatisticas['concluidas'] ?? 0) - ($estatisticas['novas'] ?? 0) - ($estatisticas['aguardando_peca'] ?? 0) ?>
                    ],
                    backgroundColor: [
                        '#10B981',
                        '#3B82F6',
                        '#F59E0B',
                        '#6B7280'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Gráfico de Imobiliárias
    const imobiliariaCtx = document.getElementById('imobiliariaChart');
    if (imobiliariaCtx) {
        const imobiliariaChart = new Chart(imobiliariaCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Imobiliária A', 'Imobiliária B', 'Imobiliária C'],
                datasets: [{
                    label: 'Solicitações',
                    data: [12, 19, 8],
                    backgroundColor: '#3B82F6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
