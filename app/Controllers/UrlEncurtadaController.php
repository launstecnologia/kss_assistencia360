<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UrlEncurtada;

class UrlEncurtadaController extends Controller
{
    /**
     * Redireciona URL encurtada para URL original
     */
    public function redirecionar(string $codigo): void
    {
        try {
            // Validar que o código é numérico de 8 dígitos
            if (!preg_match('/^[0-9]{8}$/', $codigo)) {
                http_response_code(404);
                $this->view('errors/404', [
                    'title' => 'Link não encontrado',
                    'message' => 'Este link não foi encontrado ou já expirou.'
                ]);
                return;
            }
            
            $urlEncurtadaModel = new UrlEncurtada();
            $urlEncurtada = $urlEncurtadaModel->findByCodigo($codigo);
            
            if (!$urlEncurtada) {
                // URL não encontrada
                http_response_code(404);
                $this->view('errors/404', [
                    'title' => 'Link não encontrado',
                    'message' => 'Este link não foi encontrado ou já expirou.'
                ]);
                return;
            }
            
            // Incrementar contador de acessos
            $urlEncurtadaModel->incrementarAcesso($codigo);
            
            // Redirecionar para URL original
            header('Location: ' . $urlEncurtada['url_original']);
            exit;
        } catch (\Exception $e) {
            error_log('Erro ao redirecionar URL encurtada: ' . $e->getMessage());
            http_response_code(500);
            $this->view('errors/500', [
                'title' => 'Erro ao processar link',
                'message' => 'Ocorreu um erro ao processar este link. Por favor, tente novamente.'
            ]);
        }
    }
}

