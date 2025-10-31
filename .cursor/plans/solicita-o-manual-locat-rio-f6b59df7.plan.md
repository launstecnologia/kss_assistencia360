<!-- f6b59df7-0b66-4aa9-a8f9-f210e59a2b72 7389fa70-d0af-43da-8ec9-fe2197962732 -->
# Detalhes da Solicitação — Cliente e Horários (Admin + PWA)

## Objetivo

Entregar a gestão completa de horários (cliente e seguradora) no detalhe da solicitação e garantir que o PWA grave e exiba corretamente os horários preferenciais.

## Entregas

1. Informações do Cliente corretas (Nome, CPF, WhatsApp, Imobiliária).
2. Horários enviados pelo cliente visíveis para confirmação no card, com Confirmar/Desconfirmar e destaque “Agendado para …”.
3. Card para sugerir horários alternativos da seguradora (data + 4 faixas) e remover sugestões – tudo inline, sem novas abas.
4. PWA: Step 4 grava horários preferenciais; Step 5 exibe no resumo; backend inclui no insert.

## Mudanças por camada

### View Admin (`app/Views/solicitacoes/show.php`)

- Card “Informações do Cliente”: renderizar `locatario_nome`, `locatario_cpf`, `locatario_telefone` (link `wa.me`) e `imobiliaria_nome`.
- Card “Disponibilidade do Segurado”: listar `horarios_opcoes` (JSON) com botão Confirmar/Desconfirmar; chip “Agendado para: …”.
- Card “Adicionar Horário da Seguradora”: date picker + 4 faixas (08–11, 11–14, 14–17, 17–20), salvar/remover por fetch.

### Controller Admin (`app/Controllers/SolicitacoesController.php`)

- `confirmarHorario(id)` → valida entrada, define `data_agendamento`/`horario_agendamento`, histórico.
- `desconfirmarHorario(id)` → limpa campos de agendamento, histórico.
- `sugerirHorario(id)`/`removerHorarioSugerido(id)` → mantém `horarios_sugestoes` (JSON) e histórico.

### Rotas (`app/Config/routes.php`)

- POST `/admin/solicitacoes/{id}/confirmar-horario`
- POST `/admin/solicitacoes/{id}/desconfirmar-horario`
- POST `/admin/solicitacoes/{id}/sugerir-horario`
- POST `/admin/solicitacoes/{id}/remover-horario-sugerido`

### PWA (Locatário)

- View `app/Views/locatario/nova-solicitacao.php`:
- Step 4: serializar até 3 horários selecionados para `horarios_opcoes` (JSON oculto).
- Step 5: exibir `horarios_opcoes` do `$_SESSION['nova_solicitacao']['horarios_opcoes']` (dd/mm/yyyy HH:mm).
- Controller `app/Controllers/LocatarioController.php`:
- `salvarDadosEtapa(4)` grava `horarios_opcoes` (JSON) na sessão.
- `finalizarSolicitacao()` inclui `horarios_opcoes` no insert.

### Banco (DDL)

- Garantir colunas em `solicitacoes`:
- `locatario_cpf VARCHAR(14) NULL`
- `horarios_opcoes JSON NULL`
- `horarios_sugestoes JSON NULL`

## Critérios de Aceite

- Card do cliente completo e correto.
- Confirmação/Desconfirmação de horário funcionando e visível.
- Sugestões da seguradora criadas/removidas no card.
- PWA salva e exibe os horários no resumo.
- Histórico registra todas as ações de horário.

### To-dos

- [ ] Criar migration SQL para tabela solicitacoes_manuais
- [ ] Criar Model SolicitacaoManual com métodos de listagem e migração
- [ ] Adicionar métodos no LocatarioController para fluxo de 5 etapas
- [ ] Criar view solicitacao-manual.php com 5 etapas e integração ViaCEP
- [ ] Adicionar métodos no SolicitacoesController para gerenciamento admin
- [ ] Atualizar view admin com aba de solicitações manuais e modal de detalhes
- [ ] Adicionar rotas para fluxo locatário e admin no routes.php
- [ ] Adicionar link para solicitação manual na tela de login do locatário