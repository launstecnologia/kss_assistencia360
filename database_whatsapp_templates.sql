-- ============================================
-- WHATSAPP TEMPLATES - MENSAGENS PADRÃO
-- ============================================
-- Este arquivo popula a tabela whatsapp_templates com os 5 templates padrão
-- Executar APÓS database_whatsapp_infrastructure.sql
-- mysql -u usuario -p banco < database_whatsapp_templates.sql

-- Template 1: Nova Solicitação
INSERT INTO whatsapp_templates (nome, tipo, corpo, variaveis, ativo, padrao) VALUES (
    'Nova Solicitação - Padrão',
    'Nova Solicitação',
    '🏠 Nova Solicitação - Seguro Imobiliário KSS e {{imobiliaria_nome}}

📋 Nº Atendimento: {{protocol}}
🏷️ Contrato: {{contrato_numero}}
🔖 Protocolo Seguradora: {{protocolo_seguradora}}
👤 Nome: {{cliente_nome}}
📄 CPF: {{cliente_cpf}}
📞 Telefone: {{cliente_telefone}}
🏢 Imobiliária: {{imobiliaria_nome}}
📍 Endereço: {{endereco_completo}}
📝 Descrição do Problema:
{{descricao_problema}}

📅 Agendamento: Horário à Confirmar

🔗 Acompanhe sua solicitação em:
{{link_rastreamento}}

⚠ OBSERVAÇÕES IMPORTANTES:

🏢 Condomínio: Se o serviço for realizado em apartamento ou condomínio, é obrigatório comunicar previamente a administração ou portaria sobre a visita técnica agendada.

👥 Responsável no Local: É obrigatória a presença de uma pessoa maior de 18 anos no local durante todo o período de execução do serviço para acompanhar e autorizar os trabalhos.

Caso não tiver ninguém no local, será considerado assistência perdida.

⏳ Próximos Passos: Aguarde a confirmação das opções de horários informadas para realização da assistência. Caso nenhuma das opções tenha disponibilidade, novas opções serão oferecidas.

---
Solicitação criada automaticamente pelo sistema
Não responda essa mensagem',
    JSON_ARRAY('protocol', 'contrato_numero', 'protocolo_seguradora', 'cliente_nome', 'cliente_cpf', 'cliente_telefone', 'imobiliaria_nome', 'endereco_completo', 'descricao_problema', 'link_rastreamento'),
    1,
    1
);

-- Template 2: Horário Confirmado
INSERT INTO whatsapp_templates (nome, tipo, corpo, variaveis, ativo, padrao) VALUES (
    'Horário Confirmado - Padrão',
    'Horário Confirmado',
    '✅ *Horário Confirmado*

📋 *Protocolo:* {{protocol}}
🏷️ *Contrato:* {{contrato_numero}}
👤 *Cliente:* {{cliente_nome}}
📍 *Endereço:* {{endereco_completo}}
📅 *Data:* {{data_agendamento}}
⏰ *Horário:* {{horario_agendamento}}
📝 *Problema Relatado:*
{{descricao_problema}}

Seu horário foi confirmado! Aguardamos você na data e horário acima.

🔗 Caso não possa comparecer, cancele através deste link:
{{link_cancelamento}}

⚠️ Este link expira em 48 horas.',
    JSON_ARRAY('protocol', 'contrato_numero', 'cliente_nome', 'endereco_completo', 'data_agendamento', 'horario_agendamento', 'descricao_problema', 'link_cancelamento'),
    1,
    1
);

-- Template 3: Horário Sugerido
INSERT INTO whatsapp_templates (nome, tipo, corpo, variaveis, ativo, padrao) VALUES (
    'Horário Sugerido - Padrão',
    'Horário Sugerido',
    '📅 *Horário Sugerido*

📋 *Protocolo:* {{protocol}}
🏷️ *Contrato:* {{contrato_numero}}
👤 *Cliente:* {{cliente_nome}}
📍 *Endereço:* {{endereco_completo}}
📅 *Data:* {{data_agendamento}}
⏰ *Horário:* {{horario_agendamento}}
📝 *Problema Relatado:*
{{descricao_problema}}

A seguradora sugeriu este horário para o seu atendimento.

🔗 Para confirmar, reagendar ou cancelar, clique no link abaixo:
{{link_confirmacao}}

⚠️ Este link expira em 48 horas.',
    JSON_ARRAY('protocol', 'contrato_numero', 'cliente_nome', 'endereco_completo', 'data_agendamento', 'horario_agendamento', 'descricao_problema', 'link_confirmacao'),
    1,
    1
);

-- Template 4: Confirmação de Serviço
INSERT INTO whatsapp_templates (nome, tipo, corpo, variaveis, ativo, padrao) VALUES (
    'Confirmação de Serviço - Padrão',
    'Confirmação de Serviço',
    '✅ *Confirmação de Serviço*

📋 *Protocolo:* {{protocol}}
🏷️ *Contrato:* {{contrato_numero}}
👤 *Cliente:* {{cliente_nome}}
🕐 *Horário do Serviço:* {{horario_servico}}
📝 *Problema Relatado:*
{{descricao_problema}}

*Situações Especiais do Serviço*

✅ Serviço realizado com sucesso
🚫 Prestador não compareceu
🔧 Precisa comprar peças
📝 Outros

⚠️ Marque as situações que se aplicam ao serviço agendado para melhor acompanhamento.

🔗 *Marque o status em:*
{{link_status}}',
    JSON_ARRAY('protocol', 'contrato_numero', 'cliente_nome', 'horario_servico', 'descricao_problema', 'link_status'),
    1,
    1
);

-- Template 5: Atualização de Status
INSERT INTO whatsapp_templates (nome, tipo, corpo, variaveis, ativo, padrao) VALUES (
    'Atualização de Status - Padrão',
    'Atualização de Status',
    '🔄 *Atualização de Status - Seguro Imobiliário VGT e {{imobiliaria_nome}}*

📋 *Protocolo:* {{protocol}}
🏷️ *Contrato:* {{contrato_numero}}
🔖 *Protocolo Seguradora:* {{protocolo_seguradora}}
👤 *Cliente:* {{cliente_nome}}

📊 *Status Atual:* {{status_atual}}

📝 *Problema Relatado:*
{{descricao_problema}}

{{prestador_nome}}{{data_agendamento}}{{horario_agendamento}}

🔗 *Acompanhe sua solicitação em:*
{{link_rastreamento}}

---
_Status atualizado automaticamente pelo sistema_',
    JSON_ARRAY('imobiliaria_nome', 'protocol', 'contrato_numero', 'protocolo_seguradora', 'cliente_nome', 'status_atual', 'descricao_problema', 'prestador_nome', 'data_agendamento', 'horario_agendamento', 'link_rastreamento'),
    1,
    1
);

-- Exibir resumo
SELECT 'Templates inseridos com sucesso!' AS status;
SELECT 
    id,
    nome,
    tipo,
    ativo,
    padrao
FROM whatsapp_templates
ORDER BY id;

