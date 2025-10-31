START TRANSACTION;

-- Drop added columns if they exist
ALTER TABLE solicitacoes
  DROP COLUMN IF EXISTS locatario_cpf,
  DROP COLUMN IF EXISTS horarios_opcoes,
  DROP COLUMN IF EXISTS horarios_sugestoes;

-- Drop the manual requests table if it exists
DROP TABLE IF EXISTS solicitacoes_manuais;

COMMIT;
