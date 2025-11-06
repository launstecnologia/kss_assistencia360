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
        /* Dark mode overrides (uses .dark on <html>) */
        .dark body { background-color: #0b1220; color: #e5e7eb; }
        .dark .bg-white { background-color: #111827 !important; }
        .dark .bg-gray-50 { background-color: #0f172a !important; }
        .dark .text-gray-900 { color: #e5e7eb !important; }
        .dark .text-gray-700 { color: #d1d5db !important; }
        .dark .text-gray-600 { color: #9ca3af !important; }
        .dark .text-gray-500 { color: #9ca3af !important; }
        .dark .border-gray-100 { border-color: #1f2937 !important; }
        .dark .hover\:bg-gray-100:hover { background-color: #1f2937 !important; }
        .dark .border-gray-200 { border-color: #243042 !important; }
        .dark .border-gray-300 { border-color: #334155 !important; }
        /* Cards e opções (nova solicitação) */
        .dark .categoria-card,
        .dark .subcategoria-card {
            border-color: #334155 !important;
            background-color: #0b1220 !important;
        }
        .dark .categoria-card:hover,
        .dark .subcategoria-card:hover {
            border-color: #3b82f6 !important;
        }
        .dark .categoria-check { border-color: #475569 !important; }
        /* Form controls */
        .dark input[type="text"],
        .dark input[type="search"],
        .dark input[type="number"],
        .dark input[type="date"],
        .dark select,
        .dark textarea {
            background-color: #0f172a !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }
        .dark select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 2.25rem !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23cbd5e1' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem 1rem;
            color-scheme: dark;
        }
        .dark select:focus,
        .dark input:focus,
        .dark textarea:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.35) !important; /* green ring */
            border-color: #10b981 !important;
        }
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
                        <!-- Dark mode toggle -->
                        <button id="theme-toggle" class="text-gray-600 hover:text-gray-800" title="Alternar tema">
                            <i id="theme-toggle-icon" class="fas fa-moon"></i>
                        </button>
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
        // Theme handling
        (function() {
            const root = document.documentElement;
            const stored = localStorage.getItem('theme');
            if (stored === 'dark' || (!stored && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                root.classList.add('dark');
            }
            function updateIcon() {
                const icon = document.getElementById('theme-toggle-icon');
                if (!icon) return;
                icon.classList.remove('fa-moon','fa-sun');
                icon.classList.add(root.classList.contains('dark') ? 'fa-sun' : 'fa-moon');
            }
            document.addEventListener('DOMContentLoaded', updateIcon);
            document.addEventListener('click', function(e){
                const btn = e.target.closest('#theme-toggle');
                if (!btn) return;
                root.classList.toggle('dark');
                localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
                updateIcon();
            });
        })();

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
        
        // Auto-hide alerts (APENAS mensagens com classe .alert-message)
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-message');
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
