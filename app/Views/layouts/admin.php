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
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg sidebar-transition transform -translate-x-full lg:translate-x-0">
        <div class="flex items-center justify-center h-16 bg-blue-600">
            <h1 class="text-white text-xl font-bold"><?= $app['name'] ?></h1>
        </div>
        
        <nav class="mt-8">
            <div class="px-4 space-y-2">
                <a href="<?= url('admin/dashboard') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'dashboard' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                
                <a href="<?= url('admin/solicitacoes') ?>" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 <?= $currentPage === 'solicitacoes' ? 'bg-blue-50 text-blue-700' : '' ?>">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    Solicitações
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
                <?php endif; ?>
            </div>
        </nav>
        
        <div class="absolute bottom-0 w-full p-4">
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
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-red-50, .bg-green-50');
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
