<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

class AuthController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function showLogin(): void
    {
        if ($this->isAuthenticated()) {
            redirect(url('admin/dashboard'));
        }

        $this->view('auth.login');
    }

    public function login(): void
    {
        if (!$this->isPost()) {
            redirect(url('login'));
        }

        $email = $this->input('email');
        $senha = $this->input('senha');

        $errors = $this->validate([
            'email' => 'required|email',
            'senha' => 'required|min:6'
        ], ['email' => $email, 'senha' => $senha]);

        if (!empty($errors)) {
            $this->view('auth.login', ['errors' => $errors, 'email' => $email]);
            return;
        }

        $user = $this->usuarioModel->authenticate($email, $senha);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            $_SESSION['user_level'] = $user['nivel_permissao'];
            
            redirect(url('admin/dashboard'));
        } else {
            $this->view('auth.login', [
                'error' => 'Email ou senha incorretos',
                'email' => $email
            ]);
        }
    }

    public function logout(): void
    {
        session_destroy();
        redirect(url());
    }

    public function showRegister(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/admin/dashboard');
        }

        $this->view('auth.register');
    }

    public function register(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/register');
        }

        $data = [
            'nome' => $this->input('nome'),
            'email' => $this->input('email'),
            'telefone' => $this->input('telefone'),
            'senha' => $this->input('senha'),
            'nivel_permissao' => 'OPERADOR',
            'status' => 'ATIVO'
        ];

        $errors = $this->validate([
            'nome' => 'required|min:3',
            'email' => 'required|email',
            'telefone' => 'required',
            'senha' => 'required|min:6'
        ], $data);

        if (!empty($errors)) {
            $this->view('auth.register', ['errors' => $errors, 'data' => $data]);
            return;
        }

        // Verificar se email já existe
        if ($this->usuarioModel->findByEmail($data['email'])) {
            $this->view('auth.register', [
                'error' => 'Este email já está cadastrado',
                'data' => $data
            ]);
            return;
        }

        try {
            $userId = $this->usuarioModel->create($data);
            
            // Fazer login automático
            $user = $this->usuarioModel->find($userId);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            $_SESSION['user_level'] = $user['nivel_permissao'];
            
            $this->redirect('/admin/dashboard');
        } catch (\Exception $e) {
            $this->view('auth.register', [
                'error' => 'Erro ao criar conta: ' . $e->getMessage(),
                'data' => $data
            ]);
        }
    }
}
