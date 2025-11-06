# Logs do Sistema WhatsApp

Esta pasta contém os logs de envio de mensagens WhatsApp via Evolution API.

## Arquivo de Log

- **Arquivo**: `whatsapp_evolution_api.log`
- **Formato**: Texto estruturado com JSON detalhado

## Formato do Log

Cada entrada de log contém:

### Linha Principal (Resumo)
```
[YYYY-MM-DD HH:MM:SS] [STATUS] ID:XXX | Protocolo:KSXX | Tipo:TipoMensagem | Cliente:Nome | Telefone:5511999998888@c.us | HTTP:200 | Tempo:123.45ms
```

### Detalhes Completos (JSON)
```
  DETALHES: {"solicitacao_id":123,"message_type":"Nova Solicitação",...}
```

## Status Possíveis

- **SUCESSO**: Mensagem enviada com sucesso
- **ERRO**: Erro durante o envio (detalhes no campo `erro`)
- **INICIADO**: Processo iniciado (antes de completar)

## Informações Registradas

### Em caso de Sucesso:
- ID da solicitação
- Protocolo (ex: KS18)
- Tipo de mensagem
- Nome do cliente
- Telefone original e formatado
- Tamanho da mensagem
- URL da API
- Instância da API
- Código HTTP da resposta
- Tempo de resposta
- Resposta completa da API

### Em caso de Erro:
- Todos os dados acima
- Mensagem de erro
- Tipo de exceção
- Arquivo e linha onde ocorreu o erro
- Erro cURL (se houver)
- Código de erro cURL (se houver)
- Resposta bruta da API (se disponível)

## Exemplo de Log

```
[2025-01-11 14:30:15] [SUCESSO] ID:18 | Protocolo:KS18 | Tipo:Nova Solicitação | Cliente:João Silva | Telefone:5516992422354@c.us | HTTP:200 | Tempo:245.67ms
  DETALHES: {"solicitacao_id":18,"message_type":"Nova Solicitação","timestamp":"2025-01-11 14:30:15","status":"sucesso","protocolo":"KS18","cliente_nome":"João Silva","telefone_original":"+55 16 99242-2354","telefone_formatado":"5516992422354@c.us","mensagem_tamanho":350,"api_url":"https://api.evolution.com/message/sendText/APItestes","api_instance":"APItestes","http_code":200,"tempo_resposta":"245.67ms","api_response":{"key":"abc123","message":"Message sent successfully"}}
----------------------------------------------------------------------------------------------------
```

## Manutenção

- Os logs são acumulativos (append)
- Recomenda-se fazer rotação periódica dos logs
- Considere implementar limpeza automática de logs antigos

## Notas

- O arquivo de log é criado automaticamente na primeira execução
- A pasta `logs` é criada automaticamente se não existir
- Os logs são escritos com lock de arquivo para evitar conflitos

