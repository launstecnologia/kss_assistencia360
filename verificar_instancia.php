<?php
/**
 * Verificar status da instância WhatsApp
 */

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/app/Config/config.php';
$whatsappConfig = $config['whatsapp'] ?? [];

$apiUrl = rtrim($whatsappConfig['api_url'] ?? '', '/');
$instance = $whatsappConfig['instance'] ?? '';
$apiKey = $whatsappConfig['api_key'] ?? '';
$token = $whatsappConfig['token'] ?? '';

echo "🔍 Verificando status da instância: {$instance}\n";
echo str_repeat("=", 60) . "\n\n";

// Verificar status da instância
$statusUrl = "{$apiUrl}/instance/connectionState/{$instance}";
$ch = curl_init($statusUrl);
$headers = [
    'Content-Type: application/json',
    'apikey: ' . $apiKey
];

if (!empty($token)) {
    $headers[] = 'Authorization: Bearer ' . $token;
}

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status HTTP: {$httpCode}\n";
echo "📥 Resposta:\n";
echo $response . "\n\n";

$data = json_decode($response, true);
if ($data) {
    echo "📋 Dados da Instância:\n";
    print_r($data);
}



