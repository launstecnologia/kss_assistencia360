-- Tabela para armazenar dados dos locatários
CREATE TABLE IF NOT EXISTS locatarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    imobiliaria_id INT NOT NULL,
    ksi_cliente_id VARCHAR(50) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    email VARCHAR(255),
    telefone VARCHAR(15),
    whatsapp VARCHAR(15),
    endereco_logradouro VARCHAR(255),
    endereco_numero VARCHAR(20),
    endereco_complemento VARCHAR(100),
    endereco_bairro VARCHAR(100),
    endereco_cidade VARCHAR(100),
    endereco_estado VARCHAR(2),
    endereco_cep VARCHAR(9),
    status ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
    ultima_sincronizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (imobiliaria_id) REFERENCES imobiliarias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_locatario_imobiliaria (imobiliaria_id, ksi_cliente_id),
    UNIQUE KEY unique_cpf_imobiliaria (imobiliaria_id, cpf)
);

-- Tabela para armazenar imóveis dos locatários
CREATE TABLE IF NOT EXISTS imoveis_locatarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    locatario_id INT NOT NULL,
    ksi_imovel_cod VARCHAR(50) NOT NULL,
    endereco_logradouro VARCHAR(255) NOT NULL,
    endereco_numero VARCHAR(20),
    endereco_complemento VARCHAR(100),
    endereco_bairro VARCHAR(100),
    endereco_cidade VARCHAR(100),
    endereco_estado VARCHAR(2),
    endereco_cep VARCHAR(9),
    contrato_cod VARCHAR(50),
    contrato_dv VARCHAR(10),
    status ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (locatario_id) REFERENCES locatarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_imovel_locatario (locatario_id, ksi_imovel_cod)
);

-- Inserir dados de exemplo para teste
INSERT INTO locatarios (
    imobiliaria_id, ksi_cliente_id, nome, cpf, email, telefone, whatsapp,
    endereco_logradouro, endereco_numero, endereco_complemento, 
    endereco_bairro, endereco_cidade, endereco_estado, endereco_cep
) VALUES (
    1, '12345', 'Lucas Ramos de Moraes', '43347692845', 'lucas@email.com', 
    '(16) 99242-2354', '', 'Rua das Flores', '123', 'Apto 45', 
    'Centro', 'Ribeirão Preto', 'SP', '14010-000'
) ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    email = VALUES(email),
    telefone = VALUES(telefone),
    endereco_logradouro = VALUES(endereco_logradouro),
    endereco_numero = VALUES(endereco_numero),
    endereco_complemento = VALUES(endereco_complemento),
    endereco_bairro = VALUES(endereco_bairro),
    endereco_cidade = VALUES(endereco_cidade),
    endereco_estado = VALUES(endereco_estado),
    endereco_cep = VALUES(endereco_cep),
    ultima_sincronizacao = CURRENT_TIMESTAMP;

-- Inserir imóvel do locatário
INSERT INTO imoveis_locatarios (
    locatario_id, ksi_imovel_cod, endereco_logradouro, endereco_numero,
    endereco_bairro, endereco_cidade, endereco_estado, endereco_cep,
    contrato_cod, contrato_dv
) VALUES (
    1, '1', 'Avenida Costábile Romano', '521',
    'Alphaville', 'Ribeirão Preto', 'SP', '14096-030',
    '1353', '3'
) ON DUPLICATE KEY UPDATE
    endereco_logradouro = VALUES(endereco_logradouro),
    endereco_numero = VALUES(endereco_numero),
    endereco_bairro = VALUES(endereco_bairro),
    endereco_cidade = VALUES(endereco_cidade),
    endereco_estado = VALUES(endereco_estado),
    endereco_cep = VALUES(endereco_cep),
    contrato_cod = VALUES(contrato_cod),
    contrato_dv = VALUES(contrato_dv);


