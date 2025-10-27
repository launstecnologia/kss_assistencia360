-- Script SQL para corrigir a estrutura da tabela imobiliarias
-- Remove campos duplicados e organiza a estrutura

-- 1. Remover campo duplicado api_instancia (se existir)
ALTER TABLE imobiliarias DROP COLUMN IF EXISTS api_instancia;

-- 2. Verificar se os campos necessários existem
-- Se não existirem, criar com os valores corretos

-- 3. Atualizar dados existentes para usar a estrutura correta
UPDATE imobiliarias SET 
    api_url = 'https://www.lagoimobiliaria.com.br',
    api_id = '42',
    api_token = 'bccbe9c743bd0e8edc809012f5a1234567890abcdef'
WHERE instancia IN ('demo', 'topx');

-- 4. Verificar estrutura final
DESCRIBE imobiliarias;
