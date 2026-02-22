<?php

namespace App\Models\Envelopei;

class CartaoCreditoModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_cartoes_credito';
    protected $primaryKey = 'CartaoCreditoId';

    protected $allowedFields = [
        'UsuarioId',
        'Nome',
        'Bandeira',
        'Ultimos4Digitos',
        'DiaFechamento',
        'DiaVencimento',
        'Limite',
        'Cor',
        'Ativo',
        'DataCriacao',
    ];

    public function listarAtivos(int $usuarioId): array
    {
        return $this->where('UsuarioId', $usuarioId)
                    ->where('Ativo', 1)
                    ->orderBy('Nome', 'ASC')
                    ->findAll();
    }

    public function listarTodos(int $usuarioId): array
    {
        return $this->where('UsuarioId', $usuarioId)
                    ->orderBy('Nome', 'ASC')
                    ->findAll();
    }
}
