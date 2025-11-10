<?php
/**
 * Arquivo de teste para verificar se o servidor está funcionando
 * Acesse: http://localhost:8000/test-cron.php
 */

header('Content-Type: application/json');

$response = [
    'success' => true,
    'message' => 'Servidor PHP está funcionando!',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
    'path_info' => $_SERVER['PATH_INFO'] ?? 'N/A',
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

