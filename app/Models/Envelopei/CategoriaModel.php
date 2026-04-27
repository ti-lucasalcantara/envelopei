<?php

namespace App\Models\Envelopei;

class CategoriaModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_categorias';
    protected $primaryKey = 'CategoriaId';

    protected $allowedFields = [
        'UsuarioId',
        'Nome',
        'TipoCategoria',
        'Cor',
        'Icone',
        'Ativa',
        'DataCriacao',
        'DataAtualizacao',
    ];

    public function listarAtivas(int $usuarioId): array
    {
        return $this->where('UsuarioId', $usuarioId)
                    ->where('Ativa', 1)
                    ->orderBy('Nome', 'ASC')
                    ->findAll();
    }
}
