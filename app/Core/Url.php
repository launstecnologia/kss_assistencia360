<?php

namespace App\Core;

class Url
{
    private static ?string $baseUrl = null;
    private static ?string $basePath = null;
    
    public static function init(): void
    {
        $config = require __DIR__ . '/../Config/config.php';
        self::$baseUrl = $config['app']['url'];
        self::$basePath = $config['app']['base_path'];
    }
    
    public static function base(): string
    {
        if (self::$baseUrl === null) {
            self::init();
        }
        
        return rtrim(self::$baseUrl, '/');
    }
    
    public static function path(): string
    {
        if (self::$basePath === null) {
            self::init();
        }
        
        // Retornar o base path sem a barra inicial
        return self::$basePath === '/' ? '' : trim(self::$basePath, '/');
    }
    
    public static function to(string $path = ''): string
    {
        // Detectar URL base automaticamente
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Se estiver na porta 8000 (desenvolvimento), usar porta 8000
        if (strpos($host, ':8000') !== false || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 8000)) {
            $host = 'localhost:8000';
        }
        
        $base = $protocol . '://' . $host;
        $pathPrefix = self::path();
        
        // Se estiver em produção (sem pasta), usar apenas a URL base
        if ($pathPrefix === '' || $pathPrefix === '/') {
            return $base . '/' . ltrim($path, '/');
        }
        
        // Se estiver em desenvolvimento (com pasta), usar base_path
        return $base . '/' . ltrim($path, '/');
    }
    
    public static function asset(string $path): string
    {
        return self::to('Public/assets/' . ltrim($path, '/'));
    }
    
    public static function current(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return $protocol . '://' . $host . $uri;
    }
    
    public static function previous(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? self::to();
    }
    
    public static function route(string $name, array $params = []): string
    {
        // Implementar sistema de rotas nomeadas se necessário
        return self::to($name);
    }
}
