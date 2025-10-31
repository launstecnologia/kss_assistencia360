-- ============================================
-- WHATSAPP INFRASTRUCTURE - DATABASE TABLES
-- ============================================
-- Sistema de notificações WhatsApp com envio direto via Evolution API
-- Executar: mysql -u usuario -p banco < database_whatsapp_infrastructure.sql

-- Tabela de templates de mensagens WhatsApp
CREATE TABLE IF NOT EXISTS whatsapp_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL COMMENT 'Nome descritivo do template',
    tipo VARCHAR(100) NOT NULL COMMENT 'Tipo de mensagem (ex: Nova Solicitação, Horário Confirmado)',
    corpo TEXT NOT NULL COMMENT 'Corpo do template com variáveis {{variavel}}',
    variaveis JSON NULL COMMENT 'Array JSON das variáveis disponíveis no template',
    ativo TINYINT(1) DEFAULT 1 COMMENT '1 = ativo, 0 = inativo',
    padrao TINYINT(1) DEFAULT 0 COMMENT '1 = template padrão do tipo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo),
    INDEX idx_padrao (padrao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Templates customizáveis para mensagens WhatsApp';

-- Tabela de tokens para confirmação de horários
CREATE TABLE IF NOT EXISTS schedule_confirmation_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE COMMENT 'Token único gerado (64 caracteres hex)',
    solicitacao_id INT UNSIGNED NOT NULL COMMENT 'ID da solicitação relacionada',
    protocol VARCHAR(50) NOT NULL COMMENT 'Protocolo da solicitação (ex: KS2025-001)',
    scheduled_date DATE NULL COMMENT 'Data sugerida/confirmada',
    scheduled_time VARCHAR(20) NULL COMMENT 'Horário sugerido (ex: 14:00-17:00)',
    expires_at TIMESTAMP NOT NULL COMMENT 'Data de expiração do token (48 horas)',
    used_at TIMESTAMP NULL COMMENT 'Data/hora em que o token foi usado',
    action_type ENUM('confirm', 'cancel', 'reschedule') NULL COMMENT 'Ação realizada pelo cliente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_token (token),
    INDEX idx_expires (expires_at),
    INDEX idx_solicitacao (solicitacao_id),
    INDEX idx_used (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tokens para confirmação de horários via WhatsApp (expiração 48h)';

-- Exibir resumo
SELECT 'Tabelas criadas com sucesso!' AS status;
SELECT 
    TABLE_NAME as tabela,
    TABLE_COMMENT as descricao
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_NAME IN ('whatsapp_templates', 'schedule_confirmation_tokens')
AND TABLE_SCHEMA = DATABASE();

