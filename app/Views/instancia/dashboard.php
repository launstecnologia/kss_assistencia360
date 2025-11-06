<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($imobiliaria['nome'] ?? 'KSS') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: <?= $imobiliaria['cor_primaria'] ?? '#3B82F6' ?>;
            --secondary-color: <?= $imobiliaria['cor_secundaria'] ?? '#1E40AF' ?>;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <?php if (!empty($imobiliaria['logo'])): ?>
                        <img class="h-8 w-auto mr-3" src="<?= url('Public/uploads/logos/' . $imobiliaria['logo']) ?>" alt="<?= htmlspecialchars($imobiliaria['nome']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center mr-3" style="display: none;">
                            <i class="fas fa-building text-white text-sm"></i>
                        </div>
                    <?php else: ?>
                        <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-building text-white text-sm"></i>
                        </div>
                    <?php endif; ?>
                    <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($imobiliaria['nome'] ?? 'KSS Seguros') ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Olá, <?= htmlspecialchars($locatario['nome'] ?? 'Usuário') ?></span>
                    <a href="/<?= htmlspecialchars($imobiliaria['instancia']) ?>/logout" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Seus Endereços -->
        <?php if (!empty($imoveis)): ?>
        <div class="mb-8 bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-map-marker-alt mr-2"></i>
                Seus Endereços
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($imoveis as $imovel): ?>
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start">
                        <i class="fas fa-home text-blue-500 mt-1 mr-3"></i>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">
                                <?= htmlspecialchars($imovel['endereco_logradouro']) ?>
                                <?php if (!empty($imovel['endereco_numero'])): ?>
                                    , <?= htmlspecialchars($imovel['endereco_numero']) ?>
                                <?php endif; ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <?= htmlspecialchars($imovel['endereco_bairro']) ?>, 
                                <?= htmlspecialchars($imovel['endereco_cidade']) ?> - 
                                <?= htmlspecialchars($imovel['endereco_estado']) ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                CEP: <?= htmlspecialchars($imovel['endereco_cep']) ?>
                            </p>
                            <?php if (!empty($imovel['contrato_cod'])): ?>
                            <p class="text-xs text-blue-600 mt-2">
                                Contrato: <?= htmlspecialchars($imovel['contrato_cod']) ?>
                                <?php if (!empty($imovel['contrato_dv'])): ?>
                                    -<?= htmlspecialchars($imovel['contrato_dv']) ?>
                                <?php endif; ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Ações Rápidas -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h2>
                <div class="space-y-3">
                    <a href="/<?= htmlspecialchars($imobiliaria['instancia']) ?>/nova-solicitacao" 
                       class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <i class="fas fa-plus text-green-600 mr-3"></i>
                        <span class="text-green-800 font-medium">Nova Solicitação</span>
                    </a>
                    <a href="/<?= htmlspecialchars($imobiliaria['instancia']) ?>/solicitacoes" 
                       class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <i class="fas fa-list text-blue-600 mr-3"></i>
                        <span class="text-blue-800 font-medium">Minhas Solicitações</span>
                    </a>
                    <a href="/<?= htmlspecialchars($imobiliaria['instancia']) ?>/perfil" 
                       class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <i class="fas fa-user text-purple-600 mr-3"></i>
                        <span class="text-purple-800 font-medium">Meu Perfil</span>
                    </a>
                </div>
            </div>

            <!-- Informações Importantes -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações Importantes</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <i class="fas fa-clock text-yellow-500 mt-1 mr-3"></i>
                        <span class="text-gray-700">As solicitações são processadas em até 24 horas</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-phone text-green-500 mt-1 mr-3"></i>
                        <span class="text-gray-700">Você receberá atualizações via WhatsApp</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-calendar text-blue-500 mt-1 mr-3"></i>
                        <span class="text-gray-700">Agendamentos podem ser cancelados até 1 dia antes</span>
                    </div>
                    
                    <!-- Endereço da Imobiliária -->
                    <?php if (!empty($imobiliaria['endereco_logradouro'])): ?>
                    <div class="flex items-start">
                        <i class="fas fa-map-marker-alt text-red-500 mt-1 mr-3"></i>
                        <div class="text-gray-700">
                            <div class="font-medium">Endereço da Imobiliária:</div>
                            <div class="text-sm">
                                <?= htmlspecialchars($imobiliaria['endereco_logradouro']) ?>
                                <?php if (!empty($imobiliaria['endereco_numero'])): ?>
                                    , <?= htmlspecialchars($imobiliaria['endereco_numero']) ?>
                                <?php endif; ?>
                                <?php if (!empty($imobiliaria['endereco_complemento'])): ?>
                                    - <?= htmlspecialchars($imobiliaria['endereco_complemento']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm">
                                <?php if (!empty($imobiliaria['endereco_bairro'])): ?>
                                    <?= htmlspecialchars($imobiliaria['endereco_bairro']) ?>
                                <?php endif; ?>
                                <?php if (!empty($imobiliaria['endereco_cidade'])): ?>
                                    - <?= htmlspecialchars($imobiliaria['endereco_cidade']) ?>
                                <?php endif; ?>
                                <?php if (!empty($imobiliaria['endereco_estado'])): ?>
                                    /<?= htmlspecialchars($imobiliaria['endereco_estado']) ?>
                                <?php endif; ?>
                                <?php if (!empty($imobiliaria['endereco_cep'])): ?>
                                    - CEP: <?= htmlspecialchars($imobiliaria['endereco_cep']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Seus Dados -->
        <?php if ($locatario): ?>
        <div class="mt-8 bg-white rounded-lg shadow-sm border p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-user mr-2"></i>
                    Seus Dados
                </h2>
                <a href="/<?= htmlspecialchars($imobiliaria['instancia']) ?>/perfil" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Informações Pessoais -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2 flex items-center">
                        <i class="fas fa-user mr-2"></i>
                        Informações Pessoais
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
        <?php endif; ?>

        <!-- Status da Conta -->
        <?php if ($locatario): ?>
        <div class="mt-8 bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Status da Conta</h2>
            
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    Ativo
                </span>
                <div class="text-sm text-gray-600">
                    <p>Última sincronização: <?= date('d/m/Y', strtotime($locatario['ultima_sincronizacao'])) ?></p>
                    <p class="text-xs text-gray-500">
                        Dados sincronizados em: <?= date('d/m/Y, H:i:s', strtotime($locatario['ultima_sincronizacao'])) ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>