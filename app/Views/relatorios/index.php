<?php
$title = 'Relatórios';
$currentPage = 'relatorios';
$pageTitle = 'Relatórios';
ob_start();

$totalSolicitacoes = $resumo['total_solicitacoes'] ?? 0;
$totalPendentes = $resumo['total_pendentes'] ?? 0;
$totalAgendados = $resumo['total_agendados'] ?? 0;
$totalBuscando = $resumo['total_buscando_prestador'] ?? 0;
$totalConcluidos = $resumo['total_concluidos'] ?? 0;
$totalCancelados = $resumo['total_cancelados'] ?? 0;
$totalReembolsos = $resumo['total_reembolsos'] ?? 0;
$totalSlaAtrasado = $resumo['total_sla_atrasado'] ?? 0;
$suportaSla = $suportaSla ?? false;

$statusSelecionados = $filtros['status_ids'] ?? [];
if (empty($statusSelecionados) && isset($filtros['status_id'])) {
    $statusSelecionados = [$filtros['status_id']];
}

$statusSelecionados = array_map('strval', $statusSelecionados);

$temWhatsApp = array_key_exists('whatsapp_enviado', $solicitacoes[0] ?? []) && $solicitacoes[0]['whatsapp_enviado'] !== null;
$whatsappSelecionado = array_key_exists('whatsapp_enviado', $filtros)
    ? ($filtros['whatsapp_enviado'] ? '1' : '0')
    : '';

function formatarData(?string $data, bool $incluiHora = true): string
{
    if (!$data) {
        return '-';
    }

    $timestamp = strtotime($data);
    if ($timestamp === false) {
        return $data;
    }

    return $incluiHora ? date('d/m/Y H:i', $timestamp) : date('d/m/Y', $timestamp);
}
?>

