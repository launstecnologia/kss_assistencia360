<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Assistência 360°' ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('favicon.ico') ?>">
    
    <style>
        /* Customizações específicas para o locatário */
        .locatario-gradient {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
        }
        
        .status-nova { @apply bg-blue-100 text-blue-800; }
        .status-buscando { @apply bg-yellow-100 text-yellow-800; }
        .status-agendado { @apply bg-green-100 text-green-800; }
        .status-pendencias { @apply bg-red-100 text-red-800; }
        .status-concluido { @apply bg-gray-100 text-gray-800; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <span class="text-green-600 font-bold text-xl">KSS</span>
                        <span class="text-gray-600 text-sm">ASSISTÊNCIA 360°</span>
                    </div>
                </div>
                
                <!-- User Info -->
                <?php if (isset($_SESSION['locatario'])): ?>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($_SESSION['locatario']['nome']) ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?= htmlspecialchars($_SESSION['locatario']['imobiliaria_nome']) ?>
                            </p>
                        </div>
                        
                        <!-- Menu Dropdown -->
                        <div class="relative">
                            <button onclick="toggleUserMenu()" 
                                    class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                                <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="<?= url($_SESSION['locatario']['instancia'] . '/dashboard') ?>" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-home mr-2"></i>
                                    Dashboard
                                </a>
                                <a href="<?= url($_SESSION['locatario']['instancia'] . '/solicitacoes') ?>" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-list mr-2"></i>
                                    Minhas Solicitações
                                </a>
                                <a href="<?= url($_SESSION['locatario']['instancia'] . '/perfil') ?>" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>
                                    Meu Perfil
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="<?= url($_SESSION['locatario']['instancia'] . '/logout') ?>" 
                                   class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Sair
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-500 text-sm">
                <p>&copy; <?= date('Y') ?> KSS Assistência 360°. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Toggle user menu
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('user-menu');
            const button = event.target.closest('button');
            
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleUserMenu') === -1) {
                menu.classList.add('hidden');
            }
        });
        
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-red-50, .bg-green-50, .bg-yellow-50, .bg-blue-50');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
