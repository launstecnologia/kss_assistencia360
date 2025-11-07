<?php
/**
 * View: Nova Solicitação do Locatário - Sistema de Steps
 */
$title = 'Nova Solicitação - Assistência 360°';
$currentPage = 'locatario-nova-solicitacao';
ob_start();

// Definir etapa atual (pode vir do controller, da sessão, ou padrão 1)
$etapaAtual = $etapa ?? $_SESSION['nova_solicitacao']['etapa'] ?? 1;
$etapaAtual = (int)$etapaAtual;

// Se não há dados na sessão e não é etapa 1, forçar etapa 1
if (!isset($_SESSION['nova_solicitacao']) && $etapaAtual > 1) {
    $etapaAtual = 1;
}

// Definir steps
$steps = [
    1 => ['nome' => 'Endereço', 'icone' => 'fas fa-map-marker-alt'],
    2 => ['nome' => 'Serviço', 'icone' => 'fas fa-cog'],
    3 => ['nome' => 'Descrição', 'icone' => 'fas fa-edit'],
    4 => ['nome' => 'Agendamento', 'icone' => 'fas fa-calendar'],
    5 => ['nome' => 'Confirmação', 'icone' => 'fas fa-check']
];
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-plus-circle mr-2"></i>
                Nova Solicitação
            </h1>
            <p class="text-gray-600 mt-1">
                Preencha os dados abaixo para criar uma nova solicitação de assistência
            </p>
        </div>
        <a href="<?= url($locatario['instancia'] . '/dashboard') ?>" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Progress Steps -->
<div class="mb-8">
    <!-- Mobile: Versão compacta apenas com números -->
    <div class="md:hidden">
        <div class="flex items-center justify-between">
            <?php foreach ($steps as $numero => $step): ?>
                <div class="flex flex-col items-center flex-1">
                    <!-- Step Circle -->
                    <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 <?= $numero <= $etapaAtual ? 'bg-green-600 border-green-600 text-white' : 'border-gray-300 text-gray-400' ?>">
                        <?php if ($numero < $etapaAtual): ?>
                            <i class="fas fa-check text-xs"></i>
                        <?php else: ?>
                            <span class="text-xs font-medium"><?= $numero ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Step Label (apenas para etapa atual) -->
                    <?php if ($numero == $etapaAtual): ?>
                        <p class="text-xs font-medium text-green-600 mt-1 text-center"><?= $step['nome'] ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($numero < count($steps)): ?>
                    <div class="flex-1 mx-1 h-0.5 <?= $numero < $etapaAtual ? 'bg-green-600' : 'bg-gray-300' ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Desktop: Versão completa -->
    <div class="hidden md:flex items-center justify-between">
        <?php foreach ($steps as $numero => $step): ?>
            <div class="flex items-center">
                <!-- Step Circle -->
                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 <?= $numero <= $etapaAtual ? 'bg-green-600 border-green-600 text-white' : 'border-gray-300 text-gray-400' ?>">
                    <?php if ($numero < $etapaAtual): ?>
                        <i class="fas fa-check text-sm"></i>
                    <?php else: ?>
                        <span class="text-sm font-medium"><?= $numero ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Step Label -->
                <div class="ml-3">
                    <p class="text-sm font-medium <?= $numero <= $etapaAtual ? 'text-green-600' : 'text-gray-400' ?>">
                        <?= $step['nome'] ?>
                    </p>
                </div>
                
                <!-- Connector Line -->
                <?php if ($numero < count($steps)): ?>
                    <div class="flex-1 mx-4 h-0.5 <?= $numero < $etapaAtual ? 'bg-green-600' : 'bg-gray-300' ?>"></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
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

