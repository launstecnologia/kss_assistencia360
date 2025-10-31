-- Migration: Criar tabela solicitacoes_manuais
-- Data: 30/10/2025
-- Descrição: Tabela para armazenar solicitações criadas manualmente por usuários não logados

CREATE TABLE IF NOT EXISTS solicitacoes_manuais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Relacionamento
    imobiliaria_id INT NOT NULL,
    
    -- Dados Pessoais
    nome_completo VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    
    -- Endereço
    tipo_imovel ENUM('RESIDENCIAL', 'COMERCIAL') NOT NULL,
    subtipo_imovel ENUM('CASA', 'APARTAMENTO') NULL,
    cep VARCHAR(10) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100) NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    
    -- Serviço
    categoria_id INT NOT NULL,
    subcategoria_id INT NOT NULL,
    descricao_problema TEXT NOT NULL,
    
    -- Horários e Fotos
    horarios_preferenciais JSON NULL,
    fotos JSON NULL,
    
    -- Termos e Controle
    termos_aceitos BOOLEAN DEFAULT FALSE,
    status_id INT NOT NULL DEFAULT 1,
    
    -- Migração
    migrada_para_solicitacao_id INT NULL,
    migrada_em DATETIME NULL,
    migrada_por_usuario_id INT NULL,
    
    -- Timestamps
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_imobiliaria (imobiliaria_id),
    INDEX idx_cpf (cpf),
    INDEX idx_status (status_id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_subcategoria (subcategoria_id),
    INDEX idx_migrada (migrada_para_solicitacao_id),
    INDEX idx_created (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (imobiliaria_id) REFERENCES imobiliarias(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
    FOREIGN KEY (subcategoria_id) REFERENCES subcategorias(id) ON DELETE RESTRICT,
    FOREIGN KEY (status_id) REFERENCES status(id) ON DELETE RESTRICT,
    FOREIGN KEY (migrada_para_solicitacao_id) REFERENCES solicitacoes(id) ON DELETE SET NULL,
    FOREIGN KEY (migrada_por_usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentários das colunas
ALTER TABLE solicitacoes_manuais 
    MODIFY COLUMN id INT AUTO_INCREMENT COMMENT 'ID único da solicitação manual',
    MODIFY COLUMN imobiliaria_id INT NOT NULL COMMENT 'ID da imobiliária (extraído da instância)',
    MODIFY COLUMN nome_completo VARCHAR(255) NOT NULL COMMENT 'Nome completo do solicitante',
    MODIFY COLUMN cpf VARCHAR(14) NOT NULL COMMENT 'CPF do solicitante (com máscara)',
    MODIFY COLUMN whatsapp VARCHAR(20) NOT NULL COMMENT 'WhatsApp para contato',
    MODIFY COLUMN tipo_imovel ENUM('RESIDENCIAL', 'COMERCIAL') NOT NULL COMMENT 'Tipo de propriedade',
    MODIFY COLUMN subtipo_imovel ENUM('CASA', 'APARTAMENTO') NULL COMMENT 'Subtipo (apenas para residencial)',
    MODIFY COLUMN horarios_preferenciais JSON NULL COMMENT 'Array de horários selecionados pelo usuário',
    MODIFY COLUMN fotos JSON NULL COMMENT 'Array de URLs das fotos enviadas',
    MODIFY COLUMN termos_aceitos BOOLEAN DEFAULT FALSE COMMENT 'Se o usuário aceitou os termos',
    MODIFY COLUMN migrada_para_solicitacao_id INT NULL COMMENT 'ID da solicitação criada após migração',
    MODIFY COLUMN migrada_em DATETIME NULL COMMENT 'Data/hora da migração para o sistema principal',
    MODIFY COLUMN migrada_por_usuario_id INT NULL COMMENT 'ID do operador que realizou a migração';

