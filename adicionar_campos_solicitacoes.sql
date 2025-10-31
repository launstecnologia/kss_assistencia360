-- Adicionar campos necessários para exibição completa de solicitações manuais
-- Execute este script no banco de dados

-- Adicionar campo para CPF do locatário
ALTER TABLE solicitacoes 
ADD COLUMN IF NOT EXISTS locatario_cpf VARCHAR(14) NULL AFTER locatario_email;

-- Adicionar campo para horários preferenciais (JSON)
ALTER TABLE solicitacoes 
ADD COLUMN IF NOT EXISTS horarios_opcoes JSON NULL AFTER prioridade;

-- Adicionar índice para busca por CPF
ALTER TABLE solicitacoes 
ADD INDEX IF NOT EXISTS idx_locatario_cpf (locatario_cpf);

