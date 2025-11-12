<?php

use App\Core\Router;

// Usar a instância do Router passada como parâmetro ou criar uma nova
if (!isset($router)) {
    $router = new Router();
}

// Middlewares
$router->middleware('auth', function() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        return false;
    }
    return true;
});

$router->middleware('admin', function() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'ADMINISTRADOR') {
        header('Location: /admin/dashboard');
        return false;
    }
    return true;
});

// Rotas públicas
$router->get('/', 'PwaController@index'); // Página inicial vai para PWA do locatário
$router->get('/pwa', 'PwaController@index');
$router->get('/pwa/login', 'PwaController@login');
$router->post('/pwa/login', 'PwaController@authenticate');
// APIs para busca de imobiliárias
$router->get('/api/estados', 'PwaController@getEstados');
$router->get('/api/cidades', 'PwaController@getCidades');
$router->get('/api/imobiliarias', 'PwaController@getImobiliarias');

// Webhook para receber mensagens da Evolution API (sem autenticação)
$router->post('/webhook/whatsapp', 'ChatController@webhook');

// Rotas públicas para tokens de confirmação/cancelamento (sem autenticação)
$router->get('/confirmacao-horario', 'TokenController@confirmacaoHorario');
$router->post('/confirmacao-horario', 'TokenController@confirmacaoHorario');
$router->get('/cancelamento-horario', 'TokenController@cancelamentoHorario');
$router->post('/cancelamento-horario', 'TokenController@cancelamentoHorario');
$router->get('/cancelar-solicitacao', 'TokenController@cancelarSolicitacao');
$router->post('/cancelar-solicitacao', 'TokenController@cancelarSolicitacao');
$router->get('/reagendamento-horario', 'TokenController@reagendamentoHorario');
$router->post('/reagendamento-horario', 'TokenController@reagendamentoHorario');
$router->get('/compra-peca', 'TokenController@compraPeca');
$router->post('/compra-peca', 'TokenController@compraPeca');
$router->get('/status-servico', 'TokenController@statusServico');
$router->get('/acoes-servico', 'TokenController@acoesServico');
$router->post('/acoes-servico', 'TokenController@processarAcaoServico');

