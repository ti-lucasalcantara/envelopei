<?php

namespace App\Models\Envelopei;

class PagamentoItemModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_pagamentos_item';
    protected $primaryKey = 'PagamentoItemId';

    /** Tabela não tem coluna DataCriacao; evita callback do base que a adiciona */
    protected $beforeInsert = [];

    protected $allowedFields = [
        'ItemEnvelopeId',
        'Valor',
        'Descricao',
        'DataPagamento',
        'ContaIdPagamento',
        'LancamentoId',
    ];

    public function listarPorItemEnvelope(int $itemEnvelopeId): array
    {
        return $this->where('ItemEnvelopeId', $itemEnvelopeId)
                    ->orderBy('DataPagamento', 'ASC')
                    ->orderBy('PagamentoItemId', 'ASC')
                    ->findAll();
    }
}
