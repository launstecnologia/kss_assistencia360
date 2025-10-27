<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $app['name'] ?> - Locatário</title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= $app['name'] ?>">
    
    <!-- Manifest -->
    <link rel="manifest" href="<?= \App\Core\View::url('manifest.json') ?>">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shield-alt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-bold text-gray-900"><?= $app['name'] ?></h1>
                        <p class="text-sm text-gray-500">Assistências Residenciais</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="<?= url('operador') ?>" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-user-tie mr-1"></i>
                        Operador
                    </a>
                    <a href="<?= url('admin') ?>" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-cog mr-1"></i>
                        Admin
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="max-w-md mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <div class="mx-auto h-20 w-20 bg-blue-600 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-home text-white text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                Solicite sua Assistência
            </h2>
            <p class="text-gray-600">
                Faça login para solicitar assistências para seu imóvel
            </p>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="POST" action="<?= url('pwa/login') ?>" class="space-y-6">
                <?= \App\Core\View::csrfField() ?>
                
                <div>
                    <label for="instancia" class="block text-sm font-medium text-gray-700 mb-2">
                        Instância da Imobiliária
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-building text-gray-400"></i>
                        </div>
                        <input id="instancia" name="instancia" type="text" required
                               class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite a instância da sua imobiliária">
                    </div>
                </div>
                
                <div>
                    <label for="locatario_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Seu ID de Locatário
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input id="locatario_id" name="locatario_id" type="text" required
                               class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite seu ID de locatário">
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Entrar
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Não sabe sua instância ou ID?
                </p>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800">
                    Entre em contato com sua imobiliária
                </a>
            </div>
        </div>
        
        <!-- Features -->
        <div class="mt-8 grid grid-cols-1 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-900">Atendimento Rápido</h3>
                        <p class="text-xs text-gray-500">Solicitações processadas em até 24h</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-900">Notificações WhatsApp</h3>
                        <p class="text-xs text-gray-500">Acompanhe seu atendimento em tempo real</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shield-alt text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-900">Prestadores Qualificados</h3>
                        <p class="text-xs text-gray-500">Profissionais certificados e avaliados</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="mt-12 bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="text-center">
                <p class="text-sm text-gray-500">
                    © <?= date('Y') ?> <?= $app['name'] ?>. Todos os direitos reservados.
                </p>
                <div class="mt-2">
                    <a href="<?= url('operador') ?>" class="text-xs text-gray-400 hover:text-gray-600 mr-4">
                        Operador
                    </a>
                    <a href="<?= url('admin') ?>" class="text-xs text-gray-400 hover:text-gray-600">
                        Admin
                    </a>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-red-50');
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
