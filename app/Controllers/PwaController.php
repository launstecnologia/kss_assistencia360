<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Solicitacao;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Imobiliaria;

class PwaController extends Controller
{
    private Solicitacao $solicitacaoModel;
    private Categoria $categoriaModel;
    private Subcategoria $subcategoriaModel;
    private Imobiliaria $imobiliariaModel;

    public function __construct()
    {
        $this->solicitacaoModel = new Solicitacao();
        $this->categoriaModel = new Categoria();
        $this->subcategoriaModel = new Subcategoria();
        $this->imobiliariaModel = new Imobiliaria();
    }

    public function index(): void
    {
        $this->view('pwa.index');
    }

    public function login(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/pwa/solicitar');
        }

        $this->view('pwa.login');
    }

    public function authenticate(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/pwa/login');
        }

        $instancia = $this->input('instancia');
        $locatarioId = $this->input('locatario_id');

        $errors = $this->validate([
            'instancia' => 'required',
            'locatario_id' => 'required'
        ], ['instancia' => $instancia, 'locatario_id' => $locatarioId]);

        if (!empty($errors)) {
            $this->view('pwa.login', ['errors' => $errors]);
            return;
        }

        // Buscar imobiliária pela instância
        $imobiliaria = $this->imobiliariaModel->findByInstancia($instancia);
        
        if (!$imobiliaria) {
            $this->view('pwa.login', ['error' => 'Instância não encontrada']);
            return;
        }

        // Aqui seria feita a autenticação via API KSI
        // Por enquanto, simulamos uma autenticação bem-sucedida
        $locatario = [
            'id' => $locatarioId,
            'nome' => 'Locatário Teste',
            'telefone' => '11999999999',
            'email' => 'locatario@teste.com',
            'imobiliaria_id' => $imobiliaria['id']
        ];

        $_SESSION['pwa_user'] = $locatario;
        $_SESSION['pwa_authenticated'] = true;

        $this->redirect('/pwa/solicitar');
    }

    public function solicitar(): void
    {
        if (!$this->isPwaAuthenticated()) {
            $this->redirect('/pwa/login');
        }

        $categorias = $this->categoriaModel->getAtivas();
        
        $this->view('pwa.solicitar', [
            'categorias' => $categorias,
            'user' => $_SESSION['pwa_user']
        ]);
    }

    public function createSolicitacao(): void
    {
        if (!$this->isPost() || !$this->isPwaAuthenticated()) {
            $this->redirect('/pwa/solicitar');
        }

        $user = $_SESSION['pwa_user'];
        
        $data = [
            'imobiliaria_id' => $user['imobiliaria_id'],
            'categoria_id' => $this->input('categoria_id'),
            'subcategoria_id' => $this->input('subcategoria_id'),
            'locatario_id' => $user['id'],
            'locatario_nome' => $user['nome'],
            'locatario_telefone' => $user['telefone'],
            'locatario_email' => $user['email'],
            'imovel_endereco' => $this->input('imovel_endereco'),
            'imovel_numero' => $this->input('imovel_numero'),
            'imovel_complemento' => $this->input('imovel_complemento'),
            'imovel_bairro' => $this->input('imovel_bairro'),
            'imovel_cidade' => $this->input('imovel_cidade'),
            'imovel_estado' => $this->input('imovel_estado'),
            'imovel_cep' => $this->input('imovel_cep'),
            'descricao_problema' => $this->input('descricao_problema'),
            'data_agendamento' => $this->input('data_agendamento'),
            'horario_agendamento' => $this->input('horario_agendamento'),
            'prioridade' => $this->input('prioridade', 'NORMAL')
        ];

        $errors = $this->validate([
            'categoria_id' => 'required',
            'subcategoria_id' => 'required',
            'imovel_endereco' => 'required|min:5',
            'descricao_problema' => 'required|min:10',
            'data_agendamento' => 'required',
            'horario_agendamento' => 'required'
        ], $data);

        if (!empty($errors)) {
            $categorias = $this->categoriaModel->getAtivas();
            $subcategorias = $this->subcategoriaModel->getByCategoria($data['categoria_id']);
            
            $this->view('pwa.solicitar', [
                'categorias' => $categorias,
                'subcategorias' => $subcategorias,
                'user' => $user,
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        try {
            $solicitacaoId = $this->solicitacaoModel->create($data);
            
            // Aqui seria implementado o upload de fotos
            // e o envio de notificações
            
            $this->redirect("/pwa/solicitacao/$solicitacaoId");
        } catch (\Exception $e) {
            $categorias = $this->categoriaModel->getAtivas();
            
            $this->view('pwa.solicitar', [
                'categorias' => $categorias,
                'user' => $user,
                'error' => 'Erro ao criar solicitação: ' . $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    public function solicitacoes(): void
    {
        if (!$this->isPwaAuthenticated()) {
            $this->redirect('/pwa/login');
        }

        $user = $_SESSION['pwa_user'];
        $solicitacoes = $this->solicitacaoModel->getByLocatario($user['id']);

        $this->view('pwa.solicitacoes', [
            'solicitacoes' => $solicitacoes,
            'user' => $user
        ]);
    }

    public function showSolicitacao(int $id): void
    {
        if (!$this->isPwaAuthenticated()) {
            $this->redirect('/pwa/login');
        }

        $user = $_SESSION['pwa_user'];
        $solicitacao = $this->solicitacaoModel->getDetalhes($id);
        
        if (!$solicitacao || $solicitacao['locatario_id'] != $user['id']) {
            $this->view('errors.404');
            return;
        }

        $fotos = $this->solicitacaoModel->getFotos($id);
        $historico = $this->solicitacaoModel->getHistoricoStatus($id);

        $this->view('pwa.solicitacao', [
            'solicitacao' => $solicitacao,
            'fotos' => $fotos,
            'historico' => $historico,
            'user' => $user
        ]);
    }

    private function isPwaAuthenticated(): bool
    {
        return isset($_SESSION['pwa_authenticated']) && $_SESSION['pwa_authenticated'] === true;
    }
}