// Rotas do admin/operador
$router->get('/admin', 'AuthController@showLogin'); // Admin vai para login de operador
$router->get('/operador', 'AuthController@showLogin'); // Operador também vai para login
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@sendPasswordReset');
$router->get('/reset-password', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/pwa/solicitar', 'PwaController@solicitar', ['auth']);
$router->post('/pwa/solicitar', 'PwaController@createSolicitacao', ['auth']);
$router->get('/pwa/solicitacoes', 'PwaController@solicitacoes', ['auth']);
$router->get('/pwa/solicitacao/{id}', 'PwaController@showSolicitacao', ['auth']);

// Painel Administrativo
$router->get('/admin/dashboard', 'DashboardController@index', ['auth']);
$router->get('/admin/relatorios', 'RelatoriosController@index', ['auth']);
$router->get('/admin/kanban', 'DashboardController@kanban', ['auth']);
$router->get('/admin/kanban/novas-solicitacoes', 'DashboardController@novasSolicitacoes', ['auth']);
$router->post('/admin/kanban/mover', 'DashboardController@moverCard', ['auth']);
$router->post('/admin/kanban/atualizar-condicao', 'DashboardController@atualizarCondicao', ['auth']);
$router->get('/admin/dashboard/solicitacoes-por-imobiliaria', 'DashboardController@solicitacoesPorImobiliaria', ['auth']);
$router->get('/admin/dashboard/solicitacoes-por-status', 'DashboardController@solicitacoesPorStatus', ['auth']);
$router->get('/admin/templates-whatsapp', 'WhatsappTemplatesController@index', ['auth']);
$router->post('/admin/templates-whatsapp', 'WhatsappTemplatesController@store', ['auth']);
$router->get('/admin/templates-whatsapp/{id}/edit', 'WhatsappTemplatesController@edit', ['auth']);
$router->post('/admin/templates-whatsapp/{id}', 'WhatsappTemplatesController@update', ['auth']);
$router->post('/admin/templates-whatsapp/{id}/delete', 'WhatsappTemplatesController@destroy', ['auth']);
// Rotas para gerenciar instâncias WhatsApp
$router->get('/admin/whatsapp-instances', 'WhatsappInstancesController@index', ['auth']);
$router->get('/admin/whatsapp-instances/create', 'WhatsappInstancesController@create', ['auth']);
$router->post('/admin/whatsapp-instances', 'WhatsappInstancesController@store', ['auth']);
$router->get('/admin/whatsapp-instances/{id}/qrcode', 'WhatsappInstancesController@qrcode', ['auth']);
$router->post('/admin/whatsapp-instances/{id}/atualizar-qrcode', 'WhatsappInstancesController@atualizarQrcode', ['auth']);
$router->get('/admin/whatsapp-instances/{id}/verificar-status', 'WhatsappInstancesController@verificarStatus', ['auth']);
$router->post('/admin/whatsapp-instances/{id}/set-padrao', 'WhatsappInstancesController@setPadrao', ['auth']);
$router->post('/admin/whatsapp-instances/{id}/reiniciar', 'WhatsappInstancesController@reiniciar', ['auth']);
$router->post('/admin/whatsapp-instances/{id}/logout', 'WhatsappInstancesController@logout', ['auth']);
$router->post('/admin/whatsapp-instances/{id}/desconectar', 'WhatsappInstancesController@desconectar', ['auth']);
$router->post('/admin/whatsapp-instances/{id}/delete', 'WhatsappInstancesController@destroy', ['auth']);
$router->get('/admin/solicitacoes', 'SolicitacoesController@index', ['auth']);
$router->get('/admin/solicitacoes/{id}/api', 'SolicitacoesController@api', ['auth']);
$router->get('/admin/solicitacoes/{id}', 'SolicitacoesController@show', ['auth']);
// Rotas do chat
$router->get('/admin/chat/{solicitacao_id}/mensagens', 'ChatController@getMensagens', ['auth']);
$router->post('/admin/chat/{solicitacao_id}/enviar', 'ChatController@enviarMensagem', ['auth']);
$router->post('/admin/chat/{solicitacao_id}/encerrar', 'ChatController@encerrarAtendimento', ['auth']);
$router->get('/admin/chat/instancias', 'ChatController@getInstancias', ['auth']);
$router->post('/admin/solicitacoes/{id}/status', 'SolicitacoesController@updateStatus', ['auth']);
$router->post('/admin/solicitacoes/{id}/confirmar-horario', 'SolicitacoesController@confirmarHorario', ['auth']);
$router->post('/admin/solicitacoes/{id}/desconfirmar-horario', 'SolicitacoesController@desconfirmarHorario', ['auth']);
$router->post('/admin/solicitacoes/{id}/horarios/bulk', 'SolicitacoesController@confirmarHorariosBulk', ['auth']);
$router->post('/admin/solicitacoes/{id}/confirmar-servico', 'SolicitacoesController@confirmarServico', ['auth']);
$router->post('/admin/solicitacoes/{id}/solicitar-novos-horarios', 'SolicitacoesController@solicitarNovosHorarios', ['auth']);
$router->post('/admin/solicitacoes/{id}/adicionar-horario-seguradora', 'SolicitacoesController@adicionarHorarioSeguradora', ['auth']);
$router->post('/admin/solicitacoes/{id}/remover-horario-seguradora', 'SolicitacoesController@removerHorarioSeguradora', ['auth']);
$router->post('/admin/solicitacoes/{id}/atualizar', 'SolicitacoesController@atualizarDetalhes', ['auth']);
$router->get('/admin/solicitacoes/{id}/edit', 'SolicitacoesController@edit', ['auth']);
$router->post('/admin/solicitacoes/{id}/edit', 'SolicitacoesController@update', ['auth']);

// Rotas do fluxo operacional
$router->post('/admin/solicitacoes/criar', 'SolicitacoesController@criarSolicitacao', ['auth']);
$router->post('/admin/solicitacoes/confirmar-datas', 'SolicitacoesController@confirmarDatas', ['auth']);
$router->post('/admin/solicitacoes/cancelar', 'SolicitacoesController@cancelarSolicitacao', ['auth']);

// Rotas do ciclo de agendamento
$router->post('/admin/solicitacoes/{id}/aceitar-data-prestador', 'SolicitacoesController@aceitarDataPrestador', ['auth']);
$router->post('/admin/solicitacoes/{id}/recusar-propor-datas', 'SolicitacoesController@recusarProporDatas', ['auth']);
$router->post('/admin/solicitacoes/{id}/aceitar-data-locatario', 'SolicitacoesController@aceitarDataLocatario', ['auth']);
$router->post('/admin/solicitacoes/{id}/recusar-datas-locatario', 'SolicitacoesController@recusarDatasLocatario', ['auth']);
$router->post('/admin/solicitacoes/{id}/confirmar-agendamento-final', 'SolicitacoesController@confirmarAgendamentoFinal', ['auth']);
$router->post('/admin/solicitacoes/confirmar-atendimento', 'SolicitacoesController@confirmarAtendimento');
$router->post('/admin/solicitacoes/informar-compra-peca', 'SolicitacoesController@informarCompraPeca', ['auth']);
$router->post('/admin/solicitacoes/enviar-lembretes', 'SolicitacoesController@enviarLembretes', ['auth', 'admin']);
$router->post('/admin/solicitacoes/expirar', 'SolicitacoesController@expirarSolicitacoes', ['auth', 'admin']);
$router->post('/admin/solicitacoes/enviar-notificacoes-pre-servico', 'SolicitacoesController@enviarNotificacoesPreServico', ['auth', 'admin']);
$router->post('/admin/solicitacoes/enviar-notificacoes-pos-servico', 'SolicitacoesController@enviarNotificacoesPosServico', ['auth', 'admin']);
// Rota pública para cron job (sem autenticação - configurar proteção no servidor)
$router->get('/cron/notificacoes-pre-servico', 'SolicitacoesController@cronNotificacoesPreServico');
$router->get('/cron/notificacoes-pos-servico', 'SolicitacoesController@cronNotificacoesPosServico');
$router->get('/cron/lembretes-peca', 'SolicitacoesController@cronLembretesPeca');

// Gerenciamento de Cron Jobs
$router->get('/admin/cron-jobs', 'CronJobsController@index', ['auth', 'admin']);
$router->post('/admin/cron-jobs/{id}/executar', 'CronJobsController@executar', ['auth', 'admin']);
$router->post('/admin/cron-jobs/{id}/toggle-ativo', 'CronJobsController@toggleAtivo', ['auth', 'admin']);
$router->post('/admin/cron-jobs/{id}/atualizar-frequencia', 'CronJobsController@atualizarFrequencia', ['auth', 'admin']);
$router->get('/admin/cron-jobs/{id}/historico', 'CronJobsController@historico', ['auth', 'admin']);
$router->post('/admin/cron-jobs/executar-pendentes', 'CronJobsController@executarPendentes', ['auth', 'admin']);

// Visualizador de Logs
$router->get('/admin/logs', 'LogsController@index', ['auth', 'admin']);
$router->post('/admin/logs/limpar', 'LogsController@limpar', ['auth', 'admin']);
$router->get('/admin/logs/download', 'LogsController@download', ['auth', 'admin']);

// Utilidades de manutenção (migrações rápidas)
$router->get('/admin/migracoes', 'MaintenanceController@showMigrations', ['auth', 'admin']);
$router->post('/admin/migracoes/run', 'MaintenanceController@runMigrations', ['auth', 'admin']);
$router->post('/admin/migracoes/purge', 'MaintenanceController@purgeSolicitacoes', ['auth', 'admin']);
$router->post('/admin/migracoes/limpar-disponibilidade', 'MaintenanceController@limparDisponibilidadeDescricoes', ['auth', 'admin']);

// Rotas do Locatário (PWA) - Instância na raiz
$router->get('/{instancia}', 'LocatarioController@login');
$router->post('/{instancia}', 'LocatarioController@login');
$router->get('/{instancia}/dashboard', 'LocatarioController@dashboard');
$router->get('/{instancia}/solicitacoes', 'LocatarioController@solicitacoes');
$router->get('/{instancia}/solicitacoes/{id}', 'LocatarioController@showSolicitacao');
$router->get('/{instancia}/solicitacao-emergencial/{solicitacaoId}', 'LocatarioController@solicitacaoEmergencial');
$router->get('/{instancia}/perfil', 'LocatarioController@perfil');
$router->post('/{instancia}/atualizar-perfil', 'LocatarioController@atualizarPerfil');
// Rotas de nova solicitação com steps (rotas específicas PRIMEIRO!)
$router->get('/{instancia}/nova-solicitacao/etapa/{etapa}', 'LocatarioController@processarEtapa');
$router->post('/{instancia}/nova-solicitacao/etapa/{etapa}', 'LocatarioController@processarEtapa');
$router->get('/{instancia}/nova-solicitacao', 'LocatarioController@novaSolicitacao');
$router->post('/{instancia}/nova-solicitacao', 'LocatarioController@novaSolicitacao');
// Rotas de solicitação manual (sem autenticação)
$router->get('/{instancia}/solicitacao-manual/etapa/{etapa}', 'LocatarioController@solicitacaoManualEtapa');
$router->post('/{instancia}/solicitacao-manual/etapa/{etapa}', 'LocatarioController@solicitacaoManualEtapa');
$router->get('/{instancia}/solicitacao-manual', 'LocatarioController@solicitacaoManual');
$router->post('/{instancia}/solicitacao-manual', 'LocatarioController@solicitacaoManual');
$router->get('/{instancia}/logout', 'LocatarioController@logout');
$router->post('/{instancia}/logout', 'LocatarioController@logout');

// API Routes
$router->get('/api/subcategorias', 'ApiController@getSubcategorias');

// Gerenciamento de Imobiliárias
$router->get('/admin/imobiliarias', 'ImobiliariasController@index', ['auth', 'admin']);
$router->get('/admin/imobiliarias/create', 'ImobiliariasController@create', ['auth', 'admin']);
$router->post('/admin/imobiliarias', 'ImobiliariasController@store', ['auth', 'admin']);
$router->post('/admin/imobiliarias/buscar-cnpj', 'ImobiliariasController@buscarCnpj', ['auth', 'admin']);
$router->get('/admin/imobiliarias/{id}', 'ImobiliariasController@show', ['auth', 'admin']);
$router->get('/admin/imobiliarias/{id}/api', 'ImobiliariasController@api', ['auth', 'admin']);
$router->get('/admin/imobiliarias/{id}/edit', 'ImobiliariasController@edit', ['auth', 'admin']);
$router->post('/admin/imobiliarias/{id}', 'ImobiliariasController@update', ['auth', 'admin']);
$router->post('/admin/imobiliarias/{id}/delete', 'ImobiliariasController@destroy', ['auth', 'admin']);
$router->post('/admin/imobiliarias/{id}/toggle-status', 'ImobiliariasController@toggleStatus', ['auth', 'admin']);
$router->post('/admin/imobiliarias/{id}/test-connection', 'ImobiliariasController@testConnection', ['auth', 'admin']);

// Gerenciamento de Usuários
$router->get('/admin/usuarios', 'UsuariosController@index', ['auth', 'admin']);
$router->get('/admin/usuarios/create', 'UsuariosController@create', ['auth', 'admin']);
$router->post('/admin/usuarios', 'UsuariosController@store', ['auth', 'admin']);
$router->get('/admin/usuarios/{id}/edit', 'UsuariosController@edit', ['auth', 'admin']);
$router->post('/admin/usuarios/{id}', 'UsuariosController@update', ['auth', 'admin']);
$router->post('/admin/usuarios/{id}/delete', 'UsuariosController@delete', ['auth', 'admin']);
$router->post('/admin/usuarios/{id}/toggle-status', 'UsuariosController@toggleStatus', ['auth', 'admin']);
$router->post('/admin/usuarios/{id}/resetar-senha', 'UsuariosController@resetarSenha', ['auth', 'admin']);
$router->get('/admin/usuarios/{id}/api', 'UsuariosController@api', ['auth', 'admin']);

// Gerenciamento de Categorias
$router->get('/admin/categorias', 'CategoriasController@index', ['auth', 'admin']);
$router->get('/admin/categorias/create', 'CategoriasController@create', ['auth', 'admin']);
$router->post('/admin/categorias', 'CategoriasController@store', ['auth', 'admin']);
$router->get('/admin/categorias/{id}', 'CategoriasController@show', ['auth', 'admin']);
$router->get('/admin/categorias/{id}/edit', 'CategoriasController@edit', ['auth', 'admin']);
$router->post('/admin/categorias/{id}', 'CategoriasController@update', ['auth', 'admin']);
$router->post('/admin/categorias/{id}/delete', 'CategoriasController@destroy', ['auth', 'admin']);
$router->post('/admin/categorias/{id}/toggle-status', 'CategoriasController@toggleStatus', ['auth', 'admin']);

// Subcategorias
$router->get('/admin/categorias/{categoriaId}/subcategorias/create', 'CategoriasController@createSubcategoria', ['auth', 'admin']);
$router->post('/admin/categorias/{categoriaId}/subcategorias', 'CategoriasController@storeSubcategoria', ['auth', 'admin']);
$router->get('/admin/categorias/{categoriaId}/subcategorias/{subcategoriaId}/edit', 'CategoriasController@editSubcategoria', ['auth', 'admin']);
$router->post('/admin/categorias/{categoriaId}/subcategorias/{subcategoriaId}', 'CategoriasController@updateSubcategoria', ['auth', 'admin']);
$router->post('/admin/categorias/{categoriaId}/subcategorias/{subcategoriaId}/delete', 'CategoriasController@destroySubcategoria', ['auth', 'admin']);
$router->post('/admin/categorias/{categoriaId}/subcategorias/{subcategoriaId}/toggle-status', 'CategoriasController@toggleStatusSubcategoria', ['auth', 'admin']);

// Gerenciamento de Status
$router->get('/admin/status', 'StatusController@index', ['auth', 'admin']);
$router->get('/admin/status/create', 'StatusController@create', ['auth', 'admin']);
$router->post('/admin/status', 'StatusController@store', ['auth', 'admin']);
$router->post('/admin/status/reordenar', 'StatusController@reordenar', ['auth', 'admin']); // ANTES das rotas com {id}
$router->get('/admin/status/{id}/api', 'StatusController@api', ['auth', 'admin']);
$router->get('/admin/status/{id}/edit', 'StatusController@edit', ['auth', 'admin']);
$router->post('/admin/status/{id}', 'StatusController@update', ['auth', 'admin']);
$router->post('/admin/status/{id}/delete', 'StatusController@delete', ['auth', 'admin']);

// Gerenciamento de Condições
$router->get('/admin/condicoes', 'CondicoesController@index', ['auth', 'admin']);
$router->get('/admin/condicoes/create', 'CondicoesController@create', ['auth', 'admin']);
$router->post('/admin/condicoes', 'CondicoesController@store', ['auth', 'admin']);
$router->post('/admin/condicoes/reordenar', 'CondicoesController@reordenar', ['auth', 'admin']); // ANTES das rotas com {id}
$router->get('/admin/condicoes/{id}/api', 'CondicoesController@api', ['auth', 'admin']);
$router->get('/admin/condicoes/{id}/edit', 'CondicoesController@edit', ['auth', 'admin']);
$router->post('/admin/condicoes/{id}', 'CondicoesController@update', ['auth', 'admin']);
$router->post('/admin/condicoes/{id}/delete', 'CondicoesController@delete', ['auth', 'admin']);

// Gerenciamento de Telefones de Emergência
$router->get('/admin/telefones-emergencia', 'TelefonesEmergenciaController@index', ['auth', 'admin']);
$router->get('/admin/telefones-emergencia/create', 'TelefonesEmergenciaController@create', ['auth', 'admin']);
$router->post('/admin/telefones-emergencia', 'TelefonesEmergenciaController@store', ['auth', 'admin']);
$router->get('/admin/telefones-emergencia/{id}/edit', 'TelefonesEmergenciaController@edit', ['auth', 'admin']);
$router->post('/admin/telefones-emergencia/{id}', 'TelefonesEmergenciaController@update', ['auth', 'admin']);
$router->post('/admin/telefones-emergencia/{id}/delete', 'TelefonesEmergenciaController@destroy', ['auth', 'admin']);

// Gerenciamento de Configurações
$router->get('/admin/configuracoes', 'ConfiguracoesController@index', ['auth', 'admin']);
$router->get('/admin/configuracoes/create', 'ConfiguracoesController@create', ['auth', 'admin']);
$router->post('/admin/configuracoes', 'ConfiguracoesController@store', ['auth', 'admin']);
// Rotas específicas PRIMEIRO (antes das rotas com {id})
$router->get('/admin/configuracoes/emergencia', 'ConfiguracoesController@emergencia', ['auth', 'admin']);
$router->post('/admin/configuracoes/emergencia', 'ConfiguracoesController@salvarEmergencia', ['auth', 'admin']);
$router->get('/admin/configuracoes/whatsapp', 'ConfiguracoesController@whatsapp', ['auth', 'admin']);
$router->post('/admin/configuracoes/whatsapp', 'ConfiguracoesController@salvarWhatsapp', ['auth', 'admin']);
// Rotas genéricas DEPOIS
$router->get('/admin/configuracoes/{id}/edit', 'ConfiguracoesController@edit', ['auth', 'admin']);
$router->post('/admin/configuracoes/{id}', 'ConfiguracoesController@update', ['auth', 'admin']);
$router->post('/admin/configuracoes/{id}/delete', 'ConfiguracoesController@destroy', ['auth', 'admin']);

// Gerenciamento de Solicitações Manuais
$router->get('/admin/solicitacoes-manuais', 'SolicitacoesController@solicitacoesManuais', ['auth']);
$router->get('/admin/solicitacoes-manuais/nova/api', 'SolicitacoesController@apiNovaSolicitacaoManual', ['auth']);
$router->get('/admin/solicitacoes-manuais/nova', 'SolicitacoesController@novaSolicitacaoManual', ['auth']);
$router->post('/admin/solicitacoes-manuais/nova', 'SolicitacoesController@criarSolicitacaoManual', ['auth']);
$router->get('/admin/solicitacoes-manuais/{id}/api', 'SolicitacoesController@apiSolicitacaoManual', ['auth']);
$router->get('/admin/solicitacoes-manuais/{id}/editar', 'SolicitacoesController@editarSolicitacaoManual', ['auth']);
$router->post('/admin/solicitacoes-manuais/{id}/editar', 'SolicitacoesController@atualizarSolicitacaoManual', ['auth']);
$router->get('/admin/solicitacoes-manuais/{id}', 'SolicitacoesController@verSolicitacaoManual', ['auth']);
$router->post('/admin/solicitacoes-manuais/{id}/status', 'SolicitacoesController@atualizarStatusManual', ['auth']);
$router->post('/admin/solicitacoes-manuais/{id}/migrar', 'SolicitacoesController@migrarParaSistema', ['auth']);

// Rotas de Instância (para acesso direto por imobiliária) - DEPOIS das rotas do LocatarioController
$router->get('/{instancia}/login', 'InstanciaController@login');
$router->post('/{instancia}/login', 'InstanciaController@authenticate');
$router->get('/{instancia}', 'InstanciaController@index');

$router->get('/{instancia}/dashboard', 'InstanciaController@dashboard', ['auth']);
$router->get('/{instancia}/solicitacoes', 'InstanciaController@solicitacoes', ['auth']);
$router->get('/{instancia}/solicitacoes/{id}', 'InstanciaController@solicitacao', ['auth']);
$router->get('/{instancia}/perfil', 'InstanciaController@perfil', ['auth']);
$router->post('/{instancia}/atualizar-perfil', 'InstanciaController@atualizarPerfil', ['auth']);
$router->get('/{instancia}/nova-solicitacao', 'InstanciaController@novaSolicitacao', ['auth']);
$router->post('/{instancia}/nova-solicitacao', 'InstanciaController@criarSolicitacao', ['auth']);
$router->get('/{instancia}/logout', 'InstanciaController@logout');

// API Routes
$router->get('/api/solicitacoes', 'ApiController@solicitacoes', ['auth']);
$router->get('/api/solicitacoes/{id}', 'ApiController@solicitacao', ['auth']);
$router->post('/api/solicitacoes/{id}/status', 'ApiController@updateStatus', ['auth']);
$router->get('/api/categorias', 'ApiController@categorias');
$router->get('/api/imobiliarias/{id}/locatarios', 'ApiController@locatarios', ['auth']);

return $router;
