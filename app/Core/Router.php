<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, callable|string $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function middleware(string $name, callable $middleware): void
    {
        $this->middlewares[$name] = $middleware;
    }
    
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    public function getRoutesCount(): int
    {
        return count($this->routes);
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove a base path se existir
        $basePath = \App\Core\Url::path();
        
        if ($basePath && $basePath !== '/' && strpos($path, '/' . $basePath) === 0) {
            $path = substr($path, strlen('/' . $basePath));
        }
        
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                // Debug - remover em produção
                if ($_ENV['APP_DEBUG'] ?? true) {
                    error_log("Router Debug - Route matched: {$route['method']} {$route['path']}");
                }
                
                // Executar middlewares
                foreach ($route['middleware'] as $middlewareName) {
                    if (isset($this->middlewares[$middlewareName])) {
                        $result = call_user_func($this->middlewares[$middlewareName]);
                        if ($result === false) {
                            return; // Middleware bloqueou a execução
                        }
                    }
                }

                // Executar handler
                $this->executeHandler($route['handler'], $this->extractParams($route['path'], $path));
                return;
            }
        }

        // Rota não encontrada
        http_response_code(404);
        View::render('errors.404');
    }

    private function matchPath(string $routePath, string $requestPath): bool
    {
        // Debug específico para troubleshooting
        if ($_ENV['APP_DEBUG'] ?? true) {
            error_log("matchPath: '$routePath' vs '$requestPath'");
        }
        
        // Tratar rota raiz
        if ($routePath === '/' && $requestPath === '/') {
            return true;
        }
        
        // Tratar outras rotas exatas
        if ($routePath === $requestPath) {
            return true;
        }
        
        // Tratar rotas com parâmetros
        $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        $result = preg_match($routePattern, $requestPath);
        
        if ($_ENV['APP_DEBUG'] ?? true) {
            error_log("matchPath result: " . ($result ? 'true' : 'false'));
        }
        
        return $result;
    }

    private function extractParams(string $routePath, string $requestPath): array
    {
        $params = [];
        $routeSegments = explode('/', trim($routePath, '/'));
        $requestSegments = explode('/', trim($requestPath, '/'));

        foreach ($routeSegments as $index => $segment) {
            if (preg_match('/\{([^}]+)\}/', $segment, $matches)) {
                $paramName = $matches[1];
                $params[$paramName] = $requestSegments[$index] ?? null;
            }
        }

        return $params;
    }

    private function executeHandler(callable|string $handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            // Formato: Controller@method
            if (strpos($handler, '@') !== false) {
                [$controllerName, $methodName] = explode('@', $handler);
                $controllerClass = "App\\Controllers\\{$controllerName}";
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $methodName)) {
                        call_user_func_array([$controller, $methodName], $params);
                    } else {
                        throw new \Exception("Método $methodName não encontrado no controller $controllerName");
                    }
                } else {
                    throw new \Exception("Controller $controllerName não encontrado");
                }
            }
        }
    }

    public function url(string $name, array $params = []): string
    {
        // Implementar geração de URLs por nome (opcional)
        return '';
    }
}
