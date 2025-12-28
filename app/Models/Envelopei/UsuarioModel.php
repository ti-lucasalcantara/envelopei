<?php

namespace App\Models\Envelopei;

class UsuarioModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_usuarios';
    protected $primaryKey = 'UsuarioId';

    protected $allowedFields = [
        'Nome',
        'Email',
        'SenhaHash',
        'Ativo',
        'DataCriacao',
    ];

    public function buscarPorEmail(string $email): ?array
    {
        return $this->where('Email', $email)->first();
    }
}
