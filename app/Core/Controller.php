<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        // ✅ Desabilitar exibição de erros para evitar HTML na resposta
        $oldErrorReporting = error_reporting(E_ALL);
        $oldDisplayErrors = ini_set('display_errors', '0');
        
        // Limpar todos os buffers de output
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        // ✅ Validar que os dados podem ser convertidos para JSON
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            // Se falhar, criar um JSON de erro
            $json = json_encode([
                'success' => false,
                'error' => 'Erro ao serializar resposta',
                'message' => 'Dados não puderam ser convertidos para JSON'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            http_response_code(500);
        }
        
        echo $json;
        
        // Restaurar configurações anteriores
        error_reporting($oldErrorReporting);
        if ($oldDisplayErrors !== false) {
            ini_set('display_errors', $oldDisplayErrors);
        }
        
        exit;
    }

    protected function redirect(string $url): void
    {
        redirect($url);
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    protected function isGet(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET';
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        // Primeiro, verificar se há dados JSON no corpo da requisição
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (stripos($contentType, 'application/json') !== false) {
            static $jsonData = null;
            
            if ($jsonData === null) {
                $rawInput = file_get_contents('php://input');
                $jsonData = json_decode($rawInput, true) ?? [];
            }
            
            return $jsonData[$key] ?? $default;
        }
        
        // Caso contrário, usar $_REQUEST (POST, GET, COOKIE)
        return $_REQUEST[$key] ?? $default;
    }

    protected function validate(array $rules, array $data): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "O campo $field é obrigatório";
                continue;
            }

            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "O campo $field deve ser um email válido";
            }

            if (strpos($rule, 'min:') !== false) {
                preg_match('/min:(\d+)/', $rule, $matches);
                if (strlen($value) < $matches[1]) {
                    $errors[$field] = "O campo $field deve ter pelo menos {$matches[1]} caracteres";
                }
            }
        }

        return $errors;
    }

    protected function isAuthenticated(): bool
    {
        // Verificar se há sessão ativa
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        
        // Verificar cookie de "lembrar de mim"
        if (isset($_COOKIE['remember_token'])) {
            $usuarioModel = new \App\Models\Usuario();
            $user = $usuarioModel->findByRememberToken($_COOKIE['remember_token']);
            
            if ($user) {
                // Restaurar sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                $_SESSION['user_level'] = $user['nivel_permissao'];
                return true;
            } else {
                // Token inválido, remover cookie
                setcookie('remember_token', '', time() - 3600, '/');
            }
        }
        
        return false;
    }

    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect(url('login'));
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        
        if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 'ADMINISTRADOR') {
            $this->redirect(url('admin/dashboard'));
        }
    }

    protected function getUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $_SESSION['user'] ?? null;
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
