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

// Função para gerar resumo das etapas anteriores
function gerarResumoEtapas($etapaAtual, $locatario) {
    $dados = $_SESSION['nova_solicitacao'] ?? [];
    $resumo = [];
    
    // Etapa 1: Endereço
    if ($etapaAtual > 1 && isset($dados['endereco_selecionado'])) {
        $imovel = $locatario['imoveis'][$dados['endereco_selecionado']] ?? [];
        if (!empty($imovel)) {
            $endereco = htmlspecialchars($imovel['endereco'] ?? '') . ', ' . htmlspecialchars($imovel['numero'] ?? '');
            $resumo[] = [
                'titulo' => 'Endereço',
                'icone' => 'fas fa-map-marker-alt',
                'conteudo' => $endereco
            ];
        }
    }
    
    // Etapa 2: Serviço
    if ($etapaAtual > 2 && isset($dados['subcategoria_id'])) {
        $subcategoriaModel = new \App\Models\Subcategoria();
        $subcategoria = $subcategoriaModel->find($dados['subcategoria_id']);
        if ($subcategoria) {
            $resumo[] = [
                'titulo' => 'Serviço',
                'icone' => 'fas fa-cog',
                'conteudo' => htmlspecialchars($subcategoria['nome'] ?? '')
            ];
        }
    }
    
    // Etapa 3: Descrição
    if ($etapaAtual > 3 && !empty($dados['descricao_problema'])) {
        $descricao = htmlspecialchars($dados['descricao_problema']);
        if (strlen($descricao) > 100) {
            $descricao = substr($descricao, 0, 100) . '...';
        }
        $resumo[] = [
            'titulo' => 'Descrição',
            'icone' => 'fas fa-edit',
            'conteudo' => $descricao
        ];
    }
    
    // Etapa 4: Agendamento
    if ($etapaAtual > 4 && !empty($dados['horarios_preferenciais'])) {
        $horarios = $dados['horarios_preferenciais'];
        if (is_array($horarios) && !empty($horarios)) {
            $primeiroHorario = $horarios[0];
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/', $primeiroHorario, $matches)) {
                $dataFormatada = $matches[3] . '/' . $matches[2] . '/' . $matches[1] . ' às ' . $matches[4] . ':' . $matches[5];
                $totalHorarios = count($horarios);
                $texto = $dataFormatada;
                if ($totalHorarios > 1) {
                    $opcoesAdicionais = $totalHorarios - 1;
                    $texto .= ' (+' . $opcoesAdicionais . ' ' . ($opcoesAdicionais > 1 ? 'opções' : 'opção') . ')';
                }
                $resumo[] = [
                    'titulo' => 'Agendamento',
                    'icone' => 'fas fa-calendar',
                    'conteudo' => $texto
                ];
            }
        }
    }
    
    return $resumo;
}
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-plus-circle mr-2"></i>
                Nova Solicitação
            </h1>
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
    <?php 
    // Exibir resumo das etapas anteriores (exceto na etapa 1 e na última etapa)
    if ($etapaAtual > 1 && $etapaAtual < 5):
        $resumoEtapas = gerarResumoEtapas($etapaAtual, $locatario);
        if (!empty($resumoEtapas)):
    ?>
        <!-- Resumo das Etapas Anteriores - Dropdown -->
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <button type="button" 
                    onclick="toggleResumoEtapas()" 
                    class="w-full flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-green-500 rounded-lg p-2 -m-2">
                <div class="flex items-center">
                    <i class="fas fa-list-ul text-gray-600 mr-2"></i>
                    <h3 class="text-sm font-medium text-gray-700 whitespace-nowrap">Resumo das Etapas Anteriores</h3>
                </div>
                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" id="resumo-chevron"></i>
            </button>
            <div id="resumo-conteudo" class="hidden mt-3 pt-3 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-<?= min(count($resumoEtapas), 4) ?> gap-3">
                    <?php foreach ($resumoEtapas as $item): ?>
                        <div class="bg-white rounded-lg p-3 border border-gray-200">
                            <div class="flex items-start">
                                <i class="<?= $item['icone'] ?> text-gray-400 mr-2 mt-0.5 text-sm"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-500 mb-1"><?= $item['titulo'] ?></p>
                                    <p class="text-sm text-gray-900 truncate" title="<?= htmlspecialchars($item['conteudo']) ?>">
                                        <?= $item['conteudo'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php 
        endif;
    endif; 
    ?>
    
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
                                <div class="endereco-item-<?= $index ?>" data-endereco="<?= $index ?>" data-contrato="<?= htmlspecialchars($contratoInfo) ?>" style="margin-bottom:12px;">
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
        <?php
        $finalidadeLocacao = $finalidade_locacao ?? $_SESSION['nova_solicitacao']['finalidade_locacao'] ?? 'RESIDENCIAL';
        $finalidadeTexto = $finalidadeLocacao === 'RESIDENCIAL' ? 'Residencial' : 'Comercial';
        ?>
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-cog mr-2"></i>
                Qual tipo de serviço você precisa?
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Selecione a categoria do serviço desejado
                <?php if (!empty($finalidadeLocacao)): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                        <i class="fas fa-<?= $finalidadeLocacao === 'RESIDENCIAL' ? 'home' : 'building' ?> mr-1"></i>
                        <?= $finalidadeTexto ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/2') ?>" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                
                <!-- Categoria do Serviço -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">
                        Categoria do Serviço
                        <?php if (!empty($finalidadeLocacao)): ?>
                            <span class="text-xs text-gray-500 font-normal">
                                (Mostrando categorias para <?= strtolower($finalidadeTexto) ?>)
                            </span>
                        <?php endif; ?>
                    </h3>
                    <div class="space-y-3">
                        <?php if (!empty($categorias)): ?>
                            <?php foreach ($categorias as $categoriaPai): ?>
                                <?php 
                                // Verificar se a categoria pai tem filhas
                                $temFilhas = !empty($categoriaPai['filhas']) && count($categoriaPai['filhas']) > 0;
                                ?>
                                
                                <?php if ($temFilhas): ?>
                                    <!-- Categoria Pai COM Filhas (Separadora Expansível) -->
                                    <div class="categoria-pai-container" data-categoria-pai-id="<?= $categoriaPai['id'] ?>">
                                        <!-- Botão para expandir/colapsar categoria pai -->
                                        <button type="button" 
                                                class="w-full flex items-center justify-between border-2 border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-all categoria-pai-toggle"
                                                data-categoria-pai="<?= $categoriaPai['id'] ?>"
                                                onclick="toggleCategoriaPai(<?= $categoriaPai['id'] ?>)">
                                            <div class="flex items-center">
                                                <i class="<?= $categoriaPai['icone'] ?? 'fas fa-cog' ?> text-xl text-gray-600 mr-3"></i>
                                                <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($categoriaPai['nome']) ?></span>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-chevron-down text-gray-400 categoria-pai-chevron" id="chevron-<?= $categoriaPai['id'] ?>"></i>
                                            </div>
                                        </button>
                                        
                                        <!-- Descrição da categoria pai (aparece quando expandido) -->
                                        <?php if (!empty($categoriaPai['descricao'])): ?>
                                            <div class="px-4 pb-2 categoria-pai-descricao hidden" id="descricao-pai-<?= $categoriaPai['id'] ?>">
                                                <p class="text-xs text-gray-600 italic">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    <?= htmlspecialchars($categoriaPai['descricao']) ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Categorias Filhas (ocultas por padrão) -->
                                        <div class="categoria-filhas-container hidden mt-2 ml-4 space-y-2 border-l-2 border-gray-200 pl-4" id="filhas-<?= $categoriaPai['id'] ?>" style="display: none;">
                                            <?php foreach ($categoriaPai['filhas'] as $categoriaFilha): ?>
                                                <label class="relative block categoria-label" data-categoria-label="<?= $categoriaFilha['id'] ?>">
                                                    <input type="radio" name="categoria_id" value="<?= $categoriaFilha['id'] ?>" 
                                                           class="sr-only categoria-radio" data-categoria="<?= $categoriaFilha['id'] ?>">
                                                    <div class="border-2 rounded-lg p-4 cursor-pointer transition-all hover:border-blue-300 categoria-card" 
                                                         data-categoria="<?= $categoriaFilha['id'] ?>">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                                <i class="<?= $categoriaFilha['icone'] ?? 'fas fa-cog' ?> text-lg text-gray-600 mr-3"></i>
                                                                <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($categoriaFilha['nome']) ?></span>
                                            </div>
                                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full categoria-check"></div>
                                        </div>
                                                        
                                                        <!-- Descrição da categoria (aparece quando selecionado) -->
                                                        <?php if (!empty($categoriaFilha['descricao'])): ?>
                                                            <div class="mt-2 categoria-descricao hidden">
                                                                <p class="text-xs text-gray-600 italic">
                                                                    <i class="fas fa-info-circle mr-1"></i>
                                                                    <?= htmlspecialchars($categoriaFilha['descricao']) ?>
                                                                </p>
                                                            </div>
                                                        <?php endif; ?>
                                        
                                        <!-- Subcategorias (aparece quando selecionado) -->
                                        <div class="mt-3 categoria-details hidden">
                                            <div class="bg-gray-50 rounded-lg p-3">
                                                <h4 class="text-sm font-medium text-gray-700 mb-3">
                                                    Tipo de Serviço
                                                                    <span class="text-xs text-gray-500">(<?= count($categoriaFilha['subcategorias'] ?? []) ?> opções)</span>
                                                </h4>
                                                <div class="space-y-3">
                                                                    <?php if (!empty($categoriaFilha['subcategorias'])): ?>
                                                                        <?php foreach ($categoriaFilha['subcategorias'] as $subcategoria): ?>
                                                            <label class="relative block cursor-pointer">
                                                                <input type="radio" name="subcategoria_id" value="<?= $subcategoria['id'] ?>" 
                                                                       class="sr-only subcategoria-radio">
                                                                <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-300 transition-colors subcategoria-card <?= (!empty($subcategoria['is_emergencial']) && ($subcategoria['is_emergencial'] == 1 || $subcategoria['is_emergencial'] === true)) ? 'border-red-300 bg-red-50' : '' ?>">
                                                                    <div class="flex items-start justify-between">
                                                                        <div class="flex-1">
                                                                            <div class="flex items-center gap-2 mb-1">
                                                                                <h5 class="text-sm font-medium text-gray-900">
                                                                                    <?= htmlspecialchars($subcategoria['nome']) ?>
                                                                                </h5>
                                                                                <?php if (!empty($subcategoria['is_emergencial']) && ($subcategoria['is_emergencial'] == 1 || $subcategoria['is_emergencial'] === true)): ?>
                                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                                                        Emergencial
                                                                                    </span>
                                                                                <?php endif; ?>
                                                                            </div>
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
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Categoria SEM Filhas (Categoria Normal) -->
                                    <label class="relative block categoria-label" data-categoria-label="<?= $categoriaPai['id'] ?>">
                                        <input type="radio" name="categoria_id" value="<?= $categoriaPai['id'] ?>" 
                                               class="sr-only categoria-radio" data-categoria="<?= $categoriaPai['id'] ?>">
                                        <div class="border-2 rounded-lg p-4 cursor-pointer transition-all hover:border-blue-300 categoria-card" 
                                             data-categoria="<?= $categoriaPai['id'] ?>">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <i class="<?= $categoriaPai['icone'] ?? 'fas fa-cog' ?> text-xl text-gray-600 mr-3"></i>
                                                    <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($categoriaPai['nome']) ?></span>
                                                </div>
                                                <div class="w-6 h-6 border-2 border-gray-300 rounded-full categoria-check"></div>
                                            </div>
                                            
                                            <!-- Descrição da categoria (aparece quando selecionado) -->
                                            <?php if (!empty($categoriaPai['descricao'])): ?>
                                                <div class="mt-2 categoria-descricao hidden">
                                                    <p class="text-xs text-gray-600 italic">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        <?= htmlspecialchars($categoriaPai['descricao']) ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Subcategorias (aparece quando selecionado) -->
                                            <div class="mt-3 categoria-details hidden">
                                                <div class="bg-gray-50 rounded-lg p-3">
                                                    <h4 class="text-sm font-medium text-gray-700 mb-3">
                                                        Tipo de Serviço
                                                        <span class="text-xs text-gray-500">(<?= count($categoriaPai['subcategorias'] ?? []) ?> opções)</span>
                                                    </h4>
                                                    <div class="space-y-3">
                                                        <?php if (!empty($categoriaPai['subcategorias'])): ?>
                                                            <?php foreach ($categoriaPai['subcategorias'] as $subcategoria): ?>
                                                                <label class="relative block cursor-pointer">
                                                                    <input type="radio" name="subcategoria_id" value="<?= $subcategoria['id'] ?>" 
                                                                           class="sr-only subcategoria-radio">
                                                                    <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-300 transition-colors subcategoria-card <?= (!empty($subcategoria['is_emergencial']) && ($subcategoria['is_emergencial'] == 1 || $subcategoria['is_emergencial'] === true)) ? 'border-red-300 bg-red-50' : '' ?>">
                                                                        <div class="flex items-start justify-between">
                                                                            <div class="flex-1">
                                                                                <div class="flex items-center gap-2 mb-1">
                                                                                    <h5 class="text-sm font-medium text-gray-900">
                                                                                        <?= htmlspecialchars($subcategoria['nome']) ?>
                                                                                    </h5>
                                                                                    <?php if (!empty($subcategoria['is_emergencial']) && ($subcategoria['is_emergencial'] == 1 || $subcategoria['is_emergencial'] === true)): ?>
                                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                                                            Emergencial
                                                                                        </span>
                                                                                    <?php endif; ?>
                                                                                </div>
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
                                <?php endif; ?>
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
        <?php
        // Verificar se a subcategoria é emergencial
        $subcategoriaModel = new \App\Models\Subcategoria();
        $subcategoriaId = $_SESSION['nova_solicitacao']['subcategoria_id'] ?? 0;
        $subcategoria = $subcategoriaModel->find($subcategoriaId);
        $isEmergencial = !empty($subcategoria['is_emergencial']);
        
        // Calcular data mínima para agendamento baseado no prazo_minimo
        $dataMinimaAgendamento = null;
        if (!$isEmergencial && $subcategoriaId) {
            $dataMinimaAgendamento = $subcategoriaModel->calcularDataLimiteAgendamento($subcategoriaId);
        }
        
        // Verificar se está fora do horário comercial usando configurações
        $configuracaoModel = new \App\Models\Configuracao();
        $isForaHorario = $configuracaoModel->isForaHorarioComercial();
        
        // Buscar telefone de emergência
        $telefoneEmergenciaModel = new \App\Models\TelefoneEmergencia();
        $telefoneEmergencia = $telefoneEmergenciaModel->getPrincipal();
        ?>
        
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-calendar mr-2"></i>
                <?php if ($isEmergencial): ?>
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    Atendimento Emergencial
                <?php else: ?>
                    Quando você prefere o atendimento?
                <?php endif; ?>
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
                <input type="hidden" name="is_emergencial" value="<?= $isEmergencial ? '1' : '0' ?>">
                
                <?php if ($isEmergencial): ?>
                    <!-- Emergencial: Duas opções (ou uma se fora do horário) -->
                    <div class="space-y-4">
                        <?php if (!$isForaHorario): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium mb-2">Escolha como deseja prosseguir:</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Opção 1: Atendimento em 120 minutos / Atendimento Emergencial -->
                        <label class="relative block cursor-pointer">
                            <input type="radio" name="tipo_atendimento_emergencial" value="120_minutos" 
                                   class="sr-only tipo-atendimento-radio" id="opcao_120_minutos"
                                   <?= $isForaHorario ? 'checked' : '' ?>>
                            <div class="border-2 <?= $isForaHorario ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white' ?> rounded-lg p-4 hover:border-green-300 hover:bg-green-50 transition-colors tipo-atendimento-card">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="w-5 h-5 border-2 <?= $isForaHorario ? 'border-green-600 bg-green-600 flex items-center justify-center rounded-full' : 'border-gray-300 rounded-full' ?> tipo-atendimento-check">
                                            <?php if ($isForaHorario): ?>
                                                <i class="fas fa-check text-white text-xs"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                                            <?= $isForaHorario ? 'Solicitar atendimento emergencial' : 'Solicitar Atendimento em 120 minutos' ?>
                                        </h4>
                                        <p class="text-xs text-gray-600">
                                            Sua solicitação será processada imediatamente. O atendimento será agendado automaticamente e você receberá retorno em até 120 minutos.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Box de Atendimento Emergencial (aparece quando "120 minutos" está selecionado) -->
                        <div id="box-atendimento-emergencial" class="<?= $isForaHorario ? '' : 'hidden' ?> mt-3">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-3 mt-0.5"></i>
                                    <div>
                                        <h4 class="text-sm font-medium text-red-800">Atendimento Emergencial</h4>
                                        <p class="text-sm text-red-700 mt-1">
                                            Esta é uma solicitação de emergência. O atendimento será processado imediatamente sem necessidade de agendamento. Você receberá retorno em até 120 minutos.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Opção 2: Agendar (oculta quando fora do horário) -->
                        <?php if (!$isForaHorario): ?>
                            <label class="relative block cursor-pointer">
                                <input type="radio" name="tipo_atendimento_emergencial" value="agendar" 
                                       class="sr-only tipo-atendimento-radio" id="opcao_agendar">
                                <div class="border-2 border-gray-200 rounded-lg p-4 bg-white hover:border-blue-300 hover:bg-blue-50 transition-colors tipo-atendimento-card">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full tipo-atendimento-check"></div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                                Agendar
                                            </h4>
                                            <p class="text-xs text-gray-600">
                                                Se preferir, você pode agendar um horário específico para o atendimento.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        <?php endif; ?>
                        
                        <!-- Seção de Agendamento (oculta por padrão, aparece quando selecionar "Agendar") -->
                        <div id="secao-agendamento-emergencial" class="hidden space-y-4 pt-4 border-t border-gray-200">
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
                                <label for="data_selecionada_emergencial" class="block text-sm font-medium text-gray-700 mb-3">
                                    Selecione uma Data
                                </label>
                                <div class="relative cursor-pointer">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                    <input type="date" id="data_selecionada_emergencial" name="data_selecionada" 
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
                                        <input type="radio" name="horario_selecionado_emergencial" value="08:00-11:00" class="sr-only horario-radio-emergencial">
                                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card-emergencial">
                                            <div class="text-sm font-medium text-gray-900">08h00 às 11h00</div>
                                        </div>
                                    </label>
                                    
                                    <label class="relative">
                                        <input type="radio" name="horario_selecionado_emergencial" value="11:00-14:00" class="sr-only horario-radio-emergencial">
                                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card-emergencial">
                                            <div class="text-sm font-medium text-gray-900">11h00 às 14h00</div>
                                        </div>
                                    </label>
                                    
                                    <label class="relative">
                                        <input type="radio" name="horario_selecionado_emergencial" value="14:00-17:00" class="sr-only horario-radio-emergencial">
                                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card-emergencial">
                                            <div class="text-sm font-medium text-gray-900">14h00 às 17h00</div>
                                        </div>
                                    </label>
                                    
                                    <label class="relative">
                                        <input type="radio" name="horario_selecionado_emergencial" value="17:00-20:00" class="sr-only horario-radio-emergencial">
                                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card-emergencial">
                                            <div class="text-sm font-medium text-gray-900">17h00 às 20h00</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Horários Selecionados -->
                            <div id="horarios-selecionados-emergencial" class="hidden">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">
                                    Horários Selecionados (<span id="contador-horarios-emergencial">0</span>/3)
                                </h4>
                                <div id="lista-horarios-emergencial" class="space-y-2">
                                    <!-- Horários serão inseridos aqui via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Normal: Mostrar opções de horário -->
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
                                   min="<?= $dataMinimaAgendamento ? $dataMinimaAgendamento->format('Y-m-d') : date('Y-m-d', strtotime('+1 day')) ?>"
                                   max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                                   data-min-date="<?= $dataMinimaAgendamento ? $dataMinimaAgendamento->format('Y-m-d') : '' ?>">
                        </div>
                        <div class="mt-2 flex items-center text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1.5"></i>
                            <span>
                                Atendimentos disponíveis apenas em dias úteis (segunda a sexta-feira)
                                <?php if ($dataMinimaAgendamento && !$isEmergencial): ?>
                                    <?php
                                    $prazoMinimo = $subcategoria['prazo_minimo'] ?? 1;
                                    $dataMinimaFormatada = $dataMinimaAgendamento->format('d/m/Y');
                                    ?>
                                    <br>
                                    <strong>Data mínima para agendamento: <?= $dataMinimaFormatada ?></strong>
                                    <?php if ($prazoMinimo > 0): ?>
                                        (prazo mínimo de <?= $prazoMinimo ?> dia<?= $prazoMinimo > 1 ? 's' : '' ?>)
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
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
                <?php endif; ?>
                
                <!-- Navigation -->
                <div class="flex justify-between pt-6">
                    <a href="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/3') ?>" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar
                    </a>
                    <button type="submit" id="btn-continuar" <?= $isEmergencial ? '' : 'disabled' ?>
                            class="px-6 py-3 <?= $isEmergencial ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' ?> text-white font-medium rounded-lg transition-colors">
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
    
            <?php
            // Verificar se é emergencial e fora do horário comercial
            $dados = $_SESSION['nova_solicitacao'] ?? [];
            $isEmergencial = !empty($dados['is_emergencial']);
            $isForaHorario = !empty($dados['is_fora_horario']);
            
            // Buscar telefone de emergência
            $telefoneEmergenciaModel = new \App\Models\TelefoneEmergencia();
            $telefoneEmergencia = $telefoneEmergenciaModel->getPrincipal();
            ?>
            
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
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 sm:justify-between pt-6">
                    <a href="<?= url($locatario['instancia'] . '/nova-solicitacao/etapa/4') ?>" 
                       class="w-full sm:w-auto px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-center sm:text-left">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a>
                    <button type="submit" id="btn-finalizar"
                            class="w-full sm:w-auto flex-1 sm:flex-none px-6 py-3 <?= ($isEmergencial && $isForaHorario) ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' ?> text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-check mr-2"></i><?= ($isEmergencial && $isForaHorario) ? 'Solicitar Emergência' : 'Finalizar Solicitação' ?>
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
        
        // Validação: bloquear seleção de fins de semana e verificar data mínima
        dataInput.addEventListener('change', function() {
            if (!this.value) return;
            
            const dataSelecionada = new Date(this.value + 'T12:00:00');
            const diaDaSemana = dataSelecionada.getDay(); // 0 = Domingo, 6 = Sábado
            
            // Verificar se é fim de semana
            if (diaDaSemana === 0 || diaDaSemana === 6) {
                const nomeDia = diaDaSemana === 0 ? 'domingo' : 'sábado';
                alert('⚠️ Atendimentos não são realizados aos fins de semana.\n\nA data selecionada é um ' + nomeDia + '.\nPor favor, selecione um dia útil (segunda a sexta-feira).');
                this.value = '';
                return;
            }
            
            // Verificar se a data está antes da data mínima permitida
            const dataMinima = this.getAttribute('data-min-date');
            if (dataMinima) {
                const dataMinimaObj = new Date(dataMinima + 'T12:00:00');
                if (dataSelecionada < dataMinimaObj) {
                    const dataMinimaFormatada = new Date(dataMinima).toLocaleDateString('pt-BR');
                    alert('⚠️ Data não disponível para agendamento.\n\nA data selecionada é anterior à data mínima permitida (' + dataMinimaFormatada + ').\nPor favor, selecione uma data válida.');
                    this.value = '';
                    return;
                }
            }
        });
    }
    
    // === ETAPA 5: Loading ao finalizar ===
    const btnFinalizar = document.getElementById('btn-finalizar');
    if (btnFinalizar) {
        const formFinalizar = btnFinalizar.closest('form');
        if (formFinalizar) {
            formFinalizar.addEventListener('submit', async function(e) {
                // Verificar se o termo foi aceito
                const termoAceite = formFinalizar.querySelector('input[name="termo_aceite"]');
                if (!termoAceite || !termoAceite.checked) {
                    e.preventDefault();
                    alert('Por favor, aceite os termos para continuar.');
                    return;
                }
                
                // Verificar se é emergencial e fora do horário
                const isEmergenciaForaHorario = btnFinalizar.getAttribute('data-emergencia-fora-horario') === 'true';
                const telefone = btnFinalizar.getAttribute('data-telefone');
                
                if (isEmergenciaForaHorario && telefone) {
                    e.preventDefault();
                    
                    // Mostrar loading
                    const loadingOverlay = document.getElementById('loading-overlay');
                    if (loadingOverlay) {
                        loadingOverlay.classList.remove('hidden');
                    }
                    
                    // Desabilitar botão
                    btnFinalizar.disabled = true;
                    btnFinalizar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
                    
                    try {
                        // Criar FormData e enviar para salvar no kanban
                        const formData = new FormData(formFinalizar);
                        
                        const response = await fetch(formFinalizar.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Após salvar, abrir o telefone imediatamente
                            const telefoneHref = 'tel:' + telefone.replace(/[^0-9+]/g, '');
                            
                            // Abrir o link de telefone
                            window.location.href = telefoneHref;
                            
                            // Redirecionar após um pequeno delay (para dar tempo do telefone abrir)
                            setTimeout(() => {
                                if (result.redirect) {
                                    window.location.href = result.redirect;
                                } else {
                                    window.location.href = '<?= url($locatario['instancia'] . '/solicitacoes') ?>';
                                }
                            }, 2000);
                        } else {
                            alert('Erro ao salvar solicitação: ' + (result.message || 'Erro desconhecido'));
                            btnFinalizar.disabled = false;
                            btnFinalizar.innerHTML = 'Solicitar Emergência';
                            if (loadingOverlay) {
                                loadingOverlay.classList.add('hidden');
                            }
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao processar solicitação. Tente novamente.');
                        btnFinalizar.disabled = false;
                        btnFinalizar.innerHTML = 'Solicitar Emergência';
                        if (loadingOverlay) {
                            loadingOverlay.classList.add('hidden');
                        }
                    }
                    
                    return;
                }
                
                // Para solicitações normais, continuar com o fluxo padrão
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
    
    // Função para expandir/colapsar categoria pai
    window.toggleCategoriaPai = function(categoriaPaiId) {
        const container = document.getElementById('filhas-' + categoriaPaiId);
        const chevron = document.getElementById('chevron-' + categoriaPaiId);
        const descricao = document.getElementById('descricao-pai-' + categoriaPaiId);
        
        if (!container) return;
        
        if (container.style.display === 'none' || container.classList.contains('hidden')) {
            // Expandir
            container.classList.remove('hidden');
            container.style.display = 'block';
            if (chevron) {
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-up');
            }
            // Mostrar descrição quando expandir
            if (descricao) {
                descricao.classList.remove('hidden');
            }
        } else {
            // Colapsar
            container.classList.add('hidden');
            container.style.display = 'none';
            if (chevron) {
                chevron.classList.remove('fa-chevron-up');
                chevron.classList.add('fa-chevron-down');
            }
            // Ocultar descrição quando colapsar
            if (descricao) {
                descricao.classList.add('hidden');
            }
        }
    };
    
    // Função para mostrar modal de limite atingido (definida aqui para estar disponível)
    window.mostrarModalLimite = function(totalAtual, limite) {
        const modal = document.getElementById('modal-limite-atingido');
        const mensagem = document.getElementById('modal-limite-mensagem');
        
        if (!modal || !mensagem) {
            // Fallback para alert se modal não estiver disponível
            alert(`Você já possui ${totalAtual} solicitação${totalAtual > 1 ? 'ões' : ''} desta categoria nos últimos 12 meses. O limite permitido é de ${limite} solicitação${limite > 1 ? 'ões' : ''}.`);
            return;
        }
        
        const textoMensagem = `Você já possui <strong>${totalAtual}</strong> solicitação${totalAtual > 1 ? 'ões' : ''} desta categoria nos últimos 12 meses. O limite permitido é de <strong>${limite}</strong> solicitação${limite > 1 ? 'ões' : ''}.`;
        
        mensagem.innerHTML = textoMensagem;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Prevenir scroll do body quando modal estiver aberto
        document.body.style.overflow = 'hidden';
    };
    
    // Verificar limites de todas as categorias ao carregar a página
    function verificarLimitesCategorias() {
        const enderecoSelecionado = document.querySelector('input[name="endereco_selecionado"]:checked');
        if (!enderecoSelecionado) {
            return;
        }
        
        const enderecoIndex = enderecoSelecionado.value;
        const enderecoItem = document.querySelector(`.endereco-item-${enderecoIndex}`);
        if (!enderecoItem) {
            return;
        }
        
        const numeroContrato = enderecoItem.getAttribute('data-contrato') || '';
        if (!numeroContrato) {
            return;
        }
        
        const instancia = '<?= $locatario["instancia"] ?? "" ?>';
        
        categoriaCards.forEach(card => {
            const categoriaId = card.getAttribute('data-categoria');
            if (!categoriaId) return;
            
            fetch(`/${instancia}/verificar-limite-categoria?categoria_id=${categoriaId}&numero_contrato=${encodeURIComponent(numeroContrato)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && !data.permitido) {
                        // Apenas desabilitar visualmente a categoria (sem bloquear cliques)
                        card.classList.add('opacity-60', 'cursor-not-allowed', 'bg-gray-50', 'border-gray-300');
                        card.classList.remove('hover:border-blue-300', 'cursor-pointer', 'border-gray-200');
                        card.style.cursor = 'not-allowed';
                        
                        // Adicionar ícone de bloqueio visual
                        const iconContainer = card.querySelector('.flex.items-center');
                        if (iconContainer && !iconContainer.querySelector('.fa-lock')) {
                            const lockIcon = document.createElement('i');
                            lockIcon.className = 'fas fa-lock text-gray-400 mr-2';
                            iconContainer.insertBefore(lockIcon, iconContainer.firstChild);
                        }
                        
                        // Adicionar atributo para identificar como desabilitada
                        card.setAttribute('data-limite-atingido', 'true');
                        card.setAttribute('data-total-atual', data.total_atual);
                        card.setAttribute('data-limite', data.limite);
                        
                        // Desabilitar o radio
                        const radio = document.querySelector(`.categoria-radio[value="${categoriaId}"]`);
                        if (radio) {
                            radio.disabled = true;
                            radio.setAttribute('data-limite-atingido', 'true');
                            radio.setAttribute('data-total-atual', data.total_atual);
                            radio.setAttribute('data-limite', data.limite);
                        }
                        
                        // Apenas visual no label
                        const label = card.closest('label.categoria-label');
                        if (label) {
                            label.style.cursor = 'not-allowed';
                            label.classList.add('opacity-60');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar limite da categoria:', error);
                });
        });
    }
    
    // Verificar limites quando a página carregar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', verificarLimitesCategorias);
    } else {
        verificarLimitesCategorias();
    }
    
    // Verificar limites quando mudar o endereço selecionado
    document.querySelectorAll('input[name="endereco_selecionado"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Reabilitar todas as categorias primeiro
            categoriaCards.forEach(card => {
                card.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-gray-50', 'border-gray-300', 'pointer-events-none');
                card.classList.add('hover:border-blue-300', 'cursor-pointer', 'border-gray-200');
                card.style.pointerEvents = '';
                card.style.cursor = '';
                card.removeAttribute('data-limite-atingido');
                card.removeAttribute('tabindex');
                
                // Remover ícone de bloqueio se existir
                const lockIcon = card.querySelector('.fa-lock');
                if (lockIcon) {
                    lockIcon.remove();
                }
                
                const categoriaId = card.getAttribute('data-categoria');
                const radioInput = document.querySelector(`.categoria-radio[value="${categoriaId}"]`);
                if (radioInput) {
                    radioInput.disabled = false;
                    radioInput.removeAttribute('data-limite-atingido');
                    radioInput.removeAttribute('data-total-atual');
                    radioInput.removeAttribute('data-limite');
                }
                
                // Reabilitar o label também
                const label = card.closest('label.categoria-label');
                if (label) {
                    label.style.cursor = '';
                    label.classList.remove('opacity-60');
                }
            });
            
            // Verificar limites novamente
            setTimeout(verificarLimitesCategorias, 100);
        });
    });
    
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
                
                const descricao = card.querySelector('.categoria-descricao');
                if (descricao) {
                    descricao.classList.add('hidden');
                }
            });
            
            // Selecionar o card atual
            const currentCard = document.querySelector(`.categoria-card[data-categoria="${categoriaId}"]`);
            if (currentCard) {
                selecionarCardCategoria(categoriaId, currentCard);
            }
        });
    });
    
    // Função auxiliar para selecionar card de categoria
    function selecionarCardCategoria(categoriaId, cardElement) {
        cardElement.classList.remove('border-gray-200');
        cardElement.classList.add('border-blue-500', 'bg-blue-50');
        
        const check = cardElement.querySelector('.categoria-check');
        if (check) {
            check.classList.remove('border-gray-300');
            check.classList.add('bg-blue-500', 'border-blue-500');
        }
        
        const details = cardElement.querySelector('.categoria-details');
        if (details) {
            details.classList.remove('hidden');
        }
        
        const descricao = cardElement.querySelector('.categoria-descricao');
        if (descricao) {
            descricao.classList.remove('hidden');
        }
    }
    
    // Click no card também seleciona o radio
    categoriaCards.forEach(card => {
        card.addEventListener('click', function(e) {
            const categoriaId = this.getAttribute('data-categoria');
            const radio = document.querySelector(`.categoria-radio[value="${categoriaId}"]`);
            if (radio && !radio.disabled) {
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
    
    // Sistema de agendamento para emergencial
    const tipoAtendimentoRadios = document.querySelectorAll('.tipo-atendimento-radio');
    const secaoAgendamentoEmergencial = document.getElementById('secao-agendamento-emergencial');
    const boxAtendimentoEmergencial = document.getElementById('box-atendimento-emergencial');
    const btnContinuar = document.getElementById('btn-continuar');
    
    // Função para atualizar visual e exibição baseado no tipo selecionado
    function atualizarTipoAtendimento(tipoSelecionado) {
        console.log('🔄 Atualizando tipo de atendimento para:', tipoSelecionado);
        const radio = document.querySelector(`.tipo-atendimento-radio[value="${tipoSelecionado}"]`);
        if (!radio) {
            console.error('❌ Radio não encontrado para:', tipoSelecionado);
            return;
        }
        
        // Garantir que o radio está marcado
        radio.checked = true;
        
        const card = radio.closest('label')?.querySelector('.tipo-atendimento-card');
        const check = card ? card.querySelector('.tipo-atendimento-check') : null;
        
        console.log('📦 Card encontrado:', card ? 'Sim' : 'Não');
        console.log('✅ Check encontrado:', check ? 'Sim' : 'Não');
        
        // Atualizar visual de todos os cards (limpar seleção anterior)
        document.querySelectorAll('.tipo-atendimento-card').forEach(c => {
            c.classList.remove('border-green-500', 'bg-green-50', 'border-blue-500', 'bg-blue-50');
            c.classList.add('border-gray-200', 'bg-white');
            const chk = c.querySelector('.tipo-atendimento-check');
            if (chk) {
                chk.classList.remove('bg-green-600', 'border-green-600', 'bg-blue-500', 'border-blue-500', 'flex', 'items-center', 'justify-center');
                chk.classList.add('border-gray-300', 'rounded-full');
                chk.innerHTML = '';
                chk.style.backgroundColor = '';
                chk.style.display = 'block';
            }
        });
        
        // Atualizar card selecionado
        if (tipoSelecionado === '120_minutos') {
            if (card) {
                card.classList.remove('border-gray-200', 'bg-white');
                card.classList.add('border-green-500', 'bg-green-50');
                console.log('✅ Card 120 minutos atualizado');
            }
            if (check) {
                check.classList.remove('border-gray-300');
                check.classList.add('bg-green-600', 'border-green-600', 'flex', 'items-center', 'justify-center', 'rounded-full');
                check.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
                check.style.display = 'flex';
                console.log('✅ Check 120 minutos atualizado');
            }
            // Mostrar box de atendimento emergencial
            if (boxAtendimentoEmergencial) {
                boxAtendimentoEmergencial.classList.remove('hidden');
                boxAtendimentoEmergencial.style.display = 'block';
            }
            // Ocultar seção de agendamento
            if (secaoAgendamentoEmergencial) {
                secaoAgendamentoEmergencial.classList.add('hidden');
                secaoAgendamentoEmergencial.style.display = 'none';
            }
            // Habilitar botão continuar
            if (btnContinuar) {
                btnContinuar.disabled = false;
                btnContinuar.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btnContinuar.classList.add('bg-green-600', 'hover:bg-green-700');
            }
        } else if (tipoSelecionado === 'agendar') {
            if (card) {
                card.classList.remove('border-gray-200', 'bg-white');
                card.classList.add('border-blue-500', 'bg-blue-50');
                console.log('✅ Card Agendar atualizado');
            }
            if (check) {
                check.classList.remove('border-gray-300');
                check.classList.add('bg-blue-500', 'border-blue-500', 'flex', 'items-center', 'justify-center', 'rounded-full');
                check.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
                check.style.display = 'flex';
                console.log('✅ Check Agendar atualizado');
            }
            // Ocultar box de atendimento emergencial
            if (boxAtendimentoEmergencial) {
                boxAtendimentoEmergencial.classList.add('hidden');
                boxAtendimentoEmergencial.style.display = 'none';
            }
            // Mostrar seção de agendamento
            if (secaoAgendamentoEmergencial) {
                secaoAgendamentoEmergencial.classList.remove('hidden');
                secaoAgendamentoEmergencial.style.display = 'block';
                console.log('✅ Seção de agendamento emergencial exibida');
            } else {
                console.error('❌ Seção de agendamento emergencial não encontrada!');
                const secao = document.getElementById('secao-agendamento-emergencial');
                if (secao) {
                    secao.classList.remove('hidden');
                    secao.style.display = 'block';
                }
            }
            // Desabilitar botão continuar até selecionar horários
            if (btnContinuar) {
                btnContinuar.disabled = true;
                btnContinuar.classList.add('bg-gray-400', 'cursor-not-allowed');
                btnContinuar.classList.remove('bg-green-600', 'hover:bg-green-700');
            }
        }
    }
    
    // Controlar exibição do calendário quando selecionar tipo de atendimento
    if (tipoAtendimentoRadios.length > 0) {
        tipoAtendimentoRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                atualizarTipoAtendimento(this.value);
            });
        });
        
        // Adicionar evento de clique nos labels e cards
        document.querySelectorAll('label').forEach(label => {
            if (label.querySelector('.tipo-atendimento-card')) {
                label.addEventListener('click', function(e) {
                    const radio = this.querySelector('.tipo-atendimento-radio');
                    if (radio) {
                        console.log('🖱️ Label clicado, selecionando:', radio.value);
                        // Forçar seleção do radio
                        radio.checked = true;
                        // Atualizar visual imediatamente
                        atualizarTipoAtendimento(radio.value);
                        // Disparar evento change
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            }
        });
        
        // Adicionar evento diretamente nos cards também (para garantir que funcione)
        document.querySelectorAll('.tipo-atendimento-card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function(e) {
                const label = this.closest('label');
                if (!label) return;
                
                const radio = label.querySelector('.tipo-atendimento-radio');
                if (radio) {
                    console.log('🖱️ Card clicado, selecionando:', radio.value);
                    // Forçar seleção do radio
                    radio.checked = true;
                    // Atualizar visual imediatamente
                    atualizarTipoAtendimento(radio.value);
                    // Disparar evento change
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
        
        // Verificar se a seção de agendamento existe
        console.log('🔍 Verificando seção de agendamento emergencial:', secaoAgendamentoEmergencial);
        if (secaoAgendamentoEmergencial) {
            console.log('✅ Seção encontrada no DOM');
        } else {
            console.error('❌ Seção de agendamento emergencial NÃO encontrada no DOM!');
        }
        
        // Inicializar estado inicial - garantir que a seção esteja oculta se "120 minutos" estiver selecionado
        const radio120Minutos = document.getElementById('opcao_120_minutos');
        const radioAgendar = document.getElementById('opcao_agendar');
        
        if (radio120Minutos && radio120Minutos.checked) {
            // Se "120 minutos" está selecionado, mostrar box de emergência e ocultar seção de agendamento
            if (boxAtendimentoEmergencial) {
                boxAtendimentoEmergencial.classList.remove('hidden');
                boxAtendimentoEmergencial.style.display = 'block';
            }
            if (secaoAgendamentoEmergencial) {
                secaoAgendamentoEmergencial.classList.add('hidden');
                secaoAgendamentoEmergencial.style.display = 'none';
            }
            atualizarTipoAtendimento('120_minutos');
        } else if (radioAgendar && radioAgendar.checked) {
            // Se "Agendar" está selecionado, ocultar box de emergência e mostrar seção de agendamento
            if (boxAtendimentoEmergencial) {
                boxAtendimentoEmergencial.classList.add('hidden');
                boxAtendimentoEmergencial.style.display = 'none';
            }
            if (secaoAgendamentoEmergencial) {
                secaoAgendamentoEmergencial.classList.remove('hidden');
                secaoAgendamentoEmergencial.style.display = 'block';
            }
            atualizarTipoAtendimento('agendar');
        } else {
            // Por padrão, se nenhum estiver selecionado, ocultar ambos
            if (boxAtendimentoEmergencial) {
                boxAtendimentoEmergencial.classList.add('hidden');
                boxAtendimentoEmergencial.style.display = 'none';
            }
            if (secaoAgendamentoEmergencial) {
                secaoAgendamentoEmergencial.classList.add('hidden');
                secaoAgendamentoEmergencial.style.display = 'none';
            }
        }
    } else {
        console.warn('⚠️ Nenhum radio de tipo de atendimento encontrado');
    }
    
    // Sistema de agendamento para emergencial (quando selecionar "Agendar")
    const horarioRadiosEmergencial = document.querySelectorAll('.horario-radio-emergencial');
    const horarioCardsEmergencial = document.querySelectorAll('.horario-card-emergencial');
    const horariosSelecionadosEmergencial = document.getElementById('horarios-selecionados-emergencial');
    const listaHorariosEmergencial = document.getElementById('lista-horarios-emergencial');
    const contadorHorariosEmergencial = document.getElementById('contador-horarios-emergencial');
    
    let horariosEscolhidosEmergencial = [];
    
    horarioRadiosEmergencial.forEach(radio => {
        radio.addEventListener('change', function() {
            const data = document.getElementById('data_selecionada_emergencial')?.value;
            const horario = this.value;
            
            if (data && horario) {
                const horarioCompleto = `${formatarData(data)} - ${horario}`;
                
                if (!horariosEscolhidosEmergencial.includes(horarioCompleto) && horariosEscolhidosEmergencial.length < 3) {
                    horariosEscolhidosEmergencial.push(horarioCompleto);
                    atualizarListaHorariosEmergencial();
                    
                    // Atualizar visual do card selecionado
                    const label = this.closest('label');
                    const card = label ? label.querySelector('.horario-card-emergencial') : null;
                    if (card) {
                        // Remover seleção de todos os cards primeiro
                        horarioCardsEmergencial.forEach(c => {
                            c.classList.remove('border-green-500', 'bg-green-50');
                            c.classList.add('border-gray-200', 'bg-white');
                        });
                        // Destacar o card selecionado
                        card.classList.remove('border-gray-200', 'bg-white');
                        card.classList.add('border-green-500', 'bg-green-50');
                    }
                }
            }
        });
    });
    
    // Click no card de horário emergencial também seleciona o radio
    horarioCardsEmergencial.forEach(card => {
        card.addEventListener('click', function() {
            const label = this.closest('label');
            const radio = label ? label.querySelector('.horario-radio-emergencial') : null;
            if (radio) {
                radio.checked = true;
                radio.dispatchEvent(new Event('change'));
            }
        });
    });
    
    // Atualizar lista quando data mudar
    const dataInputEmergencial = document.getElementById('data_selecionada_emergencial');
    if (dataInputEmergencial) {
        dataInputEmergencial.addEventListener('change', function() {
            if (!this.value) return;
            
            const dataSelecionada = new Date(this.value + 'T12:00:00');
            const diaDaSemana = dataSelecionada.getDay(); // 0 = Domingo, 6 = Sábado
            
            // Verificar se é fim de semana
            if (diaDaSemana === 0 || diaDaSemana === 6) {
                const nomeDia = diaDaSemana === 0 ? 'domingo' : 'sábado';
                alert('⚠️ Atendimentos não são realizados aos fins de semana.\n\nA data selecionada é um ' + nomeDia + '.\nPor favor, selecione um dia útil (segunda a sexta-feira).');
                this.value = '';
                return;
            }
            
            // Apenas desmarcar os radio buttons visuais, mas manter os horários já selecionados na lista
            // Não limpar horariosEscolhidosEmergencial para manter os horários de datas anteriores
            horarioRadiosEmergencial.forEach(radio => {
                radio.checked = false;
            });
            
            // Atualizar visual dos cards de horário para remover seleção visual
            horarioCardsEmergencial.forEach(card => {
                card.classList.remove('border-green-500', 'bg-green-50');
                card.classList.add('border-gray-200', 'bg-white');
            });
        });
    }
    
    function atualizarListaHorariosEmergencial() {
        if (horariosEscolhidosEmergencial.length > 0) {
            if (horariosSelecionadosEmergencial) {
                horariosSelecionadosEmergencial.classList.remove('hidden');
            }
            if (contadorHorariosEmergencial) {
                contadorHorariosEmergencial.textContent = horariosEscolhidosEmergencial.length;
            }
            
            if (listaHorariosEmergencial) {
                listaHorariosEmergencial.innerHTML = '';
                horariosEscolhidosEmergencial.forEach((horario, index) => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-3';
                    div.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-clock text-green-600 mr-2"></i>
                            <span class="text-sm text-green-800">${horario}</span>
                        </div>
                        <button type="button" onclick="removerHorarioEmergencial(${index})" 
                                class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    listaHorariosEmergencial.appendChild(div);
                });
            }
            
            // Habilitar botão continuar se tiver pelo menos 1 horário
            if (btnContinuar && horariosEscolhidosEmergencial.length > 0) {
                btnContinuar.disabled = false;
                btnContinuar.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btnContinuar.classList.add('bg-green-600', 'hover:bg-green-700');
            }
        } else {
            if (horariosSelecionadosEmergencial) {
                horariosSelecionadosEmergencial.classList.add('hidden');
            }
            // Só desabilitar se estiver na opção "Agendar"
            const opcaoAgendar = document.getElementById('opcao_agendar');
            if (btnContinuar && opcaoAgendar && opcaoAgendar.checked) {
                btnContinuar.disabled = true;
                btnContinuar.classList.add('bg-gray-400', 'cursor-not-allowed');
                btnContinuar.classList.remove('bg-green-600', 'hover:bg-green-700');
            }
        }
    }
    
    window.removerHorarioEmergencial = function(index) {
        horariosEscolhidosEmergencial.splice(index, 1);
        atualizarListaHorariosEmergencial();
    };
    
    // Sistema de agendamento (normal - não emergencial)
    const horarioRadios = document.querySelectorAll('.horario-radio');
    const horarioCards = document.querySelectorAll('.horario-card');
    const horariosSelecionados = document.getElementById('horarios-selecionados');
    const listaHorarios = document.getElementById('lista-horarios');
    const contadorHorarios = document.getElementById('contador-horarios');
    
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
            // Verificar se é emergencial e qual opção foi selecionada
            const tipoAtendimento = document.querySelector('.tipo-atendimento-radio:checked')?.value;
            
            if (tipoAtendimento === '120_minutos') {
                // Enviar campo indicando atendimento em 120 minutos
                const inputTipo = document.createElement('input');
                inputTipo.type = 'hidden';
                inputTipo.name = 'tipo_atendimento_emergencial';
                inputTipo.value = '120_minutos';
                form.appendChild(inputTipo);
            } else if (tipoAtendimento === 'agendar') {
                // Converter horários emergenciais: "29/10/2025 - 08:00-11:00" → "2025-10-29 08:00:00-11:00:00"
                const horariosFormatados = horariosEscolhidosEmergencial.map(horario => {
                    const [dataStr, faixaHorario] = horario.split(' - ');
                    const [dia, mes, ano] = dataStr.split('/');
                    const [horarioInicial, horarioFinal] = faixaHorario.split('-');
                    // Formato: "2025-10-29 08:00:00-11:00:00"
                    return `${ano}-${mes}-${dia} ${horarioInicial.trim()}:00-${horarioFinal.trim()}:00`;
                });
                
                // Enviar como JSON
                const inputHorarios = document.createElement('input');
                inputHorarios.type = 'hidden';
                inputHorarios.name = 'horarios_opcoes';
                inputHorarios.value = JSON.stringify(horariosFormatados);
                form.appendChild(inputHorarios);
                
                const inputTipo = document.createElement('input');
                inputTipo.type = 'hidden';
                inputTipo.name = 'tipo_atendimento_emergencial';
                inputTipo.value = 'agendar';
                form.appendChild(inputTipo);
            } else {
                // Normal (não emergencial): Converter: "29/10/2025 - 08:00-11:00" → "2025-10-29 08:00:00-11:00:00"
                const horariosFormatados = horariosEscolhidos.map(horario => {
                    const [dataStr, faixaHorario] = horario.split(' - ');
                    const [dia, mes, ano] = dataStr.split('/');
                    const [horarioInicial, horarioFinal] = faixaHorario.split('-');
                    // Formato: "2025-10-29 08:00:00-11:00:00"
                    return `${ano}-${mes}-${dia} ${horarioInicial.trim()}:00-${horarioFinal.trim()}:00`;
                });
                
                // Enviar como JSON
                const inputHorarios = document.createElement('input');
                inputHorarios.type = 'hidden';
                inputHorarios.name = 'horarios_opcoes';
                inputHorarios.value = JSON.stringify(horariosFormatados);
                form.appendChild(inputHorarios);
            }
        });
    }
    
    // Controlar visibilidade do campo "Tipo de Imóvel" baseado em "Finalidade da Locação"
    const finalidadeSelect = document.getElementById('finalidade_locacao');
    const tipoImovelContainer = document.getElementById('tipo_imovel_container');
    
    if (finalidadeSelect && tipoImovelContainer) {
        // Criar campo hidden para tipo_imovel quando for Comercial
        let hiddenTipoImovel = document.getElementById('hidden_tipo_imovel');
        if (!hiddenTipoImovel) {
            hiddenTipoImovel = document.createElement('input');
            hiddenTipoImovel.type = 'hidden';
            hiddenTipoImovel.name = 'tipo_imovel';
            hiddenTipoImovel.id = 'hidden_tipo_imovel';
            tipoImovelContainer.parentNode.insertBefore(hiddenTipoImovel, tipoImovelContainer);
        }
        
        function toggleTipoImovel() {
            if (finalidadeSelect.value === 'COMERCIAL') {
                tipoImovelContainer.style.display = 'none';
                // Limpar seleção dos radio buttons e desabilitar para não enviar
                const radioButtons = tipoImovelContainer.querySelectorAll('input[type="radio"]');
                radioButtons.forEach(radio => {
                    radio.checked = false;
                    radio.removeAttribute('required');
                    radio.disabled = true; // Desabilitar para não enviar
                });
                // Definir valor padrão para Comercial no campo hidden
                hiddenTipoImovel.value = 'COMERCIAL';
                hiddenTipoImovel.disabled = false; // Garantir que está habilitado
            } else {
                tipoImovelContainer.style.display = 'block';
                // Habilitar radio buttons novamente
                const radioButtons = tipoImovelContainer.querySelectorAll('input[type="radio"]');
                radioButtons.forEach(radio => {
                    radio.disabled = false;
                });
                // Restaurar seleção padrão (Casa)
                const radioCasa = tipoImovelContainer.querySelector('input[value="CASA"]');
                if (radioCasa) {
                    radioCasa.checked = true;
                }
                // Desabilitar o campo hidden para não enviar (os radio buttons vão enviar o valor)
                hiddenTipoImovel.disabled = true;
                hiddenTipoImovel.value = '';
            }
        }
        
        // Executar na carga da página
        toggleTipoImovel();
        
        // Executar quando mudar a seleção
        finalidadeSelect.addEventListener('change', toggleTipoImovel);
        
        // Garantir que quando os radio buttons mudarem, o hidden seja limpo
        const radioButtons = tipoImovelContainer.querySelectorAll('input[type="radio"]');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                if (finalidadeSelect.value === 'RESIDENCIAL') {
                    hiddenTipoImovel.value = '';
                }
            });
        });
    }
});
</script>

<!-- Modal de Limite Atingido -->
<div id="modal-limite-atingido" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
            <!-- Header do Modal -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Limite Atingido</h3>
                </div>
                <button type="button" id="fechar-modal-limite" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Conteúdo do Modal -->
            <div class="mb-6">
                <p class="text-sm text-gray-700 mb-4" id="modal-limite-mensagem">
                    <!-- Mensagem será inserida aqui via JavaScript -->
                </p>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-xs text-yellow-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Importante:</strong> O limite é calculado com base nas solicitações dos últimos 12 meses para esta categoria.
                    </p>
                </div>
            </div>
            
            <!-- Botão de Fechar -->
            <div class="flex justify-end">
                <button type="button" id="btn-fechar-modal-limite" class="px-6 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    Entendi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Função para fechar modal
function fecharModalLimite() {
    const modal = document.getElementById('modal-limite-atingido');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

// Event listeners para fechar modal (aguardar DOM estar pronto)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('fechar-modal-limite')?.addEventListener('click', fecharModalLimite);
        document.getElementById('btn-fechar-modal-limite')?.addEventListener('click', fecharModalLimite);
        
        // Fechar modal ao clicar fora dele
        const modal = document.getElementById('modal-limite-atingido');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharModalLimite();
                }
            });
        }
    });
} else {
    document.getElementById('fechar-modal-limite')?.addEventListener('click', fecharModalLimite);
    document.getElementById('btn-fechar-modal-limite')?.addEventListener('click', fecharModalLimite);
    
    // Fechar modal ao clicar fora dele
    const modal = document.getElementById('modal-limite-atingido');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalLimite();
            }
        });
    }
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modal-limite-atingido');
        if (modal && !modal.classList.contains('hidden')) {
            fecharModalLimite();
        }
    }
});

// === Toggle Resumo das Etapas Anteriores ===
function toggleResumoEtapas() {
    const conteudo = document.getElementById('resumo-conteudo');
    const chevron = document.getElementById('resumo-chevron');
    
    if (conteudo && chevron) {
        conteudo.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }
}
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/locatario.php';
?>