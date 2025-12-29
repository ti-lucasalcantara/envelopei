<?php

namespace App\Models\Envelopei;

use CodeIgniter\Model;

class RateioModeloModel extends Model
{
    protected $table            = 'tb_rateios_modelo';
    protected $primaryKey       = 'RateioModeloId';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'UsuarioId',
        'Nome',
        'Padrao',
        'Ativo',
        'DataCriacao',
    ];

    protected $useTimestamps    = false;

    // ---------------------------------------------
    // Helpers
    // ---------------------------------------------

    public function getPadraoDoUsuario(int $usuarioId): ?array
    {
        return $this->where('UsuarioId', $usuarioId)
            ->where('Padrao', 1)
            ->where('Ativo', 1)
            ->first();
    }

    public function limparPadraoDoUsuario(int $usuarioId): bool
    {
        return (bool) $this->where('UsuarioId', $usuarioId)
            ->set(['Padrao' => 0])
            ->update();
    }

    public function listarDoUsuario(int $usuarioId): array
    {
        return $this->where('UsuarioId', $usuarioId)
            ->where('Ativo', 1)
            ->orderBy('Padrao', 'DESC')
            ->orderBy('Nome', 'ASC')
            ->findAll();
    }
}
