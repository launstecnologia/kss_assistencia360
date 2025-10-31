<?php
/**
 * View: Solicitação Manual (sem autenticação)
 * Fluxo de 5 etapas para usuários não logados
 */
$title = 'Solicitação de Assistência - ' . ($imobiliaria['nome'] ?? 'Assistência 360°');
$currentPage = 'solicitacao-manual';
ob_start();

// Definir etapa atual
$etapaAtual = $etapa ?? 1;
$etapaAtual = (int)$etapaAtual;

// Definir steps
$steps = [
    1 => ['nome' => 'Dados Pessoais', 'icone' => 'fas fa-user'],
    2 => ['nome' => 'Endereço', 'icone' => 'fas fa-map-marker-alt'],
    3 => ['nome' => 'Serviço', 'icone' => 'fas fa-cog'],
    4 => ['nome' => 'Fotos e Horários', 'icone' => 'fas fa-images'],
    5 => ['nome' => 'Confirmação', 'icone' => 'fas fa-check']
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <?= htmlspecialchars($imobiliaria['nome'] ?? 'Assistência 360°') ?>
                </h1>
                <p class="text-gray-600">Solicitação de Assistência</p>
            </div>
            
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <?php foreach ($steps as $numero => $step): ?>
                        <div class="flex items-center <?= $numero < count($steps) ? 'flex-1' : '' ?>">
                            <!-- Step Circle -->
                            <div class="flex flex-col items-center">
                                <div class="flex items-center justify-center w-12 h-12 rounded-full border-2 <?= $numero <= $etapaAtual ? 'bg-green-600 border-green-600 text-white' : 'border-gray-300 text-gray-400 bg-white' ?>">
                                    <?php if ($numero < $etapaAtual): ?>
                                        <i class="fas fa-check"></i>
                                    <?php else: ?>
                                        <i class="<?= $step['icone'] ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs mt-2 font-medium <?= $numero <= $etapaAtual ? 'text-green-600' : 'text-gray-400' ?>">
                                    <?= $step['nome'] ?>
                                </p>
                            </div>
                            
                            <!-- Connector Line -->
                            <?php if ($numero < count($steps)): ?>
                                <div class="flex-1 h-0.5 mx-2 <?= $numero < $etapaAtual ? 'bg-green-600' : 'bg-gray-300' ?>"></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Messages -->
            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>
            
            <!-- Step Content -->
            <div class="bg-white rounded-lg shadow-md">
                
                <?php if ($etapaAtual == 1): ?>
                    <!-- ETAPA 1: DADOS PESSOAIS -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-user mr-2 text-green-600"></i>
                            Dados Pessoais
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Por favor, informe seus dados de contato</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="<?= url($instancia . '/solicitacao-manual') ?>" class="space-y-6">
                            <?= \App\Core\View::csrfField() ?>
                            
                            <!-- Nome Completo -->
                            <div>
                                <label for="nome_completo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nome Completo <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="nome_completo" name="nome_completo" required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="Digite seu nome completo"
                                       value="<?= htmlspecialchars($dados['nome_completo'] ?? '') ?>">
                            </div>
                            
                            <!-- CPF -->
                            <div>
                                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                                    CPF <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="cpf" name="cpf" required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="000.000.000-00"
                                       maxlength="14"
                                       value="<?= htmlspecialchars($dados['cpf'] ?? '') ?>">
                            </div>
                            
                            <!-- WhatsApp -->
                            <div>
                                <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-2">
                                    WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="whatsapp" name="whatsapp" required
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="(00) 00000-0000"
                                       maxlength="15"
                                       value="<?= htmlspecialchars($dados['whatsapp'] ?? '') ?>">
                                <p class="text-xs text-gray-500 mt-1">Informe um WhatsApp válido para contato</p>
                            </div>
                            
                            <!-- Navigation -->
                            <div class="flex justify-between pt-6">
                                <a href="<?= url($instancia) ?>" 
                                   class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    Cancelar
                                </a>
                                <button type="submit"
                                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    Continuar <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                <?php elseif ($etapaAtual == 2): ?>
                    <!-- ETAPA 2: ENDEREÇO -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                            Endereço do Imóvel
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Informe o endereço onde o serviço será realizado</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="<?= url($instancia . '/solicitacao-manual/etapa/2') ?>" class="space-y-6">
                            <?= \App\Core\View::csrfField() ?>
                            
                            <!-- Tipo de Imóvel -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Tipo de Imóvel <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative">
                                        <input type="radio" name="tipo_imovel" value="RESIDENCIAL" required 
                                               class="sr-only tipo-imovel-radio"
                                               <?= ($dados['tipo_imovel'] ?? '') === 'RESIDENCIAL' ? 'checked' : '' ?>>
                                        <div class="border-2 rounded-lg p-4 cursor-pointer text-center transition-all tipo-imovel-card"
                                             data-tipo="RESIDENCIAL">
                                            <i class="fas fa-home text-2xl mb-2"></i>
                                            <p class="font-medium">Residencial</p>
                                        </div>
                                    </label>
                                    <label class="relative">
                                        <input type="radio" name="tipo_imovel" value="COMERCIAL" required 
                                               class="sr-only tipo-imovel-radio"
                                               <?= ($dados['tipo_imovel'] ?? '') === 'COMERCIAL' ? 'checked' : '' ?>>
                                        <div class="border-2 rounded-lg p-4 cursor-pointer text-center transition-all tipo-imovel-card"
                                             data-tipo="COMERCIAL">
                                            <i class="fas fa-building text-2xl mb-2"></i>
                                            <p class="font-medium">Comercial</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Subtipo (condicional para RESIDENCIAL) -->
                            <div id="subtipo-container" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Subtipo do Imóvel
                                </label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative">
                                        <input type="radio" name="subtipo_imovel" value="CASA" 
                                               class="sr-only subtipo-imovel-radio"
                                               <?= ($dados['subtipo_imovel'] ?? '') === 'CASA' ? 'checked' : '' ?>>
                                        <div class="border-2 rounded-lg p-4 cursor-pointer text-center transition-all subtipo-imovel-card">
                                            <i class="fas fa-home text-xl mb-2"></i>
                                            <p class="font-medium">Casa</p>
                                        </div>
                                    </label>
                                    <label class="relative">
                                        <input type="radio" name="subtipo_imovel" value="APARTAMENTO" 
                                               class="sr-only subtipo-imovel-radio"
                                               <?= ($dados['subtipo_imovel'] ?? '') === 'APARTAMENTO' ? 'checked' : '' ?>>
                                        <div class="border-2 rounded-lg p-4 cursor-pointer text-center transition-all subtipo-imovel-card">
                                            <i class="fas fa-building text-xl mb-2"></i>
                                            <p class="font-medium">Apartamento</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- CEP -->
                            <div>
                                <label for="cep" class="block text-sm font-medium text-gray-700 mb-2">
                                    CEP <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <input type="text" id="cep" name="cep" required
                                           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           placeholder="00000-000"
                                           maxlength="9"
                                           value="<?= htmlspecialchars($dados['cep'] ?? '') ?>">
                                    <button type="button" id="btn-buscar-cep"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-search mr-2"></i>Buscar
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Informe o CEP para preenchimento automático</p>
                            </div>
                            
                            <!-- Endereço -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label for="endereco" class="block text-sm font-medium text-gray-700 mb-2">
                                        Endereço <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="endereco" name="endereco" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           placeholder="Rua, Avenida..."
                                           value="<?= htmlspecialchars($dados['endereco'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                                        Número <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="numero" name="numero" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           placeholder="Nº"
                                           value="<?= htmlspecialchars($dados['numero'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <!-- Complemento -->
                            <div>
                                <label for="complemento" class="block text-sm font-medium text-gray-700 mb-2">
                                    Complemento
                                </label>
                                <input type="text" id="complemento" name="complemento"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="Apto, Bloco, Sala..."
                                       value="<?= htmlspecialchars($dados['complemento'] ?? '') ?>">
                            </div>
                            
                            <!-- Bairro, Cidade, Estado -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="bairro" class="block text-sm font-medium text-gray-700 mb-2">
                                        Bairro <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="bairro" name="bairro" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           value="<?= htmlspecialchars($dados['bairro'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="cidade" class="block text-sm font-medium text-gray-700 mb-2">
                                        Cidade <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="cidade" name="cidade" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           value="<?= htmlspecialchars($dados['cidade'] ?? '') ?>">
                                </div>
                                <div>
                                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <select id="estado" name="estado" required
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                        <option value="">Selecione</option>
                                        <?php
                                        $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                        foreach ($estados as $uf): ?>
                                            <option value="<?= $uf ?>" <?= ($dados['estado'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Navigation -->
                            <div class="flex justify-between pt-6">
                                <a href="<?= url($instancia . '/solicitacao-manual') ?>" 
                                   class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                                </a>
                                <button type="submit"
                                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    Continuar <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                <?php elseif ($etapaAtual == 3): ?>
                    <!-- ETAPA 3: SERVIÇO -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-cog mr-2 text-green-600"></i>
                            Serviço Necessário
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Selecione o tipo de serviço que você precisa</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="<?= url($instancia . '/solicitacao-manual/etapa/3') ?>" class="space-y-6">
                            <?= \App\Core\View::csrfField() ?>
                            
                            <!-- Categorias -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 mb-3">Categoria do Serviço</h3>
                                <div class="space-y-3">
                                    <?php if (!empty($categorias)): ?>
                                        <?php 
                                        // Filtrar categorias pelo tipo de imóvel
                                        $tipoImovel = $dados['tipo_imovel'] ?? 'RESIDENCIAL';
                                        $categoriasFiltradas = array_filter($categorias, function($cat) use ($tipoImovel) {
                                            return empty($cat['tipo_assistencia']) || 
                                                   $cat['tipo_assistencia'] === $tipoImovel || 
                                                   $cat['tipo_assistencia'] === 'AMBOS';
                                        });
                                        ?>
                                        <?php foreach ($categoriasFiltradas as $categoria): ?>
                                            <label class="relative block">
                                                <input type="radio" name="categoria_id" value="<?= $categoria['id'] ?>" 
                                                       class="sr-only categoria-radio" data-categoria="<?= $categoria['id'] ?>"
                                                       <?= ($dados['categoria_id'] ?? '') == $categoria['id'] ? 'checked' : '' ?>>
                                                <div class="border-2 rounded-lg p-4 cursor-pointer transition-all categoria-card" 
                                                     data-categoria="<?= $categoria['id'] ?>">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center">
                                                            <i class="<?= $categoria['icone'] ?? 'fas fa-cog' ?> text-xl text-gray-600 mr-3"></i>
                                                            <span class="font-medium text-gray-900"><?= htmlspecialchars($categoria['nome']) ?></span>
                                                        </div>
                                                        <div class="w-6 h-6 border-2 border-gray-300 rounded-full categoria-check"></div>
                                                    </div>
                                                    
                                                    <!-- Subcategorias -->
                                                    <div class="mt-3 categoria-details hidden">
                                                        <div class="bg-gray-50 rounded-lg p-3">
                                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Tipo de Serviço</h4>
                                                            <div class="space-y-2">
                                                                <?php if (!empty($categoria['subcategorias'])): ?>
                                                                    <?php foreach ($categoria['subcategorias'] as $subcategoria): ?>
                                                                        <label class="relative block cursor-pointer">
                                                                            <input type="radio" name="subcategoria_id" value="<?= $subcategoria['id'] ?>" 
                                                                                   class="sr-only subcategoria-radio"
                                                                                   <?= ($dados['subcategoria_id'] ?? '') == $subcategoria['id'] ? 'checked' : '' ?>>
                                                                            <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-300 transition-colors subcategoria-card">
                                                                                <div class="flex items-start justify-between">
                                                                                    <div class="flex-1">
                                                                                        <h5 class="text-sm font-medium text-gray-900"><?= htmlspecialchars($subcategoria['nome']) ?></h5>
                                                                                        <?php if (!empty($subcategoria['descricao'])): ?>
                                                                                            <p class="text-xs text-gray-600 mt-1"><?= htmlspecialchars($subcategoria['descricao']) ?></p>
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
                                                                    <p class="text-sm text-gray-500">Nenhum serviço disponível.</p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-center py-8 text-gray-500">Nenhuma categoria disponível</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Descrição do Problema -->
                            <div>
                                <label for="descricao_problema" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrição do Problema <span class="text-red-500">*</span>
                                </label>
                                <textarea id="descricao_problema" name="descricao_problema" rows="6" required
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                          placeholder="Descreva detalhadamente o problema que precisa ser resolvido..."><?= htmlspecialchars($dados['descricao_problema'] ?? '') ?></textarea>
                            </div>
                            
                            <!-- Navigation -->
                            <div class="flex justify-between pt-6">
                                <a href="<?= url($instancia . '/solicitacao-manual/etapa/2') ?>" 
                                   class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                                </a>
                                <button type="submit"
                                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    Continuar <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                <?php elseif ($etapaAtual == 4): ?>
                    <!-- ETAPA 4: FOTOS E HORÁRIOS -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-images mr-2 text-green-600"></i>
                            Fotos e Horários
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Adicione fotos (opcional) e selecione horários de sua preferência</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="<?= url($instancia . '/solicitacao-manual/etapa/4') ?>" enctype="multipart/form-data" class="space-y-6">
                            <?= \App\Core\View::csrfField() ?>
                            
                            <!-- Upload de Fotos -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fotos do Problema (Opcional)
                                </label>
                                <p class="text-sm text-gray-500 mb-3">Adicione até 5 fotos (máx. 5MB cada)</p>
                                
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-green-400 transition-colors cursor-pointer" 
                                     onclick="document.getElementById('fotos').click()">
                                    <i class="fas fa-camera text-4xl text-gray-400 mb-3"></i>
                                    <p class="text-sm text-gray-600 font-medium">Clique para adicionar fotos</p>
                                    <p class="text-xs text-gray-400 mt-1">PNG, JPG até 5MB por arquivo</p>
                                </div>
                                
                                <input type="file" id="fotos" name="fotos[]" multiple accept="image/*" 
                                       class="hidden" onchange="previewPhotos(this)">
                                
                                <!-- Preview -->
                                <div id="fotos-preview" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4 hidden"></div>
                            </div>
                            
                            <!-- Horários Preferenciais -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Horários Preferenciais <span class="text-red-500">*</span>
                                </label>
                                <p class="text-sm text-gray-500 mb-3">Selecione até 3 opções de data e horário</p>
                                
                                <!-- Data -->
                                <div class="mb-4">
                                    <label for="data_selecionada" class="block text-sm font-medium text-gray-700 mb-2">
                                        Data
                                    </label>
                                    <input type="date" id="data_selecionada" 
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                           max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                </div>
                                
                                <!-- Horário -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Horário
                                    </label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <label class="relative">
                                            <input type="radio" name="horario_temp" value="08:00-11:00" class="sr-only horario-radio">
                                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                                <div class="text-sm font-medium text-gray-900">08h - 11h</div>
                                            </div>
                                        </label>
                                        <label class="relative">
                                            <input type="radio" name="horario_temp" value="11:00-14:00" class="sr-only horario-radio">
                                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                                <div class="text-sm font-medium text-gray-900">11h - 14h</div>
                                            </div>
                                        </label>
                                        <label class="relative">
                                            <input type="radio" name="horario_temp" value="14:00-17:00" class="sr-only horario-radio">
                                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                                <div class="text-sm font-medium text-gray-900">14h - 17h</div>
                                            </div>
                                        </label>
                                        <label class="relative">
                                            <input type="radio" name="horario_temp" value="17:00-20:00" class="sr-only horario-radio">
                                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                                <div class="text-sm font-medium text-gray-900">17h - 20h</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Horários Selecionados -->
                                <div id="horarios-selecionados" class="hidden">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">
                                        Horários Selecionados (<span id="contador-horarios">0</span>/3)
                                    </h4>
                                    <div id="lista-horarios" class="space-y-2"></div>
                                </div>
                            </div>
                            
                            <!-- Navigation -->
                            <div class="flex justify-between pt-6">
                                <a href="<?= url($instancia . '/solicitacao-manual/etapa/3') ?>" 
                                   class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                                </a>
                                <button type="submit" id="btn-continuar" disabled
                                        class="px-6 py-3 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed transition-colors">
                                    Continuar <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                <?php elseif ($etapaAtual == 5): ?>
                    <!-- ETAPA 5: CONFIRMAÇÃO -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-check mr-2 text-green-600"></i>
                            Confirmação
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Revise os dados e confirme sua solicitação</p>
                    </div>
                    
                    <div class="p-6">
                        <!-- Resumo -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumo da Solicitação</h3>
                            
                            <div class="space-y-4">
                                <!-- Dados Pessoais -->
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Nome:</span>
                                    <p class="text-sm text-gray-900"><?= htmlspecialchars($dados['nome_completo'] ?? '') ?></p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">CPF:</span>
                                    <p class="text-sm text-gray-900"><?= htmlspecialchars($dados['cpf'] ?? '') ?></p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">WhatsApp:</span>
                                    <p class="text-sm text-gray-900"><?= htmlspecialchars($dados['whatsapp'] ?? '') ?></p>
                                </div>
                                
                                <!-- Endereço -->
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Endereço:</span>
                                    <p class="text-sm text-gray-900">
                                        <?= htmlspecialchars($dados['endereco'] ?? '') ?>, <?= htmlspecialchars($dados['numero'] ?? '') ?>
                                        <?= !empty($dados['complemento']) ? ' - ' . htmlspecialchars($dados['complemento']) : '' ?><br>
                                        <?= htmlspecialchars($dados['bairro'] ?? '') ?>, <?= htmlspecialchars($dados['cidade'] ?? '') ?> - <?= htmlspecialchars($dados['estado'] ?? '') ?><br>
                                        CEP: <?= htmlspecialchars($dados['cep'] ?? '') ?>
                                    </p>
                                </div>
                                
                                <!-- Serviço -->
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Serviço:</span>
                                    <p class="text-sm text-gray-900">
                                        <?php
                                        if (!empty($dados['subcategoria_id'])) {
                                            $subcategoriaModel = new \App\Models\Subcategoria();
                                            $subcategoria = $subcategoriaModel->find($dados['subcategoria_id']);
                                            echo htmlspecialchars($subcategoria['nome'] ?? 'Não informado');
                                        }
                                        ?>
                                    </p>
                                </div>
                                
                                <!-- Descrição -->
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Descrição:</span>
                                    <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($dados['descricao_problema'] ?? '')) ?></p>
                                </div>
                                
                                <!-- Horários -->
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Horários Preferenciais:</span>
                                    <?php if (!empty($dados['horarios_preferenciais'])): ?>
                                        <ul class="text-sm text-gray-900 list-disc list-inside">
                                            <?php foreach ($dados['horarios_preferenciais'] as $horario): ?>
                                                <li><?= htmlspecialchars($horario) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500">Nenhum horário informado</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Termos -->
                        <form method="POST" action="<?= url($instancia . '/solicitacao-manual/etapa/5') ?>" class="space-y-6">
                            <?= \App\Core\View::csrfField() ?>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="termo_aceite" value="1" required
                                           class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-blue-900">
                                        Li e aceito os <a href="#" onclick="abrirModalTermos(); return false;" class="underline font-medium">termos e condições</a> de prestação de serviços. Estou ciente de que devo comunicar a administração/portaria quando necessário e garantir a presença de um responsável maior de idade durante o atendimento. <span class="text-red-600">*</span>
                                    </span>
                                </label>
                            </div>
                            
                            <!-- Navigation -->
                            <div class="flex justify-between pt-6">
                                <a href="<?= url($instancia . '/solicitacao-manual/etapa/4') ?>" 
                                   class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                                </a>
                                <button type="submit" id="btn-finalizar"
                                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-check mr-2"></i>Finalizar Solicitação
                                </button>
                            </div>
                        </form>
                    </div>
                    
                <?php endif; ?>
                
            </div>
            
        </div>
    </div>
    
    <!-- Modal de Termos -->
    <div id="modal-termos" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Termos e Condições de Prestação de Serviços</h3>
                <button onclick="fecharModalTermos()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4 text-sm text-gray-700">
                <p><strong>1. Prestação de Serviços:</strong> Os serviços de assistência técnica serão prestados por profissionais qualificados e devidamente autorizados pela imobiliária parceira.</p>
                
                <p><strong>2. Emergências:</strong> Em caso de emergências fora do horário comercial, entre em contato pelo telefone: <strong><?= htmlspecialchars($imobiliaria['telefone'] ?? '0800-XXX-XXXX') ?></strong></p>
                
                <p><strong>3. Privacidade:</strong> Seus dados pessoais serão tratados de acordo com a Lei Geral de Proteção de Dados (LGPD) e utilizados exclusivamente para o gerenciamento da solicitação.</p>
                
                <p><strong>4. Responsabilidades:</strong> É obrigatória a presença de uma pessoa maior de 18 anos durante todo o período de execução do serviço. Em casos de condomínio, o solicitante deve comunicar previamente a administração/portaria.</p>
                
                <p><strong>5. Cancelamento:</strong> O cancelamento pode ser realizado até 24 horas antes do horário agendado sem custos adicionais.</p>
                
                <p><strong>6. Peças e Materiais:</strong> Caso seja necessária a compra de peças, o locatário será informado previamente e terá até 10 dias para providenciar os materiais.</p>
            </div>
            <div class="border-t border-gray-200 px-6 py-4">
                <button onclick="fecharModalTermos()" 
                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Entendi
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
            <div class="mb-4">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-green-600"></div>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Enviando solicitação...</h3>
            <p class="text-gray-600">Por favor, aguarde.</p>
        </div>
    </div>
    
    <script>
    // Máscaras de input
    document.getElementById('cpf')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    });
    
    document.getElementById('whatsapp')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        e.target.value = value;
    });
    
    document.getElementById('cep')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/^(\d{5})(\d)/, '$1-$2');
        e.target.value = value;
    });
    
    // Buscar CEP
    document.getElementById('btn-buscar-cep')?.addEventListener('click', async function() {
        const cep = document.getElementById('cep').value.replace(/\D/g, '');
        
        if (cep.length !== 8) {
            alert('CEP inválido');
            return;
        }
        
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Buscando...';
        this.disabled = true;
        
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (data.erro) {
                alert('CEP não encontrado');
            } else {
                document.getElementById('endereco').value = data.logradouro || '';
                document.getElementById('bairro').value = data.bairro || '';
                document.getElementById('cidade').value = data.localidade || '';
                document.getElementById('estado').value = data.uf || '';
            }
        } catch (error) {
            alert('Erro ao buscar CEP');
        } finally {
            this.innerHTML = '<i class="fas fa-search mr-2"></i>Buscar';
            this.disabled = false;
        }
    });
    
    // Tipo de imóvel - mostrar/ocultar subtipo
    document.querySelectorAll('.tipo-imovel-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const subtipoContainer = document.getElementById('subtipo-container');
            
            // Visual dos cards
            document.querySelectorAll('.tipo-imovel-card').forEach(card => {
                card.classList.remove('border-green-500', 'bg-green-50');
                card.classList.add('border-gray-200');
            });
            
            const selectedCard = this.closest('label').querySelector('.tipo-imovel-card');
            selectedCard.classList.remove('border-gray-200');
            selectedCard.classList.add('border-green-500', 'bg-green-50');
            
            // Mostrar subtipo apenas para RESIDENCIAL
            if (this.value === 'RESIDENCIAL') {
                subtipoContainer.classList.remove('hidden');
            } else {
                subtipoContainer.classList.add('hidden');
                // Limpar seleção de subtipo
                document.querySelectorAll('.subtipo-imovel-radio').forEach(r => r.checked = false);
            }
        });
    });
    
    // Subtipo de imóvel - visual
    document.querySelectorAll('.subtipo-imovel-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.subtipo-imovel-card').forEach(card => {
                card.classList.remove('border-green-500', 'bg-green-50');
                card.classList.add('border-gray-200');
            });
            
            const selectedCard = this.closest('label').querySelector('.subtipo-imovel-card');
            selectedCard.classList.remove('border-gray-200');
            selectedCard.classList.add('border-green-500', 'bg-green-50');
        });
    });
    
    // Inicializar estados dos cards de tipo/subtipo se já tiver seleção
    document.addEventListener('DOMContentLoaded', function() {
        const tipoSelecionado = document.querySelector('.tipo-imovel-radio:checked');
        if (tipoSelecionado) {
            tipoSelecionado.dispatchEvent(new Event('change'));
        }
        
        const subtipoSelecionado = document.querySelector('.subtipo-imovel-radio:checked');
        if (subtipoSelecionado) {
            subtipoSelecionado.dispatchEvent(new Event('change'));
        }
    });
    
    // Categorias e Subcategorias (igual à solicitação normal)
    document.querySelectorAll('.categoria-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const categoriaId = this.value;
            
            document.querySelectorAll('.categoria-card').forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
                card.classList.add('border-gray-200');
                
                const check = card.querySelector('.categoria-check');
                if (check) {
                    check.classList.remove('bg-blue-500', 'border-blue-500');
                    check.classList.add('border-gray-300');
                }
                
                const details = card.querySelector('.categoria-details');
                if (details) details.classList.add('hidden');
            });
            
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
                if (details) details.classList.remove('hidden');
            }
        });
    });
    
    document.querySelectorAll('.categoria-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.categoria-details')) {
                const categoriaId = this.getAttribute('data-categoria');
                const radio = document.querySelector(`.categoria-radio[value="${categoriaId}"]`);
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            }
        });
    });
    
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('subcategoria-radio')) {
            document.querySelectorAll('.subcategoria-card').forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
                card.classList.add('border-gray-200');
            });
            document.querySelectorAll('.subcategoria-check').forEach(check => {
                check.classList.remove('bg-blue-500', 'border-blue-500');
                check.classList.add('border-gray-300');
            });
            
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
    
    // Preview de fotos
    window.previewPhotos = function(input) {
        const preview = document.getElementById('fotos-preview');
        const files = Array.from(input.files).slice(0, 5); // Máximo 5 fotos
        
        if (files.length > 0) {
            preview.classList.remove('hidden');
            preview.innerHTML = '';
            
            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative';
                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border border-gray-200">
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
    
    window.removePhoto = function(index) {
        const input = document.getElementById('fotos');
        const dt = new DataTransfer();
        
        Array.from(input.files).forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });
        
        input.files = dt.files;
        previewPhotos(input);
    };
    
    // Sistema de horários
    let horariosEscolhidos = [];
    
    document.querySelectorAll('.horario-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const data = document.getElementById('data_selecionada').value;
            const horario = this.value;
            
            if (data && horario) {
                const horarioCompleto = `${formatarData(data)} - ${horario}`;
                
                if (!horariosEscolhidos.includes(horarioCompleto) && horariosEscolhidos.length < 3) {
                    horariosEscolhidos.push(horarioCompleto);
                    atualizarListaHorarios();
                }
                
                // Limpar seleção de radio
                this.checked = false;
            } else if (!data) {
                alert('Selecione uma data primeiro');
                this.checked = false;
            }
        });
    });
    
    document.querySelectorAll('.horario-card').forEach(card => {
        card.addEventListener('click', function() {
            const label = this.closest('label');
            const radio = label ? label.querySelector('.horario-radio') : null;
            if (radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            }
        });
    });
    
    function atualizarListaHorarios() {
        const container = document.getElementById('horarios-selecionados');
        const lista = document.getElementById('lista-horarios');
        const contador = document.getElementById('contador-horarios');
        const btnContinuar = document.getElementById('btn-continuar');
        
        if (horariosEscolhidos.length > 0) {
            container.classList.remove('hidden');
            contador.textContent = horariosEscolhidos.length;
            
            lista.innerHTML = '';
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
                lista.appendChild(div);
            });
            
            btnContinuar.disabled = false;
            btnContinuar.classList.remove('bg-gray-400', 'cursor-not-allowed');
            btnContinuar.classList.add('bg-green-600', 'hover:bg-green-700');
        } else {
            container.classList.add('hidden');
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
    
    // Salvar horários antes de enviar
    const formEtapa4 = document.querySelector('form[action*="etapa/4"]');
    if (formEtapa4) {
        formEtapa4.addEventListener('submit', function(e) {
            const horariosFormatados = horariosEscolhidos.map(horario => {
                const [dataStr, faixaHorario] = horario.split(' - ');
                const [dia, mes, ano] = dataStr.split('/');
                const horarioInicial = faixaHorario.split('-')[0];
                return `${ano}-${mes}-${dia} ${horarioInicial}:00`;
            });
            
            const inputHorarios = document.createElement('input');
            inputHorarios.type = 'hidden';
            inputHorarios.name = 'horarios_opcoes';
            inputHorarios.value = JSON.stringify(horariosFormatados);
            this.appendChild(inputHorarios);
        });
    }
    
    // Modal de termos
    function abrirModalTermos() {
        document.getElementById('modal-termos').classList.remove('hidden');
    }
    
    function fecharModalTermos() {
        document.getElementById('modal-termos').classList.add('hidden');
    }
    
    // Loading overlay no envio final
    const btnFinalizar = document.getElementById('btn-finalizar');
    if (btnFinalizar) {
        btnFinalizar.closest('form').addEventListener('submit', function() {
            document.getElementById('loading-overlay').classList.remove('hidden');
            btnFinalizar.disabled = true;
            btnFinalizar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        });
    }
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>

