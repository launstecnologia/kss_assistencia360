<?php
$title = $title ?? 'Reagendar Horário';
ob_start();
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                    <i class="fas fa-calendar-alt text-yellow-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Reagendar Horário</h2>
                <p class="text-gray-600 mt-2">Selecione novas datas e horários preferenciais</p>
            </div>

            <!-- Informações da Solicitação -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">Informações do Agendamento Atual</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Protocolo:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($tokenData['protocol'] ?? $solicitacao['numero_solicitacao'] ?? 'N/A') ?></span>
                    </div>
                    <?php if ($tokenData['scheduled_date']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Data Atual:</span>
                        <span class="font-semibold text-gray-900"><?= date('d/m/Y', strtotime($tokenData['scheduled_date'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($tokenData['scheduled_time']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Horário Atual:</span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($tokenData['scheduled_time']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Formulário de Reagendamento -->
            <form method="POST" action="<?= url('reagendamento-horario?token=' . urlencode($token)) ?>" id="formReagendamento" class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Selecione até 3 datas e horários preferenciais. Após sua escolha, entraremos em contato para confirmar o novo horário.
                    </p>
                </div>

                <!-- Seleção de Datas e Horários -->
                <div id="horarios-container" class="space-y-4">
                    <!-- Horários serão adicionados aqui dinamicamente -->
                </div>

                <!-- Botão para adicionar mais horários -->
                <button type="button" 
                        onclick="adicionarHorario()" 
                        id="btnAdicionarHorario"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i>
                    Adicionar Outro Horário
                </button>

                <input type="hidden" name="novas_datas" id="novas_datas" value="[]">

                <div class="flex gap-3 mt-6">
                    <a href="<?= url('confirmacao-horario?token=' . urlencode($token)) ?>" 
                       class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition-colors duration-200 text-center">
                        Voltar
                    </a>
                    <button type="submit" 
                            class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Solicitar Reagendamento
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-sm text-gray-500">
            <div class="flex items-center justify-center mb-2">
                <?= kss_logo('', 'KSS ASSISTÊNCIA 360°', 24) ?>
            </div>
            <p>KSS Seguros - Assistência 360°</p>
        </div>
    </div>
</div>

<script>
let horarioCount = 0;
const maxHorarios = 3;

function adicionarHorario() {
    if (horarioCount >= maxHorarios) {
        alert('Você pode selecionar no máximo 3 horários');
        return;
    }

    const container = document.getElementById('horarios-container');
    const horarioDiv = document.createElement('div');
    horarioDiv.className = 'bg-gray-50 border border-gray-200 rounded-lg p-4 horario-item';
    horarioDiv.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-gray-900">Horário ${horarioCount + 1}</h4>
            ${horarioCount > 0 ? '<button type="button" onclick="removerHorario(this)" class="text-red-600 hover:text-red-800"><i class="fas fa-times"></i></button>' : ''}
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Data</label>
                <input type="date" 
                       class="data-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500"
                       min="${new Date(Date.now() + 86400000).toISOString().split('T')[0]}"
                       max="${new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0]}"
                       required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-2">Horário</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="horario_${horarioCount}" value="08:00-11:00" class="sr-only horario-radio">
                        <div class="border-2 border-gray-200 rounded-lg p-2 text-center hover:border-yellow-300 transition-colors horario-card">
                            <div class="text-xs font-medium text-gray-900">08h00 às 11h00</div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="horario_${horarioCount}" value="11:00-14:00" class="sr-only horario-radio">
                        <div class="border-2 border-gray-200 rounded-lg p-2 text-center hover:border-yellow-300 transition-colors horario-card">
                            <div class="text-xs font-medium text-gray-900">11h00 às 14h00</div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="horario_${horarioCount}" value="14:00-17:00" class="sr-only horario-radio">
                        <div class="border-2 border-gray-200 rounded-lg p-2 text-center hover:border-yellow-300 transition-colors horario-card">
                            <div class="text-xs font-medium text-gray-900">14h00 às 17h00</div>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="horario_${horarioCount}" value="17:00-20:00" class="sr-only horario-radio">
                        <div class="border-2 border-gray-200 rounded-lg p-2 text-center hover:border-yellow-300 transition-colors horario-card">
                            <div class="text-xs font-medium text-gray-900">17h00 às 20h00</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(horarioDiv);
    horarioCount++;
    
    if (horarioCount >= maxHorarios) {
        document.getElementById('btnAdicionarHorario').style.display = 'none';
    }
    
    // Adicionar listeners para estilização dos cards
    adicionarListenersHorarios();
}

function removerHorario(btn) {
    btn.closest('.horario-item').remove();
    horarioCount--;
    
    if (horarioCount < maxHorarios) {
        document.getElementById('btnAdicionarHorario').style.display = 'block';
    }
    
    atualizarHorarios();
}

function adicionarListenersHorarios() {
    // Estilização dos cards de horário
    document.querySelectorAll('.horario-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const card = this.closest('label').querySelector('.horario-card');
            const allCards = this.closest('.grid').querySelectorAll('.horario-card');
            
            allCards.forEach(c => {
                c.classList.remove('border-yellow-500', 'bg-yellow-50');
                c.classList.add('border-gray-200');
            });
            
            if (this.checked) {
                card.classList.remove('border-gray-200');
                card.classList.add('border-yellow-500', 'bg-yellow-50');
            }
        });
    });
}

function atualizarHorarios() {
    const horarios = [];
    const items = document.querySelectorAll('.horario-item');
    
    items.forEach(item => {
        const dataInput = item.querySelector('.data-input');
        const horarioRadio = item.querySelector('.horario-radio:checked');
        
        if (dataInput && dataInput.value && horarioRadio) {
            const data = new Date(dataInput.value + 'T' + horarioRadio.value.split('-')[0] + ':00');
            const dia = String(data.getDate()).padStart(2, '0');
            const mes = String(data.getMonth() + 1).padStart(2, '0');
            const ano = data.getFullYear();
            const [horaInicio, horaFim] = horarioRadio.value.split('-');
            
            horarios.push(`${dia}/${mes}/${ano} - ${horaInicio}:00-${horaFim}:00`);
        }
    });
    
    document.getElementById('novas_datas').value = JSON.stringify(horarios);
}

// Adicionar listener ao formulário
document.getElementById('formReagendamento').addEventListener('submit', function(e) {
    atualizarHorarios();
    const horarios = JSON.parse(document.getElementById('novas_datas').value);
    
    if (horarios.length === 0) {
        e.preventDefault();
        alert('Por favor, selecione pelo menos uma data e horário');
        return false;
    }
});

// Adicionar listeners para atualizar horários quando mudar
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('data-input') || e.target.classList.contains('horario-radio')) {
        atualizarHorarios();
    }
});

// Adicionar primeiro horário automaticamente
adicionarHorario();
</script>

<style>
.horario-card {
    transition: all 0.2s;
}
</style>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/public.php';
?>

