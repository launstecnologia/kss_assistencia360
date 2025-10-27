-- Script SQL para criação do banco de dados KSS Seguros
-- Execute este script no MySQL/MariaDB para criar todas as tabelas necessárias

CREATE DATABASE IF NOT EXISTS kss_seguros CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kss_seguros;

-- Tabela de usuários (operadores e administradores)
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    nivel_permissao ENUM('ADMINISTRADOR', 'OPERADOR') NOT NULL DEFAULT 'OPERADOR',
    status ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de imobiliárias
CREATE TABLE imobiliarias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    url_base VARCHAR(500) NOT NULL,
    token VARCHAR(500) NOT NULL,
    instancia VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('ATIVA', 'INATIVA') NOT NULL DEFAULT 'ATIVA',
    cache_ttl INT DEFAULT 300,
    configuracoes JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de categorias
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    tipo_assistencia ENUM('RESIDENCIAL', 'COMERCIAL') NOT NULL DEFAULT 'RESIDENCIAL',
    prazo_minimo INT DEFAULT 1 COMMENT 'Prazo mínimo em dias para agendamento',
    status_0800 BOOLEAN DEFAULT FALSE COMMENT 'Se deve redirecionar para 0800 em emergências',
    observacoes TEXT,
    status ENUM('ATIVA', 'INATIVA') NOT NULL DEFAULT 'ATIVA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de subcategorias
CREATE TABLE subcategorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    prazo_minimo INT DEFAULT 1 COMMENT 'Prazo mínimo em dias para agendamento',
    status_0800 BOOLEAN DEFAULT FALSE COMMENT 'Se deve redirecionar para 0800 em emergências',
    observacoes TEXT,
    status ENUM('ATIVA', 'INATIVA') NOT NULL DEFAULT 'ATIVA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- Tabela de status (colunas do Kanban)
CREATE TABLE status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    cor VARCHAR(7) DEFAULT '#3B82F6' COMMENT 'Cor em hexadecimal',
    icone VARCHAR(50) DEFAULT 'fas fa-circle',
    ordem INT NOT NULL DEFAULT 1,
    template_mensagem TEXT COMMENT 'Template para notificações automáticas',
    notificar_automatico BOOLEAN DEFAULT FALSE,
    status ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela principal de solicitações
CREATE TABLE solicitacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    imobiliaria_id INT NOT NULL,
    categoria_id INT NOT NULL,
    subcategoria_id INT NOT NULL,
    status_id INT NOT NULL,
    
    -- Dados do locatário
    locatario_id VARCHAR(100) NOT NULL COMMENT 'ID do locatário na API KSI',
    locatario_nome VARCHAR(255) NOT NULL,
    locatario_telefone VARCHAR(20) NOT NULL,
    locatario_email VARCHAR(255),
    
    -- Dados do imóvel
    imovel_endereco VARCHAR(500) NOT NULL,
    imovel_numero VARCHAR(20),
    imovel_complemento VARCHAR(100),
    imovel_bairro VARCHAR(100),
    imovel_cidade VARCHAR(100),
    imovel_estado VARCHAR(2),
    imovel_cep VARCHAR(10),
    
    -- Dados da solicitação
    descricao_problema TEXT NOT NULL,
    observacoes TEXT,
    prioridade ENUM('BAIXA', 'NORMAL', 'ALTA', 'URGENTE') DEFAULT 'NORMAL',
    
    -- Dados de agendamento
    data_agendamento DATE,
    horario_agendamento TIME,
    
    -- Dados do prestador
    prestador_nome VARCHAR(255),
    prestador_telefone VARCHAR(20),
    
    -- Dados financeiros
    valor_orcamento DECIMAL(10,2),
    numero_ncp VARCHAR(100) COMMENT 'Número do NCP (Nota de Conclusão de Prestação)',
    
    -- Avaliação
    avaliacao_satisfacao INT COMMENT 'Nota de 1 a 5',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (imobiliaria_id) REFERENCES imobiliarias(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (subcategoria_id) REFERENCES subcategorias(id),
    FOREIGN KEY (status_id) REFERENCES status(id)
);

-- Tabela de fotos das solicitações
CREATE TABLE fotos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    solicitacao_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    url_arquivo VARCHAR(500) NOT NULL,
    tamanho INT COMMENT 'Tamanho em bytes',
    tipo_mime VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE
);

-- Tabela de histórico de status
CREATE TABLE historico_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    solicitacao_id INT NOT NULL,
    status_id INT NOT NULL,
    usuario_id INT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES status(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de notificações enviadas
CREATE TABLE notificacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    solicitacao_id INT NOT NULL,
    tipo ENUM('WHATSAPP', 'SMS', 'EMAIL', 'PUSH') NOT NULL,
    destinatario VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    status ENUM('ENVIADA', 'FALHOU', 'PENDENTE') DEFAULT 'PENDENTE',
    resposta_api TEXT COMMENT 'Resposta da API de notificação',
    enviada_em TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE
);

