<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Solicitacao;
use App\Models\Imobiliaria;
use App\Models\Usuario;
use App\Models\Categoria;

class DashboardController extends Controller
{
    private Solicitacao $solicitacaoModel;
    private Imobiliaria $imobiliariaModel;
    private Usuario $usuarioModel;
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->solicitacaoModel = new Solicitacao();
        $this->imobiliariaModel = new Imobiliaria();
        $this->usuarioModel = new Usuario();
        $this->categoriaModel = new Categoria();
    }

    public function index(): void
    {
        $user = $this->getUser();
        $periodo = $this->input('periodo', '30');

        // Estatísticas gerais
        $estatisticas = $this->solicitacaoModel->getEstatisticas($periodo);
        
        // Dados do Kanban
        $kanbanData = $this->solicitacaoModel->getKanbanData();
        
        // Solicitações recentes
        $solicitacoesRecentes = array_slice($kanbanData, 0, 10);
        
        // Solicitações pendentes (aguardando há mais de 10 dias)
        $solicitacoesPendentes = $this->solicitacaoModel->getSolicitacoesPendentes();

        // Dados específicos por nível de usuário
        $dadosAdicionais = [];
        
        if ($this->usuarioModel->isAdmin($user)) {
            $dadosAdicionais = [
                'total_imobiliarias' => $this->imobiliariaModel->count(['status' => 'ATIVA']),
                'total_usuarios' => $this->usuarioModel->count(['status' => 'ATIVO']),
                'total_categorias' => $this->categoriaModel->count(['status' => 'ATIVA']),
                'imobiliarias' => $this->imobiliariaModel->getAtivas()
            ];
        }

        $this->view('dashboard.index', array_merge([
            'estatisticas' => $estatisticas,
            'kanbanData' => $kanbanData,
            'solicitacoesRecentes' => $solicitacoesRecentes,
            'solicitacoesPendentes' => $solicitacoesPendentes,
            'periodo' => $periodo,
            'user' => $user
        ], $dadosAdicionais));
    }

    public function estatisticas(): void
    {
        $periodo = $this->input('periodo', '30');
        $imobiliariaId = $this->input('imobiliaria_id');
        
        $estatisticas = $this->solicitacaoModel->getEstatisticas($periodo);
        
        if ($imobiliariaId) {
            $estatisticasImobiliaria = $this->imobiliariaModel->getEstatisticas($imobiliariaId, $periodo);
            $estatisticas = array_merge($estatisticas, $estatisticasImobiliaria);
        }

        $this->json($estatisticas);
    }

    public function solicitacoesPorStatus(): void
    {
        $periodo = $this->input('periodo', '30');
        
        $sql = "
            SELECT 
                st.nome as status_nome,
                st.cor as status_cor,
                COUNT(*) as quantidade
            FROM solicitacoes s
            LEFT JOIN status st ON s.status_id = st.id
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY st.id, st.nome, st.cor
            ORDER BY st.ordem ASC
        ";
        
        $dados = \App\Core\Database::fetchAll($sql, [$periodo]);
        
        $this->json($dados);
    }

    public function solicitacoesPorImobiliaria(): void
    {
        $periodo = $this->input('periodo', '30');
        
        $sql = "
            SELECT 
                i.nome as imobiliaria_nome,
                COUNT(*) as quantidade
            FROM solicitacoes s
            LEFT JOIN imobiliarias i ON s.imobiliaria_id = i.id
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY i.id, i.nome
            ORDER BY quantidade DESC
        ";
        
        $dados = \App\Core\Database::fetchAll($sql, [$periodo]);
        
        $this->json($dados);
    }

    public function solicitacoesPorCategoria(): void
    {
        $periodo = $this->input('periodo', '30');
        
        $sql = "
            SELECT 
                c.nome as categoria_nome,
                COUNT(*) as quantidade
            FROM solicitacoes s
            LEFT JOIN categorias c ON s.categoria_id = c.id
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY c.id, c.nome
            ORDER BY quantidade DESC
        ";
        
        $dados = \App\Core\Database::fetchAll($sql, [$periodo]);
        
        $this->json($dados);
    }

    public function tempoMedioResolucao(): void
    {
        $periodo = $this->input('periodo', '30');
        
        $sql = "
            SELECT 
                DATE(s.created_at) as data,
                AVG(TIMESTAMPDIFF(HOUR, s.created_at, s.updated_at)) as tempo_medio_horas
            FROM solicitacoes s
            WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND s.status_id = (SELECT id FROM status WHERE nome = 'Concluído')
            GROUP BY DATE(s.created_at)
            ORDER BY data ASC
        ";
        
        $dados = \App\Core\Database::fetchAll($sql, [$periodo]);
        
        $this->json($dados);
    }
}
