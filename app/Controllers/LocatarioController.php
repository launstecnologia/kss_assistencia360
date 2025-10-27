<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\KsiApiService;
use App\Models\Solicitacao;

class LocatarioController extends Controller
{
    private Solicitacao $solicitacaoModel;
    
    public function __construct()
    {
        $this->solicitacaoModel = new Solicitacao();
    }
    
    /**
     * Login do locatário
     */
    public function login(string $instancia = ''): void
    {
        if ($this->isPost()) {
            $this->processarLogin();
            return;
        }
        
        // Se a instância não foi passada como parâmetro, extrair da URL
        if (empty($instancia)) {
            $instancia = $this->getInstanciaFromUrl();
        }
        
        $imobiliaria = KsiApiService::getImobiliariaByInstancia($instancia);
        
        if (!$imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Imobiliária não encontrada'
            ]);
            return;
        }
        
        $this->view('locatario.login', [
            'imobiliaria' => $imobiliaria,
            'instancia' => $instancia
        ]);
    }
    
    /**
     * Redirect com parâmetros de query
     */
    private function redirectWithParams(string $url, array $params = []): void
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $this->redirect($url);
    }
    
    /**
     * Processar login do locatário
     */
    private function processarLogin(): void
    {
        $cpf = $this->input('cpf');
        $senha = $this->input('senha');
        $instancia = $this->input('instancia');
        
        if (empty($cpf) || empty($senha) || empty($instancia)) {
            $this->redirectWithParams(url($instancia), [
                'error' => 'Todos os campos são obrigatórios'
            ]);
            return;
        }
        
        // Buscar dados da imobiliária
        $imobiliaria = KsiApiService::getImobiliariaByInstancia($instancia);
        
        if (!$imobiliaria) {
            $this->redirectWithParams(url($instancia), [
                'error' => 'Imobiliária não encontrada'
            ]);
            return;
        }
        
        // Criar serviço da API KSI
        $ksiApi = KsiApiService::fromImobiliaria($imobiliaria);
        
        // Autenticar na API
        $resultado = $ksiApi->autenticarLocatario($cpf, $senha);
        
        if ($resultado['success']) {
            $cliente = $resultado['cliente'];
            
            // Buscar dados do imóvel
            $imovelResult = $ksiApi->buscarImovelLocatario($cliente['id_cliente']);
            
            // Salvar dados na sessão
            $_SESSION['locatario'] = [
                'id' => $cliente['id_cliente'],
                'nome' => $cliente['nome'],
                'cpf' => $cpf,
                'imobiliaria_id' => $imobiliaria['id'],
                'imobiliaria_nome' => $imobiliaria['nome'],
                'instancia' => $instancia,
                'imoveis' => $imovelResult['success'] ? $imovelResult['imoveis'] : [],
                'login_time' => time()
            ];
            
            $this->redirect(url($instancia . '/dashboard'));
        } else {
            $this->redirectWithParams(url($instancia), [
                'error' => $resultado['message']
            ]);
        }
    }
    
    /**
     * Dashboard do locatário
     */
    public function dashboard(string $instancia = ''): void
    {
        $this->requireLocatarioAuth();
        
        $locatario = $_SESSION['locatario'];
        
        // Buscar solicitações do locatário
        $solicitacoes = $this->solicitacaoModel->getByLocatario($locatario['id']);
        
        // Estatísticas
        $stats = [
            'total' => count($solicitacoes),
            'ativas' => count(array_filter($solicitacoes, fn($s) => !in_array($s['status_nome'], ['Concluído (NCP)', 'Cancelado', 'Expirado']))),
            'concluidas' => count(array_filter($solicitacoes, fn($s) => $s['status_nome'] === 'Concluído (NCP)'))
        ];
        
        $this->view('locatario.dashboard', [
            'locatario' => $locatario,
            'solicitacoes' => $solicitacoes,
            'stats' => $stats
        ]);
    }
    
    /**
     * Lista de solicitações do locatário
     */
    public function solicitacoes(string $instancia = ''): void
    {
        $this->requireLocatarioAuth();
        
        $locatario = $_SESSION['locatario'];
        $solicitacoes = $this->solicitacaoModel->getByLocatario($locatario['id']);
        
        $this->view('locatario.solicitacoes', [
            'locatario' => $locatario,
            'solicitacoes' => $solicitacoes
        ]);
    }
    
    /**
     * Perfil do locatário
     */
    public function perfil(string $instancia = ''): void
    {
        $this->requireLocatarioAuth();
        
        $locatario = $_SESSION['locatario'];
        
        $this->view('locatario.perfil', [
            'locatario' => $locatario
        ]);
    }
    
    /**
     * Nova solicitação
     */
    public function novaSolicitacao(string $instancia = ''): void
    {
        $this->requireLocatarioAuth();
        
        // Limpar dados da sessão para começar uma nova solicitação
        unset($_SESSION['nova_solicitacao']);
        
        if ($this->isPost()) {
            // Debug temporário
            error_log("DEBUG: Processando POST na etapa 1");
            error_log("DEBUG: POST data: " . print_r($_POST, true));
            $this->salvarDadosEtapa(1);
            return;
        }
        
        $locatario = $_SESSION['locatario'];
        
        // Buscar categorias e subcategorias
        $categoriaModel = new \App\Models\Categoria();
        $subcategoriaModel = new \App\Models\Subcategoria();
        $categorias = $categoriaModel->getAtivas();
        $subcategorias = $subcategoriaModel->getAtivas();
        
        // Organizar subcategorias por categoria
        foreach ($categorias as &$categoria) {
            $categoria['subcategorias'] = array_filter($subcategorias, function($sub) use ($categoria) {
                return $sub['categoria_id'] == $categoria['id'];
            });
        }
        
        $this->view('locatario.nova-solicitacao', [
            'locatario' => $locatario,
            'categorias' => $categorias,
            'subcategorias' => $subcategorias
        ]);
    }
    
    /**
     * Processar etapa específica do fluxo de nova solicitação
     */
    public function processarEtapa(string $instancia, int $etapa): void
    {
        $this->requireLocatarioAuth();
        
        // Se não há dados na sessão e não é etapa 1, redirecionar para etapa 1
        if (!isset($_SESSION['nova_solicitacao']) && $etapa > 1) {
            $this->redirect(url($instancia . '/nova-solicitacao'));
            return;
        }
        
        if ($this->isPost()) {
            $this->salvarDadosEtapa($etapa);
            return;
        }
        
        // Redirecionar para próxima etapa
        $proximaEtapa = $etapa + 1;
        if ($proximaEtapa <= 5) {
            $this->redirect(url($instancia . '/nova-solicitacao/etapa/' . $proximaEtapa));
        } else {
            $this->redirect(url($instancia . '/nova-solicitacao'));
        }
    }
    
    /**
     * Salvar dados da etapa atual
     */
    private function salvarDadosEtapa(int $etapa): void
    {
        // Debug temporário
        error_log("DEBUG: Salvando dados da etapa $etapa");
        
        // Inicializar sessão de nova solicitação se não existir
        if (!isset($_SESSION['nova_solicitacao'])) {
            $_SESSION['nova_solicitacao'] = [];
        }
        
        // Salvar dados da etapa atual
        switch ($etapa) {
            case 1:
                // Validar campos obrigatórios da etapa 1
                $enderecoSelecionado = $this->input('endereco_selecionado');
                $finalidadeLocacao = $this->input('finalidade_locacao');
                $tipoImovel = $this->input('tipo_imovel');
                
                if (empty($enderecoSelecionado) || empty($finalidadeLocacao) || empty($tipoImovel)) {
                    $instancia = $this->getInstanciaFromUrl();
                    $this->redirect(url($instancia . '/nova-solicitacao'), [
                        'error' => 'Todos os campos são obrigatórios'
                    ]);
                    return;
                }
                
                $_SESSION['nova_solicitacao']['endereco_selecionado'] = $enderecoSelecionado;
                $_SESSION['nova_solicitacao']['finalidade_locacao'] = $finalidadeLocacao;
                $_SESSION['nova_solicitacao']['tipo_imovel'] = $tipoImovel;
                break;
                
            case 2:
                $_SESSION['nova_solicitacao']['categoria_id'] = $this->input('categoria_id');
                $_SESSION['nova_solicitacao']['subcategoria_id'] = $this->input('subcategoria_id');
                break;
                
            case 3:
                $_SESSION['nova_solicitacao']['local_manutencao'] = $this->input('local_manutencao');
                $_SESSION['nova_solicitacao']['descricao_problema'] = $this->input('descricao_problema');
                
                // Processar upload de fotos se houver
                if (!empty($_FILES['fotos']['name'][0])) {
                    $_SESSION['nova_solicitacao']['fotos'] = $this->processarUploadFotos();
                }
                break;
                
            case 4:
                $_SESSION['nova_solicitacao']['horarios_preferenciais'] = $this->input('horarios_preferenciais', []);
                break;
                
            case 5:
                $_SESSION['nova_solicitacao']['termo_aceite'] = $this->input('termo_aceite');
                $this->finalizarSolicitacao();
                return;
        }
        
        $_SESSION['nova_solicitacao']['etapa'] = $etapa;
        
        $instancia = $this->getInstanciaFromUrl();
        $proximaEtapa = $etapa + 1;
        
        if ($proximaEtapa <= 5) {
            $this->redirect(url($instancia . '/nova-solicitacao/etapa/' . $proximaEtapa));
        } else {
            $this->redirect(url($instancia . '/nova-solicitacao'));
        }
    }
    
    /**
     * Processar upload de fotos
     */
    private function processarUploadFotos(): array
    {
        $fotosSalvas = [];
        $uploadDir = 'Public/uploads/solicitacoes/';
        
        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($_FILES['fotos']['name'] as $key => $name) {
            if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['fotos']['tmp_name'][$key];
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $fileName = uniqid() . '_' . time() . '.' . $extension;
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    $fotosSalvas[] = $fileName;
                }
            }
        }
        
        return $fotosSalvas;
    }
    
    /**
     * Finalizar solicitação com todos os dados coletados
     */
    private function finalizarSolicitacao(): void
    {
        $dados = $_SESSION['nova_solicitacao'] ?? [];
        $locatario = $_SESSION['locatario'];
        
        // Validar dados obrigatórios
        $required = ['endereco_selecionado', 'categoria_id', 'subcategoria_id', 'descricao_problema'];
        foreach ($required as $field) {
            if (empty($dados[$field])) {
                $instancia = $this->getInstanciaFromUrl();
                $this->redirect($instancia . '/nova-solicitacao', [
                    'error' => 'Dados obrigatórios não foram preenchidos'
                ]);
                return;
            }
        }
        
        // Preparar dados para criação da solicitação
        $imovel = $locatario['imoveis'][$dados['endereco_selecionado']];
        
        $data = [
            'locatario_id' => $locatario['id'],
            'imobiliaria_id' => $locatario['imobiliaria_id'],
            'categoria_id' => $dados['categoria_id'],
            'subcategoria_id' => $dados['subcategoria_id'],
            'descricao_problema' => $dados['descricao_problema'],
            'local_manutencao' => $dados['local_manutencao'] ?? '',
            'tipo_atendimento' => $dados['finalidade_locacao'] ?? 'RESIDENCIAL',
            'tipo_imovel' => $dados['tipo_imovel'] ?? 'CASA',
            'endereco' => $imovel['endereco'],
            'numero' => $imovel['numero'],
            'complemento' => $imovel['complemento'] ?? '',
            'bairro' => $imovel['bairro'],
            'cidade' => $imovel['cidade'],
            'uf' => $imovel['uf'],
            'cep' => $imovel['cep'],
            'datas_opcoes' => json_encode($dados['horarios_preferenciais'] ?? []),
            'periodos_preferenciais' => json_encode($dados['horarios_preferenciais'] ?? []),
            'fotos' => json_encode($dados['fotos'] ?? []),
            'prioridade' => 'NORMAL'
        ];
        
        // Criar solicitação
        $solicitacaoId = $this->solicitacaoModel->create($data);
        
        $instancia = $this->getInstanciaFromUrl();
        if ($solicitacaoId) {
            // Limpar dados da sessão
            unset($_SESSION['nova_solicitacao']);
            
            $this->redirect(url($instancia . '/solicitacoes'), [
                'success' => 'Solicitação criada com sucesso!'
            ]);
        } else {
            $this->redirect($instancia . '/nova-solicitacao', [
                'error' => 'Erro ao criar solicitação. Tente novamente.'
            ]);
        }
    }
    
    /**
     * Ver detalhes de uma solicitação
     */
    public function showSolicitacao(string $instancia, int $id): void
    {
        $this->requireLocatarioAuth();
        
        $locatario = $_SESSION['locatario'];
        $solicitacao = $this->solicitacaoModel->getDetalhes($id);
        
        if (!$solicitacao || $solicitacao['locatario_id'] !== $locatario['id']) {
            $this->view('errors.404', [
                'message' => 'Solicitação não encontrada'
            ]);
            return;
        }
        
        $this->view('locatario.show-solicitacao', [
            'locatario' => $locatario,
            'solicitacao' => $solicitacao
        ]);
    }
    
    /**
     * Processar nova solicitação
     */
    private function processarNovaSolicitacao(): void
    {
        $this->requireLocatarioAuth();
        
        $locatario = $_SESSION['locatario'];
        
        $data = [
            'imobiliaria_id' => $locatario['imobiliaria_id'],
            'categoria_id' => $this->input('categoria_id'),
            'subcategoria_id' => $this->input('subcategoria_id'),
            'locatario_id' => $locatario['id'],
            'locatario_nome' => $locatario['nome'],
            'locatario_telefone' => $this->input('telefone'),
            'locatario_email' => $this->input('email'),
            'imovel_endereco' => $this->input('endereco'),
            'imovel_numero' => $this->input('numero'),
            'imovel_complemento' => $this->input('complemento'),
            'imovel_bairro' => $this->input('bairro'),
            'imovel_cidade' => $this->input('cidade'),
            'imovel_estado' => $this->input('estado'),
            'imovel_cep' => $this->input('cep'),
            'descricao_problema' => $this->input('descricao'),
            'tipo_atendimento' => $this->input('tipo_atendimento', 'RESIDENCIAL'),
            'datas_opcoes' => json_decode($this->input('datas_opcoes', '[]'), true),
            'prioridade' => $this->input('prioridade', 'NORMAL')
        ];
        
        // Validar dados obrigatórios
        $required = ['categoria_id', 'subcategoria_id', 'descricao_problema'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $instancia = $this->getInstanciaFromUrl();
                $this->redirect($instancia . '/nova-solicitacao', [
                    'error' => 'Todos os campos obrigatórios devem ser preenchidos'
                ]);
                return;
            }
        }
        
        // Criar solicitação
        $solicitacaoId = $this->solicitacaoModel->create($data);
        
        $instancia = $this->getInstanciaFromUrl();
        if ($solicitacaoId) {
            $this->redirect(url($instancia . '/solicitacoes'), [
                'success' => 'Solicitação criada com sucesso!'
            ]);
        } else {
            $this->redirect($instancia . '/nova-solicitacao', [
                'error' => 'Erro ao criar solicitação. Tente novamente.'
            ]);
        }
    }
    
    /**
     * Atualizar perfil do locatário
     */
    public function atualizarPerfil(string $instancia = ''): void
    {
        $this->requireLocatarioAuth();
        
        // Implementar atualização do perfil
        $this->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso'
        ]);
    }
    
    /**
     * Logout do locatário
     */
    public function logout(string $instancia = ''): void
    {
        if (empty($instancia)) {
            $instancia = $this->getInstanciaFromUrl();
        }
        unset($_SESSION['locatario']);
        $this->redirect(url($instancia));
    }
    
    /**
     * Verificar se locatário está autenticado
     */
    private function requireLocatarioAuth(): void
    {
        if (!isset($_SESSION['locatario'])) {
            $instancia = $this->getInstanciaFromUrl();
            $this->redirect(url($instancia));
        }
        
        // Verificar se sessão não expirou (24 horas)
        if (time() - $_SESSION['locatario']['login_time'] > 86400) {
            unset($_SESSION['locatario']);
            $instancia = $this->getInstanciaFromUrl();
            $this->redirect(url($instancia));
        }
    }
    
    /**
     * Extrair instância da URL
     */
    private function getInstanciaFromUrl(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        // Para a nova estrutura: /{instancia} ou /{instancia}/dashboard, etc.
        // A instância é sempre o primeiro segmento após o base path
        if (!empty($segments)) {
            // Remover o base path se existir
            $basePath = trim(FOLDER, '/');
            if (!empty($basePath) && $segments[0] === $basePath) {
                array_shift($segments);
            }
            
            // O primeiro segmento restante é a instância
            if (!empty($segments[0])) {
                return $segments[0];
            }
        }
        
        return '';
    }
}
