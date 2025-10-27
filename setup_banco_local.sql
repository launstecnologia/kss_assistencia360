-- Script SQL para configurar banco local KSS
-- Execute este script no phpMyAdmin

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS kss_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kss_local;

-- Tabela usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_permissao ENUM('ADMINISTRADOR', 'OPERADOR') DEFAULT 'OPERADOR',
    status ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela imobiliarias
CREATE TABLE IF NOT EXISTS imobiliarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    instancia VARCHAR(100) NOT NULL,
    token VARCHAR(500) NOT NULL,
    url_base VARCHAR(500) NOT NULL,
    status ENUM('ATIVA', 'INATIVA') DEFAULT 'ATIVA',
    cnpj VARCHAR(18),
    razao_social VARCHAR(255),
    nome_fantasia VARCHAR(255),
    endereco_logradouro VARCHAR(255),
    endereco_numero VARCHAR(20),
    endereco_complemento VARCHAR(100),
    endereco_bairro VARCHAR(100),
    endereco_cidade VARCHAR(100),
    endereco_estado VARCHAR(2),
    endereco_cep VARCHAR(9),
    telefone VARCHAR(15),
    email VARCHAR(255),
    logo VARCHAR(255),
    cor_primaria VARCHAR(7) DEFAULT '#3B82F6',
    cor_secundaria VARCHAR(7) DEFAULT '#1E40AF',
    api_url VARCHAR(255),
    api_id VARCHAR(100),
    api_token VARCHAR(255),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    status ENUM('ATIVA', 'INATIVA') DEFAULT 'ATIVA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela subcategorias
CREATE TABLE IF NOT EXISTS subcategorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    status ENUM('ATIVA', 'INATIVA') DEFAULT 'ATIVA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- Tabela status
CREATE TABLE IF NOT EXISTS status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    cor VARCHAR(7) DEFAULT '#6B7280',
    ordem INT DEFAULT 0,
    status ENUM('ATIVA', 'INATIVA') DEFAULT 'ATIVA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela solicitacoes
CREATE TABLE IF NOT EXISTS solicitacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_solicitacao VARCHAR(50) UNIQUE NOT NULL,
    locatario_id INT,
    imobiliaria_id INT,
    categoria_id INT,
    subcategoria_id INT,
    status_id INT,
    descricao TEXT NOT NULL,
    tipo_atendimento ENUM('RESIDENCIAL', 'COMERCIAL') DEFAULT 'RESIDENCIAL',
    datas_opcoes JSON,
    data_confirmada DATETIME,
    mawdy_id VARCHAR(100),
    data_limite_cancelamento DATETIME,
    data_limite_peca DATETIME,
    token_confirmacao VARCHAR(255),
    avaliacao_imobiliaria INT,
    avaliacao_app INT,
    avaliacao_prestador INT,
    comentarios_avaliacao TEXT,
    link_confirmacao VARCHAR(500),
    whatsapp_enviado BOOLEAN DEFAULT FALSE,
    lembretes_enviados INT DEFAULT 0,
    observacoes_operador TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (imobiliaria_id) REFERENCES imobiliarias(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    FOREIGN KEY (subcategoria_id) REFERENCES subcategorias(id) ON DELETE SET NULL,
    FOREIGN KEY (status_id) REFERENCES status(id) ON DELETE SET NULL
);

-- Inserir usuário admin
INSERT INTO usuarios (nome, email, senha, nivel_permissao, status) 
VALUES ('Administrador', 'admin@kss.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMINISTRADOR', 'ATIVO')
ON DUPLICATE KEY UPDATE senha = VALUES(senha);

-- Inserir imobiliária demo
INSERT INTO imobiliarias (nome, instancia, token, url_base, nome_fantasia, razao_social, cnpj, api_url, api_id, api_token) 
VALUES ('Demo', 'demo', 'demo_token_123', 'http://localhost/kss', 'Demo Imobiliária', 'Demo Imobiliária LTDA', '12.345.678/0001-90', 'https://www.lagoimobiliaria.com.br', '42', 'bccbe9c743bd0e8edc809012f5a1234567890abcdef')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Inserir imobiliária TOPX
INSERT INTO imobiliarias (nome, instancia, token, url_base, nome_fantasia, razao_social, cnpj, api_url, api_id, api_token) 
VALUES ('TOPX', 'topx', 'topx_token_456', 'http://localhost/kss', 'TOPX Empreendimentos', 'TOPX Empreendimentos LTDA', '98.765.432/0001-10', 'https://www.lagoimobiliaria.com.br', '42', 'bccbe9c743bd0e8edc809012f5a1234567890abcdef')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Inserir categorias básicas
INSERT INTO categorias (nome, descricao, status) VALUES
('Elétrica', 'Problemas relacionados à instalação elétrica', 'ATIVA'),
('Hidráulica', 'Problemas relacionados à instalação hidráulica', 'ATIVA'),
('Pintura', 'Serviços de pintura e acabamento', 'ATIVA'),
('Limpeza', 'Serviços de limpeza e manutenção', 'ATIVA')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Inserir subcategorias
INSERT INTO subcategorias (categoria_id, nome, descricao, status) VALUES
(1, 'Troca de Tomada', 'Substituição de tomadas elétricas', 'ATIVA'),
(1, 'Troca de Interruptor', 'Substituição de interruptores', 'ATIVA'),
(1, 'Instalação de Ventilador', 'Instalação de ventiladores de teto', 'ATIVA'),
(2, 'Vazamento de Torneira', 'Reparo de vazamentos em torneiras', 'ATIVA'),
(2, 'Desentupimento', 'Desentupimento de ralos e canos', 'ATIVA'),
(3, 'Pintura de Parede', 'Pintura interna de paredes', 'ATIVA'),
(4, 'Limpeza Geral', 'Limpeza completa do imóvel', 'ATIVA')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Inserir status do Kanban
INSERT INTO status (nome, descricao, cor, ordem, status) VALUES
('Nova Solicitação', 'Solicitação criada pelo locatário', '#3B82F6', 1, 'ATIVA'),
('Buscando Prestador', 'Enviada para Mawdy para designação', '#F59E0B', 2, 'ATIVA'),
('Serviço Agendado', 'Prestador confirmado e agendado', '#10B981', 3, 'ATIVA'),
('Pendências', 'Aguardando resolução de pendências', '#EF4444', 4, 'ATIVA')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Mostrar mensagem de sucesso
SELECT 'Banco de dados configurado com sucesso!' as mensagem;
