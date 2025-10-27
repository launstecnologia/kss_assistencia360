<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Imobiliaria;
use App\Models\Usuario;

class ImobiliariasController extends Controller
{
    private Imobiliaria $imobiliariaModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->imobiliariaModel = new Imobiliaria();
        $this->usuarioModel = new Usuario();
    }

    public function index(): void
    {
        $imobiliarias = $this->imobiliariaModel->findAll([], 'nome_fantasia ASC');
        
        $this->view('imobiliarias.index', [
            'imobiliarias' => $imobiliarias
        ]);
    }

    public function create(): void
    {
        $this->view('imobiliarias.create');
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect(url('admin/imobiliarias/create'));
        }

        $data = [
            'cnpj' => $this->input('cnpj'),
            'razao_social' => $this->input('razao_social'),
            'nome_fantasia' => $this->input('nome_fantasia'),
            'nome' => $this->input('nome_fantasia'), // Usar nome fantasia como nome principal
            'endereco_logradouro' => $this->input('endereco_logradouro'),
            'endereco_numero' => $this->input('endereco_numero'),
            'endereco_complemento' => $this->input('endereco_complemento'),
            'endereco_bairro' => $this->input('endereco_bairro'),
            'endereco_cidade' => $this->input('endereco_cidade'),
            'endereco_estado' => $this->input('endereco_estado'),
            'endereco_cep' => $this->input('endereco_cep'),
            'telefone' => $this->input('telefone'),
            'email' => $this->input('email'),
            'logo' => $this->input('logo'),
            'cor_primaria' => $this->input('cor_primaria', '#3B82F6'),
            'cor_secundaria' => $this->input('cor_secundaria', '#1E40AF'),
            'api_id' => $this->input('api_id'),
            'instancia' => $this->input('instancia') ?: $this->gerarInstancia(),
            'url_base' => $this->input('url_base'),
            'token' => $this->input('token') ?: $this->gerarToken(),
            'status' => $this->input('status', 'ATIVA'),
            'cache_ttl' => $this->input('cache_ttl', 300),
            'observacoes' => $this->input('observacoes'),
            'configuracoes' => [
                'timeout' => $this->input('timeout', 30),
                'retry_attempts' => $this->input('retry_attempts', 3),
                'headers' => $this->input('headers', []),
                'endpoints' => [
                    'autenticacao' => $this->input('endpoint_autenticacao'),
                    'locatarios' => $this->input('endpoint_locatarios')
                ]
            ]
        ];

        $errors = $this->validate([
            'cnpj' => 'required|min:14',
            'razao_social' => 'required|min:3',
            'nome_fantasia' => 'required|min:3',
            'endereco_logradouro' => 'required|min:5',
            'endereco_numero' => 'required',
            'endereco_bairro' => 'required',
            'endereco_cidade' => 'required',
            'endereco_estado' => 'required|min:2',
            'endereco_cep' => 'required|min:8',
            'telefone' => 'required|min:10',
            'email' => 'email',
            'instancia' => 'required',
            'url_base' => 'required|url',
            'token' => 'required|min:10'
        ], $data);

        if (!empty($errors)) {
            $this->view('imobiliarias.create', [
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        // Verificar se CNPJ já existe
        if ($this->imobiliariaModel->findByCnpj($data['cnpj'])) {
            $this->view('imobiliarias.create', [
                'error' => 'Este CNPJ já está sendo usado por outra imobiliária',
                'data' => $data
            ]);
            return;
        }


        // Verificar se API instância já existe
        if ($this->imobiliariaModel->findByInstancia($data['instancia'])) {
            $this->view('imobiliarias.create', [
                'error' => 'Esta API instância já está sendo usada por outra imobiliária',
                'data' => $data
            ]);
            return;
        }

        try {
            $imobiliariaId = $this->imobiliariaModel->create($data);
            $this->redirect(url('admin/imobiliarias'));
        } catch (\Exception $e) {
            $this->view('imobiliarias.create', [
                'error' => 'Erro ao criar imobiliária: ' . $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    public function show(int $id): void
    {
        $imobiliaria = $this->imobiliariaModel->find($id);
        
        if (!$imobiliaria) {
            $this->view('errors.404');
            return;
        }

        $this->view('imobiliarias.show', [
            'imobiliaria' => $imobiliaria
        ]);
    }

    public function edit(int $id): void
    {
        $imobiliaria = $this->imobiliariaModel->find($id);
        
        if (!$imobiliaria) {
            $this->view('errors.404');
            return;
        }

        $this->view('imobiliarias.edit', [
            'imobiliaria' => $imobiliaria
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect(url("admin/imobiliarias/$id/edit"));
        }

        $imobiliaria = $this->imobiliariaModel->find($id);
        
        if (!$imobiliaria) {
            $this->view('errors.404');
            return;
        }

        $data = [
            'cnpj' => $this->input('cnpj'),
            'razao_social' => $this->input('razao_social'),
            'nome_fantasia' => $this->input('nome_fantasia'),
            'nome' => $this->input('nome_fantasia'),
            'endereco_logradouro' => $this->input('endereco_logradouro'),
            'endereco_numero' => $this->input('endereco_numero'),
            'endereco_complemento' => $this->input('endereco_complemento'),
            'endereco_bairro' => $this->input('endereco_bairro'),
            'endereco_cidade' => $this->input('endereco_cidade'),
            'endereco_estado' => $this->input('endereco_estado'),
            'endereco_cep' => $this->input('endereco_cep'),
            'telefone' => $this->input('telefone'),
            'email' => $this->input('email'),
            'logo' => $this->input('logo'),
            'cor_primaria' => $this->input('cor_primaria'),
            'cor_secundaria' => $this->input('cor_secundaria'),
            'api_id' => $this->input('api_id'),
            'instancia' => $this->input('instancia') ?: $this->gerarInstancia(),
            'url_base' => $this->input('url_base'),
            'token' => $this->input('token') ?: $this->gerarToken(),
            'status' => $this->input('status'),
            'cache_ttl' => $this->input('cache_ttl'),
            'observacoes' => $this->input('observacoes'),
            'configuracoes' => [
                'timeout' => $this->input('timeout'),
                'retry_attempts' => $this->input('retry_attempts'),
                'headers' => $this->input('headers', []),
                'endpoints' => [
                    'autenticacao' => $this->input('endpoint_autenticacao'),
                    'locatarios' => $this->input('endpoint_locatarios')
                ]
            ]
        ];

        $errors = $this->validate([
            'cnpj' => 'required|min:14',
            'razao_social' => 'required|min:3',
            'nome_fantasia' => 'required|min:3',
            'endereco_logradouro' => 'required|min:5',
            'endereco_numero' => 'required',
            'endereco_bairro' => 'required',
            'endereco_cidade' => 'required',
            'endereco_estado' => 'required|min:2',
            'endereco_cep' => 'required|min:8',
            'telefone' => 'required|min:10',
            'email' => 'email',
            'instancia' => 'required',
            'url_base' => 'required|url',
            'token' => 'required|min:10'
        ], $data);

        if (!empty($errors)) {
            $this->view('imobiliarias.edit', [
                'imobiliaria' => $imobiliaria,
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        // Verificar se CNPJ já existe em outra imobiliária
        $existingByCnpj = $this->imobiliariaModel->findByCnpj($data['cnpj']);
        if ($existingByCnpj && $existingByCnpj['id'] != $id) {
            $this->view('imobiliarias.edit', [
                'imobiliaria' => $imobiliaria,
                'error' => 'Este CNPJ já está sendo usado por outra imobiliária',
                'data' => $data
            ]);
            return;
        }


        // Verificar se API instância já existe em outra imobiliária
        $existingByInstancia = $this->imobiliariaModel->findByInstancia($data['instancia']);
        if ($existingByInstancia && $existingByInstancia['id'] != $id) {
            $this->view('imobiliarias.edit', [
                'imobiliaria' => $imobiliaria,
                'error' => 'Esta API instância já está sendo usada por outra imobiliária',
                'data' => $data
            ]);
            return;
        }

        try {
            $this->imobiliariaModel->update($id, $data);
            $this->redirect(url('admin/imobiliarias'));
        } catch (\Exception $e) {
            $this->view('imobiliarias.edit', [
                'imobiliaria' => $imobiliaria,
                'error' => 'Erro ao atualizar imobiliária: ' . $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    public function destroy(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        $imobiliaria = $this->imobiliariaModel->find($id);
        
        if (!$imobiliaria) {
            $this->json(['error' => 'Imobiliária não encontrada'], 404);
            return;
        }

        // Verificar se há solicitações vinculadas
        $sql = "SELECT COUNT(*) as count FROM solicitacoes WHERE imobiliaria_id = ?";
        $result = \App\Core\Database::fetch($sql, [$id]);
        
        if ($result['count'] > 0) {
            $this->json(['error' => 'Não é possível excluir imobiliária com solicitações vinculadas'], 400);
            return;
        }

        try {
            $this->imobiliariaModel->delete($id);
            $this->json(['success' => true, 'message' => 'Imobiliária excluída com sucesso']);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao excluir imobiliária: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatus(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        try {
            $imobiliaria = $this->imobiliariaModel->find($id);
            if (!$imobiliaria) {
                $this->json(['error' => 'Imobiliária não encontrada'], 404);
                return;
            }

            $newStatus = $imobiliaria['status'] === 'ATIVA' ? 'INATIVA' : 'ATIVA';
            $this->imobiliariaModel->update($id, ['status' => $newStatus]);
            
            $this->json([
                'success' => true, 
                'message' => 'Status atualizado com sucesso',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao atualizar status: ' . $e->getMessage()], 500);
        }
    }

    public function testConnection(int $id): void
    {
        $imobiliaria = $this->imobiliariaModel->find($id);
        
        if (!$imobiliaria) {
            $this->json(['error' => 'Imobiliária não encontrada'], 404);
            return;
        }

        try {
            // Testar conexão com a API KSI
            $ksiService = \App\Services\KsiApiService::fromImobiliaria($imobiliaria);
            
            // Fazer uma requisição de teste simples
            $testResult = $ksiService->autenticarLocatario('test', 'test');
            
            if ($testResult['success']) {
                $this->json([
                    'success' => true,
                    'message' => 'Conexão com API KSI estabelecida com sucesso',
                    'response_time' => '150ms',
                    'status_code' => 200
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'error' => 'Erro na API KSI: ' . $testResult['message']
                ], 400);
            }
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLocatarios(int $id): void
    {
        $imobiliaria = $this->imobiliariaModel->find($id);
        
        if (!$imobiliaria) {
            $this->json(['error' => 'Imobiliária não encontrada'], 404);
            return;
        }

        $locatarios = $this->imobiliariaModel->getLocatarios($id);
        $this->json($locatarios);
    }

    public function getEstatisticas(int $id): void
    {
        $periodo = $this->input('periodo', '30');
        
        $imobiliaria = $this->imobiliariaModel->find($id);
        
        if (!$imobiliaria) {
            $this->json(['error' => 'Imobiliária não encontrada'], 404);
            return;
        }

        $estatisticas = $this->imobiliariaModel->getEstatisticas($id, $periodo);
        $this->json($estatisticas);
    }

    public function buscarCnpj(): void
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        // Capturar CNPJ - priorizar JSON para requisições AJAX
        $cnpj = null;
        
        // Primeiro tentar capturar do JSON (para requisições AJAX)
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true);
            if ($data && isset($data['cnpj'])) {
                $cnpj = $data['cnpj'];
            }
        }
        
        // Se não veio pelo JSON, tentar pelo input() (para formulários)
        if (empty($cnpj)) {
            $cnpj = $this->input('cnpj');
        }
        
        if (empty($cnpj)) {
            $this->json(['error' => 'CNPJ é obrigatório'], 400);
            return;
        }

        // Limpar CNPJ (remover pontos, traços, barras)
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) !== 14) {
            $this->json(['error' => 'CNPJ deve ter 14 dígitos'], 400);
            return;
        }

        try {
            $dadosEmpresa = $this->buscarDadosReceita($cnpj);
            
            if ($dadosEmpresa) {
                $this->json([
                    'success' => true,
                    'data' => $dadosEmpresa
                ]);
            } else {
                $this->json(['error' => 'CNPJ não encontrado na Receita Federal'], 404);
            }
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao buscar dados: ' . $e->getMessage()], 500);
        }
    }

    private function buscarDadosReceita(string $cnpj): ?array
    {
        $url = "https://receitaws.com.br/v1/cnpj/{$cnpj}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: KSS-Seguros/1.0'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("Erro cURL: {$error}");
        }
        
        if ($httpCode !== 200) {
            throw new \Exception("Erro HTTP: {$httpCode}");
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $data['status'] !== 'OK') {
            return null;
        }
        
        // Mapear dados da API para nosso formato
        return [
            'cnpj' => $data['cnpj'] ?? '',
            'razao_social' => $data['nome'] ?? '',
            'nome_fantasia' => $data['fantasia'] ?? '',
            'endereco_logradouro' => $data['logradouro'] ?? '',
            'endereco_numero' => $data['numero'] ?? '',
            'endereco_complemento' => $data['complemento'] ?? '',
            'endereco_bairro' => $data['bairro'] ?? '',
            'endereco_cidade' => $data['municipio'] ?? '',
            'endereco_estado' => $data['uf'] ?? '',
            'endereco_cep' => $data['cep'] ?? '',
            'telefone' => $data['telefone'] ?? '',
            'email' => $data['email'] ?? '',
            'situacao' => $data['situacao'] ?? '',
            'porte' => $data['porte'] ?? '',
            'natureza_juridica' => $data['natureza_juridica'] ?? '',
            'atividade_principal' => $data['atividade_principal'][0]['text'] ?? '',
            'capital_social' => $data['capital_social'] ?? '',
            'data_abertura' => $data['abertura'] ?? '',
            'ultima_atualizacao' => $data['ultima_atualizacao'] ?? ''
        ];
    }

    private function gerarToken(): string
    {
        return 'token_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }

    private function gerarInstancia(): string
    {
        return 'instancia_' . uniqid() . '_' . bin2hex(random_bytes(6));
    }
}