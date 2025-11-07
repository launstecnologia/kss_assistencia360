<?php
/**
 * KSS Seguros - Configurações da Aplicação
 * Carrega configurações do arquivo .env
 */

// Função para carregar variáveis do .env (apenas se não existir)
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return [];
        }
        
        $content = file_get_contents($path);
        
        // Detectar e converter encoding UTF-16 para UTF-8
        // UTF-16 LE BOM: FF FE
        // UTF-16 BE BOM: FE FF
        if (substr($content, 0, 2) === "\xFF\xFE") {
            // UTF-16 Little Endian
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
        } elseif (substr($content, 0, 2) === "\xFE\xFF") {
            // UTF-16 Big Endian
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16BE');
        } elseif (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            // UTF-8 BOM
            $content = substr($content, 3);
        }
        
        // Normalizar quebras de linha (Windows \r\n, Unix \n, Mac \r)
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);
        $env = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            if (strpos($line, '=') === false) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remover aspas se houver
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            $env[$name] = $value;
        }
        
        return $env;
    }
}

// Carregar variáveis do .env
$env = loadEnv(__DIR__ . '/../../.env');

// Função helper para obter variável do .env (apenas se não existir)
if (!function_exists('env')) {
    function env($key, $default = null) {
        global $env;
        return isset($env[$key]) ? $env[$key] : $default;
    }
}

// Configuração de ambiente (definidas no index.php)

return [
    'app' => [
        'name' => env('APP_NAME', 'KSS Seguros'),
        'version' => '1.0.0',
        'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : env('APP_ENVIRONMENT', 'development'),
        'debug' => defined('DEBUG') ? DEBUG : (env('APP_DEBUG', 'true') === 'true'),
        'url' => defined('URL') ? str_replace('\\', '', URL) : str_replace('\\', '', env('APP_URL', 'http://localhost' . env('APP_BASE_PATH', '/kss'))),
        'base_path' => defined('FOLDER') ? str_replace('\\', '/', trim(FOLDER, '/\\')) : str_replace('\\', '/', trim(env('APP_BASE_PATH', '/kss'), '/\\')),
        'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),
    ],
    
    'database' => [
        'host' => env('DB_HOST', '186.209.113.149'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'launs_kss'),
        'username' => env('DB_USERNAME', 'launs_kss'),
        'password' => env('DB_PASSWORD', '117910Campi!25'),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],
    
    'session' => [
        'name' => 'KSS_SESSION',
        'lifetime' => env('SESSION_LIFETIME', 3600), // 1 hora
        'secure' => (defined('ENVIRONMENT') ? ENVIRONMENT : env('APP_ENVIRONMENT', 'development')) === 'production',
        'httponly' => true,
        'samesite' => 'Strict',
    ],
    
    'security' => [
        'csrf_token_name' => '_token',
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutos
    ],
    
    'upload' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 10 * 1024 * 1024), // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'path' => __DIR__ . '/../storage/uploads/',
    ],
    
    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', 'true') === 'true',
        'api_url' => env('WHATSAPP_API_URL', ''),
        'instance' => env('WHATSAPP_INSTANCE', ''),
        'token' => env('WHATSAPP_TOKEN', ''),
        'api_key' => env('WHATSAPP_API_KEY', ''),
        // URL base para links enviados nas mensagens WhatsApp (links de token, confirmação, etc.)
        'links_base_url' => env('WHATSAPP_LINKS_BASE_URL', 'https:///kss.launs.com.br'),
    ],
    
    'ksi_api' => [
        'url' => env('KSI_API_URL', ''),
        'token' => env('KSI_API_TOKEN', ''),
        'timeout' => env('KSI_API_TIMEOUT', 30),
    ],
    
    'cache' => [
        'ttl' => env('CACHE_TTL', 300),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],
    
    'routes' => [
        'pwa' => '/',
        'operador' => '/operador',
        'admin' => '/admin',
        'login' => '/login',
    ]
];