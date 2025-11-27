<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Imobiliaria;

class UploadController extends Controller
{
    private Imobiliaria $imobiliariaModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->imobiliariaModel = new Imobiliaria();
    }

    public function index(): void
    {
        $this->view('admin/upload/index', [
            'title' => 'Upload de CSV',
            'currentPage' => 'upload',
            'pageTitle' => 'Upload de CSV'
        ]);
    }

    /**
     * Processar upload de CSV
     */
    public function processar(): void
    {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'error' => 'Método não permitido'], 405);
            return;
        }

        // Verificar se arquivo foi enviado
        if (empty($_FILES['csv_file']['name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'Nenhum arquivo foi enviado ou ocorreu um erro no upload'], 400);
            return;
        }

        $file = $_FILES['csv_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];

        // Validar tamanho (máximo 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($fileSize > $maxSize) {
            $this->json(['success' => false, 'error' => 'Arquivo muito grande. Tamanho máximo: 10MB'], 400);
            return;
        }

        // Validar extensão
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $this->json(['success' => false, 'error' => 'Formato de arquivo não permitido. Use .csv'], 400);
            return;
        }

        // Verificar se a tabela locatarios_contratos existe
        $this->garantirTabelaLocatariosContratos();

        try {
            // Processar CSV
            $handle = fopen($fileTmpName, 'r');
            if ($handle === false) {
                throw new \Exception('Não foi possível abrir o arquivo CSV');
            }

            // Detectar separador (vírgula ou ponto e vírgula)
            $primeiraLinha = fgets($handle);
            rewind($handle);
            
            $separador = ',';
            if (strpos($primeiraLinha, ';') !== false && substr_count($primeiraLinha, ';') >= substr_count($primeiraLinha, ',')) {
                $separador = ';';
            }

            // Ler cabeçalho
            $header = fgetcsv($handle, 0, $separador);
            if (!$header) {
                throw new \Exception('Não foi possível ler o cabeçalho do arquivo');
            }

            // Normalizar nomes das colunas (remover espaços, converter para minúsculas)
            $headerNormalizado = array_map(function($col) {
                return strtolower(trim($col));
            }, $header);

            // Mapear índices das colunas
            $indices = [
                'cpf' => array_search('inquilino_doc', $headerNormalizado),
                'nome' => array_search('inquilino_nome', $headerNormalizado),
                'tipo_imovel' => array_search('imofinalidade', $headerNormalizado),
                'cidade' => array_search('cidade', $headerNormalizado),
                'estado' => array_search('estado', $headerNormalizado),
                'bairro' => array_search('bairro', $headerNormalizado),
                'cep' => array_search('cep', $headerNormalizado),
                'endereco' => array_search('endereco', $headerNormalizado),
                'numero' => array_search('numero', $headerNormalizado),
                'complemento' => array_search('complemento', $headerNormalizado),
                'unidade' => array_search('unidade', $headerNormalizado),
                'contrato' => array_search('contrato', $headerNormalizado),
                'empresa_fiscal' => array_search('empresa_fiscal', $headerNormalizado)
            ];

            // Verificar se todas as colunas obrigatórias foram encontradas
            $colunasFaltando = [];
            foreach (['cpf', 'contrato', 'empresa_fiscal'] as $col) {
                if ($indices[$col] === false) {
                    $colunasFaltando[] = $col;
                }
            }

            if (!empty($colunasFaltando)) {
                fclose($handle);
                $this->json([
                    'success' => false,
                    'error' => 'Colunas obrigatórias não encontradas: ' . implode(', ', $colunasFaltando)
                ], 400);
                return;
            }

            // Buscar todas as imobiliárias para matching
            $imobiliarias = $this->imobiliariaModel->getAll();
            $imobiliariasMap = [];
            foreach ($imobiliarias as $imob) {
                $nomeNormalizado = $this->normalizarNome($imob['nome_fantasia'] ?? $imob['nome'] ?? '');
                $imobiliariasMap[$nomeNormalizado] = $imob;
            }

            $sucessos = 0;
            $erros = 0;
            $detalhesErros = [];
            $linha = 1;

            // Processar linhas
            while (($row = fgetcsv($handle, 0, $separador)) !== false) {
                $linha++;
                
                try {
                    // Extrair dados
                    $cpf = isset($row[$indices['cpf']]) ? trim($row[$indices['cpf']]) : '';
                    $contrato = isset($row[$indices['contrato']]) ? trim($row[$indices['contrato']]) : '';
                    $empresaFiscal = isset($row[$indices['empresa_fiscal']]) ? trim($row[$indices['empresa_fiscal']]) : '';

                    // Validar dados obrigatórios
                    if (empty($cpf)) {
                        $erros++;
                        $detalhesErros[] = "Linha {$linha}: CPF/CNPJ não informado";
                        continue;
                    }

                    if (empty($contrato)) {
                        $erros++;
                        $detalhesErros[] = "Linha {$linha}: Número do contrato não informado";
                        continue;
                    }

                    if (empty($empresaFiscal)) {
                        $erros++;
                        $detalhesErros[] = "Linha {$linha}: Empresa fiscal não informada";
                        continue;
                    }

                    // Limpar CPF/CNPJ
                    $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
                    
                    // Validar CPF (11 dígitos) ou CNPJ (14 dígitos)
                    if (strlen($cpfLimpo) !== 11 && strlen($cpfLimpo) !== 14) {
                        $erros++;
                        $detalhesErros[] = "Linha {$linha}: CPF/CNPJ inválido (deve ter 11 ou 14 dígitos)";
                        continue;
                    }

                    // Identificar imobiliária por nome similar
                    $imobiliaria = $this->identificarImobiliaria($empresaFiscal, $imobiliarias);
                    
                    if (!$imobiliaria) {
                        $erros++;
                        $detalhesErros[] = "Linha {$linha}: Imobiliária não encontrada para '{$empresaFiscal}'";
                        continue;
                    }

                    // Verificar se já existe
                    $sql = "SELECT * FROM locatarios_contratos 
                            WHERE imobiliaria_id = ? AND cpf = ? AND numero_contrato = ?";
                    $existente = \App\Core\Database::fetch($sql, [$imobiliaria['id'], $cpfLimpo, $contrato]);

                    if ($existente) {
                        // Atualizar registro existente
                        $updateSql = "UPDATE locatarios_contratos 
                                     SET updated_at = NOW() 
                                     WHERE id = ?";
                        \App\Core\Database::query($updateSql, [$existente['id']]);
                        $sucessos++;
                    } else {
                        // Criar novo registro
                        $insertSql = "INSERT INTO locatarios_contratos 
                                     (imobiliaria_id, cpf, numero_contrato, created_at, updated_at) 
                                     VALUES (?, ?, ?, NOW(), NOW())";
                        \App\Core\Database::query($insertSql, [$imobiliaria['id'], $cpfLimpo, $contrato]);
                        $sucessos++;
                    }
                } catch (\Exception $e) {
                    $erros++;
                    $mensagemErro = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8');
                    $detalhesErros[] = "Linha {$linha}: Erro ao processar - " . $mensagemErro;
                    error_log("Erro ao processar linha {$linha} do CSV: " . $e->getMessage());
                }
            }

            fclose($handle);

            $mensagem = "Processamento concluído: {$sucessos} registro(s) processado(s) com sucesso";
            if ($erros > 0) {
                $mensagem .= ", {$erros} erro(s) encontrado(s)";
            }

            // Limitar e sanitizar detalhes de erros
            $detalhesErrosLimitados = array_slice($detalhesErros, 0, 100);
            $detalhesErrosSanitizados = array_map(function($erro) {
                return mb_convert_encoding($erro, 'UTF-8', 'UTF-8');
            }, $detalhesErrosLimitados);

            // Limpar qualquer output anterior
            if (ob_get_level()) {
                ob_clean();
            }
            
            $this->json([
                'success' => true,
                'message' => $mensagem,
                'sucessos' => $sucessos,
                'erros' => $erros,
                'detalhes_erros' => $detalhesErrosSanitizados
            ]);

        } catch (\Exception $e) {
            error_log("Erro ao processar CSV: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Garantir que sempre retornamos JSON válido
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            
            $errorMessage = 'Erro ao processar arquivo CSV: ' . $e->getMessage();
            // Limpar qualquer output anterior
            if (ob_get_level()) {
                ob_clean();
            }
            
            echo json_encode([
                'success' => false,
                'error' => $errorMessage
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * Identificar imobiliária por nome similar ou instância
     */
    private function identificarImobiliaria(string $empresaFiscal, array $imobiliarias): ?array
    {
        $empresaFiscalNormalizada = $this->normalizarNome($empresaFiscal);
        
        // Extrair palavra-chave principal (ex: "Lago" de "Lago - Administrativo")
        $palavras = explode(' ', $empresaFiscalNormalizada);
        $palavraChave = !empty($palavras) ? $palavras[0] : $empresaFiscalNormalizada;
        
        // Se a palavra-chave for muito curta, usar o nome completo
        if (strlen($palavraChave) < 3) {
            $palavraChave = $empresaFiscalNormalizada;
        }
        
        $melhorMatch = null;
        $melhorScore = 0;
        
        // Tentar match exato primeiro
        foreach ($imobiliarias as $imob) {
            $nomeNormalizado = $this->normalizarNome($imob['nome_fantasia'] ?? $imob['nome'] ?? '');
            
            // Match exato
            if ($nomeNormalizado === $empresaFiscalNormalizada) {
                return $imob;
            }
            
            // Verificar se a palavra-chave está no nome (match por palavra-chave)
            if (stripos($nomeNormalizado, $palavraChave) !== false && strlen($palavraChave) >= 3) {
                // Calcular similaridade para priorizar
                similar_text($empresaFiscalNormalizada, $nomeNormalizado, $percent);
                if ($percent > $melhorScore) {
                    $melhorScore = $percent;
                    $melhorMatch = $imob;
                }
            }
            
            // Verificar pela instância
            $instancia = strtolower(trim($imob['instancia'] ?? ''));
            if (!empty($instancia)) {
                // Verificar se a instância está no empresa_fiscal ou vice-versa
                if (stripos($empresaFiscalNormalizada, $instancia) !== false || 
                    stripos($instancia, $palavraChave) !== false) {
                    similar_text($empresaFiscalNormalizada, $nomeNormalizado, $percent);
                    if ($percent > $melhorScore) {
                        $melhorScore = $percent;
                        $melhorMatch = $imob;
                    }
                }
            }
            
            // Verificar se palavras-chave do nome estão no empresa_fiscal
            $palavrasNome = explode(' ', $nomeNormalizado);
            foreach ($palavrasNome as $palavraNome) {
                if (strlen($palavraNome) >= 3 && stripos($empresaFiscalNormalizada, $palavraNome) !== false) {
                    similar_text($empresaFiscalNormalizada, $nomeNormalizado, $percent);
                    if ($percent > $melhorScore) {
                        $melhorScore = $percent;
                        $melhorMatch = $imob;
                    }
                }
            }
        }

        // Se encontrou match por palavra-chave, retornar
        if ($melhorMatch && $melhorScore > 30) {
            return $melhorMatch;
        }

        // Tentar match por similaridade geral (fallback)
        foreach ($imobiliarias as $imob) {
            $nomeNormalizado = $this->normalizarNome($imob['nome_fantasia'] ?? $imob['nome'] ?? '');
            
            // Calcular similaridade
            similar_text($empresaFiscalNormalizada, $nomeNormalizado, $percent);
            
            // Se a similaridade for maior que 30%, considerar match
            if ($percent > 30 && $percent > $melhorScore) {
                $melhorScore = $percent;
                $melhorMatch = $imob;
            }
        }

        return $melhorMatch;
    }

    /**
     * Normalizar nome para comparação
     */
    private function normalizarNome(string $nome): string
    {
        // Converter para minúsculas, remover acentos, espaços extras, etc.
        $nome = mb_strtolower($nome, 'UTF-8');
        $nome = preg_replace('/\s+/', ' ', trim($nome));
        
        // Remover sufixos comuns como " - Administrativo", " - Locação", etc.
        $nome = preg_replace('/\s*-\s*(administrativo|administração|locacao|locação|sumaré|sumare).*$/i', '', $nome);
        
        // Remover palavras comuns
        $palavrasRemover = ['ltda', 'me', 'eireli', 'administração', 'administrativo', 'de', 'da', 'do', 'dos', 'das', 'imoveis', 'imóveis'];
        foreach ($palavrasRemover as $palavra) {
            $nome = preg_replace('/\b' . preg_quote($palavra, '/') . '\b/i', '', $nome);
        }
        
        $nome = preg_replace('/\s+/', ' ', trim($nome));
        
        return $nome;
    }

    /**
     * Garantir que a tabela locatarios_contratos existe
     */
    private function garantirTabelaLocatariosContratos(): void
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM information_schema.tables 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'locatarios_contratos'";
            $result = \App\Core\Database::fetch($sql);
            
            if (empty($result) || ($result['count'] ?? 0) == 0) {
                $createTableSql = "CREATE TABLE locatarios_contratos (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    imobiliaria_id INT NOT NULL,
                    cpf VARCHAR(14) NOT NULL,
                    numero_contrato VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (imobiliaria_id) REFERENCES imobiliarias(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_cpf_contrato_imobiliaria (imobiliaria_id, cpf, numero_contrato),
                    INDEX idx_cpf_imobiliaria (imobiliaria_id, cpf)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                \App\Core\Database::query($createTableSql);
                error_log("Tabela locatarios_contratos criada automaticamente");
            }
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate table') === false) {
                error_log("Erro ao verificar/criar tabela locatarios_contratos: " . $e->getMessage());
            }
        }
    }
}

