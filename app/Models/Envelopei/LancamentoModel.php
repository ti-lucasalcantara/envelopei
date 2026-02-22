<?php

namespace App\Models\Envelopei;

class LancamentoModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_lancamentos';
    protected $primaryKey = 'LancamentoId';

    protected $allowedFields = [
        'UsuarioId',
        'CategoriaId',
        'CartaoCreditoId',
        'FaturaId',
        'TipoLancamento',
        'Descricao',
        'DataLancamento',
        'DataCriacao',
    ];

    public function listarPorPeriodo(int $usuarioId, string $dataInicio, string $dataFim): array
    {
        return $this->where('UsuarioId', $usuarioId)
                    ->where('DataLancamento >=', $dataInicio)
                    ->where('DataLancamento <=', $dataFim)
                    ->orderBy('DataLancamento', 'DESC')
                    ->orderBy('LancamentoId', 'DESC')
                    ->findAll();
    }
}
