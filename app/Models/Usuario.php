<?php

namespace App\Models;

use App\Core\Database;

class Usuario extends Model
{
    protected string $table = 'usuarios';
    protected array $fillable = [
        'nome', 'email', 'telefone', 'cpf', 'senha', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'cep', 'nivel_permissao', 'status', 'created_at', 'updated_at'
    ];
    protected array $hidden = ['senha'];
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        return Database::fetch($sql, [$email]);
    }

    public function findByCpf(string $cpf): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE cpf = ?";
        return Database::fetch($sql, [$cpf]);
    }

    public function authenticate(string $email, string $password): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND status = 'ATIVO'";
        $user = Database::fetch($sql, [$email]);
        
        if ($user && password_verify($password, $user['senha'])) {
            return $this->hide($user);
        }
        
        return null;
    }

    public function getAll(array $filtros = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (isset($filtros['busca']) && !empty($filtros['busca'])) {
            $sql .= " AND (nome LIKE ? OR email LIKE ? OR cpf LIKE ? OR id LIKE ?)";
            $busca = "%{$filtros['busca']}%";
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
            $params[] = $busca;
        }

        $sql .= " ORDER BY created_at DESC";

        return Database::fetchAll($sql, $params);
    }

    public function count(array $where = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($where)) {
            $conditions = [];
            $params = [];
            
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = ?";
                $params[] = $value;
            }
            
            $sql .= " WHERE " . implode(' AND ', $conditions);
            
            $result = Database::fetch($sql, $params);
        } else {
            $result = Database::fetch($sql);
        }
        
        return (int) ($result['total'] ?? 0);
    }

    public function create(array $data): int
    {
        if (isset($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return parent::create($data);
    }

    public function update(int $id, array $data): bool
    {
        if (isset($data['senha']) && !empty($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        } else {
            unset($data['senha']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return parent::update($id, $data);
    }

    public function getOperadores(): array
    {
        return $this->findAll(['nivel_permissao' => 'OPERADOR', 'status' => 'ATIVO']);
    }

    public function getAdministradores(): array
    {
        return $this->findAll(['nivel_permissao' => 'ADMINISTRADOR', 'status' => 'ATIVO']);
    }

    public function isAdmin(array $user): bool
    {
        return $user['nivel_permissao'] === 'ADMINISTRADOR';
    }

    public function isOperador(array $user): bool
    {
        return $user['nivel_permissao'] === 'OPERADOR';
    }

    public function canAccess(array $user, string $resource): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // Operadores só podem acessar solicitações
        if ($this->isOperador($user)) {
            return in_array($resource, ['solicitacoes', 'dashboard']);
        }

        return false;
    }
    
    /**
     * Salva token de "lembrar de mim"
     */
    public function saveRememberToken(int $userId, string $token, int $expires): void
    {
        // Criar tabela se não existir
        $sql = "
            CREATE TABLE IF NOT EXISTS remember_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expires_at INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_token (token),
                INDEX idx_usuario (usuario_id),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        Database::query($sql);
        
        // Limpar tokens expirados
        $this->cleanExpiredRememberTokens();
        
        // Salvar novo token
        $sql = "INSERT INTO remember_tokens (usuario_id, token, expires_at) VALUES (?, ?, ?)";
        Database::query($sql, [$userId, $token, $expires]);
    }
    
    /**
     * Busca usuário por token de "lembrar de mim"
     */
    public function findByRememberToken(string $token): ?array
    {
        $sql = "
            SELECT u.* FROM {$this->table} u
            INNER JOIN remember_tokens rt ON u.id = rt.usuario_id
            WHERE rt.token = ? AND rt.expires_at > ? AND u.status = 'ATIVO'
        ";
        $user = Database::fetch($sql, [$token, time()]);
        
        if ($user) {
            return $this->hide($user);
        }
        
        return null;
    }
    
    /**
     * Remove token de "lembrar de mim"
     */
    public function deleteRememberToken(string $token): void
    {
        $sql = "DELETE FROM remember_tokens WHERE token = ?";
        Database::query($sql, [$token]);
    }
    
    /**
     * Limpa tokens expirados
     */
    private function cleanExpiredRememberTokens(): void
    {
        $sql = "DELETE FROM remember_tokens WHERE expires_at < ?";
        Database::query($sql, [time()]);
    }
    
    /**
     * Salva token de recuperação de senha
     */
    public function savePasswordResetToken(int $userId, string $token, int $expires): void
    {
        // Criar tabela se não existir
        $sql = "
            CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expires_at INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_token (token),
                INDEX idx_usuario (usuario_id),
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        Database::query($sql);
        
        // Limpar tokens expirados
        $this->cleanExpiredPasswordResetTokens();
        
        // Remover tokens antigos do mesmo usuário
        $sql = "DELETE FROM password_reset_tokens WHERE usuario_id = ?";
        Database::query($sql, [$userId]);
        
        // Salvar novo token
        $sql = "INSERT INTO password_reset_tokens (usuario_id, token, expires_at) VALUES (?, ?, ?)";
        Database::query($sql, [$userId, $token, $expires]);
    }
    
    /**
     * Valida token de recuperação de senha
     */
    public function validatePasswordResetToken(string $token): ?array
    {
        $sql = "
            SELECT * FROM password_reset_tokens
            WHERE token = ? AND expires_at > ?
            ORDER BY created_at DESC
            LIMIT 1
        ";
        return Database::fetch($sql, [$token, time()]);
    }
    
    /**
     * Remove token de recuperação de senha
     */
    public function deletePasswordResetToken(string $token): void
    {
        $sql = "DELETE FROM password_reset_tokens WHERE token = ?";
        Database::query($sql, [$token]);
    }
    
    /**
     * Limpa tokens de recuperação expirados
     */
    private function cleanExpiredPasswordResetTokens(): void
    {
        $sql = "DELETE FROM password_reset_tokens WHERE expires_at < ?";
        Database::query($sql, [time()]);
    }
    
    /**
     * Atualiza senha do usuário
     */
    public function updatePassword(int $userId, string $newPassword): void
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->update($userId, ['senha' => $hashedPassword]);
    }
}
