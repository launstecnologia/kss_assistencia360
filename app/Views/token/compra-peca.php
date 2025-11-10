<?php
$title = $title ?? 'Informar Compra de Peça';
ob_start();
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-shopping-cart text-green-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Informar Compra de Peça</h2>
                <p class="text-gray-600 mt-2">Confirme que você comprou a peça e selecione novos horários para o atendimento</p>
            </div>

            <?php if (isset($error) && $error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Informações da Solicitação -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">Informações da Solicitação</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nº de Atendimento:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($tokenData['protocol'] ?? $solicitacao['numero_solicitacao'] ?? 'N/A') ?></span>
                    </div>
                    <?php if (!empty($solicitacao['categoria_nome'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Categoria:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($solicitacao['categoria_nome']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($solicitacao['subcategoria_nome'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subcategoria:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($solicitacao['subcategoria_nome']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Formulário -->
            <form method="POST" action="<?= url('compra-peca?token=' . urlencode($token)) ?>" id="formCompraPeca" class="space-y-6">
                
                <!-- Seleção de Data -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Selecione uma Data <span class="text-red-500">*</span>
                    </label>
                    <div class="relative cursor-pointer">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <input type="date" id="data_agendamento" name="data_agendamento" 
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm text-gray-700 cursor-pointer transition-colors"
                               placeholder="dd/mm/2025"
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                               max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                               required>
                    </div>
                    <div class="mt-2 flex items-center text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1.5"></i>
                        <span>Atendimentos disponíveis apenas em dias úteis (segunda a sexta-feira)</span>
                    </div>
                </div>
                
                <!-- Seleção de Horário -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Selecione um Horário <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="relative">
                            <input type="radio" name="horario_selecionado" value="08:00-11:00" class="sr-only horario-radio" required>
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                <div class="text-sm font-medium text-gray-900">08h00 às 11h00</div>
                            </div>
                        </label>
                        
                        <label class="relative">
                            <input type="radio" name="horario_selecionado" value="11:00-14:00" class="sr-only horario-radio" required>
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                <div class="text-sm font-medium text-gray-900">11h00 às 14h00</div>
                            </div>
                        </label>
                        
                        <label class="relative">
                            <input type="radio" name="horario_selecionado" value="14:00-17:00" class="sr-only horario-radio" required>
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                <div class="text-sm font-medium text-gray-900">14h00 às 17h00</div>
                            </div>
                        </label>
                        
                        <label class="relative">
                            <input type="radio" name="horario_selecionado" value="17:00-20:00" class="sr-only horario-radio" required>
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center cursor-pointer hover:border-green-300 transition-colors horario-card">
                                <div class="text-sm font-medium text-gray-900">17h00 às 20h00</div>
                            </div>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span>Selecione o horário que melhor se adequa à sua disponibilidade</span>
                    </p>
                </div>

                <!-- Botões -->
                <div class="flex gap-3 pt-4">
                    <button type="submit" 
                            class="flex-1 bg-green-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-check mr-2"></i>
                        Confirmar Compra e Agendar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validação de fim de semana
document.getElementById('data_agendamento').addEventListener('change', function(e) {
    const selectedDate = new Date(e.target.value);
    const dayOfWeek = selectedDate.getDay();
    
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        alert('Por favor, selecione um dia útil (segunda a sexta-feira)');
        e.target.value = '';
        return;
    }
});

// Estilização dos horários selecionados
document.querySelectorAll('.horario-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remover seleção anterior
        document.querySelectorAll('.horario-card').forEach(card => {
            card.classList.remove('border-green-500', 'bg-green-50');
            card.classList.add('border-gray-200');
        });
        
        // Marcar o selecionado
        if (this.checked) {
            const card = this.closest('label').querySelector('.horario-card');
            card.classList.remove('border-gray-200');
            card.classList.add('border-green-500', 'bg-green-50');
        }
    });
});

// Validação do formulário
document.getElementById('formCompraPeca').addEventListener('submit', function(e) {
    const data = document.getElementById('data_agendamento').value;
    const horario = document.querySelector('input[name="horario_selecionado"]:checked');
    
    if (!data) {
        e.preventDefault();
        alert('Por favor, selecione uma data');
        return false;
    }
    
    if (!horario) {
        e.preventDefault();
        alert('Por favor, selecione um horário');
        return false;
    }
    
    // Validar se não é fim de semana
    const selectedDate = new Date(data);
    const dayOfWeek = selectedDate.getDay();
    
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        e.preventDefault();
        alert('Por favor, selecione um dia útil (segunda a sexta-feira)');
        return false;
    }
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/public.php';
?>

