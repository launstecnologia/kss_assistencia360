<?php
/**
 * View: Nova Solicitação do Locatário - Sistema de Steps
 */
$title = 'Nova Solicitação - Assistência 360°';
$currentPage = 'locatario-nova-solicitacao';
ob_start();

// Definir etapa atual (vem da URL ou sessão)
$etapaAtual = $_GET['etapa'] ?? $_SESSION['nova_solicitacao']['etapa'] ?? 1;
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
    <div class="flex items-center justify-between">
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
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
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
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/2') ?>" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                
                <!-- Endereços Salvos -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Endereços Salvos</h3>
                    <div class="space-y-3">
                        <?php if (!empty($locatario['imoveis'])): ?>
                            <?php foreach ($locatario['imoveis'] as $index => $imovel): ?>
                                <label class="relative block">
                                    <input type="radio" name="endereco_selecionado" value="<?= $index ?>" 
                                           class="sr-only" <?= $index == 0 ? 'checked' : '' ?>>
                                    <div class="border-2 rounded-lg p-4 cursor-pointer transition-all hover:border-green-300 <?= $index == 0 ? 'border-green-500 bg-green-50' : 'border-gray-200' ?>">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-3">
                                                        Imóvel Contratual
                                                    </span>
                                                </div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($imovel['endereco']) ?>, <?= htmlspecialchars($imovel['numero']) ?>
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    <?= htmlspecialchars($imovel['bairro']) ?>, <?= htmlspecialchars($imovel['cidade']) ?> - <?= htmlspecialchars($imovel['uf']) ?>
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    <?= htmlspecialchars($imovel['cep']) ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xs text-gray-500 mb-1">
                                                    Contrato: <?= !empty($imovel['contratos']) ? htmlspecialchars($imovel['contratos'][0]['CtrCod'] . '-' . $imovel['contratos'][0]['CtrDV']) : 'N/A' ?>
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    Cód: <?= htmlspecialchars($imovel['codigo']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Checkmark -->
                                    <div class="absolute top-4 right-4 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                    </label>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-home text-3xl mb-2"></i>
                                <p>Nenhum endereço encontrado</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Finalidade da Locação -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Finalidade da Locação</h3>
                    <select name="finalidade_locacao" required 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="RESIDENCIAL" selected>Residencial</option>
                        <option value="COMERCIAL">Comercial</option>
                    </select>
                </div>
                
                <!-- Tipo de Imóvel -->
                <div>
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
                    <button type="submit" 
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
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/3') ?>" class="space-y-6">
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
                                                <h4 class="text-sm font-medium text-gray-700 mb-2">Tipo de Serviço</h4>
                                                <div class="space-y-2">
                                                    <?php if (!empty($categoria['subcategorias'])): ?>
                                                        <?php foreach ($categoria['subcategorias'] as $subcategoria): ?>
                                                            <label class="flex items-center">
                                                                <input type="radio" name="subcategoria_id" value="<?= $subcategoria['id'] ?>" 
                                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                                                <span class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($subcategoria['nome']) ?></span>
                                                            </label>
                                                        <?php endforeach; ?>
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
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/4') ?>" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                
                <!-- Local da Manutenção -->
                <div>
                    <label for="local_manutencao" class="block text-sm font-medium text-gray-700 mb-2">
                        Local da Manutenção
                    </label>
                    <input type="text" id="local_manutencao" name="local_manutencao" 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="Ex: Banheiro Social, Cozinha, Sala..."
                           value="<?= htmlspecialchars($_SESSION['nova_solicitacao']['local_manutencao'] ?? '') ?>">
                </div>
                
                <!-- Descrição do Problema -->
                <div>
                    <label for="descricao_problema" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição do Problema
                    </label>
                    <textarea id="descricao_problema" name="descricao_problema" rows="6" required
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
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
            <!-- Avisos Importantes -->
            <div class="mb-6 space-y-3">
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
            
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/5') ?>" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                
                <!-- Instruções -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-2">Selecione até 3 datas e horários preferenciais</p>
                        <p class="mb-2">Após sua escolha, o prestador verificará a disponibilidade. Caso algum dos horários não esteja livre, poderão ser sugeridas novas opções.</p>
                        <p>Você receberá uma notificação confirmando a data e o horário final definidos (via WhatsApp e aplicativo).</p>
                    </div>
                </div>
                
                <!-- Seleção de Data -->
                <div>
                    <label for="data_selecionada" class="block text-sm font-medium text-gray-700 mb-2">
                        Selecione uma Data
                    </label>
                    <input type="date" id="data_selecionada" name="data_selecionada" 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
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
                        <p class="text-sm text-gray-900">
                            <?php
                            $horarios = $dados['horarios_preferenciais'] ?? [];
                            if (!empty($horarios)) {
                                foreach ($horarios as $horario) {
                                    echo htmlspecialchars($horario) . '<br>';
                                }
                            } else {
                                echo 'Não informado';
                            }
                            ?>
                        </p>
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
        <button type="submit" 
                class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Finalizar
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

<script>
// JavaScript para interação dos cards de categoria
document.addEventListener('DOMContentLoaded', function() {
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
                check.classList.remove('bg-blue-500', 'border-blue-500');
                check.classList.add('border-gray-300');
                
                const details = card.querySelector('.categoria-details');
                details.classList.add('hidden');
            });
            
            // Selecionar o card atual
            const currentCard = document.querySelector(`[data-categoria="${categoriaId}"]`);
            if (currentCard) {
                currentCard.classList.remove('border-gray-200');
                currentCard.classList.add('border-blue-500', 'bg-blue-50');
                
                const check = currentCard.querySelector('.categoria-check');
                check.classList.remove('border-gray-300');
                check.classList.add('bg-blue-500', 'border-blue-500');
                
                const details = currentCard.querySelector('.categoria-details');
                details.classList.remove('hidden');
            }
        });
    });
    
    // Click no card também seleciona o radio
    categoriaCards.forEach(card => {
        card.addEventListener('click', function() {
            const categoriaId = this.getAttribute('data-categoria');
            const radio = document.querySelector(`input[value="${categoriaId}"]`);
            if (radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            }
        });
    });
    
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
            const radio = this.querySelector('.horario-radio');
            if (radio) {
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
            // Adicionar campos hidden com os horários selecionados
            horariosEscolhidos.forEach((horario, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'horarios_preferenciais[]';
                input.value = horario;
                form.appendChild(input);
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/locatario.php';
?>