-- Tabela de cache da API KSI
CREATE TABLE cache_api_ksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    imobiliaria_id INT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    parametros JSON,
    resposta JSON NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (imobiliaria_id) REFERENCES imobiliarias(id) ON DELETE CASCADE,
    INDEX idx_cache_lookup (imobiliaria_id, endpoint, expires_at)
);

-- Tabela de logs da API KSI
CREATE TABLE logs_api_ksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    imobiliaria_id INT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    metodo VARCHAR(10) NOT NULL,
    parametros JSON,
    resposta JSON,
    status_code INT,
    tempo_resposta_ms INT,
    erro TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (imobiliaria_id) REFERENCES imobiliarias(id) ON DELETE CASCADE
);

-- Tabela de configurações do sistema
CREATE TABLE configuracoes_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    tipo ENUM('STRING', 'NUMBER', 'BOOLEAN', 'JSON') DEFAULT 'STRING',
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir dados iniciais

-- Usuário administrador padrão
INSERT INTO usuarios (nome, email, senha, nivel_permissao) VALUES 
('Administrador', 'admin@kssseguros.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMINISTRADOR');

-- Status padrão do Kanban
INSERT INTO status (nome, cor, icone, ordem, template_mensagem, notificar_automatico) VALUES 
('Nova Solicitação', '#3B82F6', 'fas fa-plus-circle', 1, 'Olá [NOME], recebemos sua solicitação de assistência. Em breve entraremos em contato.', true),
('Buscando Prestador', '#F59E0B', 'fas fa-search', 2, 'Olá [NOME], estamos buscando um prestador qualificado para sua solicitação.', true),
('Serviço Agendado', '#8B5CF6', 'fas fa-calendar-check', 3, 'Olá [NOME], seu serviço foi agendado para [DATA] às [HORA].', true),
('Aguardando Peça', '#EF4444', 'fas fa-clock', 4, 'Olá [NOME], o prestador identificou que é necessária uma peça. Aguardamos sua compra.', true),
('Concluído', '#10B981', 'fas fa-check-circle', 5, 'Olá [NOME], seu serviço foi concluído! Avalie nossa prestação: [LINK]', true);

-- Categorias padrão
INSERT INTO categorias (nome, tipo_assistencia, prazo_minimo, status_0800) VALUES 
('Elétrica', 'RESIDENCIAL', 1, true),
('Hidráulica', 'RESIDENCIAL', 1, true),
('Pintura', 'RESIDENCIAL', 3, false),
('Limpeza', 'RESIDENCIAL', 1, false),
('Manutenção Geral', 'RESIDENCIAL', 2, false);

-- Subcategorias padrão
INSERT INTO subcategorias (categoria_id, nome, prazo_minimo, status_0800) VALUES 
(1, 'Troca de Lâmpada', 1, false),
(1, 'Instalação de Tomada', 2, false),
(1, 'Reparo de Fiação', 1, true),
(2, 'Vazamento de Torneira', 1, true),
(2, 'Desentupimento', 1, true),
(2, 'Instalação de Chuveiro', 2, false),
(3, 'Pintura de Parede', 3, false),
(3, 'Pintura de Porta', 2, false),
(4, 'Limpeza Geral', 1, false),
(4, 'Limpeza Pós-Obra', 1, false),
(5, 'Instalação de Prateleira', 2, false),
(5, 'Reparo de Porta', 2, false);

-- Configurações padrão do sistema
INSERT INTO configuracoes_sistema (chave, valor, tipo, descricao) VALUES 
('app_name', 'KSS Seguros', 'STRING', 'Nome da aplicação'),
('app_version', '1.0.0', 'STRING', 'Versão da aplicação'),
('whatsapp_enabled', 'true', 'BOOLEAN', 'Se as notificações WhatsApp estão habilitadas'),
('max_photos_per_request', '3', 'NUMBER', 'Máximo de fotos por solicitação'),
('default_cache_ttl', '300', 'NUMBER', 'TTL padrão do cache em segundos'),
('emergency_phone', '0800-123-4567', 'STRING', 'Telefone de emergência'),
('business_hours_start', '08:00', 'STRING', 'Horário de início do expediente'),
('business_hours_end', '18:00', 'STRING', 'Horário de fim do expediente');

-- Índices para performance
CREATE INDEX idx_solicitacoes_status ON solicitacoes(status_id);
CREATE INDEX idx_solicitacoes_imobiliaria ON solicitacoes(imobiliaria_id);
CREATE INDEX idx_solicitacoes_categoria ON solicitacoes(categoria_id);
CREATE INDEX idx_solicitacoes_created_at ON solicitacoes(created_at);
CREATE INDEX idx_solicitacoes_locatario ON solicitacoes(locatario_id);

CREATE INDEX idx_notificacoes_solicitacao ON notificacoes(solicitacao_id);
CREATE INDEX idx_notificacoes_tipo ON notificacoes(tipo);
CREATE INDEX idx_notificacoes_status ON notificacoes(status);

CREATE INDEX idx_logs_api_imobiliaria ON logs_api_ksi(imobiliaria_id);
CREATE INDEX idx_logs_api_created_at ON logs_api_ksi(created_at);
