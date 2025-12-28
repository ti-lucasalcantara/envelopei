<?php

namespace App\Models\Envelopei;

class RateioReceitaModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_rateios_receita';
    protected $primaryKey = 'RateioReceitaId';

    protected $allowedFields = [
        'LancamentoId',
        'EnvelopeId',
        'ModoRateio',
        'ValorInformado',
        'ValorCalculado',
        'DataCriacao',
    ];

    public function listarPorLancamento(int $lancamentoId): array
    {
        return $this->where('LancamentoId', $lancamentoId)
                    ->orderBy('RateioReceitaId', 'ASC')
                    ->findAll();
    }
}
