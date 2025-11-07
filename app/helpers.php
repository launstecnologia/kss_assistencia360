<?php

// Helper functions globais para URLs
if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return \App\Core\Url::to($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return \App\Core\Url::asset($path);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        return \App\Core\Url::route($name, $params);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        // Inicializar Url se não estiver inicializada
        \App\Core\Url::init();
        return \App\Core\Url::to($path);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, string $message = '', string $type = 'info'): void
    {
        if (!empty($message)) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        header("Location: $url");
        exit;
    }
}

if (!function_exists('back')) {
    function back(): void
    {
        redirect(\App\Core\Url::previous());
    }
}

// Carregar helper da logo KSS
require_once __DIR__ . '/Helpers/kss_logo.php';