<div class="space-y-8">
    <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-4">
        <form method="GET" class="space-y-4" id="filtros-relatorios-form">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label for="locatario_nome" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Buscar</label>
                    <input type="text" id="locatario_nome" name="locatario_nome" value="<?= htmlspecialchars($filtros['locatario_nome'] ?? '') ?>" placeholder="Nome, telefone, CPF ou e-mail" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <div class="w-48 min-w-[170px]">
                    <label for="imobiliaria_id" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Imobiliária</label>
                    <select id="imobiliaria_id" name="imobiliaria_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Todas</option>
                        <?php foreach ($imobiliarias as $imobiliaria): ?>
                            <option value="<?= $imobiliaria['id'] ?>" <?= ($filtros['imobiliaria_id'] ?? '') == $imobiliaria['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($imobiliaria['nome'] ?? $imobiliaria['nome_fantasia'] ?? 'Sem nome') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-48 min-w-[170px]">
                    <label for="status_id" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Status</label>
                    <select id="status_id" name="status_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Todos</option>
                        <?php foreach ($statusLista as $status): ?>
                            <option value="<?= $status['id'] ?>" <?= ($filtros['status_id'] ?? '') == $status['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-48 min-w-[170px]">
                    <label for="condicao_id" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Condição</label>
                    <select id="condicao_id" name="condicao_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Todas</option>
                        <?php foreach ($condicoes as $condicao): ?>
                            <option value="<?= $condicao['id'] ?>" <?= ($filtros['condicao_id'] ?? '') == $condicao['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($condicao['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" id="toggle-advanced-filters" class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">
                        <i class="fas fa-sliders-h mr-2"></i>Avançado
                    </button>
                    <a href="<?= url('admin/relatorios') ?>" class="inline-flex items-center px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Limpar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                </div>
            </div>

            <div id="advanced-filters-panel" class="hidden border-t border-gray-200 pt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Categoria</label>
                        <select id="categoria_id" name="categoria_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= ($filtros['categoria_id'] ?? '') == $categoria['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="cpf" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">CPF do locatário</label>
                        <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($filtros['cpf'] ?? '') ?>" placeholder="000.000.000-00" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="data_inicio" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Criação (de)</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="data_fim" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Criação (até)</label>
                        <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus;border-blue-500 transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Subcategoria</label>
                        <select id="subcategoria_id" name="subcategoria_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">Todas</option>
                            <?php foreach ($subcategorias as $categoriaId => $grupo): ?>
                                <optgroup label="<?= htmlspecialchars($grupo['categoria_nome']) ?>">
                                    <?php foreach ($grupo['itens'] as $subcategoria): ?>
                                        <option value="<?= $subcategoria['id'] ?>" <?= ($filtros['subcategoria_id'] ?? '') == $subcategoria['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($subcategoria['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="numero_contrato" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Número do contrato</label>
                        <input type="text" id="numero_contrato" name="numero_contrato" value="<?= htmlspecialchars($filtros['numero_contrato'] ?? '') ?>" placeholder="Informe o nº" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="agendamento_inicio" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Agendamento (de)</label>
                        <input type="date" id="agendamento_inicio" name="agendamento_inicio" value="<?= htmlspecialchars($filtros['agendamento_inicio'] ?? '') ?>" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="agendamento_fim" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Agendamento (até)</label>
                        <input type="date" id="agendamento_fim" name="agendamento_fim" value="<?= htmlspecialchars($filtros['agendamento_fim'] ?? '') ?>" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Reembolso</label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2">
                            <input type="checkbox" name="precisa_reembolso" value="1" class="text-blue-600 rounded" <?= !empty($filtros['precisa_reembolso']) ? 'checked' : '' ?>>
                            <span>Com reembolso</span>
                        </label>
                    </div>

                    <?php if ($suportaSla): ?>
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">SLA</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2">
                                <input type="checkbox" name="sla_atrasado" value="1" class="text-blue-600 rounded" <?= !empty($filtros['sla_atrasado']) ? 'checked' : '' ?>>
                                <span>Só SLA estourado</span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <?php if ($temWhatsApp): ?>
                        <div class="space-y-2">
                            <label for="whatsapp_enviado" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">WhatsApp</label>
                            <select id="whatsapp_enviado" name="whatsapp_enviado" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="" <?= $whatsappSelecionado === '' ? 'selected' : '' ?>>Todos</option>
                                <option value="1" <?= $whatsappSelecionado === '1' ? 'selected' : '' ?>>Enviado</option>
                                <option value="0" <?= $whatsappSelecionado === '0' ? 'selected' : '' ?>>Pendente</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Aplicar filtros avançados
                    </button>
                </div>
            </div>
        </form>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-6">
        <article class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total de Solicitações</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-2"><?= number_format($totalSolicitacoes, 0, ',', '.') ?></p>
                </div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600"><i class="fas fa-list-ul"></i></span>
            </div>
        </article>
        <article class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pendentes</p>
                    <p class="text-3xl font-semibold text-amber-600 mt-2"><?= number_format($totalPendentes, 0, ',', '.') ?></p>
                </div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-amber-600"><i class="fas fa-clock"></i></span>
            </div>
        </article>
        <article class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Agendados</p>
                    <p class="text-3xl font-semibold text-green-600 mt-2"><?= number_format($totalAgendados, 0, ',', '.') ?></p>
                </div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-600"><i class="fas fa-calendar-check"></i></span>
            </div>
        </article>
        <article class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Buscando Prestador</p>
                    <p class="text-3xl font-semibold text-blue-600 mt-2"><?= number_format($totalBuscando, 0, ',', '.') ?></p>
                </div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600"><i class="fas fa-user-search"></i></span>
            </div>
        </article>
        <article class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Concluídos</p>
                    <p class="text-3xl font-semibold text-emerald-600 mt-2"><?= number_format($totalConcluidos, 0, ',', '.') ?></p>
                </div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100 text-emerald-600"><i class="fas fa-check-circle"></i></span>
            </div>
        </article>
        <article class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Cancelados</p>
                    <p class="text-3xl font-semibold text-rose-600 mt-2"><?= number_format($totalCancelados, 0, ',', '.') ?></p>
                </div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-rose-100 text-rose-600"><i class="fas fa-times-circle"></i></span>
            </div>
        </article>
        <article class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Reembolsos pendentes</p>
                    <p class="text-3xl font-semibold text-purple-600 mt-2"><?= number_format($totalReembolsos, 0, ',', '.') ?></p>
                </div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 text-purple-600"><i class="fas fa-file-invoice-dollar"></i></span>
            </div>
        </article>
        <?php if ($suportaSla): ?>
            <article class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">SLA estourado</p>
                        <p class="text-3xl font-semibold text-red-600 mt-2"><?= number_format($totalSlaAtrasado, 0, ',', '.') ?></p>
                    </div>
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-100 text-red-600"><i class="fas fa-exclamation-triangle"></i></span>
                </div>
            </article>
        <?php endif; ?>
    </section>

    <section class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Solicitações encontradas</h3>
                <p class="text-sm text-gray-500">Mostrando até 100 registros recentes conforme filtros aplicados.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-gray-500 bg-gray-100 rounded-full">
                <?= number_format(count($solicitacoes ?? []), 0, ',', '.') ?> resultados
            </span>
        </header>

        <?php if (!empty($solicitacoes)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Reembolso</th>
                            <th class="px-6 py-3 text-left">Contrato</th>
                            <th class="px-6 py-3 text-left">Locatário</th>
                            <th class="px-6 py-3 text-left">CPF</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Condição</th>
                            <th class="px-6 py-3 text-left">Categoria</th>
                            <th class="px-6 py-3 text-left">Criada em</th>
                            <th class="px-6 py-3 text-left">Agendamento</th>
                            <th class="px-6 py-3 text-left">Atualização</th>
                            <th class="px-6 py-3 text-left">Indicadores</th>
                            <th class="px-6 py-3 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if (!empty($solicitacao['precisa_reembolso'])): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                            Reembolso
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= $solicitacao['numero_contrato'] ? htmlspecialchars($solicitacao['numero_contrato']) : '-' ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($solicitacao['locatario_nome'] ?? 'Não informado') ?></div>
                                    <?php if (!empty($solicitacao['imobiliaria_nome'])): ?>
                                        <div class="text-xs text-gray-500">Imobiliária: <?= htmlspecialchars($solicitacao['imobiliaria_nome']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= !empty($solicitacao['locatario_cpf']) ? htmlspecialchars($solicitacao['locatario_cpf']) : '-' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php $statusCor = $solicitacao['status_cor'] ?? '#3B82F6'; ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: <?= htmlspecialchars($statusCor) ?>1a; color: <?= htmlspecialchars($statusCor) ?>;">
                                        <?= htmlspecialchars($solicitacao['status_nome'] ?? 'Sem status') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if (!empty($solicitacao['condicao_nome'])): ?>
                                        <?php $condicaoCor = $solicitacao['condicao_cor'] ?? '#6B7280'; ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: <?= htmlspecialchars($condicaoCor) ?>1a; color: <?= htmlspecialchars($condicaoCor) ?>;">
                                            <?= htmlspecialchars($solicitacao['condicao_nome']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php if (!empty($solicitacao['categoria_nome'])): ?>
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($solicitacao['categoria_nome']) ?></div>
                                        <?php if (!empty($solicitacao['subcategoria_nome'])): ?>
                                            <div class="text-xs text-gray-500">Sub: <?= htmlspecialchars($solicitacao['subcategoria_nome']) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= formatarData($solicitacao['created_at'] ?? null) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= formatarData($solicitacao['data_agendamento'] ?? null, false) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= formatarData($solicitacao['updated_at'] ?? null) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php
                                        $badges = [];
                                        if (!empty($solicitacao['prioridade'])) {
                                            $badges[] = ['label' => 'Prioridade: ' . ucfirst(strtolower($solicitacao['prioridade'])), 'class' => 'bg-amber-100 text-amber-700'];
                                        }
                                        if (!empty($solicitacao['precisa_reembolso'])) {
                                            $badges[] = ['label' => 'Reembolso', 'class' => 'bg-purple-100 text-purple-700'];
                                        }
                                        if ($temWhatsApp && array_key_exists('whatsapp_enviado', $solicitacao)) {
                                            if ($solicitacao['whatsapp_enviado']) {
                                                $badges[] = ['label' => 'WhatsApp enviado', 'class' => 'bg-emerald-100 text-emerald-700'];
                                            } else {
                                                $badges[] = ['label' => 'WhatsApp pendente', 'class' => 'bg-red-100 text-red-700'];
                                            }
                                        }
                                    ?>
                                    <?php if (!empty($badges)): ?>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($badges as $badge): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $badge['class'] ?>">
                                                    <?= htmlspecialchars($badge['label']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="<?= url('admin/solicitacoes/' . $solicitacao['id']) ?>" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                                        <i class="fas fa-external-link-alt text-xs"></i> Ver detalhes
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-6">
                <p class="text-gray-600 text-sm">Nenhuma solicitação encontrada com os filtros atuais. Ajuste os critérios e tente novamente.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleAdvancedBtn = document.getElementById('toggle-advanced-filters');
    const advancedPanel = document.getElementById('advanced-filters-panel');

    if (toggleAdvancedBtn && advancedPanel) {
        toggleAdvancedBtn.addEventListener('click', () => {
            const isHidden = advancedPanel.classList.toggle('hidden');
            if (isHidden) {
                toggleAdvancedBtn.classList.remove('bg-blue-600', 'text-white');
                toggleAdvancedBtn.classList.add('text-blue-600', 'border-blue-200');
            } else {
                toggleAdvancedBtn.classList.add('bg-blue-600', 'text-white');
                toggleAdvancedBtn.classList.remove('text-blue-600', 'border-blue-200');
            }
        });
    }
});
</script>