<!-- Step Content -->
    <div class="bg-white rounded-lg shadow-sm">
    <?php if ($etapaAtual == 1): ?>
        <!-- ETAPA 1: ENDEREÇO -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-map-marker-alt mr-2"></i>
                Onde será realizado o serviço?
            </h2>
            <p class="text-sm text-gray-500 mt-1">Selecione um endereço salvo e o tipo de propriedade</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao') ?>" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                <input type="hidden" name="etapa" value="1">
                
                <!-- Endereços Salvos - ABORDAGEM SIMPLIFICADA COM INLINE STYLES -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Endereços Salvos</h3>
                    <div id="endereco-container-live">
                        <?php if (!empty($locatario['imoveis'])): ?>
                            <?php foreach ($locatario['imoveis'] as $index => $imovel): ?>
                                <?php
                                $endereco = $imovel['endereco'] ?? '';
                                $numero = $imovel['numero'] ?? '';
                                $bairro = $imovel['bairro'] ?? '';
                                $cidade = $imovel['cidade'] ?? '';
                                $uf = $imovel['uf'] ?? '';
                                $cep = $imovel['cep'] ?? '';
                                $codigo = $imovel['codigo'] ?? '';
                                
                                $contratoInfo = '';
                                if (!empty($imovel['contratos'])) {
                                    foreach ($imovel['contratos'] as $c) {
                                        if ($c['CtrTipo'] == 'PRINCIPAL') {
                                            $contratoInfo = $c['CtrCod'] . '-' . $c['CtrDV'];
                                            break;
                                        }
                                    }
                                    if (!$contratoInfo && !empty($imovel['contratos'][0])) {
                                        $contratoInfo = $imovel['contratos'][0]['CtrCod'] . '-' . $imovel['contratos'][0]['CtrDV'];
                                    }
                                }
                                ?>
                                <div class="endereco-item-<?= $index ?>" data-endereco="<?= $index ?>" style="margin-bottom:12px;">
                                    <input type="radio" name="endereco_selecionado" value="<?= $index ?>" id="end-<?= $index ?>" style="position:absolute;opacity:0;" <?= $index == 0 ? 'checked' : '' ?>>
                                    <label for="end-<?= $index ?>" style="display:block;border:2px solid <?= $index == 0 ? '#10b981' : '#d1d5db' ?>;background:<?= $index == 0 ? '#ecfdf5' : '#fff' ?>;border-radius:8px;padding:16px;cursor:pointer;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                            <div style="flex:1;padding-right:32px;">
                                                <div style="background:#dbeafe;color:#1e40af;padding:4px 8px;border-radius:4px;font-size:11px;display:inline-block;margin-bottom:8px;font-weight:500;">
                                                    Imóvel Contratual
                                                </div>
                                                <div style="font-weight:600;font-size:14px;color:#111827;margin-bottom:4px;">
                                                    <?= htmlspecialchars($endereco . ', ' . $numero) ?>
                                                </div>
                                                <div style="color:#6b7280;font-size:14px;margin-bottom:2px;">
                                                    <?= htmlspecialchars($bairro . ', ' . $cidade . ' - ' . $uf) ?>
                                                </div>
                                                <div style="color:#6b7280;font-size:14px;margin-bottom:6px;">
                                                    CEP: <?= htmlspecialchars($cep) ?>
                                                </div>
                                                <div style="color:#9ca3af;font-size:12px;">
                                                    Contrato: <?= htmlspecialchars($contratoInfo) ?> | Cód: <?= htmlspecialchars($codigo) ?>
                                                </div>
                                            </div>
                                            <div style="width:24px;height:24px;border-radius:50%;background:<?= $index == 0 ? '#10b981' : '#fff' ?>;border:2px solid <?= $index == 0 ? '#10b981' : '#d1d5db' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <i class="fas fa-check" style="color:<?= $index == 0 ? '#fff' : 'transparent' ?>;font-size:10px;"></i>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <div class="mb-4">
                                    <i class="fas fa-home text-5xl text-gray-300"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                    Nenhum imóvel encontrado
                                </h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    Não foi possível carregar seus imóveis. Isso pode ocorrer por:
                                </p>
                                <ul class="text-sm text-gray-600 text-left inline-block mb-6">
                                    <li class="mb-2">• Você não possui imóveis cadastrados no sistema</li>
                                    <li class="mb-2">• Erro de conexão com a imobiliária</li>
                                    <li class="mb-2">• Sessão expirada</li>
                                </ul>
                                <div class="flex justify-center space-x-3">
                                    <a href="<?= url($locatario['instancia'] . '/dashboard') ?>" 
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-arrow-left mr-2"></i>
                                        Voltar ao Dashboard
                                    </a>
                                    <button onclick="location.reload()" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-sync-alt mr-2"></i>
                                        Tentar Novamente
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Finalidade da Locação -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Finalidade da Locação</h3>
                    <select name="finalidade_locacao" id="finalidade_locacao" required 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                        <option value="RESIDENCIAL" selected>Residencial</option>
                        <option value="COMERCIAL">Comercial</option>
                    </select>
                </div>
                
                <!-- Tipo de Imóvel -->
                <div id="tipo_imovel_container">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Tipo de Imóvel</h3>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="tipo_imovel" value="CASA" checked 
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-home mr-1"></i>
                                Casa
                            </span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="tipo_imovel" value="APARTAMENTO" 
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">
                                <i class="fas fa-building mr-1"></i>
                                Apartamento
                            </span>
                    </label>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="flex justify-between pt-6">
                    <a href="<?= url($locatario['instancia'] . '/dashboard') ?>" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar
                    </a>
                    <button type="submit" id="btn-continuar-etapa1"
                            class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Continuar
                    </button>
                </div>
            </form>
    </div>
    
    <?php elseif ($etapaAtual == 2): ?>
        <!-- ETAPA 2: SERVIÇO -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-cog mr-2"></i>
                Qual tipo de serviço você precisa?
            </h2>
            <p class="text-sm text-gray-500 mt-1">Selecione a categoria do serviço desejado</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/2') ?>" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                
                <!-- Categoria do Serviço -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Categoria do Serviço</h3>
                    <div class="space-y-3">
                        <?php if (!empty($categorias)): ?>
                            <?php foreach ($categorias as $categoria): ?>
                                <label class="relative block">
                                    <input type="radio" name="categoria_id" value="<?= $categoria['id'] ?>" 
                                           class="sr-only categoria-radio" data-categoria="<?= $categoria['id'] ?>">
                                    <div class="border-2 rounded-lg p-4 cursor-pointer transition-all hover:border-blue-300 categoria-card" 
                                         data-categoria="<?= $categoria['id'] ?>">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="<?= $categoria['icone'] ?? 'fas fa-cog' ?> text-xl text-gray-600 mr-3"></i>
                                                <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($categoria['nome']) ?></span>
                                            </div>
                                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full categoria-check"></div>
                                        </div>
                                        
                                        <!-- Subcategorias (aparece quando selecionado) -->
                                        <div class="mt-3 categoria-details hidden">
                                            <div class="bg-gray-50 rounded-lg p-3">
                                                <h4 class="text-sm font-medium text-gray-700 mb-3">
                                                    Tipo de Serviço
                                                    <span class="text-xs text-gray-500">(<?= count($categoria['subcategorias'] ?? []) ?> opções)</span>
                                                </h4>
                                                <div class="space-y-3">
                                                    <?php if (!empty($categoria['subcategorias'])): ?>
                                                        <?php foreach ($categoria['subcategorias'] as $subcategoria): ?>
                                                            <label class="relative block cursor-pointer">
                                                                <input type="radio" name="subcategoria_id" value="<?= $subcategoria['id'] ?>" 
                                                                       class="sr-only subcategoria-radio">
                                                                <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-300 transition-colors subcategoria-card">
                                                                    <div class="flex items-start justify-between">
                                                                        <div class="flex-1">
                                                                            <h5 class="text-sm font-medium text-gray-900 mb-1">
                                                                                <?= htmlspecialchars($subcategoria['nome']) ?>
                                                                            </h5>
                                                                            <?php if (!empty($subcategoria['descricao'])): ?>
                                                                                <p class="text-xs text-gray-600">
                                                                                    <?= htmlspecialchars($subcategoria['descricao']) ?>
                                                                                </p>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div class="ml-3">
                                                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full subcategoria-check"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <p class="text-sm text-gray-500">Nenhum tipo de serviço disponível para esta categoria.</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                    </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-cog text-3xl mb-2"></i>
                                <p>Nenhuma categoria disponível</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="flex justify-between pt-6">
                    <a href="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/1') ?>" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Continuar
                    </button>
                </div>
            </form>
    </div>
    
    <?php elseif ($etapaAtual == 3): ?>
        <!-- ETAPA 3: DESCRIÇÃO -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-edit mr-2"></i>
                Descreva o problema
            </h2>
            <p class="text-sm text-gray-500 mt-1">Forneça detalhes sobre o serviço necessário</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/3') ?>" 
                  enctype="multipart/form-data" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                
                <!-- Local da Manutenção -->
                <div>
                    <label for="local_manutencao" class="block text-sm font-medium text-gray-700 mb-2">
                        Local do imóvel onde será feito a manutenção
                    </label>
                    <input type="text" id="local_manutencao" name="local_manutencao" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                           placeholder="Ex: Fechadura do Portão da Rua"
                           value="<?= htmlspecialchars($_SESSION['nova_solicitacao']['local_manutencao'] ?? '') ?>">
                </div>
                
                <!-- Descrição do Problema -->
                <div>
                    <label for="descricao_problema" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição do Problema
                    </label>
                    <textarea id="descricao_problema" name="descricao_problema" rows="6" required
                              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm resize-none"
                              placeholder="Descreva detalhadamente o problema que precisa ser resolvido..."><?= htmlspecialchars($_SESSION['nova_solicitacao']['descricao_problema'] ?? '') ?></textarea>
                </div>
                
                <!-- Upload de Fotos -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fotos (Opcional)
                    </label>
                    <p class="text-sm text-gray-500 mb-3">Adicione fotos para ajudar a entender melhor o problema</p>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-green-400 transition-colors cursor-pointer" 
                         onclick="document.getElementById('fotos').click()">
                        <i class="fas fa-camera text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Clique para adicionar uma foto</p>
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG até 10MB</p>
                </div>
                
                    <input type="file" id="fotos" name="fotos[]" multiple accept="image/*" 
                           class="hidden" onchange="previewPhotos(this)">
                    
                    <!-- Preview das fotos -->
                    <div id="fotos-preview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 hidden">
                        <!-- Fotos serão inseridas aqui via JavaScript -->
                </div>
                </div>
                
                <!-- Navigation -->
                <div class="flex justify-between pt-6">
                    <a href="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/2') ?>" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Continuar
                    </button>
                </div>
            </form>
    </div>
    
    <?php elseif ($etapaAtual == 4): ?>
        <!-- ETAPA 4: AGENDAMENTO -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-calendar mr-2"></i>
                Quando você prefere o atendimento?
            </h2>
        </div>
        
        <div class="p-6">
            <!-- Avisos Importantes (não desaparecem mais) -->
            <div class="mb-6 space-y-3 relative z-0">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800">Condomínio</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                Se o serviço for realizado em apartamento ou condomínio, é obrigatório comunicar previamente a administração ou portaria sobre a visita técnica agendada.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800">Responsável no Local</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                É obrigatória a presença de uma pessoa maior de 18 anos no local durante todo o período de execução do serviço para acompanhar e autorizar os trabalhos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/4') ?>" class="space-y-6 relative z-10">
                <?= \App\Core\View::csrfField() ?>
                
                <!-- Instruções -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 relative z-0">
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-2">Selecione até 3 datas e horários preferenciais</p>
                        <p class="mb-2">Após sua escolha, o prestador verificará a disponibilidade. Caso algum dos horários não esteja livre, poderão ser sugeridas novas opções.</p>
                        <p>Você receberá uma notificação confirmando a data e o horário final definidos (via WhatsApp e aplicativo).</p>
                    </div>
                </div>
                
                <!-- Seleção de Data -->
                <div>
                    <label for="data_selecionada" class="block text-sm font-medium text-gray-700 mb-3">
                        Selecione uma Data
                    </label>
                    <div class="relative cursor-pointer">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <input type="date" id="data_selecionada" name="data_selecionada" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm text-gray-700 cursor-pointer transition-colors"
                               placeholder="Selecione uma data"
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                               max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                    <div class="mt-2 flex items-center text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1.5"></i>
                        <span>Atendimentos disponíveis apenas em dias úteis (segunda a sexta-feira)</span>
                    </div>
                </div>
                
                <!-- Seleção de Horário -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Selecione um Horário
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="relative">
                            <input type="radio" name="horario_selecionado" value="08:00-11:00" class="sr-only horario-radio">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                <div class="text-sm font-medium text-gray-900">08h00 às 11h00</div>
                            </div>
                        </label>
                        
                        <label class="relative">
                            <input type="radio" name="horario_selecionado" value="11:00-14:00" class="sr-only horario-radio">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                <div class="text-sm font-medium text-gray-900">11h00 às 14h00</div>
                            </div>
                        </label>
                        
                        <label class="relative">
                            <input type="radio" name="horario_selecionado" value="14:00-17:00" class="sr-only horario-radio">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                <div class="text-sm font-medium text-gray-900">14h00 às 17h00</div>
                            </div>
                        </label>
                        
                        <label class="relative">
                            <input type="radio" name="horario_selecionado" value="17:00-20:00" class="sr-only horario-radio">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                <div class="text-sm font-medium text-gray-900">17h00 às 20h00</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Horários Selecionados -->
                <div id="horarios-selecionados" class="hidden">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">
                        Horários Selecionados (<span id="contador-horarios">0</span>/3)
                    </h4>
                    <div id="lista-horarios" class="space-y-2">
                        <!-- Horários serão inseridos aqui via JavaScript -->
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="flex justify-between pt-6">
                    <a href="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/3') ?>" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar
                    </a>
                    <button type="submit" id="btn-continuar" disabled
                            class="px-6 py-3 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed transition-colors">
                        Continuar
                    </button>
                </div>
            </form>
        </div>
        
    <?php elseif ($etapaAtual == 5): ?>
        <!-- ETAPA 5: CONFIRMAÇÃO -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-check mr-2"></i>
                Confirmação da Solicitação
            </h2>
            </div>
            
        <div class="p-6">
            <!-- Aviso Responsável -->
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Responsável no Local</h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            É obrigatória a presença de uma pessoa maior de 18 anos no local durante todo o período de execução do serviço para acompanhar e autorizar os trabalhos.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Resumo da Solicitação -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Resumo da Solicitação</h3>
                
                <div class="space-y-4">
                    <!-- Endereço -->
                    <div>
                        <span class="text-sm font-medium text-gray-500">Endereço:</span>
                        <p class="text-sm text-gray-900">
                            <?php 
                            $dados = $_SESSION['nova_solicitacao'] ?? [];
                            $imovel = $locatario['imoveis'][$dados['endereco_selecionado'] ?? 0] ?? [];
                            echo htmlspecialchars($imovel['endereco'] ?? '') . ', ' . htmlspecialchars($imovel['numero'] ?? '') . ' ' . htmlspecialchars($imovel['bairro'] ?? '') . ', ' . htmlspecialchars($imovel['cidade'] ?? '') . ' - ' . htmlspecialchars($imovel['cep'] ?? '');
                            ?>
                        </p>
                    </div>
                    
                    <!-- Imobiliária -->
                    <div>
                        <span class="text-sm font-medium text-gray-500">Imobiliária:</span>
                        <p class="text-sm text-gray-900"><?= htmlspecialchars($locatario['imobiliaria_nome']) ?></p>
                    </div>
                    
                    <!-- Serviço -->
                    <div>
                        <span class="text-sm font-medium text-gray-500">Serviço:</span>
                        <p class="text-sm text-gray-900">
                            <?php
                            // Buscar nome da subcategoria
                            $subcategoriaModel = new \App\Models\Subcategoria();
                            $subcategoria = $subcategoriaModel->find($dados['subcategoria_id'] ?? 0);
                            echo htmlspecialchars($subcategoria['nome'] ?? 'Serviço selecionado');
                            ?>
                        </p>
                    </div>
                    
                    <!-- Local da Manutenção -->
                    <div>
                        <span class="text-sm font-medium text-gray-500">Local da Manutenção:</span>
                        <p class="text-sm text-gray-900"><?= htmlspecialchars($dados['local_manutencao'] ?? 'Não informado') ?></p>
                    </div>
                    
                    <!-- Descrição -->
                    <div>
                        <span class="text-sm font-medium text-gray-500">Descrição:</span>
                        <p class="text-sm text-gray-900"><?= htmlspecialchars($dados['descricao_problema'] ?? 'Não informada') ?></p>
                    </div>
                    
                    <!-- Horários Preferenciais -->
                    <div>
                        <span class="text-sm font-medium text-gray-500">Horários Preferenciais:</span>
                        <?php
                        $horarios = $dados['horarios_preferenciais'] ?? [];
                        if (!empty($horarios) && is_array($horarios)):
                        ?>
                            <div class="mt-2 space-y-2">
                                <?php foreach ($horarios as $index => $horario): ?>
                                    <div class="flex items-center bg-green-50 border border-green-200 rounded-lg p-3">
                                        <i class="fas fa-clock text-green-600 mr-3"></i>
                                        <div>
                                            <span class="text-sm font-medium text-green-800">Opção <?= $index + 1 ?>:</span>
                                            <span class="text-sm text-green-700 ml-2">
                                                <?php
                                                // Formatar horário: 2025-10-29 08:00:00 → 29/10/2025 às 08:00
                                                if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/', $horario, $matches)) {
                                                    echo $matches[3] . '/' . $matches[2] . '/' . $matches[1] . ' às ' . $matches[4] . ':' . $matches[5];
                                                } else {
                                                    echo htmlspecialchars($horario);
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-900 mt-1">Não informado</p>
                        <?php endif; ?>
            </div>
        </div>
    </div>
    
            <!-- Termo de Aceite -->
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/5') ?>" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <label class="flex items-start">
                        <input type="checkbox" name="termo_aceite" value="1" required
                               class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <span class="ml-3 text-sm text-gray-700">
                            Li e aceito todas as informações e avisos acima. Confirmo que estarei presente no local durante o atendimento e que comunicarei a administração/portaria quando necessário.
                        </span>
                    </label>
                </div>
                
                <!-- Navigation -->
                <div class="flex justify-between pt-6">
                    <a href="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/4') ?>" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar
                    </a>
                    <button type="submit" id="btn-finalizar"
                            class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Finalizar Solicitação
                    </button>
                </div>
            </form>
        </div>
        
    <?php else: ?>
        <!-- ETAPA INVÁLIDA -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Etapa Inválida
            </h2>
        </div>
        
        <div class="p-6 text-center">
            <div class="py-12">
                <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Etapa não encontrada</h3>
                <p class="text-gray-500 mb-6">A etapa solicitada não existe.</p>
                <a href="<?= url($locatario['instancia'] . '/nova-solicitacao') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Voltar ao Início
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
        <div class="mb-4">
            <!-- Spinner animado -->
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-green-600"></div>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Criando sua solicitação...</h3>
        <p class="text-gray-600 mb-4">Por favor, aguarde enquanto processamos seus dados.</p>
        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
            <div class="bg-green-600 h-2.5 rounded-full animate-pulse" style="width: 70%"></div>
        </div>
        <p class="text-sm text-gray-500 mt-4">Isso pode levar alguns segundos...</p>
    </div>
</div>

<style>
/* Melhorar aparência do input de data */
input[type="date"] {
    position: relative;
    cursor: pointer;
    font-family: inherit;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    position: absolute;
    right: 12px;
    width: 20px;
    height: 20px;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

input[type="date"]:focus {
    outline: none;
}

/* Estilo quando a data está vazia (placeholder visual) */
input[type="date"]:not(:focus)::-webkit-datetime-edit {
    color: transparent;
}

input[type="date"]:not(:focus)::before {
    content: attr(placeholder);
    color: #9ca3af;
    margin-right: 8px;
}

input[type="date"]:valid:not(:focus)::before,
input[type="date"]:focus::before {
    content: none;
}

input[type="date"]:valid:not(:focus)::-webkit-datetime-edit {
    color: #374151;
}
</style>

<script>
// JavaScript para interação básica
document.addEventListener('DOMContentLoaded', function() {
    console.log('✓ Sistema carregado');
    
    // === ETAPA 4: Abrir calendário ao clicar no campo de data ===
    const dataInput = document.getElementById('data_selecionada');
    if (dataInput) {
        // Remover readonly para permitir a seleção
        dataInput.removeAttribute('readonly');
        
        // Abrir calendário ao clicar no campo ou no container
        const abrirCalendario = function() {
            try {
                if (dataInput.showPicker) {
                    dataInput.showPicker();
                } else {
                    // Fallback para navegadores mais antigos
                    dataInput.focus();
                    dataInput.click();
                }
            } catch (e) {
                console.log('Calendário será aberto pelo navegador');
            }
        };
        
        // Adicionar evento de clique
        dataInput.addEventListener('click', abrirCalendario);
        
        // Também no container pai (div relativo)
        const containerData = dataInput.closest('.relative');
        if (containerData) {
            containerData.addEventListener('click', function(e) {
                if (e.target !== dataInput) {
                    abrirCalendario();
                }
            });
        }
        
        // Validação: bloquear seleção de fins de semana
        dataInput.addEventListener('change', function() {
            if (!this.value) return;
            
            const dataSelecionada = new Date(this.value + 'T12:00:00');
            const diaDaSemana = dataSelecionada.getDay(); // 0 = Domingo, 6 = Sábado
            
            if (diaDaSemana === 0 || diaDaSemana === 6) {
                const nomeDia = diaDaSemana === 0 ? 'domingo' : 'sábado';
                alert('⚠️ Atendimentos não são realizados aos fins de semana.\n\nA data selecionada é um ' + nomeDia + '.\nPor favor, selecione um dia útil (segunda a sexta-feira).');
                this.value = '';
            }
        });
    }
    
    // === ETAPA 5: Loading ao finalizar ===
    const btnFinalizar = document.getElementById('btn-finalizar');
    if (btnFinalizar) {
        const formFinalizar = btnFinalizar.closest('form');
        if (formFinalizar) {
            formFinalizar.addEventListener('submit', function(e) {
                // Verificar se o termo foi aceito
                const termoAceite = formFinalizar.querySelector('input[name="termo_aceite"]');
                if (!termoAceite || !termoAceite.checked) {
                    e.preventDefault();
                    alert('Por favor, aceite os termos para continuar.');
                    return;
                }
                
                // Mostrar loading
                const loadingOverlay = document.getElementById('loading-overlay');
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('hidden');
                }
                
                // Desabilitar botão para evitar cliques duplos
                btnFinalizar.disabled = true;
                btnFinalizar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';
            });
        }
    }
    
    // DEBUG: Monitorar submit do formulário da etapa 1
    const btnContinuarEtapa1 = document.getElementById('btn-continuar-etapa1');
    if (btnContinuarEtapa1) {
        const form = btnContinuarEtapa1.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('📤 Formulário sendo submetido...');
                console.log('Action:', form.action);
                console.log('Method:', form.method);
                
                const formData = new FormData(form);
                console.log('Dados do formulário:');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value}`);
                }
                
                // Não prevenir o submit, deixar funcionar normalmente
                console.log('✓ Permitindo submit do formulário');
            });
            
            btnContinuarEtapa1.addEventListener('click', function() {
                console.log('🖱️ Botão "Continuar" clicado!');
            });
        }
    }
    
    // === ETAPA 2: Seleção de Categoria ===
    const categoriaRadios = document.querySelectorAll('.categoria-radio');
    const categoriaCards = document.querySelectorAll('.categoria-card');
    
    categoriaRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const categoriaId = this.value;
            
            // Remover seleção de todos os cards
            categoriaCards.forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
                card.classList.add('border-gray-200');
                
                const check = card.querySelector('.categoria-check');
                if (check) {
                    check.classList.remove('bg-blue-500', 'border-blue-500');
                    check.classList.add('border-gray-300');
                }
                
                const details = card.querySelector('.categoria-details');
                if (details) {
                    details.classList.add('hidden');
                }
            });
            
            // Selecionar o card atual (específico para .categoria-card)
            const currentCard = document.querySelector(`.categoria-card[data-categoria="${categoriaId}"]`);
            if (currentCard) {
                currentCard.classList.remove('border-gray-200');
                currentCard.classList.add('border-blue-500', 'bg-blue-50');
                
                const check = currentCard.querySelector('.categoria-check');
                if (check) {
                    check.classList.remove('border-gray-300');
                    check.classList.add('bg-blue-500', 'border-blue-500');
                }
                
                const details = currentCard.querySelector('.categoria-details');
                if (details) {
                    details.classList.remove('hidden');
                }
            }
        });
    });
    
    // Click no card também seleciona o radio
    categoriaCards.forEach(card => {
        card.addEventListener('click', function() {
            const categoriaId = this.getAttribute('data-categoria');
            const radio = document.querySelector(`.categoria-radio[value="${categoriaId}"]`);
            if (radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            }
        });
    });
    
    // === SUBCATEGORIAS: Seleção visual ===
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('subcategoria-radio')) {
            const allSubCards = document.querySelectorAll('.subcategoria-card');
            const allSubChecks = document.querySelectorAll('.subcategoria-check');
            
            // Remover seleção de todos
            allSubCards.forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
                card.classList.add('border-gray-200');
            });
            allSubChecks.forEach(check => {
                check.classList.remove('bg-blue-500', 'border-blue-500');
                check.classList.add('border-gray-300');
            });
            
            // Adicionar seleção ao card pai do radio selecionado
            const selectedCard = e.target.closest('label').querySelector('.subcategoria-card');
            const selectedCheck = e.target.closest('label').querySelector('.subcategoria-check');
            
            if (selectedCard) {
                selectedCard.classList.remove('border-gray-200');
                selectedCard.classList.add('border-blue-500', 'bg-blue-50');
            }
            if (selectedCheck) {
                selectedCheck.classList.remove('border-gray-300');
                selectedCheck.classList.add('bg-blue-500', 'border-blue-500');
            }
        }
    });
    
    // Click no card da subcategoria seleciona o radio (COM stopPropagation para não conflitar)
    document.addEventListener('click', function(e) {
        const subCard = e.target.closest('.subcategoria-card');
        if (subCard) {
            // Garantir que não é um clique no card de categoria
            const isCategoriaCard = e.target.closest('.categoria-card');
            if (!isCategoriaCard) {
                e.stopPropagation();
                const label = subCard.closest('label');
                const radio = label ? label.querySelector('.subcategoria-radio') : null;
                if (radio) {
                    console.log('🔵 Subcategoria clicada:', radio.value);
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }
    }, true); // Captura na fase de captura
    
    // Preview de fotos
    window.previewPhotos = function(input) {
        const preview = document.getElementById('fotos-preview');
        const files = input.files;
        
        if (files.length > 0) {
            preview.classList.remove('hidden');
            preview.innerHTML = '';
            
            Array.from(files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative';
                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg">
                            <button type="button" onclick="removePhoto(${index})" 
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                ×
                            </button>
                        `;
                        preview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
        } else {
            preview.classList.add('hidden');
        }
    };
    
    // Remover foto
    window.removePhoto = function(index) {
        const input = document.getElementById('fotos');
        const dt = new DataTransfer();
        
        Array.from(input.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        input.files = dt.files;
        previewPhotos(input);
    };
    
    // Sistema de agendamento
    const horarioRadios = document.querySelectorAll('.horario-radio');
    const horarioCards = document.querySelectorAll('.horario-card');
    const horariosSelecionados = document.getElementById('horarios-selecionados');
    const listaHorarios = document.getElementById('lista-horarios');
    const contadorHorarios = document.getElementById('contador-horarios');
    const btnContinuar = document.getElementById('btn-continuar');
    
    let horariosEscolhidos = [];
    
    horarioRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const data = document.getElementById('data_selecionada').value;
            const horario = this.value;
            
            if (data && horario) {
                const horarioCompleto = `${formatarData(data)} - ${horario}`;
                
                if (!horariosEscolhidos.includes(horarioCompleto) && horariosEscolhidos.length < 3) {
                    horariosEscolhidos.push(horarioCompleto);
                    atualizarListaHorarios();
                }
            }
        });
    });

    // Click no card de horário também seleciona o radio
    horarioCards.forEach(card => {
        card.addEventListener('click', function() {
            // O radio está no label pai, não dentro do card
            const label = this.closest('label');
            const radio = label ? label.querySelector('.horario-radio') : null;
            if (radio) {
                console.log('⏰ Horário clicado:', radio.value);
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            }
        });
    });
    
    function atualizarListaHorarios() {
        if (horariosEscolhidos.length > 0) {
            horariosSelecionados.classList.remove('hidden');
            contadorHorarios.textContent = horariosEscolhidos.length;
            
            listaHorarios.innerHTML = '';
            horariosEscolhidos.forEach((horario, index) => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-3';
                div.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-clock text-green-600 mr-2"></i>
                        <span class="text-sm text-green-800">${horario}</span>
                    </div>
                    <button type="button" onclick="removerHorario(${index})" 
                            class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                listaHorarios.appendChild(div);
            });
            
            // Habilitar botão continuar se tiver pelo menos 1 horário
            if (horariosEscolhidos.length > 0) {
                btnContinuar.disabled = false;
                btnContinuar.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btnContinuar.classList.add('bg-green-600', 'hover:bg-green-700');
            }
        } else {
            horariosSelecionados.classList.add('hidden');
            btnContinuar.disabled = true;
            btnContinuar.classList.add('bg-gray-400', 'cursor-not-allowed');
            btnContinuar.classList.remove('bg-green-600', 'hover:bg-green-700');
        }
    }
    
    window.removerHorario = function(index) {
        horariosEscolhidos.splice(index, 1);
        atualizarListaHorarios();
    };
    
    function formatarData(data) {
        const [ano, mes, dia] = data.split('-');
        return `${dia}/${mes}/${ano}`;
    }
    
    // Salvar horários no formulário antes de enviar
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Converter: "29/10/2025 - 08:00-11:00" → "2025-10-29 08:00:00"
            const horariosFormatados = horariosEscolhidos.map(horario => {
                const [dataStr, faixaHorario] = horario.split(' - ');
                const [dia, mes, ano] = dataStr.split('/');
                const horarioInicial = faixaHorario.split('-')[0];
                return `${ano}-${mes}-${dia} ${horarioInicial}:00`;
            });
            
            // Enviar como JSON
            const inputHorarios = document.createElement('input');
            inputHorarios.type = 'hidden';
            inputHorarios.name = 'horarios_opcoes';
            inputHorarios.value = JSON.stringify(horariosFormatados);
            form.appendChild(inputHorarios);
        });
    }
    
    // Controlar visibilidade do campo "Tipo de Imóvel" baseado em "Finalidade da Locação"
    const finalidadeSelect = document.getElementById('finalidade_locacao');
    const tipoImovelContainer = document.getElementById('tipo_imovel_container');
    
    if (finalidadeSelect && tipoImovelContainer) {
        function toggleTipoImovel() {
            if (finalidadeSelect.value === 'COMERCIAL') {
                tipoImovelContainer.style.display = 'none';
                // Limpar seleção dos radio buttons
                const radioButtons = tipoImovelContainer.querySelectorAll('input[type="radio"]');
                radioButtons.forEach(radio => {
                    radio.checked = false;
                    radio.removeAttribute('required');
                });
            } else {
                tipoImovelContainer.style.display = 'block';
                // Restaurar seleção padrão (Casa)
                const radioCasa = tipoImovelContainer.querySelector('input[value="CASA"]');
                if (radioCasa) {
                    radioCasa.checked = true;
                }
            }
        }
        
        // Executar na carga da página
        toggleTipoImovel();
        
        // Executar quando mudar a seleção
        finalidadeSelect.addEventListener('change', toggleTipoImovel);
    }
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/locatario.php';
?>