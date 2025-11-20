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
    /**
     * Endpoint para verificar limite de solicitações ao selecionar categoria
     * GET /{instancia}/verificar-limite-categoria?categoria_id=X&numero_contrato=Y
     */
    public function verificarLimiteCategoria(string $instancia = ''): void
    {
        $this->requireLocatarioAuth();
        
        $categoriaId = $this->input('categoria_id');
        $numeroContrato = $this->input('numero_contrato', '');
        
        if (empty($categoriaId)) {
            $this->json([
                'success' => false,
                'message' => 'Categoria não informada'
            ], 400);
            return;
        }
        
        if (empty($numeroContrato)) {
            // Se não houver contrato, permitir (sem limite)
            $this->json([
                'success' => true,
                'permitido' => true,
                'limite' => null,
                'total_atual' => 0,
                'mensagem' => 'Sem contrato informado'
            ]);
            return;
        }
        
        try {
            $categoriaModel = new \App\Models\Categoria();
            $verificacao = $categoriaModel->verificarLimiteSolicitacoes((int)$categoriaId, $numeroContrato);
            
            $this->json([
                'success' => true,
                'permitido' => $verificacao['permitido'],
                'limite' => $verificacao['limite'],
                'total_atual' => $verificacao['total_atual'],
                'mensagem' => $verificacao['mensagem']
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao verificar limite de categoria: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Erro ao verificar limite'
            ], 500);
        }
    }

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
        
        // Na etapa 1, mostrar todas as categorias (ainda não há seleção de finalidade)
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
                
                // Filtrar categorias baseado na finalidade da locação selecionada
                $finalidadeLocacao = $novaSolicitacao['finalidade_locacao'] ?? 'RESIDENCIAL';
                
                // Buscar categorias que correspondem ao tipo de imóvel selecionado
                // Se for RESIDENCIAL, mostrar categorias com tipo_imovel = 'RESIDENCIAL' ou 'AMBOS'
                // Se for COMERCIAL, mostrar categorias com tipo_imovel = 'COMERCIAL' ou 'AMBOS'
                if ($finalidadeLocacao === 'RESIDENCIAL') {
                    $categorias = $categoriaModel->getByTipoImovel('RESIDENCIAL');
                } elseif ($finalidadeLocacao === 'COMERCIAL') {
                    $categorias = $categoriaModel->getByTipoImovel('COMERCIAL');
                } else {
                    // Fallback: mostrar todas se não houver seleção
                    $categorias = $categoriaModel->getAtivas();
                }
                
                $subcategorias = $subcategoriaModel->getAtivas();
                
                // Organizar subcategorias por categoria
                foreach ($categorias as $key => $categoria) {
                    $categorias[$key]['subcategorias'] = array_values(array_filter($subcategorias, function($sub) use ($categoria) {
                        return $sub['categoria_id'] == $categoria['id'];
                    }));
                }
                
                $data['categorias'] = $categorias;
                $data['subcategorias'] = $subcategorias;
                $data['finalidade_locacao'] = $finalidadeLocacao; // Passar para a view
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
                
                // Validar campos obrigatórios
                if ($enderecoSelecionado === null || $finalidadeLocacao === null) {
                    $instancia = $this->getInstanciaFromUrl();
                    file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - ERRO: Campos obrigatórios faltando! Redirecionando...\n", FILE_APPEND);
                    $this->redirect(url($instancia . '/nova-solicitacao?error=campos_obrigatorios'));
                    return;
                }
                
                // Se for COMERCIAL, definir tipo_imovel como COMERCIAL se não foi enviado
                if ($finalidadeLocacao === 'COMERCIAL' && ($tipoImovel === null || $tipoImovel === '')) {
                    $tipoImovel = 'COMERCIAL';
                }
                
                // Se for RESIDENCIAL, tipo_imovel é obrigatório (CASA ou APARTAMENTO)
                if ($finalidadeLocacao === 'RESIDENCIAL' && ($tipoImovel === null || $tipoImovel === '')) {
                    $instancia = $this->getInstanciaFromUrl();
                    file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - ERRO: Tipo de imóvel obrigatório para Residencial! Redirecionando...\n", FILE_APPEND);
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
                // Verificar se é emergencial
                $isEmergencial = $this->input('is_emergencial', 0);
                
                // Calcular se está fora do horário comercial usando configurações
                $configuracaoModel = new \App\Models\Configuracao();
                $isForaHorario = $configuracaoModel->isForaHorarioComercial() ? 1 : 0;
                
                $_SESSION['nova_solicitacao']['is_emergencial'] = $isEmergencial;
                $_SESSION['nova_solicitacao']['is_fora_horario'] = $isForaHorario;
                
                if ($isEmergencial) {
                    // Emergencial: verificar tipo de atendimento escolhido
                    $tipoAtendimentoEmergencial = $this->input('tipo_atendimento_emergencial', '120_minutos');
                    $_SESSION['nova_solicitacao']['tipo_atendimento_emergencial'] = $tipoAtendimentoEmergencial;
                    
                    if ($tipoAtendimentoEmergencial === '120_minutos') {
                        // Atendimento em 120 minutos: não precisa de horários
                        $_SESSION['nova_solicitacao']['horarios_preferenciais'] = [];
                    } else if ($tipoAtendimentoEmergencial === 'agendar') {
                        // Agendar: receber horários enviados pelo JavaScript
                        $horariosRaw = $this->input('horarios_opcoes');
                        $horarios = [];
                        
                        if (!empty($horariosRaw)) {
                            // Se for string JSON, decodificar
                            $horarios = is_string($horariosRaw) ? json_decode($horariosRaw, true) : $horariosRaw;
                        }
                        
                        // Log para debug
                        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Horários emergenciais recebidos: " . print_r($horariosRaw, true) . "\n", FILE_APPEND);
                        file_put_contents('C:\xampp\htdocs\debug_kss.log', date('H:i:s') . " - Horários emergenciais processados: " . print_r($horarios, true) . "\n", FILE_APPEND);
                        
                        // Salvar horários formatados na sessão
                        $_SESSION['nova_solicitacao']['horarios_preferenciais'] = $horarios;
                    }
                } else {
                    // Normal: receber horários enviados pelo JavaScript
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
                }
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
        
        // Verificar se é emergencial e fora do horário comercial
        $isEmergencial = !empty($dados['is_emergencial']);
        $isForaHorario = !empty($dados['is_fora_horario']);
        $isEmergencialForaHorario = $isEmergencial && $isForaHorario;
        
        // Se for emergencial, definir prioridade como ALTA
        $prioridade = $isEmergencial ? 'ALTA' : 'NORMAL';
        
        // Verificar limite de solicitações da categoria (se houver número de contrato)
        if (!empty($numeroContrato)) {
            $categoriaModel = new \App\Models\Categoria();
            $verificacaoLimite = $categoriaModel->verificarLimiteSolicitacoes($dados['categoria_id'], $numeroContrato);
            
            if (!$verificacaoLimite['permitido']) {
                $instancia = $locatario['instancia'];
                $this->redirect(url($instancia . '/nova-solicitacao?error=' . urlencode($verificacaoLimite['mensagem'])));
                return;
            }
        }
        
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
            'prioridade' => $prioridade,
            'is_emergencial_fora_horario' => $isEmergencialForaHorario ? 1 : 0,
            'observacoes' => ($dados['local_manutencao'] ?? '') . "\nFinalidade: " . ($dados['finalidade_locacao'] ?? 'RESIDENCIAL') . "\nTipo: " . ($dados['tipo_imovel'] ?? 'CASA'),
            
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
            
            // Verificar se é requisição AJAX
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            if ($isAjax) {
                // Retornar JSON para requisições AJAX
                $this->json([
                    'success' => true,
                    'solicitacao_id' => $solicitacaoId,
                    'message' => 'Solicitação criada com sucesso!',
                    'redirect' => $isEmergencialForaHorario 
                        ? url($instancia . '/solicitacao-emergencial/' . $solicitacaoId)
                        : url($instancia . '/dashboard?success=' . urlencode('Solicitação criada com sucesso! ID: #' . $solicitacaoId))
                ]);
                return;
            }
            
            // Se for emergencial e fora do horário comercial, mostrar tela com telefone
            if ($isEmergencialForaHorario) {
                $this->redirect(url($instancia . '/solicitacao-emergencial/' . $solicitacaoId));
            } else {
                // Redirecionar para o dashboard com mensagem de sucesso
                $this->redirect(url($instancia . '/dashboard?success=' . urlencode('Solicitação criada com sucesso! ID: #' . $solicitacaoId)));
            }
        } else {
            // Verificar se é requisição AJAX
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            if ($isAjax) {
                $this->json([
                    'success' => false,
                    'message' => 'Erro ao criar solicitação. Tente novamente.'
                ]);
                return;
            }
            
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
        
        // Buscar fotos
        try {
            $fotos = $this->solicitacaoModel->getFotos($id);
        } catch (\Exception $e) {
            $fotos = [];
        }
        
        // Buscar histórico de status (linha do tempo)
        try {
            $historicoStatus = $this->solicitacaoModel->getHistoricoStatus($id);
        } catch (\Exception $e) {
            $historicoStatus = [];
        }
        
        // Buscar histórico de WhatsApp (se método existir)
        $whatsappHistorico = [];
        if (method_exists($this->solicitacaoModel, 'getWhatsAppHistorico')) {
            try {
                $whatsappHistorico = $this->solicitacaoModel->getWhatsAppHistorico($id);
            } catch (\Exception $e) {
                $whatsappHistorico = [];
            }
        }
        
        $this->view('locatario.show-solicitacao', [
            'locatario' => $locatario,
            'solicitacao' => $solicitacao,
            'fotos' => $fotos,
            'historicoStatus' => $historicoStatus,
            'whatsappHistorico' => $whatsappHistorico
        ]);
    }
    
    /**
     * Processar nova solicitação
     */
    private function processarNovaSolicitacao(): void
    {
        $this->requireLocatarioAuth();
        
        $locatario = $_SESSION['locatario'];
        
        // Processar horários (máximo 3)
        $horariosRaw = $this->input('horarios_opcoes', '[]');
        $horariosArray = is_string($horariosRaw) ? json_decode($horariosRaw, true) : $horariosRaw;
        
        if (!is_array($horariosArray)) {
            $horariosArray = [];
        }
        
        // Limitar a 3 horários máximo
        if (count($horariosArray) > 3) {
            $horariosArray = array_slice($horariosArray, 0, 3);
        }
        
        // Validar que há pelo menos 1 horário
        if (empty($horariosArray)) {
            $instancia = $this->getInstanciaFromUrl();
            $this->redirect($instancia . '/nova-solicitacao', [
                'error' => 'É necessário selecionar pelo menos 1 horário (máximo 3)'
            ]);
            return;
        }
        
        $horarios = json_encode($horariosArray);
        
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
        
        // Definir condição inicial: "Aguardando Resposta do Prestador"
        $condicaoModel = new \App\Models\Condicao();
        $condicaoAguardando = $condicaoModel->findByNome('Aguardando Resposta do Prestador');
        if ($condicaoAguardando) {
            $data['condicao_id'] = $condicaoAguardando['id'];
        }
        
        // Definir status inicial: "Nova Solicitação" ou "Buscando Prestador"
        $statusModel = new \App\Models\Status();
        $statusNova = $statusModel->findByNome('Nova Solicitação');
        if (!$statusNova) {
            $statusNova = $statusModel->findByNome('Buscando Prestador');
        }
        if ($statusNova) {
            $data['status_id'] = $statusNova['id'];
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
            
            // Filtrar categorias baseado na finalidade da locação selecionada (se estiver na etapa 2 ou superior)
            $dados = $_SESSION['solicitacao_manual'] ?? [];
            $tipoImovel = $dados['tipo_imovel'] ?? 'RESIDENCIAL';
            
            // Se estiver na etapa 2 ou superior, filtrar categorias
            if ($etapa >= 2 && !empty($tipoImovel)) {
                if ($tipoImovel === 'RESIDENCIAL') {
                    $categorias = $categoriaModel->getByTipoImovel('RESIDENCIAL');
                } elseif ($tipoImovel === 'COMERCIAL') {
                    $categorias = $categoriaModel->getByTipoImovel('COMERCIAL');
                } else {
                    $categorias = $categoriaModel->getAtivas();
                }
            } else {
                // Na etapa 1, mostrar todas
                $categorias = $categoriaModel->getAtivas();
            }
            
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
                'dados' => $dados
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
            case 1: // Dados e Endereço
                $nome = trim($this->input('nome_completo'));
                $cpf = trim($this->input('cpf'));
                $whatsapp = trim($this->input('whatsapp'));
                $tipoImovel = $this->input('tipo_imovel');
                $subtipoImovel = $this->input('subtipo_imovel');
                $cep = trim($this->input('cep'));
                $endereco = trim($this->input('endereco'));
                $numero = trim($this->input('numero'));
                $complemento = trim($this->input('complemento'));
                $bairro = trim($this->input('bairro'));
                $cidade = trim($this->input('cidade'));
                $estado = trim($this->input('estado'));
                $numeroContrato = trim($this->input('numero_contrato'));

                if (empty($nome) || empty($cpf) || empty($whatsapp) || empty($tipoImovel) ||
                    empty($cep) || empty($endereco) || empty($numero) || empty($bairro) ||
                    empty($cidade) || empty($estado)) {
                    $this->redirect(url($instancia . '/solicitacao-manual?error=' . urlencode('Preencha todos os dados obrigatórios antes de continuar')));
                    return;
                }

                $solicitacaoManualModel = new \App\Models\SolicitacaoManual();
                if (!$solicitacaoManualModel->validarCPF($cpf)) {
                    $this->redirect(url($instancia . '/solicitacao-manual?error=' . urlencode('CPF inválido')));
                    return;
                }

                $whatsappLimpo = preg_replace('/\D/', '', $whatsapp);
                if (strlen($whatsappLimpo) < 10 || strlen($whatsappLimpo) > 11) {
                    $this->redirect(url($instancia . '/solicitacao-manual?error=' . urlencode('WhatsApp inválido')));
                    return;
                }

                $_SESSION['solicitacao_manual']['nome_completo'] = $nome;
                $_SESSION['solicitacao_manual']['cpf'] = $cpf;
                $_SESSION['solicitacao_manual']['whatsapp'] = $whatsapp;
                $_SESSION['solicitacao_manual']['tipo_imovel'] = $tipoImovel;
                $_SESSION['solicitacao_manual']['subtipo_imovel'] = $subtipoImovel;
                $_SESSION['solicitacao_manual']['cep'] = $cep;
                $_SESSION['solicitacao_manual']['endereco'] = $endereco;
                $_SESSION['solicitacao_manual']['numero'] = $numero;
                $_SESSION['solicitacao_manual']['complemento'] = $complemento;
                $_SESSION['solicitacao_manual']['bairro'] = $bairro;
                $_SESSION['solicitacao_manual']['cidade'] = $cidade;
                $_SESSION['solicitacao_manual']['estado'] = $estado;
                $_SESSION['solicitacao_manual']['numero_contrato'] = $numeroContrato;
                break;

            case 2: // Serviço
                $categoriaId = $this->input('categoria_id');
                $subcategoriaId = $this->input('subcategoria_id');

                if (empty($categoriaId) || empty($subcategoriaId)) {
                    $this->redirect(url($instancia . '/solicitacao-manual/etapa/2?error=' . urlencode('Selecione a categoria e o tipo de serviço para continuar')));
                    return;
                }

                $_SESSION['solicitacao_manual']['categoria_id'] = $categoriaId;
                $_SESSION['solicitacao_manual']['subcategoria_id'] = $subcategoriaId;
                break;

            case 3: // Descrição + Fotos
                $localManutencao = trim($this->input('local_manutencao'));
                $descricaoProblema = trim($this->input('descricao_problema'));

                if (empty($descricaoProblema)) {
                    $this->redirect(url($instancia . '/solicitacao-manual/etapa/3?error=' . urlencode('Descreva o problema para continuar')));
                    return;
                }

                // Upload de fotos (agora nesta etapa)
                $fotos = [];
                if (!empty($_FILES['fotos']['name'][0])) {
                    $fotos = $this->processarUploadFotos();
                }

                $_SESSION['solicitacao_manual']['local_manutencao'] = $localManutencao;
                $_SESSION['solicitacao_manual']['descricao_problema'] = $descricaoProblema;
                $_SESSION['solicitacao_manual']['fotos'] = $fotos;
                break;

            case 4: // Agendamento (somente horários)
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
            'numero_contrato' => $dados['numero_contrato'] ?? null,
            'local_manutencao' => $dados['local_manutencao'] ?? null,
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
     * Exibir tela de emergência com telefone 0800
     */
    public function solicitacaoEmergencial(string $instancia, int $solicitacaoId): void
    {
        $this->requireLocatarioAuth();
        
        $locatario = $_SESSION['locatario'];
        
        // Buscar solicitação
        $solicitacao = $this->solicitacaoModel->getDetalhes($solicitacaoId);
        
        if (!$solicitacao) {
            $this->redirect(url($instancia . '/dashboard?error=' . urlencode('Solicitação não encontrada')));
            return;
        }
        
        // Verificar se a solicitação pertence ao locatário
        $locatarioIdComparar = $locatario['codigo_locatario'] ?? $locatario['id'];
        if ($solicitacao['locatario_id'] != $locatarioIdComparar) {
            $this->redirect(url($instancia . '/dashboard?error=' . urlencode('Solicitação não encontrada')));
            return;
        }
        
        // Buscar telefone de emergência
        $telefoneEmergenciaModel = new \App\Models\TelefoneEmergencia();
        $telefoneEmergencia = $telefoneEmergenciaModel->getPrincipal();
        
        $this->view('locatario.solicitacao-emergencial', [
            'locatario' => $locatario,
            'solicitacao' => $solicitacao,
            'telefoneEmergencia' => $telefoneEmergencia,
            'instancia' => $instancia
        ]);
    }
    
    /**
     * Executar ação na solicitação (concluir, cancelar, etc)
     * POST /{instancia}/solicitacoes/{id}/acao
     */
    public function executarAcao(string $instancia, int $id): void
    {
        $this->requireLocatarioAuth();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        $acao = $this->input('acao');
        $solicitacao = $this->solicitacaoModel->find($id);
        
        if (!$solicitacao) {
            $this->json(['success' => false, 'message' => 'Solicitação não encontrada'], 404);
            return;
        }
        
        // Verificar se a solicitação pertence ao locatário logado
        $locatario = $_SESSION['locatario'];
        if ($solicitacao['locatario_id'] != $locatario['id']) {
            $this->json(['success' => false, 'message' => 'Você não tem permissão para executar esta ação'], 403);
            return;
        }
        
        try {
            $statusModel = new \App\Models\Status();
            $observacaoInput = $this->input('observacao', '');
            $valorReembolso = $this->input('valor_reembolso');
            
            // Processar upload de anexos
            $anexosSalvos = [];
            if (isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
                $anexosSalvos = $this->processarUploadAnexos($id, $_FILES['anexos']);
            }
            
            $observacaoBase = $solicitacao['observacoes'] ?? '';
            $timestamp = date('d/m/Y H:i:s');
            
            switch ($acao) {
                case 'concluido':
                    $statusConcluido = $statusModel->findByNome('Concluído');
                    if (!$statusConcluido) {
                        $statusConcluido = $statusModel->findByNome('Concluido');
                    }
                    if ($statusConcluido) {
                        $observacaoFinal = $observacaoBase;
                        if (!empty($observacaoInput)) {
                            $observacaoFinal .= "\n\n[Concluído em {$timestamp}] Observação do locatário: {$observacaoInput}";
                        } else {
                            $observacaoFinal .= "\n\n[Concluído em {$timestamp}] Serviço concluído - confirmado pelo locatário";
                        }
                        if (!empty($anexosSalvos)) {
                            $observacaoFinal .= "\nAnexos: " . implode(', ', $anexosSalvos);
                        }
                        
                        $this->solicitacaoModel->update($id, [
                            'status_id' => $statusConcluido['id'],
                            'observacoes' => $observacaoFinal
                        ]);
                        $this->json(['success' => true, 'message' => 'Solicitação marcada como concluída com sucesso!']);
                    } else {
                        $this->json(['success' => false, 'message' => 'Status "Concluído" não encontrado no sistema']);
                    }
                    break;
                    
                case 'cancelado':
                    if (empty($observacaoInput)) {
                        $this->json(['success' => false, 'message' => 'Observação é obrigatória para cancelamento'], 400);
                        return;
                    }
                    
                    // Buscar status "Cancelado"
                    $statusCancelado = $statusModel->findByNome('Cancelado');
                    if (!$statusCancelado) {
                        $statusCancelado = $statusModel->findByNome('Cancelada');
                    }
                    
                    if (!$statusCancelado) {
                        $this->json(['success' => false, 'message' => 'Status "Cancelado" não encontrado no sistema']);
                        return;
                    }
                    
                    // Buscar categoria "Cancelado"
                    $categoriaModel = new \App\Models\Categoria();
                    $sqlCategoria = "SELECT * FROM categorias WHERE nome = 'Cancelado' AND status = 'ATIVA' LIMIT 1";
                    $categoriaCancelado = \App\Core\Database::fetch($sqlCategoria);
                    
                    // Se não encontrar, buscar qualquer categoria com "Cancelado" no nome
                    if (!$categoriaCancelado) {
                        $sqlCategoria = "SELECT * FROM categorias WHERE nome LIKE '%Cancelado%' AND status = 'ATIVA' LIMIT 1";
                        $categoriaCancelado = \App\Core\Database::fetch($sqlCategoria);
                    }
                    
                    // Buscar condição "Cancelado pelo Locatário"
                    $condicaoModel = new \App\Models\Condicao();
                    $condicaoCancelado = $condicaoModel->findByNome('Cancelado pelo Locatário');
                    
                    // Se não encontrar, buscar qualquer condição com "Cancelado" no nome
                    if (!$condicaoCancelado) {
                        $sqlCondicao = "SELECT * FROM condicoes WHERE nome LIKE '%Cancelado%' AND status = 'ATIVO' LIMIT 1";
                        $condicaoCancelado = \App\Core\Database::fetch($sqlCondicao);
                    }
                    
                    $observacaoFinal = $observacaoBase . "\n\n[Cancelado em {$timestamp}] Motivo: {$observacaoInput}";
                    if (!empty($anexosSalvos)) {
                        $observacaoFinal .= "\nAnexos: " . implode(', ', $anexosSalvos);
                    }
                    
                    $updateData = [
                        'status_id' => $statusCancelado['id'],
                        'observacoes' => $observacaoFinal,
                        'motivo_cancelamento' => $observacaoInput
                    ];
                    
                    // Adicionar categoria se encontrada
                    if ($categoriaCancelado) {
                        $updateData['categoria_id'] = $categoriaCancelado['id'];
                    }
                    
                    // Adicionar condição se encontrada
                    if ($condicaoCancelado) {
                        $updateData['condicao_id'] = $condicaoCancelado['id'];
                    }
                    
                    $this->solicitacaoModel->update($id, $updateData);
                    
                    // Registrar no histórico
                    $sqlHistorico = "
                        INSERT INTO historico_status (solicitacao_id, status_id, usuario_id, observacoes, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ";
                    \App\Core\Database::query($sqlHistorico, [
                        $id,
                        $statusCancelado['id'],
                        null,
                        'Solicitação cancelada pelo locatário. Motivo: ' . $observacaoInput
                    ]);
                    
                    $this->json(['success' => true, 'message' => 'Solicitação cancelada com sucesso!']);
                    break;
                    
                case 'servico_nao_realizado':
                    $observacaoFinal = $observacaoBase . "\n\n[Serviço não realizado em {$timestamp}]";
                    if (!empty($observacaoInput)) {
                        $observacaoFinal .= " Observação: {$observacaoInput}";
                    }
                    if (!empty($anexosSalvos)) {
                        $observacaoFinal .= "\nAnexos: " . implode(', ', $anexosSalvos);
                    }
                    
                    $this->solicitacaoModel->update($id, [
                        'observacoes' => $observacaoFinal
                    ]);
                    $this->json(['success' => true, 'message' => 'Informação registrada: serviço não realizado']);
                    break;
                    
                case 'comprar_pecas':
                    $observacaoFinal = $observacaoBase . "\n\n[Comprar peças em {$timestamp}]";
                    if (!empty($observacaoInput)) {
                        $observacaoFinal .= " Observação: {$observacaoInput}";
                    }
                    if (!empty($anexosSalvos)) {
                        $observacaoFinal .= "\nAnexos: " . implode(', ', $anexosSalvos);
                    }
                    
                    $this->solicitacaoModel->update($id, [
                        'observacoes' => $observacaoFinal
                    ]);
                    $this->json(['success' => true, 'message' => 'Informação registrada: necessário comprar peças']);
                    break;
                    
                case 'reembolso':
                    if (empty($observacaoInput)) {
                        $this->json(['success' => false, 'message' => 'Justificativa é obrigatória para reembolso'], 400);
                        return;
                    }
                    if (empty($valorReembolso) || $valorReembolso <= 0) {
                        $this->json(['success' => false, 'message' => 'Valor do reembolso é obrigatório'], 400);
                        return;
                    }
                    
                    $observacaoFinal = $observacaoBase . "\n\n[Reembolso solicitado em {$timestamp}]";
                    $observacaoFinal .= "\nJustificativa: {$observacaoInput}";
                    $observacaoFinal .= "\nValor solicitado: R$ " . number_format($valorReembolso, 2, ',', '.');
                    if (!empty($anexosSalvos)) {
                        $observacaoFinal .= "\nAnexos: " . implode(', ', $anexosSalvos);
                    }
                    
                    $this->solicitacaoModel->update($id, [
                        'observacoes' => $observacaoFinal,
                        'precisa_reembolso' => 1,
                        'valor_reembolso' => floatval($valorReembolso)
                    ]);
                    $this->json(['success' => true, 'message' => 'Solicitação de reembolso registrada com sucesso!']);
                    break;
                    
                default:
                    $this->json(['success' => false, 'message' => 'Ação não reconhecida'], 400);
                    return;
            }
            
        } catch (\Exception $e) {
            error_log('Erro ao executar ação [LocatarioController]: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro ao executar ação: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Processar upload de anexos
     */
    private function processarUploadAnexos(int $solicitacaoId, array $files): array
    {
        $anexosSalvos = [];
        $uploadDir = __DIR__ . '/../../Public/uploads/solicitacoes/' . $solicitacaoId . '/anexos/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            if ($files['size'][$i] > $maxSize) {
                continue;
            }
            
            $fileType = $files['type'][$i];
            if (!in_array($fileType, $allowedTypes)) {
                continue;
            }
            
            $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $fileName = uniqid('anexo_') . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                $anexosSalvos[] = 'uploads/solicitacoes/' . $solicitacaoId . '/anexos/' . $fileName;
            }
        }
        
        return $anexosSalvos;
    }
    
    /**
     * Página de avaliação NPS
     * GET /{instancia}/solicitacoes/{id}/avaliacao
     */
    public function avaliacao(string $instancia, int $id): void
    {
        $this->requireLocatarioAuth();
        
        if ($this->isPost()) {
            $this->salvarAvaliacao($instancia, $id);
            return;
        }
        
        $solicitacao = $this->solicitacaoModel->find($id);
        if (!$solicitacao) {
            $this->redirect($instancia . '/solicitacoes');
            return;
        }
        
        $locatario = $_SESSION['locatario'];
        if ($solicitacao['locatario_id'] != $locatario['id']) {
            $this->redirect($instancia . '/solicitacoes');
            return;
        }
        
        $this->view('locatario.avaliacao', [
            'locatario' => $locatario,
            'solicitacao' => $solicitacao,
            'instancia' => $instancia
        ]);
    }
    
    /**
     * Salvar avaliação NPS
     */
    private function salvarAvaliacao(string $instancia, int $id): void
    {
        $npsScore = $this->input('nps_score');
        $comentario = $this->input('comentario', '');
        
        if (empty($npsScore) || !is_numeric($npsScore)) {
            $this->json(['success' => false, 'message' => 'Score NPS é obrigatório'], 400);
            return;
        }
        
        $solicitacao = $this->solicitacaoModel->find($id);
        if (!$solicitacao) {
            $this->json(['success' => false, 'message' => 'Solicitação não encontrada'], 404);
            return;
        }
        
        $locatario = $_SESSION['locatario'];
        if ($solicitacao['locatario_id'] != $locatario['id']) {
            $this->json(['success' => false, 'message' => 'Sem permissão'], 403);
            return;
        }
        
        try {
            // Salvar avaliação (pode criar uma tabela de avaliações ou salvar nas observações)
            $observacao = ($solicitacao['observacoes'] ?? '') . "\n\n[AVALIAÇÃO NPS - " . date('d/m/Y H:i:s') . "]";
            $observacao .= "\nScore: {$npsScore}/10";
            if (!empty($comentario)) {
                $observacao .= "\nComentário: {$comentario}";
            }
            
            $this->solicitacaoModel->update($id, [
                'observacoes' => $observacao
            ]);
            
            // TODO: Criar tabela de avaliações se necessário
            // Por enquanto, salvar nas observações
            
            $this->json(['success' => true, 'message' => 'Avaliação registrada com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Erro ao salvar avaliação [LocatarioController]: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Erro ao salvar avaliação'], 500);
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
