<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Imobiliaria;
use App\Models\Locatario;
use App\Services\KsiApiService;

class InstanciaController extends Controller
{
    private Imobiliaria $imobiliariaModel;
    private Locatario $locatarioModel;
    private ?array $imobiliaria = null;

    public function __construct()
    {
        $this->imobiliariaModel = new Imobiliaria();
        $this->locatarioModel = new Locatario();
        
        // Buscar imobiliária pela instância da URL
        $instancia = $this->getInstanciaFromUrl();
        if ($instancia) {
            $this->imobiliaria = $this->imobiliariaModel->findByInstancia($instancia);
        }
    }

    public function index(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Instância não encontrada ou inativa'
            ]);
            return;
        }

        // Redirecionar para login se não estiver autenticado
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['instancia']) || $_SESSION['instancia'] !== $this->imobiliaria['instancia']) {
            $this->redirect('/' . $this->imobiliaria['instancia'] . '/login');
            return;
        }

        $this->redirect('/' . $this->imobiliaria['instancia'] . '/dashboard');
    }

    public function login(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Instância não encontrada ou inativa'
            ]);
            return;
        }

        // Se já estiver logado, redirecionar para dashboard
        if (isset($_SESSION['user_id']) && isset($_SESSION['instancia']) && $_SESSION['instancia'] === $this->imobiliaria['instancia']) {
            $this->redirect('/' . $this->imobiliaria['instancia'] . '/dashboard');
            return;
        }

        $this->view('instancia.login', [
            'imobiliaria' => $this->imobiliaria,
            'error' => $_GET['error'] ?? null
        ]);
    }

    public function authenticate(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->json(['error' => 'Instância não encontrada'], 404);
            return;
        }

        $cpf = $this->input('cpf');
        $senha = $this->input('senha');

        if (empty($cpf) || empty($senha)) {
            $this->redirect('/' . $this->imobiliaria['instancia'] . '/login?error=' . urlencode('CPF e senha são obrigatórios'));
            return;
        }

        try {
            // Debug: verificar dados da imobiliária
            error_log('Imobiliária encontrada: ' . json_encode($this->imobiliaria));
            
            // Autenticar via API KSI
            $ksiService = KsiApiService::fromImobiliaria($this->imobiliaria);
            $result = $ksiService->autenticarLocatario($cpf, $senha);
            
            error_log('Resultado da autenticação: ' . json_encode($result));

            if ($result['success']) {
                // Salvar/atualizar dados do locatário no banco
                $clienteData = $result['cliente'];
                $locatarioData = [
                    'imobiliaria_id' => $this->imobiliaria['id'],
                    'ksi_cliente_id' => $clienteData['id_cliente'],
                    'nome' => $clienteData['nome'],
                    'cpf' => $cpf,
                    'email' => $clienteData['email'] ?? null,
                    'telefone' => $clienteData['telefone'] ?? null,
                    'endereco_logradouro' => $clienteData['endereco'] ?? null,
                    'endereco_numero' => $clienteData['numero'] ?? null,
                    'endereco_complemento' => $clienteData['complemento'] ?? null,
                    'endereco_bairro' => $clienteData['bairro'] ?? null,
                    'endereco_cidade' => $clienteData['cidade'] ?? null,
                    'endereco_estado' => $clienteData['estado'] ?? null,
                    'endereco_cep' => $clienteData['cep'] ?? null
                ];
                
                $locatario = $this->locatarioModel->createOrUpdate($locatarioData);
                
                // Buscar imóveis do locatário via API
                $imoveisResult = $ksiService->buscarImovelLocatario($clienteData['id_cliente']);
                if ($imoveisResult['success'] && !empty($imoveisResult['imoveis'])) {
                    foreach ($imoveisResult['imoveis'] as $imovel) {
                        $imovelData = [
                            'ksi_imovel_cod' => $imovel['ImoCod'],
                            'endereco_logradouro' => $imovel['ImoEnd'],
                            'endereco_numero' => $imovel['ImoEndNum'],
                            'endereco_complemento' => $imovel['ImoEndCompl'],
                            'endereco_bairro' => $imovel['ImoBaiNom'],
                            'endereco_cidade' => $imovel['ImoCidNom'],
                            'endereco_estado' => $imovel['ImoUF'],
                            'endereco_cep' => $imovel['ImoEndCep'],
                            'contrato_cod' => $imovel['Ctr'][0]['CtrCod'] ?? null,
                            'contrato_dv' => $imovel['Ctr'][0]['CtrDV'] ?? null
                        ];
                        $this->locatarioModel->addImovel($locatario['id'], $imovelData);
                    }
                }

                // Salvar dados do usuário na sessão
                $_SESSION['user_id'] = $clienteData['id_cliente'];
                $_SESSION['locatario_id'] = $locatario['id'];
                $_SESSION['user_name'] = $clienteData['nome'];
                $_SESSION['user_cpf'] = $cpf;
                $_SESSION['instancia'] = $this->imobiliaria['instancia'];
                $_SESSION['imobiliaria_id'] = $this->imobiliaria['id'];
                $_SESSION['user_level'] = 'LOCATARIO';
                $_SESSION['cliente_data'] = $clienteData;

                $this->redirect('/' . $this->imobiliaria['instancia'] . '/dashboard');
            } else {
                $this->redirect('/' . $this->imobiliaria['instancia'] . '/login?error=' . urlencode($result['message']));
            }
        } catch (\Exception $e) {
            $this->redirect('/' . $this->imobiliaria['instancia'] . '/login?error=' . urlencode('Erro interno do servidor'));
        }
    }

    public function dashboard(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Instância não encontrada ou inativa'
            ]);
            return;
        }

        $this->checkAuth();

        // Buscar dados do locatário no banco
        $locatario = $this->locatarioModel->find($_SESSION['locatario_id']);
        $imoveis = $this->locatarioModel->getImoveis($_SESSION['locatario_id']);

        $this->view('instancia.dashboard', [
            'imobiliaria' => $this->imobiliaria,
            'locatario' => $locatario,
            'imoveis' => $imoveis,
            'cliente' => $_SESSION['cliente_data'] ?? null
        ]);
    }

    public function solicitacoes(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Instância não encontrada ou inativa'
            ]);
            return;
        }

        $this->checkAuth();

        // Buscar solicitações do locatário
        $solicitacoes = $this->getSolicitacoesLocatario();

        $this->view('instancia.solicitacoes', [
            'imobiliaria' => $this->imobiliaria,
            'solicitacoes' => $solicitacoes
        ]);
    }

    public function solicitacao(string $instancia = null, int $id = null): void
    {
        if (!$this->imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Instância não encontrada ou inativa'
            ]);
            return;
        }

        $this->checkAuth();

        // Buscar solicitação específica
        $solicitacao = $this->getSolicitacaoById($id);

        if (!$solicitacao || $solicitacao['locatario_id'] != $_SESSION['user_id']) {
            $this->view('errors.404', [
                'message' => 'Solicitação não encontrada'
            ]);
            return;
        }

        $this->view('instancia.solicitacao', [
            'imobiliaria' => $this->imobiliaria,
            'solicitacao' => $solicitacao
        ]);
    }

    public function perfil(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Instância não encontrada ou inativa'
            ]);
            return;
        }

        $this->checkAuth();

        $this->view('instancia.perfil', [
            'imobiliaria' => $this->imobiliaria,
            'cliente' => $_SESSION['cliente_data'] ?? null
        ]);
    }

    public function atualizarPerfil(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->json(['error' => 'Instância não encontrada'], 404);
            return;
        }

        $this->checkAuth();

        // Implementar atualização de perfil
        $this->json(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
    }

    public function novaSolicitacao(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Instância não encontrada ou inativa'
            ]);
            return;
        }

        $this->checkAuth();

        // Buscar categorias disponíveis
        $categorias = $this->getCategorias();

        $this->view('instancia.nova-solicitacao', [
            'imobiliaria' => $this->imobiliaria,
            'categorias' => $categorias
        ]);
    }

    public function criarSolicitacao(string $instancia = null): void
    {
        if (!$this->imobiliaria) {
            $this->json(['error' => 'Instância não encontrada'], 404);
            return;
        }

        $this->checkAuth();

        // Implementar criação de solicitação
        $this->json(['success' => true, 'message' => 'Solicitação criada com sucesso']);
    }

    public function logout(string $instancia = null): void
    {
        // Limpar sessão
        session_destroy();
        
        if ($this->imobiliaria) {
            $this->redirect('/' . $this->imobiliaria['instancia'] . '/login');
        } else {
            $this->redirect('/');
        }
    }

    private function getInstanciaFromUrl(): ?string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        return $segments[0] ?? null;
    }

    private function checkAuth(): void
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['instancia']) || $_SESSION['instancia'] !== $this->imobiliaria['instancia']) {
            $this->redirect('/' . $this->imobiliaria['instancia'] . '/login');
        }
    }

    private function getSolicitacoesLocatario(): array
    {
        // Implementar busca de solicitações do locatário
        return [];
    }

    private function getSolicitacaoById(int $id): ?array
    {
        // Implementar busca de solicitação específica
        return null;
    }

    private function getCategorias(): array
    {
        // Implementar busca de categorias
        return [];
    }
}
