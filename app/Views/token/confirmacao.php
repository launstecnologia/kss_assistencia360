<?php
$title = $title ?? 'Confirmar Horário';
ob_start();
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Confirmar Horário</h2>
                <p class="text-gray-600 mt-2">Confirme seu horário de atendimento</p>
            </div>

            <!-- Informações da Solicitação -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">Informações do Agendamento</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Protocolo:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($tokenData['protocol'] ?? $solicitacao['numero_solicitacao'] ?? 'N/A') ?></span>
                    </div>
                    <?php if ($tokenData['scheduled_date']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Data:</span>
                        <span class="font-semibold text-gray-900"><?= date('d/m/Y', strtotime($tokenData['scheduled_date'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($tokenData['scheduled_time']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Horário:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($tokenData['scheduled_time']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($solicitacao['data_agendamento']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Data Agendada:</span>
                        <span class="font-semibold text-gray-900"><?= date('d/m/Y', strtotime($solicitacao['data_agendamento'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($solicitacao['horario_agendamento']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Horário Agendado:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($solicitacao['horario_agendamento']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Endereço -->
            <?php if ($solicitacao['imovel_endereco']): ?>
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-2">Endereço do Atendimento</h3>
                <p class="text-sm text-gray-700">
                    <?= htmlspecialchars($solicitacao['imovel_endereco']) ?>
                    <?php if ($solicitacao['imovel_numero']): ?>, nº <?= htmlspecialchars($solicitacao['imovel_numero']) ?><?php endif; ?>
                    <?php if ($solicitacao['imovel_complemento']): ?>, <?= htmlspecialchars($solicitacao['imovel_complemento']) ?><?php endif; ?>
                    <br>
                    <?php if ($solicitacao['imovel_bairro']): ?><?= htmlspecialchars($solicitacao['imovel_bairro']) ?>, <?php endif; ?>
                    <?php if ($solicitacao['imovel_cidade']): ?><?= htmlspecialchars($solicitacao['imovel_cidade']) ?><?php endif; ?>
                    <?php if ($solicitacao['imovel_estado']): ?>/<?= htmlspecialchars($solicitacao['imovel_estado']) ?><?php endif; ?>
                    <?php if ($solicitacao['imovel_cep']): ?> - CEP: <?= htmlspecialchars($solicitacao['imovel_cep']) ?><?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Formulário de Confirmação -->
            <form method="POST" action="<?= url('confirmacao-horario?token=' . urlencode($token)) ?>" class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Ao confirmar, você estará concordando com o horário agendado. Certifique-se de estar disponível no local durante o período informado.
                    </p>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirmar Horário
                </button>
            </form>

            <!-- Link para cancelar -->
            <div class="mt-4 text-center">
                <a href="<?= url('cancelamento-horario?token=' . urlencode($token)) ?>" class="text-sm text-red-600 hover:text-red-700">
                    Não posso neste horário, quero cancelar
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-sm text-gray-500">
            <p>KSS Seguros - Assistência 360°</p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/public.php';
?>

