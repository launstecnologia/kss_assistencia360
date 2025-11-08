<?php
/**
 * View: Tela de Emergência - Telefone 0800
 */
$title = 'Atendimento Emergencial - Assistência 360°';
$currentPage = 'locatario-emergencial';
ob_start();
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                Atendimento Emergencial
            </h1>
            <p class="text-gray-600 mt-1">
                Sua solicitação foi criada com sucesso
            </p>
        </div>
    </div>
</div>

<!-- Card Principal -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <!-- Header do Card -->
    <div class="bg-red-600 px-6 py-4 text-white">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
            <div>
                <h2 class="text-xl font-bold">Solicitação Emergencial Criada</h2>
                <p class="text-red-100 text-sm mt-1">Nº Solicitação: <?= htmlspecialchars($solicitacao['numero_solicitacao'] ?? 'KSI' . $solicitacao['id']) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo -->
    <div class="p-6">
        <!-- Aviso Fora do Horário Comercial -->
        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <i class="fas fa-clock text-red-600 text-2xl mr-4 mt-1"></i>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-red-900 mb-2">Fora do Horário Comercial</h3>
                    <p class="text-red-800 mb-4">
                        Sua solicitação foi registrada, mas como está sendo feita fora do horário comercial (08h00 às 17h30) ou em final de semana, 
                        é necessário entrar em contato pelo telefone de emergência para garantir um atendimento imediato.
                    </p>
                    
                    <?php if ($telefoneEmergencia): ?>
                        <div class="bg-white rounded-lg p-4 border border-red-300">
                            <p class="text-sm font-medium text-gray-700 mb-3">Ligue agora para o nosso atendimento de emergência:</p>
                            <div class="flex items-center justify-center">
                                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $telefoneEmergencia['numero']) ?>" 
                                   class="inline-flex items-center px-8 py-4 bg-red-600 text-white font-bold text-xl rounded-lg hover:bg-red-700 transition-colors shadow-lg"
                                   onclick="if(navigator.userAgent.match(/iPhone|iPad|iPod/i)) { window.location.href='tel:<?= preg_replace('/[^0-9+]/', '', $telefoneEmergencia['numero']) ?>'; return false; }">
                                    <i class="fas fa-phone mr-3 text-2xl"></i>
                                    Ligar <?= htmlspecialchars($telefoneEmergencia['numero']) ?>
                                </a>
                            </div>
                            <?php if (!empty($telefoneEmergencia['descricao'])): ?>
                                <p class="text-xs text-gray-600 mt-3 text-center">
                                    <?= htmlspecialchars($telefoneEmergencia['descricao']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                            <p class="text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Telefone de emergência não configurado. Entre em contato com a administração.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Informações da Solicitação -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                Informações da Solicitação
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Número da Solicitação</p>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['numero_solicitacao'] ?? 'KSI' . $solicitacao['id']) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Categoria</p>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['categoria_nome'] ?? 'Não informado') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Subcategoria</p>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($solicitacao['subcategoria_nome'] ?? 'Não informado') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Data de Criação</p>
                    <p class="text-sm font-medium text-gray-900"><?= date('d/m/Y H:i', strtotime($solicitacao['created_at'])) ?></p>
                </div>
            </div>
        </div>
        
        <!-- Instruções -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-medium text-blue-900 mb-2">
                <i class="fas fa-lightbulb mr-2"></i>
                O que fazer agora?
            </h4>
            <ul class="text-sm text-blue-800 space-y-2 list-disc list-inside">
                <li>Ligue para o telefone de emergência acima</li>
                <li>Informe o número da sua solicitação: <strong><?= htmlspecialchars($solicitacao['numero_solicitacao'] ?? 'KSI' . $solicitacao['id']) ?></strong></li>
                <li>Nossa equipe entrará em contato em até 120 minutos</li>
                <li>Você receberá atualizações via WhatsApp</li>
            </ul>
        </div>
        
        <!-- Botão para Dashboard -->
        <div class="flex justify-center">
            <a href="<?= url($instancia . '/dashboard') ?>" 
               class="inline-flex items-center px-6 py-3 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar para o Dashboard
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/locatario.php';
?>

