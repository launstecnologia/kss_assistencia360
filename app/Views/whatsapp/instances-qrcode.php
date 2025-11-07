<?php
ob_start();
?>

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-gray-800">Conectar Instância: <?= htmlspecialchars($instance['nome']) ?></h3>
        <a href="<?= url('admin/whatsapp-instances') ?>" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    <div class="max-w-md mx-auto">
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <?php if (!empty($instance['qrcode'])): ?>
                <div class="mb-4">
                    <img id="qrcode-img" 
                         src="data:image/png;base64,<?= htmlspecialchars($instance['qrcode']) ?>" 
                         alt="QR Code WhatsApp"
                         class="mx-auto border-4 border-white shadow-lg">
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Escaneie este QR code com o WhatsApp para conectar a instância.
                </p>
                <div class="flex items-center justify-center space-x-4">
                    <button onclick="atualizarQrcode()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-sync-alt mr-2"></i>Atualizar QR Code
                    </button>
                    <button onclick="verificarStatus()" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Verificar Status
                    </button>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                    <p class="text-gray-600 mb-4">QR Code não disponível</p>
                    <button onclick="atualizarQrcode()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-sync-alt mr-2"></i>Gerar QR Code
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div id="status-info" class="mt-4 p-4 rounded-lg hidden">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span id="status-message"></span>
            </div>
        </div>

        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800">
                <i class="fas fa-lightbulb mr-2"></i>
                <strong>Dica:</strong> O QR code expira após alguns minutos. Se não conseguir escanear, clique em "Atualizar QR Code".
            </p>
        </div>
    </div>
</div>

<script>
let statusInterval = null;

function atualizarQrcode() {
    location.reload();
}

function verificarStatus() {
    const statusInfo = document.getElementById('status-info');
    const statusMessage = document.getElementById('status-message');
    
    statusInfo.classList.remove('hidden');
    statusMessage.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Verificando...';
    statusInfo.className = 'mt-4 p-4 rounded-lg bg-blue-50 border border-blue-200';
    
    fetch('<?= url('admin/whatsapp-instances/' . $instance['id'] . '/verificar-status') ?>')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.status === 'CONECTADO') {
                    statusInfo.className = 'mt-4 p-4 rounded-lg bg-green-50 border border-green-200';
                    statusMessage.innerHTML = '<i class="fas fa-check-circle text-green-600 mr-2"></i>Instância conectada! Número: ' + (data.numero_whatsapp || 'N/A');
                    
                    // Parar verificação automática
                    if (statusInterval) {
                        clearInterval(statusInterval);
                    }
                    
                    // Redirecionar após 2 segundos
                    setTimeout(() => {
                        window.location.href = '<?= url('admin/whatsapp-instances') ?>';
                    }, 2000);
                } else if (data.status === 'CONECTANDO') {
                    statusInfo.className = 'mt-4 p-4 rounded-lg bg-yellow-50 border border-yellow-200';
                    statusMessage.innerHTML = '<i class="fas fa-clock text-yellow-600 mr-2"></i>Aguardando conexão...';
                    
                    // Iniciar verificação automática
                    if (!statusInterval) {
                        statusInterval = setInterval(verificarStatus, 3000);
                    }
                } else {
                    statusInfo.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200';
                    statusMessage.innerHTML = '<i class="fas fa-times-circle text-red-600 mr-2"></i>Instância desconectada. Atualize o QR code.';
                }
            } else {
                statusInfo.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200';
                statusMessage.innerHTML = '<i class="fas fa-exclamation-circle text-red-600 mr-2"></i>Erro: ' + (data.error || 'Erro desconhecido');
            }
        })
        .catch(err => {
            statusInfo.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200';
            statusMessage.innerHTML = '<i class="fas fa-exclamation-circle text-red-600 mr-2"></i>Erro ao verificar status';
            console.error(err);
        });
}

// Verificar status automaticamente a cada 5 segundos se estiver conectando
<?php if ($instance['status'] === 'CONECTANDO'): ?>
document.addEventListener('DOMContentLoaded', function() {
    statusInterval = setInterval(verificarStatus, 5000);
});
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>

