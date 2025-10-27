<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Subcategoria;

class CategoriasController extends Controller
{
    private Categoria $categoriaModel;
    private Subcategoria $subcategoriaModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->categoriaModel = new Categoria();
        $this->subcategoriaModel = new Subcategoria();
    }

    public function index(): void
    {
        $categorias = $this->categoriaModel->getAll();
        
        // Adicionar contagem de subcategorias para cada categoria
        foreach ($categorias as &$categoria) {
            $categoria['subcategorias_count'] = $this->subcategoriaModel->countByCategoria($categoria['id']);
        }

        $this->view('categorias.index', [
            'categorias' => $categorias
        ]);
    }

    public function create(): void
    {
        $this->view('categorias.create');
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect(url('categorias'));
        }

        $data = [
            'nome' => $this->input('nome'),
            'descricao' => $this->input('descricao'),
            'icone' => $this->input('icone'),
            'cor' => $this->input('cor'),
            'status' => $this->input('status', 'ATIVA'),
            'ordem' => $this->input('ordem', 0)
        ];

        $errors = $this->validate([
            'nome' => 'required|min:3|max:100',
            'descricao' => 'max:500',
            'status' => 'required|in:ATIVA,INATIVA'
        ], $data);

        if (!empty($errors)) {
            $this->view('categorias.create', [
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        try {
            $id = $this->categoriaModel->create($data);
            $this->redirect(url('categorias/' . $id));
        } catch (\Exception $e) {
            $this->view('categorias.create', [
                'error' => 'Erro ao criar categoria: ' . $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    public function show(int $id): void
    {
        $categoria = $this->categoriaModel->getById($id);
        
        if (!$categoria) {
            $this->view('errors.404');
            return;
        }

        $subcategorias = $this->subcategoriaModel->getByCategoria($id);

        $this->view('categorias.show', [
            'categoria' => $categoria,
            'subcategorias' => $subcategorias
        ]);
    }

    public function edit(int $id): void
    {
        $categoria = $this->categoriaModel->getById($id);
        
        if (!$categoria) {
            $this->view('errors.404');
            return;
        }

        $this->view('categorias.edit', [
            'categoria' => $categoria
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect(url('categorias/' . $id . '/edit'));
        }

        $data = [
            'nome' => $this->input('nome'),
            'descricao' => $this->input('descricao'),
            'icone' => $this->input('icone'),
            'cor' => $this->input('cor'),
            'status' => $this->input('status'),
            'ordem' => $this->input('ordem', 0)
        ];

        $errors = $this->validate([
            'nome' => 'required|min:3|max:100',
            'descricao' => 'max:500',
            'status' => 'required|in:ATIVA,INATIVA'
        ], $data);

        if (!empty($errors)) {
            $categoria = $this->categoriaModel->getById($id);
            $this->view('categorias.edit', [
                'categoria' => $categoria,
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        try {
            $this->categoriaModel->update($id, $data);
            $this->redirect(url('categorias/' . $id));
        } catch (\Exception $e) {
            $categoria = $this->categoriaModel->getById($id);
            $this->view('categorias.edit', [
                'categoria' => $categoria,
                'error' => 'Erro ao atualizar categoria: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        try {
            // Verificar se há subcategorias vinculadas
            $subcategoriasCount = $this->subcategoriaModel->countByCategoria($id);
            if ($subcategoriasCount > 0) {
                $this->json(['error' => 'Não é possível excluir categoria com subcategorias vinculadas'], 400);
                return;
            }

            // Verificar se há solicitações vinculadas
            $solicitacoesCount = $this->categoriaModel->countSolicitacoes($id);
            if ($solicitacoesCount > 0) {
                $this->json(['error' => 'Não é possível excluir categoria com solicitações vinculadas'], 400);
                return;
            }

            $this->categoriaModel->delete($id);
            $this->json(['success' => true, 'message' => 'Categoria excluída com sucesso']);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao excluir categoria: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatus(int $id): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        try {
            $categoria = $this->categoriaModel->getById($id);
            if (!$categoria) {
                $this->json(['error' => 'Categoria não encontrada'], 404);
                return;
            }

            $newStatus = $categoria['status'] === 'ATIVA' ? 'INATIVA' : 'ATIVA';
            $this->categoriaModel->update($id, ['status' => $newStatus]);
            
            $this->json([
                'success' => true, 
                'message' => 'Status atualizado com sucesso',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao atualizar status: ' . $e->getMessage()], 500);
        }
    }

    // Métodos para Subcategorias
    public function createSubcategoria(int $categoriaId): void
    {
        $categoria = $this->categoriaModel->getById($categoriaId);
        
        if (!$categoria) {
            $this->view('errors.404');
            return;
        }

        $this->view('categorias.create-subcategoria', [
            'categoria' => $categoria
        ]);
    }

    public function storeSubcategoria(int $categoriaId): void
    {
        if (!$this->isPost()) {
            $this->redirect(url('categorias/' . $categoriaId . '/subcategorias/create'));
        }

        $data = [
            'categoria_id' => $categoriaId,
            'nome' => $this->input('nome'),
            'descricao' => $this->input('descricao'),
            'tempo_estimado' => $this->input('tempo_estimado'),
            'status' => $this->input('status', 'ATIVA'),
            'ordem' => $this->input('ordem', 0)
        ];

        $errors = $this->validate([
            'nome' => 'required|min:3|max:100',
            'descricao' => 'max:500',
            'tempo_estimado' => 'numeric|min:0',
            'status' => 'required|in:ATIVA,INATIVA'
        ], $data);

        if (!empty($errors)) {
            $categoria = $this->categoriaModel->getById($categoriaId);
            $this->view('categorias.create-subcategoria', [
                'categoria' => $categoria,
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        try {
            $id = $this->subcategoriaModel->create($data);
            $this->redirect(url('categorias/' . $categoriaId));
        } catch (\Exception $e) {
            $categoria = $this->categoriaModel->getById($categoriaId);
            $this->view('categorias.create-subcategoria', [
                'categoria' => $categoria,
                'error' => 'Erro ao criar subcategoria: ' . $e->getMessage(),
                'data' => $data
            ]);
        }
    }

    public function editSubcategoria(int $categoriaId, int $subcategoriaId): void
    {
        $categoria = $this->categoriaModel->getById($categoriaId);
        $subcategoria = $this->subcategoriaModel->getById($subcategoriaId);
        
        if (!$categoria || !$subcategoria || $subcategoria['categoria_id'] != $categoriaId) {
            $this->view('errors.404');
            return;
        }

        $this->view('categorias.edit-subcategoria', [
            'categoria' => $categoria,
            'subcategoria' => $subcategoria
        ]);
    }

    public function updateSubcategoria(int $categoriaId, int $subcategoriaId): void
    {
        if (!$this->isPost()) {
            $this->redirect(url('categorias/' . $categoriaId . '/subcategorias/' . $subcategoriaId . '/edit'));
        }

        $data = [
            'nome' => $this->input('nome'),
            'descricao' => $this->input('descricao'),
            'tempo_estimado' => $this->input('tempo_estimado'),
            'status' => $this->input('status'),
            'ordem' => $this->input('ordem', 0)
        ];

        $errors = $this->validate([
            'nome' => 'required|min:3|max:100',
            'descricao' => 'max:500',
            'tempo_estimado' => 'numeric|min:0',
            'status' => 'required|in:ATIVA,INATIVA'
        ], $data);

        if (!empty($errors)) {
            $categoria = $this->categoriaModel->getById($categoriaId);
            $subcategoria = $this->subcategoriaModel->getById($subcategoriaId);
            $this->view('categorias.edit-subcategoria', [
                'categoria' => $categoria,
                'subcategoria' => $subcategoria,
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        try {
            $this->subcategoriaModel->update($subcategoriaId, $data);
            $this->redirect(url('categorias/' . $categoriaId));
        } catch (\Exception $e) {
            $categoria = $this->categoriaModel->getById($categoriaId);
            $subcategoria = $this->subcategoriaModel->getById($subcategoriaId);
            $this->view('categorias.edit-subcategoria', [
                'categoria' => $categoria,
                'subcategoria' => $subcategoria,
                'error' => 'Erro ao atualizar subcategoria: ' . $e->getMessage()
            ]);
        }
    }

    public function destroySubcategoria(int $categoriaId, int $subcategoriaId): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        try {
            // Verificar se há solicitações vinculadas
            $solicitacoesCount = $this->subcategoriaModel->countSolicitacoes($subcategoriaId);
            if ($solicitacoesCount > 0) {
                $this->json(['error' => 'Não é possível excluir subcategoria com solicitações vinculadas'], 400);
                return;
            }

            $this->subcategoriaModel->delete($subcategoriaId);
            $this->json(['success' => true, 'message' => 'Subcategoria excluída com sucesso']);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao excluir subcategoria: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatusSubcategoria(int $categoriaId, int $subcategoriaId): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
            return;
        }

        try {
            $subcategoria = $this->subcategoriaModel->getById($subcategoriaId);
            if (!$subcategoria) {
                $this->json(['error' => 'Subcategoria não encontrada'], 404);
                return;
            }

            $newStatus = $subcategoria['status'] === 'ATIVA' ? 'INATIVA' : 'ATIVA';
            $this->subcategoriaModel->update($subcategoriaId, ['status' => $newStatus]);
            
            $this->json([
                'success' => true, 
                'message' => 'Status atualizado com sucesso',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erro ao atualizar status: ' . $e->getMessage()], 500);
        }
    }
}
