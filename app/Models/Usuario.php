<?php

namespace App\Models;

use App\Core\Database;

class Usuario extends Model
{
    protected string $table = 'usuarios';
    protected array $fillable = [
        'nome', 'email', 'telefone', 'senha', 'nivel_permissao', 'status', 'created_at', 'updated_at'
    ];
    protected array $hidden = ['senha'];
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND status = 'ATIVO'";
        return Database::fetch($sql, [$email]);
    }

    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['senha'])) {
            return $this->hide($user);
        }
        
        return null;
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
}
