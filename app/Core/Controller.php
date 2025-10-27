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
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
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
        return isset($_SESSION['user_id']);
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
}
