<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\KsiApiService;
use App\Models\Solicitacao;
use App\Models\Locatario;

class LocatarioController extends Controller
{
    private Solicitacao $solicitacaoModel;
    private Locatario $locatarioModel;
    
    public function __construct()
    {
        $this->solicitacaoModel = new Solicitacao();
        $this->locatarioModel = new Locatario();
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
                'email' => $cliente['email'] ?? null,
                'telefone' => $cliente['telefone'] ?? null,
                'whatsapp' => $cliente['whatsapp'] ?? $cliente['telefone'] ?? null,
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
        
        // Buscar WhatsApp do banco se estiver vazio
        if (empty($locatario['whatsapp'])) {
            $cpfLimpo = str_replace(['.', '-'], '', $locatario['cpf']);
            $locatarioBanco = $this->locatarioModel->findByCpfAndImobiliaria($cpfLimpo, $locatario['imobiliaria_id']);
            
            if ($locatarioBanco) {
                $locatario['whatsapp'] = $locatarioBanco['whatsapp'] ?? '';
                $locatario['telefone'] = $locatarioBanco['telefone'] ?? '';
                $locatario['email'] = $locatarioBanco['email'] ?? '';
                
                // Atualizar sessão
                $_SESSION['locatario']['whatsapp'] = $locatario['whatsapp'];
                $_SESSION['locatario']['telefone'] = $locatario['telefone'];
                $_SESSION['locatario']['email'] = $locatario['email'];
            }
        }
        
        // Buscar solicitações do locatário
        $solicitacoes = $this->solicitacaoModel->getByLocatario($locatario['id']);
        
        // Estatísticas
        $stats = [
            'total' => count($solicitacoes),
            'agendadas' => count(array_filter($solicitacoes, fn($s) => $s['status_nome'] === 'Serviço Agendado')),
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
        
        // Sempre buscar dados atualizados do banco
        $locatarioBanco = null;
        
        // Tentar buscar por CPF primeiro
        if (!empty($locatario['cpf'])) {
            $cpfLimpo = str_replace(['.', '-'], '', $locatario['cpf']);
            $locatarioBanco = $this->locatarioModel->findByCpfAndImobiliaria($cpfLimpo, $locatario['imobiliaria_id']);
        }
        
        // Se não encontrou por CPF, tentar por ksi_cliente_id
        if (!$locatarioBanco && !empty($locatario['id']) && !empty($locatario['imobiliaria_id'])) {
            $locatarioBanco = $this->locatarioModel->findByKsiIdAndImobiliaria($locatario['id'], $locatario['imobiliaria_id']);
        }
        
        if ($locatarioBanco) {
            // Atualizar dados do locatário com dados do banco
            $locatario['whatsapp'] = $locatarioBanco['whatsapp'] ?? '';
            $locatario['telefone'] = $locatarioBanco['telefone'] ?? '';
            $locatario['email'] = $locatarioBanco['email'] ?? '';
            $locatario['nome'] = $locatarioBanco['nome'] ?? $locatario['nome'];
            
            // Atualizar sessão
            $_SESSION['locatario']['whatsapp'] = $locatario['whatsapp'];
            $_SESSION['locatario']['telefone'] = $locatario['telefone'];
            $_SESSION['locatario']['email'] = $locatario['email'];
            $_SESSION['locatario']['nome'] = $locatario['nome'];
        }
        
        $this->view('locatario.perfil', [
            'locatario' => $locatario
        ]);
    }
    
    /**
     * Nova solicitação
     */
    public function novaSolicitacao(string $instancia = ''): void
    {
        // LOG CRÍTICO: Verificar se método é chamado
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - novaSolicitacao() chamado - Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
        
        $this->requireLocatarioAuth();
        
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Passou requireLocatarioAuth\n", FILE_APPEND);
        
        if ($this->isPost()) {
            // Processar envio do formulário da etapa 1
            file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - É POST! Processando etapa 1\n", FILE_APPEND);
            file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
            
            file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - ANTES de chamar salvarDadosEtapa\n", FILE_APPEND);
            error_log("CRÍTICO: ANTES de chamar salvarDadosEtapa");
            
            $this->salvarDadosEtapa(1);
            
            file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - DEPOIS de salvarDadosEtapa\n", FILE_APPEND);
            error_log("CRÍTICO: DEPOIS de salvarDadosEtapa");
            return;
        }
        
        // Limpar dados da sessão apenas quando é GET (começar nova solicitação)
        unset($_SESSION['nova_solicitacao']);
        
        $locatario = $_SESSION['locatario'];
        
        // IMPORTANTE: Recarregar imóveis da API se estiverem vazios
        if (empty($locatario['imoveis'])) {
            error_log("DEBUG: Imóveis vazios, recarregando da API...");
            
            // Buscar imobiliária
            $imobiliaria = KsiApiService::getImobiliariaByInstancia($locatario['instancia']);
            
            if ($imobiliaria) {
                // Criar serviço da API
                $ksiApi = KsiApiService::fromImobiliaria($imobiliaria);
                
                // Buscar imóveis do locatário
                $imovelResult = $ksiApi->buscarImovelLocatario($locatario['id']);
                
                if ($imovelResult['success']) {
                    $locatario['imoveis'] = $imovelResult['imoveis'];
                    $_SESSION['locatario']['imoveis'] = $imovelResult['imoveis'];
                    error_log("DEBUG: Imóveis recarregados: " . count($imovelResult['imoveis']));
                } else {
                    error_log("DEBUG: Erro ao recarregar imóveis: " . $imovelResult['message']);
                }
            }
        } else {
            error_log("DEBUG: Imóveis já carregados na sessão: " . count($locatario['imoveis']));
        }
        
        // Buscar categorias e subcategorias
        $categoriaModel = new \App\Models\Categoria();
        $subcategoriaModel = new \App\Models\Subcategoria();
        $categorias = $categoriaModel->getAtivas();
        $subcategorias = $subcategoriaModel->getAtivas();
        
        // Organizar subcategorias por categoria
        foreach ($categorias as $key => $categoria) {
            $categorias[$key]['subcategorias'] = array_values(array_filter($subcategorias, function($sub) use ($categoria) {
                return $sub['categoria_id'] == $categoria['id'];
            }));
        }
        
        $this->view('locatario.nova-solicitacao', [
            'locatario' => $locatario,
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
            'etapa' => 1, // Sempre começa na etapa 1
            'nova_solicitacao' => $_SESSION['nova_solicitacao'] ?? []
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
        
        // GET: Exibir a view da etapa correspondente
        $locatario = $_SESSION['locatario'];
        $novaSolicitacao = $_SESSION['nova_solicitacao'] ?? [];
        
        // Preparar dados específicos para cada etapa
        $data = [
            'locatario' => $locatario,
            'etapa' => $etapa,
            'nova_solicitacao' => $novaSolicitacao
        ];
        
        // Adicionar dados extras conforme necessário para cada etapa
        switch ($etapa) {
            case 2:
                // Buscar categorias e subcategorias
                $categoriaModel = new \App\Models\Categoria();
                $subcategoriaModel = new \App\Models\Subcategoria();
                $categorias = $categoriaModel->getAtivas();
                $subcategorias = $subcategoriaModel->getAtivas();
                
                // Organizar subcategorias por categoria
                foreach ($categorias as $key => $categoria) {
                    $categorias[$key]['subcategorias'] = array_values(array_filter($subcategorias, function($sub) use ($categoria) {
                        return $sub['categoria_id'] == $categoria['id'];
                    }));
                }
                
                $data['categorias'] = $categorias;
                $data['subcategorias'] = $subcategorias;
                break;
            case 3:
                // Fotos já estão em $novaSolicitacao
                break;
            case 4:
                // Horários
                break;
            case 5:
                // Resumo final
                break;
        }
        
        $this->view('locatario.nova-solicitacao', $data);
    }
    
    /**
     * Salvar dados da etapa atual
     */
    private function salvarDadosEtapa(int $etapa): void
    {
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - salvarDadosEtapa($etapa) iniciado\n", FILE_APPEND);
        
        // Inicializar sessão de nova solicitação se não existir
        if (!isset($_SESSION['nova_solicitacao'])) {
            $_SESSION['nova_solicitacao'] = [];
        }
        
        // Salvar dados da etapa atual
        switch ($etapa) {
            case 1:
                file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Dentro do case 1\n", FILE_APPEND);
                
                // Validar campos obrigatórios da etapa 1
                $enderecoSelecionado = $this->input('endereco_selecionado');
                $finalidadeLocacao = $this->input('finalidade_locacao');
                $tipoImovel = $this->input('tipo_imovel');
                
                file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Valores: endereco=$enderecoSelecionado, finalidade=$finalidadeLocacao, tipo=$tipoImovel\n", FILE_APPEND);
                
                // Usar isset() e !== null ao invés de empty() porque "0" é um valor válido!
                if ($enderecoSelecionado === null || $finalidadeLocacao === null || $tipoImovel === null) {
                    $instancia = $this->getInstanciaFromUrl();
                    file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - ERRO: Campos faltando! Redirecionando...\n", FILE_APPEND);
                    $this->redirect(url($instancia . '/nova-solicitacao?error=campos_obrigatorios'));
                    return;
                }
                
                file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Salvando na sessão...\n", FILE_APPEND);
                
                $_SESSION['nova_solicitacao']['endereco_selecionado'] = $enderecoSelecionado;
                $_SESSION['nova_solicitacao']['finalidade_locacao'] = $finalidadeLocacao;
                $_SESSION['nova_solicitacao']['tipo_imovel'] = $tipoImovel;
                
                file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Salvo! Sessão: " . print_r($_SESSION['nova_solicitacao'], true) . "\n", FILE_APPEND);
                break;
                
            case 2:
                $_SESSION['nova_solicitacao']['categoria_id'] = $this->input('categoria_id');
                $_SESSION['nova_solicitacao']['subcategoria_id'] = $this->input('subcategoria_id');
                break;
                
            case 3:
                $_SESSION['nova_solicitacao']['local_manutencao'] = $this->input('local_manutencao');
                $_SESSION['nova_solicitacao']['descricao_problema'] = $this->input('descricao_problema');
                
                // Processar upload de fotos se houver
                error_log("🔍 Etapa 3 - Verificando upload de fotos");
                error_log("🔍 \$_FILES: " . print_r($_FILES, true));
                
                if (!empty($_FILES['fotos']['name'][0])) {
                    error_log("✅ Fotos detectadas no upload");
                    $fotosSalvas = $this->processarUploadFotos();
                    error_log("✅ Fotos processadas: " . print_r($fotosSalvas, true));
                    $_SESSION['nova_solicitacao']['fotos'] = $fotosSalvas;
                } else {
                    error_log("⚠️ Nenhuma foto detectada no upload");
                    error_log("⚠️ \$_FILES['fotos']: " . print_r($_FILES['fotos'] ?? 'NÃO DEFINIDO', true));
                }
                break;
                
            case 4:
                // Receber horários enviados pelo JavaScript
                $horariosRaw = $this->input('horarios_opcoes');
                $horarios = [];
                
                if (!empty($horariosRaw)) {
                    // Se for string JSON, decodificar
                    $horarios = is_string($horariosRaw) ? json_decode($horariosRaw, true) : $horariosRaw;
                }
                
                // Log para debug
                file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Horários recebidos: " . print_r($horariosRaw, true) . "\n", FILE_APPEND);
                file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Horários processados: " . print_r($horarios, true) . "\n", FILE_APPEND);
                
                // Salvar horários formatados na sessão
                $_SESSION['nova_solicitacao']['horarios_preferenciais'] = $horarios;
                break;
                
            case 5:
                $_SESSION['nova_solicitacao']['termo_aceite'] = $this->input('termo_aceite');
                $this->finalizarSolicitacao();
                return;
        }
        
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Salvou etapa $etapa, preparando redirect\n", FILE_APPEND);
        
        $_SESSION['nova_solicitacao']['etapa'] = $etapa;
        
        $instancia = $this->getInstanciaFromUrl();
        $proximaEtapa = $etapa + 1;
        
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Instancia retornada: '$instancia'\n", FILE_APPEND);
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - URL construída: " . ($instancia . '/nova-solicitacao/etapa/' . $proximaEtapa) . "\n", FILE_APPEND);
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Redirecionando para etapa $proximaEtapa da instancia $instancia\n", FILE_APPEND);
        
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
        $uploadDir = __DIR__ . '/../../Public/uploads/solicitacoes/';
        
        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            error_log("📁 Diretório criado: {$uploadDir}");
        }
        
        error_log("📸 Processando " . count($_FILES['fotos']['name']) . " arquivo(s)");
        
        foreach ($_FILES['fotos']['name'] as $key => $name) {
            $error = $_FILES['fotos']['error'][$key];
            error_log("📸 Arquivo {$key}: {$name}, Erro: {$error}");
            
            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['fotos']['tmp_name'][$key];
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                // Validar extensão
                $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $extensoesPermitidas)) {
                    error_log("❌ Extensão não permitida: {$extension}");
                    continue;
                }
                
                // Validar tamanho (10MB)
                $tamanho = $_FILES['fotos']['size'][$key];
                $tamanhoMaximo = 10 * 1024 * 1024; // 10MB
                if ($tamanho > $tamanhoMaximo) {
                    error_log("❌ Arquivo muito grande: " . number_format($tamanho / 1024 / 1024, 2) . " MB");
                    continue;
                }
                
                $fileName = uniqid() . '_' . time() . '.' . $extension;
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    $fotosSalvas[] = $fileName;
                    error_log("✅ Foto salva: {$fileName} em {$filePath}");
                } else {
                    error_log("❌ Erro ao mover arquivo: {$tmpName} para {$filePath}");
                }
            } else {
                $mensagensErro = [
                    UPLOAD_ERR_INI_SIZE => 'Arquivo excede upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'Arquivo excede MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'Upload parcial',
                    UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
                    UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não encontrado',
                    UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo',
                    UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
                ];
                error_log("❌ Erro no upload: " . ($mensagensErro[$error] ?? "Erro desconhecido ({$error})"));
            }
        }
        
        error_log("📸 Total de fotos salvas: " . count($fotosSalvas));
        return $fotosSalvas;
    }
    
    /**
     * Finalizar solicitação com todos os dados coletados
     */
    private function finalizarSolicitacao(): void
    {
        $dados = $_SESSION['nova_solicitacao'] ?? [];
        $locatario = $_SESSION['locatario'];
        
        // DEBUG: Ver o que tem na sessão
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - finalizarSolicitacao() iniciado\n", FILE_APPEND);
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Dados na sessão: " . print_r($dados, true) . "\n", FILE_APPEND);
        
        // Validar dados obrigatórios (usar !isset para permitir valor "0")
        $required = ['endereco_selecionado', 'categoria_id', 'subcategoria_id', 'descricao_problema'];
        foreach ($required as $field) {
            if (!isset($dados[$field]) || $dados[$field] === '' || $dados[$field] === null) {
                $instancia = $locatario['instancia'];
                file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Campo faltando: $field (valor: " . var_export($dados[$field] ?? 'UNDEFINED', true) . ")\n", FILE_APPEND);
                $this->redirect(url($instancia . '/nova-solicitacao?error=' . urlencode("Campo obrigatório faltando: $field")));
                return;
            }
        }
        
        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Validação OK, criando solicitação...\n", FILE_APPEND);
        
        // Preparar dados para criação da solicitação
        $imovel = $locatario['imoveis'][$dados['endereco_selecionado']];
        
        // Buscar número do contrato do imóvel
        $numeroContrato = null;
        if (!empty($imovel['contratos'])) {
            foreach ($imovel['contratos'] as $contrato) {
                if (isset($contrato['CtrTipo']) && $contrato['CtrTipo'] == 'PRINCIPAL') {
                    $numeroContrato = ($contrato['CtrCod'] ?? '') . '-' . ($contrato['CtrDV'] ?? '');
                    break;
                }
            }
            // Se não encontrou principal, pegar o primeiro
            if (!$numeroContrato && !empty($imovel['contratos'][0])) {
                $contrato = $imovel['contratos'][0];
                $numeroContrato = ($contrato['CtrCod'] ?? '') . '-' . ($contrato['CtrDV'] ?? '');
            }
        }
        
        // Buscar status inicial (geralmente "Nova Solicitação" ou similar)
        $statusModel = new \App\Models\Status();
        $statusInicial = $statusModel->findByNome('Nova Solicitação') 
                      ?? $statusModel->findByNome('Nova') 
                      ?? $statusModel->findByNome('NOVA')
                      ?? ['id' => 1];
        
        // Preparar horários para salvar (converter array para JSON)
        $horarios = $dados['horarios_preferenciais'] ?? [];
        $horariosJson = !empty($horarios) ? json_encode($horarios) : null;
        
        $data = [
            // IDs e relacionamentos
            'locatario_id' => $locatario['codigo_locatario'] ?? $locatario['id'],
            'locatario_nome' => $locatario['nome'],
            'locatario_telefone' => $locatario['whatsapp'] ?? $locatario['telefone'] ?? '',
            'locatario_email' => $locatario['email'] ?? '',
            'imobiliaria_id' => $locatario['imobiliaria_id'],
            'categoria_id' => $dados['categoria_id'],
            'subcategoria_id' => $dados['subcategoria_id'],
            'status_id' => $statusInicial['id'],
            
            // Descrição
            'descricao_problema' => $dados['descricao_problema'],
            'observacoes' => ($dados['local_manutencao'] ?? '') . "\nFinalidade: " . ($dados['finalidade_locacao'] ?? 'RESIDENCIAL') . "\nTipo: " . ($dados['tipo_imovel'] ?? 'CASA'),
            'prioridade' => 'NORMAL',
            
            // Horários preferenciais
            'horarios_opcoes' => $horariosJson,
            
            // Número do contrato
            'numero_contrato' => $numeroContrato,
            
            // Dados do imóvel (com prefixo imovel_)
            'imovel_endereco' => $imovel['endereco'] ?? '',
            'imovel_numero' => $imovel['numero'] ?? '',
            'imovel_complemento' => $imovel['complemento'] ?? '',
            'imovel_bairro' => $imovel['bairro'] ?? '',
            'imovel_cidade' => $imovel['cidade'] ?? '',
            'imovel_estado' => $imovel['uf'] ?? '',
            'imovel_cep' => $imovel['cep'] ?? ''
        ];
        
        // Criar solicitação
        $solicitacaoId = $this->solicitacaoModel->create($data);
        
        $instancia = $locatario['instancia'];
        if ($solicitacaoId) {
            // Gerar token de cancelamento permanente (não expira)
            $tokenCancelamento = $this->solicitacaoModel->gerarTokenCancelamento($solicitacaoId);
            error_log("✅ Token de cancelamento gerado para solicitação #{$solicitacaoId}: {$tokenCancelamento}");
            
            // Salvar fotos na tabela fotos se houver
            error_log("🔍 finalizarSolicitacao - Verificando fotos na sessão");
            error_log("🔍 finalizarSolicitacao - dados['fotos']: " . print_r($dados['fotos'] ?? 'NÃO DEFINIDO', true));
            
            if (!empty($dados['fotos']) && is_array($dados['fotos'])) {
                error_log("✅ finalizarSolicitacao - Encontradas " . count($dados['fotos']) . " foto(s) para salvar");
                foreach ($dados['fotos'] as $fotoNome) {
                    $urlArquivo = 'Public/uploads/solicitacoes/' . $fotoNome;
                    $sqlFoto = "INSERT INTO fotos (solicitacao_id, nome_arquivo, url_arquivo, created_at) 
                                VALUES (?, ?, ?, NOW())";
                    try {
                        \App\Core\Database::query($sqlFoto, [$solicitacaoId, $fotoNome, $urlArquivo]);
                        error_log("✅ Foto salva: {$fotoNome} para solicitação #{$solicitacaoId}");
                    } catch (\Exception $e) {
                        error_log("❌ Erro ao salvar foto {$fotoNome}: " . $e->getMessage());
                    }
                }
            } else {
                error_log("⚠️ finalizarSolicitacao - Nenhuma foto encontrada ou não é array");
            }
            
            // Enviar notificação WhatsApp
            try {
                $this->enviarNotificacaoWhatsApp($solicitacaoId, 'Nova Solicitação');
            } catch (\Exception $e) {
                // Log do erro mas não bloquear o fluxo
                error_log('Erro ao enviar WhatsApp no LocatarioController [ID:' . $solicitacaoId . ']: ' . $e->getMessage());
            }
            
            // Limpar dados da sessão
            unset($_SESSION['nova_solicitacao']);
            
            // Redirecionar para o dashboard com mensagem de sucesso
            $this->redirect(url($instancia . '/dashboard?success=' . urlencode('Solicitação criada com sucesso! ID: #' . $solicitacaoId)));
        } else {
            $this->redirect(url($instancia . '/nova-solicitacao?error=' . urlencode('Erro ao criar solicitação. Tente novamente.')));
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
        
        // Processar horários
        $horariosRaw = $this->input('horarios_opcoes', '[]');
        $horarios = is_string($horariosRaw) ? $horariosRaw : json_encode($horariosRaw);
        
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
            'horarios_opcoes' => $horarios,
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
        try {
            $this->requireLocatarioAuth();
            
            if (!$this->isPost()) {
                $this->json([
                    'success' => false,
                    'message' => 'Método não permitido'
                ]);
                return;
            }
            
            $locatario = $_SESSION['locatario'];
            
            // Receber dados do formulário
            $nome = trim($this->input('nome'));
            $email = trim($this->input('email'));
            $whatsapp = trim($this->input('whatsapp'));
            
            // Validar dados
            if (empty($nome)) {
                $this->json([
                    'success' => false,
                    'message' => 'O nome é obrigatório'
                ]);
                return;
            }
            
            // Validar email se fornecido
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->json([
                    'success' => false,
                    'message' => 'E-mail inválido'
                ]);
                return;
            }
            
            // Validar WhatsApp se fornecido
            if (!empty($whatsapp)) {
                $whatsappLimpo = preg_replace('/\D/', '', $whatsapp);
                if (strlen($whatsappLimpo) < 10 || strlen($whatsappLimpo) > 11) {
                    $this->json([
                        'success' => false,
                        'message' => 'WhatsApp inválido. Use o formato (XX) XXXXX-XXXX'
                    ]);
                    return;
                }
            }
            
            // Buscar locatário no banco
            $cpfLimpo = str_replace(['.', '-'], '', $locatario['cpf']);
            $locatarioBanco = $this->locatarioModel->findByCpfAndImobiliaria($cpfLimpo, $locatario['imobiliaria_id']);
            
            // Preparar dados para atualização
            $dados = [
                'nome' => $nome,
                'email' => $email,
                'whatsapp' => $whatsapp,
                'telefone' => $whatsapp // Usar whatsapp como telefone também
            ];
            
            // Se o locatário existe no banco, atualizar
            if ($locatarioBanco) {
                $sucesso = $this->locatarioModel->updateDadosPessoais($locatarioBanco['id'], $dados);
            } else {
                // Se não existe, criar novo registro
                $dados['cpf'] = $cpfLimpo;
                $dados['imobiliaria_id'] = $locatario['imobiliaria_id'];
                $dados['ksi_cliente_id'] = $locatario['id'];
                $dados['status'] = 'ATIVO';
                
                $sucesso = $this->locatarioModel->create($dados);
            }
            
            if ($sucesso) {
                // Atualizar dados na sessão
                $_SESSION['locatario']['nome'] = $nome;
                $_SESSION['locatario']['email'] = $email;
                $_SESSION['locatario']['whatsapp'] = $whatsapp;
                $_SESSION['locatario']['telefone'] = $whatsapp;
                
                $this->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso!'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar perfil. Tente novamente.'
                ]);
            }
        } catch (\Exception $e) {
            error_log('Erro ao atualizar perfil: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            $this->json([
                'success' => false,
                'message' => 'Erro ao processar requisição: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Logout do locatário
     */
    public function logout(string $instancia = ''): void
    {
        // Tentar pegar a instância da sessão antes de limpar
        if (empty($instancia) && isset($_SESSION['locatario']['instancia'])) {
            $instancia = $_SESSION['locatario']['instancia'];
        }
        
        // Se ainda estiver vazio, tentar da URL
        if (empty($instancia)) {
            $instancia = $this->getInstanciaFromUrl();
        }
        
        // Se ainda estiver vazio, usar 'demo' como padrão
        if (empty($instancia)) {
            $instancia = 'demo';
        }
        
        // Log para debug
        error_log('Logout iniciado - Instância: ' . $instancia);
        error_log('Sessão antes do logout: ' . json_encode($_SESSION));
        
        // Limpar completamente a sessão do locatário
        if (isset($_SESSION['locatario'])) {
            unset($_SESSION['locatario']);
        }
        if (isset($_SESSION['locatario_id'])) {
            unset($_SESSION['locatario_id']);
        }
        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
        }
        if (isset($_SESSION['instancia'])) {
            unset($_SESSION['instancia']);
        }
        if (isset($_SESSION['imobiliaria_id'])) {
            unset($_SESSION['imobiliaria_id']);
        }
        if (isset($_SESSION['cliente_data'])) {
            unset($_SESSION['cliente_data']);
        }
        
        // Limpar todas as variáveis de sessão relacionadas
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'locatario') !== false || strpos($key, 'imobiliaria') !== false) {
                unset($_SESSION[$key]);
            }
        }
        
        // Garantir que a sessão foi limpa
        $_SESSION['locatario'] = null;
        
        // Regenerar ID da sessão para segurança
        session_regenerate_id(true);
        
        // Log para debug
        error_log('Sessão após logout: ' . json_encode($_SESSION));
        error_log('Redirecionando para: /' . $instancia);
        
        $this->redirect('/' . $instancia);
    }
    
    /**
     * Verificar se locatário está autenticado
     */
    private function requireLocatarioAuth(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!isset($_SESSION['locatario'])) {
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Sessão expirada. Por favor, faça login novamente.',
                    'redirect' => true
                ], 401);
            }
            
            $instancia = $this->getInstanciaFromUrl();
            $this->redirect(url($instancia));
        }
        
        // Verificar se sessão não expirou (24 horas)
        if (time() - $_SESSION['locatario']['login_time'] > 86400) {
            unset($_SESSION['locatario']);
            
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Sessão expirada. Por favor, faça login novamente.',
                    'redirect' => true
                ], 401);
            }
            
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
    
    // ============================================================
    // SOLICITAÇÃO MANUAL (SEM AUTENTICAÇÃO)
    // ============================================================
    
    /**
     * Solicitação Manual - Fluxo para usuários não logados
     */
    public function solicitacaoManual(string $instancia = ''): void
    {
        if ($this->isPost()) {
            $this->processarSolicitacaoManual(1);
            return;
        }
        
        // Extrair instância da URL se não foi passada
        if (empty($instancia)) {
            $instancia = $this->getInstanciaFromUrl();
        }
        
        // Buscar imobiliária
        $imobiliaria = KsiApiService::getImobiliariaByInstancia($instancia);
        
        if (!$imobiliaria) {
            $this->view('errors.404', [
                'message' => 'Imobiliária não encontrada'
            ]);
            return;
        }
        
        // Limpar dados da sessão ao começar nova solicitação
        unset($_SESSION['solicitacao_manual']);
        
        // Buscar categorias para as próximas etapas
        $categoriaModel = new \App\Models\Categoria();
        $subcategoriaModel = new \App\Models\Subcategoria();
        $categorias = $categoriaModel->getAtivas();
        $subcategorias = $subcategoriaModel->getAtivas();
        
        // Organizar subcategorias por categoria
        foreach ($categorias as $key => $categoria) {
            $categorias[$key]['subcategorias'] = array_values(array_filter($subcategorias, function($sub) use ($categoria) {
                return $sub['categoria_id'] == $categoria['id'];
            }));
        }
        
        $this->view('locatario.solicitacao-manual', [
            'imobiliaria' => $imobiliaria,
            'instancia' => $instancia,
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
            'etapa' => 1,
            'dados' => $_SESSION['solicitacao_manual'] ?? []
        ]);
    }
    
    /**
     * Processar etapa específica da solicitação manual
     */
    public function solicitacaoManualEtapa(string $instancia, int $etapa): void
    {
        // GET: exibir a etapa
        if (!$this->isPost()) {
            // Se não há dados na sessão e não é etapa 1, redirecionar
            if (!isset($_SESSION['solicitacao_manual']) && $etapa > 1) {
                $this->redirect(url($instancia . '/solicitacao-manual'));
                return;
            }
            
            // Buscar imobiliária
            $imobiliaria = KsiApiService::getImobiliariaByInstancia($instancia);
            
            if (!$imobiliaria) {
                $this->view('errors.404', ['message' => 'Imobiliária não encontrada']);
                return;
            }
            
            // Buscar categorias e subcategorias
            $categoriaModel = new \App\Models\Categoria();
            $subcategoriaModel = new \App\Models\Subcategoria();
            $categorias = $categoriaModel->getAtivas();
            $subcategorias = $subcategoriaModel->getAtivas();
            
            // Organizar subcategorias por categoria
            foreach ($categorias as $key => $categoria) {
                $categorias[$key]['subcategorias'] = array_values(array_filter($subcategorias, function($sub) use ($categoria) {
                    return $sub['categoria_id'] == $categoria['id'];
                }));
            }
            
            $this->view('locatario.solicitacao-manual', [
                'imobiliaria' => $imobiliaria,
                'instancia' => $instancia,
                'categorias' => $categorias,
                'subcategorias' => $subcategorias,
                'etapa' => $etapa,
                'dados' => $_SESSION['solicitacao_manual'] ?? []
            ]);
            return;
        }
        
        // POST: processar dados da etapa
        $this->processarSolicitacaoManual($etapa);
    }
    
    /**
     * Processar dados de cada etapa da solicitação manual
     */
    private function processarSolicitacaoManual(int $etapa): void
    {
        $instancia = $this->getInstanciaFromUrl();
        
        // Inicializar sessão se não existir
        if (!isset($_SESSION['solicitacao_manual'])) {
            $_SESSION['solicitacao_manual'] = [];
        }
        
        switch ($etapa) {
            case 1: // Dados Pessoais
                $nome = trim($this->input('nome_completo'));
                $cpf = trim($this->input('cpf'));
                $whatsapp = trim($this->input('whatsapp'));
                
                // Validações
                if (empty($nome) || empty($cpf) || empty($whatsapp)) {
                    $this->redirect(url($instancia . '/solicitacao-manual?error=' . urlencode('Todos os campos são obrigatórios')));
                    return;
                }
                
                // Validar CPF
                $solicitacaoManualModel = new \App\Models\SolicitacaoManual();
                if (!$solicitacaoManualModel->validarCPF($cpf)) {
                    $this->redirect(url($instancia . '/solicitacao-manual?error=' . urlencode('CPF inválido')));
                    return;
                }
                
                // Validar WhatsApp
                $whatsappLimpo = preg_replace('/\D/', '', $whatsapp);
                if (strlen($whatsappLimpo) < 10 || strlen($whatsappLimpo) > 11) {
                    $this->redirect(url($instancia . '/solicitacao-manual?error=' . urlencode('WhatsApp inválido')));
                    return;
                }
                
                $_SESSION['solicitacao_manual']['nome_completo'] = $nome;
                $_SESSION['solicitacao_manual']['cpf'] = $cpf;
                $_SESSION['solicitacao_manual']['whatsapp'] = $whatsapp;
                break;
                
            case 2: // Endereço
                $tipoImovel = $this->input('tipo_imovel');
                $subtipoImovel = $this->input('subtipo_imovel');
                $cep = trim($this->input('cep'));
                $endereco = trim($this->input('endereco'));
                $numero = trim($this->input('numero'));
                $complemento = trim($this->input('complemento'));
                $bairro = trim($this->input('bairro'));
                $cidade = trim($this->input('cidade'));
                $estado = trim($this->input('estado'));
                
                // Validações
                if (empty($tipoImovel) || empty($cep) || empty($endereco) || 
                    empty($numero) || empty($bairro) || empty($cidade) || empty($estado)) {
                    $this->redirect(url($instancia . '/solicitacao-manual/etapa/2?error=' . urlencode('Todos os campos obrigatórios devem ser preenchidos')));
                    return;
                }
                
                $_SESSION['solicitacao_manual']['tipo_imovel'] = $tipoImovel;
                $_SESSION['solicitacao_manual']['subtipo_imovel'] = $subtipoImovel;
                $_SESSION['solicitacao_manual']['cep'] = $cep;
                $_SESSION['solicitacao_manual']['endereco'] = $endereco;
                $_SESSION['solicitacao_manual']['numero'] = $numero;
                $_SESSION['solicitacao_manual']['complemento'] = $complemento;
                $_SESSION['solicitacao_manual']['bairro'] = $bairro;
                $_SESSION['solicitacao_manual']['cidade'] = $cidade;
                $_SESSION['solicitacao_manual']['estado'] = $estado;
                break;
                
            case 3: // Serviço
                $categoriaId = $this->input('categoria_id');
                $subcategoriaId = $this->input('subcategoria_id');
                $descricaoProblema = trim($this->input('descricao_problema'));
                
                // Validações
                if (empty($categoriaId) || empty($subcategoriaId) || empty($descricaoProblema)) {
                    $this->redirect(url($instancia . '/solicitacao-manual/etapa/3?error=' . urlencode('Todos os campos são obrigatórios')));
                    return;
                }
                
                $_SESSION['solicitacao_manual']['categoria_id'] = $categoriaId;
                $_SESSION['solicitacao_manual']['subcategoria_id'] = $subcategoriaId;
                $_SESSION['solicitacao_manual']['descricao_problema'] = $descricaoProblema;
                break;
                
            case 4: // Fotos e Horários
                // Processar upload de fotos
                $fotos = [];
                if (!empty($_FILES['fotos']['name'][0])) {
                    $fotos = $this->processarUploadFotos();
                }
                
                // Horários preferenciais
                $horariosRaw = $this->input('horarios_opcoes');
                $horarios = [];
                
                if (!empty($horariosRaw)) {
                    $horarios = is_string($horariosRaw) ? json_decode($horariosRaw, true) : $horariosRaw;
                }
                
                // Validar que pelo menos 1 horário foi selecionado
                if (empty($horarios)) {
                    $this->redirect(url($instancia . '/solicitacao-manual/etapa/4?error=' . urlencode('Selecione pelo menos um horário preferencial')));
                    return;
                }
                
                $_SESSION['solicitacao_manual']['fotos'] = $fotos;
                $_SESSION['solicitacao_manual']['horarios_preferenciais'] = $horarios;
                break;
                
            case 5: // Confirmação
                $termosAceitos = $this->input('termo_aceite');
                
                if (!$termosAceitos) {
                    $this->redirect(url($instancia . '/solicitacao-manual/etapa/5?error=' . urlencode('Você deve aceitar os termos para continuar')));
                    return;
                }
                
                $_SESSION['solicitacao_manual']['termos_aceitos'] = true;
                
                // Finalizar e salvar
                $this->finalizarSolicitacaoManual();
                return;
        }
        
        // Salvar etapa atual
        $_SESSION['solicitacao_manual']['etapa'] = $etapa;
        
        // Redirecionar para próxima etapa
        $proximaEtapa = $etapa + 1;
        if ($proximaEtapa <= 5) {
            $this->redirect(url($instancia . '/solicitacao-manual/etapa/' . $proximaEtapa));
        }
    }
    
    /**
     * Finalizar e salvar solicitação manual no banco de dados
     */
    private function finalizarSolicitacaoManual(): void
    {
        $instancia = $this->getInstanciaFromUrl();
        $dados = $_SESSION['solicitacao_manual'] ?? [];
        
        // Validar que todos os dados necessários estão presentes
        $camposObrigatorios = ['nome_completo', 'cpf', 'whatsapp', 'tipo_imovel', 'cep', 
                               'endereco', 'numero', 'bairro', 'cidade', 'estado',
                               'categoria_id', 'subcategoria_id', 'descricao_problema', 'termos_aceitos'];
        
        foreach ($camposObrigatorios as $campo) {
            if (!isset($dados[$campo]) || $dados[$campo] === '' || $dados[$campo] === null) {
                $this->redirect(url($instancia . '/solicitacao-manual?error=' . urlencode('Dados incompletos. Por favor, preencha todos os campos.')));
                return;
            }
        }
        
        // Buscar imobiliária
        $imobiliaria = KsiApiService::getImobiliariaByInstancia($instancia);
        
        if (!$imobiliaria) {
            $this->redirect(url($instancia . '/solicitacao-manual?error=' . urlencode('Imobiliária não encontrada')));
            return;
        }
        
        // Preparar dados para salvar
        $dadosParaSalvar = [
            'imobiliaria_id' => $imobiliaria['id'],
            'nome_completo' => $dados['nome_completo'],
            'cpf' => $dados['cpf'],
            'whatsapp' => $dados['whatsapp'],
            'tipo_imovel' => $dados['tipo_imovel'],
            'subtipo_imovel' => $dados['subtipo_imovel'] ?? null,
            'cep' => $dados['cep'],
            'endereco' => $dados['endereco'],
            'numero' => $dados['numero'],
            'complemento' => $dados['complemento'] ?? null,
            'bairro' => $dados['bairro'],
            'cidade' => $dados['cidade'],
            'estado' => $dados['estado'],
            'categoria_id' => $dados['categoria_id'],
            'subcategoria_id' => $dados['subcategoria_id'],
            'descricao_problema' => $dados['descricao_problema'],
            'horarios_preferenciais' => $dados['horarios_preferenciais'] ?? [],
            'fotos' => $dados['fotos'] ?? [],
            'termos_aceitos' => $dados['termos_aceitos']
        ];
        
        // Criar solicitação manual
        $solicitacaoManualModel = new \App\Models\SolicitacaoManual();
        $id = $solicitacaoManualModel->create($dadosParaSalvar);
        
        if ($id) {
            // Limpar sessão
            unset($_SESSION['solicitacao_manual']);
            
            // Redirecionar com mensagem de sucesso
            $this->redirect(url($instancia . '?success=' . urlencode('Solicitação enviada com sucesso! Em breve entraremos em contato. ID: #' . $id)));
        } else {
            $this->redirect(url($instancia . '/solicitacao-manual/etapa/5?error=' . urlencode('Erro ao salvar solicitação. Tente novamente.')));
        }
    }
    
    /**
     * Enviar notificação WhatsApp
     */
    private function enviarNotificacaoWhatsApp(int $solicitacaoId, string $tipo, array $extraData = []): void
    {
        try {
            $whatsappService = new \App\Services\WhatsAppService();
            $result = $whatsappService->sendMessage($solicitacaoId, $tipo, $extraData);
            
            if (!$result['success']) {
                error_log('Erro WhatsApp [LocatarioController]: ' . $result['message']);
            }
        } catch (\Exception $e) {
            error_log('Erro ao enviar WhatsApp [LocatarioController]: ' . $e->getMessage());
        }
    }
}
