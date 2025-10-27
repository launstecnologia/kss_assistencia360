<?php

// Dados da imobiliária demo (conforme banco)
$imobiliaria = [
    'nome' => 'Demo',
    'instancia' => 'demo',
    'url_base' => 'https://www.ksidemo.com.br',
    'token' => 'a3bb38ef31986068b79cf2306b8a4c37',
    'api_id' => '155'
];

echo "=== COMO A API ESTÁ SENDO MONTADA ===\n\n";

echo "1. DADOS DA IMOBILIÁRIA:\n";
echo "   Nome: " . $imobiliaria['nome'] . "\n";
echo "   Instância: " . $imobiliaria['instancia'] . "\n";
echo "   URL Base: " . $imobiliaria['url_base'] . "\n";
echo "   Token: " . $imobiliaria['token'] . "\n";
echo "   API ID: " . $imobiliaria['api_id'] . "\n\n";

echo "2. ENDPOINT MONTADO:\n";
$endpoint = $imobiliaria['url_base'] . '/kurole_include/api/webservice/escopos/';
echo "   URL: " . $endpoint . "\n\n";

echo "3. PARÂMETROS DA REQUISIÇÃO:\n";
echo "   URL Params:\n";
echo "     ws_destino: CLIENTES_AUTENTICACOES\n";
echo "     id: " . $imobiliaria['api_id'] . "\n";
echo "     token: " . $imobiliaria['token'] . "\n";
echo "   Body Params:\n";
echo "     ksi_cli_usuario: [CPF_DO_USUARIO]\n";
echo "     ksi_cli_senha: [SENHA_DO_USUARIO]\n";
echo "\n";

echo "4. HEADERS HTTP:\n";
echo "   Authorization: Bearer " . $imobiliaria['token'] . "\n";
echo "   Content-Type: application/x-www-form-urlencoded\n";
echo "   Accept: application/json\n\n";

echo "5. REQUISIÇÃO CURL COMPLETA:\n";
$urlParams = [
    'ws_destino' => 'CLIENTES_AUTENTICACOES',
    'id' => $imobiliaria['api_id'],
    'token' => $imobiliaria['token']
];
$bodyParams = [
    'ksi_cli_usuario' => '[CPF_DO_USUARIO]',
    'ksi_cli_senha' => '[SENHA_DO_USUARIO]'
];

echo "   curl -X POST \\\n";
echo "     '" . $endpoint . "?" . http_build_query($urlParams) . "' \\\n";
echo "     -H 'Authorization: Bearer " . $imobiliaria['token'] . "' \\\n";
echo "     -H 'Content-Type: application/x-www-form-urlencoded' \\\n";
echo "     -H 'Accept: application/json' \\\n";
echo "     -d '" . http_build_query($bodyParams) . "'\n\n";

echo "6. URL COMPLETA COM PARÂMETROS:\n";
echo "   " . $endpoint . "?" . http_build_query($urlParams) . "\n\n";

echo "=== TESTE MANUAL ===\n";
echo "Para testar manualmente, você pode usar:\n";
echo "1. Postman ou Insomnia\n";
echo "2. curl no terminal\n";
echo "3. Ou acessar diretamente: " . $endpoint . "?" . http_build_query($urlParams) . "\n";
