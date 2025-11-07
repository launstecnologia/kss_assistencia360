<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' : '' ?><?= $app['name'] ?></title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        /* Dark mode overrides (uses .dark on <html>) */
        .dark body { background-color: #050a12; color: #e5e7eb; }
        .dark .bg-white { background-color: #0b1220 !important; }
        .dark .bg-gray-50 { background-color: #0a0f1a !important; }
        .dark .bg-gray-100 { background-color: #0a0f1a !important; }
        .dark .text-gray-900 { color: #e5e7eb !important; }
        .dark .text-gray-700 { color: #d1d5db !important; }
        .dark .text-gray-600 { color: #9ca3af !important; }
        .dark .text-gray-500 { color: #9ca3af !important; }
        .dark .border-gray-200 { border-color: #243042 !important; }
        .dark .border-gray-100 { border-color: #243042 !important; }
        .dark .border-gray-300 { border-color: #334155 !important; }
        .dark .hover\:bg-gray-100:hover { background-color: #1f2937 !important; }
        .dark .hover\:bg-gray-50:hover { background-color: #111827 !important; }
        .dark .hover\:bg-white:hover { background-color: #0f172a !important; }
        .dark .bg-blue-50 { background-color: #0b2a4a !important; }
        .dark .text-blue-700 { color: #93c5fd !important; }
        .dark .shadow-lg { box-shadow: 0 10px 25px rgba(0,0,0,0.7) !important; }
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
        /* Improve select appearance and arrow in dark */
        .dark select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 2.25rem !important; /* room for arrow */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23cbd5e1' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem 1rem;
            color-scheme: dark; /* dark native dropdown on supported browsers */
        }
        .dark select:focus,
        .dark input:focus,
        .dark textarea:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.35) !important; /* blue-500 ring */
            border-color: #3b82f6 !important;
        }
        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #6b7280 !important;
        }
        .dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) { border-color: #1f2937 !important; }
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
        
        .kanban-column {
            min-height: 500px;
        }
        
        .drag-item {
            transition: all 0.2s ease-in-out;
        }
        
        .drag-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .drag-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-nova-solicitacao {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        
        .status-buscando-prestador {
            background-color: #FEF3C7;
            color: #92400E;
        }
        
        .status-servico-agendado {
            background-color: #D1FAE5;
            color: #065F46;
        }
        
        .status-em-andamento {
            background-color: #E0E7FF;
            color: #3730A3;
        }
        
        .status-concluido {
            background-color: #D1FAE5;
            color: #065F46;
        }
        
        .status-cancelado {
            background-color: #FEE2E2;
            color: #991B1B;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg sidebar-transition transform -translate-x-full lg:translate-x-0 flex flex-col">
        <div class="flex items-center justify-center h-16 px-4 flex-shrink-0">
            <?php 
            $logoUrl = \App\Core\Url::kssLogo();
            if (!empty($logoUrl)): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="KSS ASSISTÊNCIA 360°" class="h-10 w-auto" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="flex items-center space-x-2" style="display: none;">
                    <span class="text-green-600 font-bold text-xl">KSS</span>
                    <span class="text-gray-600 text-sm">ASSISTÊNCIA 360°</span>
                </div>
            <?php else: ?>
                <div class="flex items-center space-x-2">
                    <span class="text-green-600 font-bold text-xl">KSS</span>
                    <span class="text-gray-600 text-sm">ASSISTÊNCIA 360°</span>
                </div>
            <?php endif; ?>
        </div>
        
        <nav class="mt-8 flex-1 overflow-y-auto">
            <div class="px-4 space-y-2 pb-4">
                <a href="<?= url('admin/dashboard') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'dashboard' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                
                <a href="<?= url('admin/kanban') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'kanban' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-columns mr-3"></i>
                    Kanban
                </a>
                
                <a href="<?= url('admin/solicitacoes') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'solicitacoes' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    Solicitações
                </a>
                
                <a href="<?= url('admin/templates-whatsapp') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'templates-whatsapp' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-file-code mr-3"></i>
                    Templates WhatsApp
                </a>
                <a href="<?= url('admin/whatsapp-instances') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'whatsapp-instances' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-mobile-alt mr-3"></i>
                    Instâncias WhatsApp
                </a>

                <a href="<?= url('admin/solicitacoes-manuais') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'solicitacoes-manuais' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-file-alt mr-3"></i>
                    Solicitações Manuais
                    <?php
                    // Contador de solicitações manuais não migradas
                    try {
                        $solicitacaoManualModel = new \App\Models\SolicitacaoManual();
                        $naoMigradas = count($solicitacaoManualModel->getNaoMigradas(999));
                        if ($naoMigradas > 0):
                    ?>
                        <span class="ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-yellow-800 bg-yellow-200 rounded-full">
                            <?= $naoMigradas ?>
                        </span>
                    <?php 
                        endif;
                    } catch (\Exception $e) {
                        // Silencioso se der erro
                    }
                    ?>
                </a>
                
                <?php if ($user && $user['nivel_permissao'] === 'ADMINISTRADOR'): ?>
                <div class="pt-4">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administração</h3>
                </div>
                
                <a href="<?= url('admin/imobiliarias') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'imobiliarias' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-building mr-3"></i>
                    Imobiliárias
                </a>
                
                <a href="<?= url('admin/usuarios') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'usuarios' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-users mr-3"></i>
                    Usuários
                </a>
                
                <a href="<?= url('admin/categorias') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'categorias' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-tags mr-3"></i>
                    Categorias
                </a>
                
                <a href="<?= url('admin/status') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'status' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-list mr-3"></i>
                    Status
                </a>
                
                <a href="<?= url('admin/condicoes') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'condicoes' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-tag mr-3"></i>
                    Condições
                </a>
                <?php endif; ?>
            </div>
        </nav>
        
        <div class="w-full p-4 flex-shrink-0 border-t border-gray-200">
            <div class="flex items-center px-4 py-2 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900"><?= $user['nome'] ?? 'Usuário' ?></p>
                    <p class="text-xs text-gray-500"><?= $user['nivel_permissao'] ?? 'OPERADOR' ?></p>
                </div>
                <a href="<?= url('logout') ?>" class="ml-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="ml-4 text-2xl font-semibold text-gray-900"><?= $pageTitle ?? 'Dashboard' ?></h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Dark mode toggle -->
                    <button id="theme-toggle" class="text-gray-500 hover:text-gray-700" title="Alternar tema">
                        <i id="theme-toggle-icon" class="fas fa-moon text-xl"></i>
                    </button>
                    <!-- Notifications -->
                    <button class="relative text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                    </button>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button class="flex items-center text-gray-500 hover:text-gray-700">
                            <i class="fas fa-cog text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-6">
            <?php 
            // Mostrar mensagens flash
            if (isset($_SESSION['flash_message'])): 
                $flashType = $_SESSION['flash_type'] ?? 'info';
                $bgColor = match($flashType) {
                    'success' => 'bg-green-50 border-green-200 text-green-700',
                    'error' => 'bg-red-50 border-red-200 text-red-700',
                    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
                    default => 'bg-blue-50 border-blue-200 text-blue-700'
                };
                $icon = match($flashType) {
                    'success' => 'fa-check-circle',
                    'error' => 'fa-exclamation-circle',
                    'warning' => 'fa-exclamation-triangle',
                    default => 'fa-info-circle'
                };
            ?>
            <div class="mb-6 <?= $bgColor ?> border px-4 py-3 rounded-lg alert-message">
                <div class="flex">
                    <i class="fas <?= $icon ?> mt-1 mr-3"></i>
                    <div><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
                </div>
            </div>
            <?php 
                unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            endif; 
            ?>
            
            <?php if (isset($error) && $error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                    <div><?= $error ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($success) && $success): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <i class="fas fa-check-circle mt-1 mr-3"></i>
                    <div><?= $success ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($errors) && !empty($errors)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                    <div>
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?= $content ?? '' ?>
        </main>
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black bg-opacity-50 hidden lg:hidden"></div>
    
    <!-- Scripts -->
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

        // Sidebar toggle for mobile
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
        
        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
        
        // Auto-hide alerts (APENAS mensagens com classe .alert-message)
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-message');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
