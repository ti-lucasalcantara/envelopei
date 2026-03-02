<?php

namespace App\Models\Envelopei;

class RendimentoInvestimentoModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_rendimentos_investimento';
    protected $primaryKey = 'RendimentoId';

    protected $allowedFields = [
        'ProdutoInvestimentoId',
        'UsuarioId',
        'Valor',
        'DataRendimento',
        'Descricao',
        'DataCriacao',
    ];

    public function listarPorProduto(int $produtoInvestimentoId): array
    {
        return $this->where('ProdutoInvestimentoId', $produtoInvestimentoId)
                    ->orderBy('DataRendimento', 'DESC')
                    ->orderBy('DataCriacao', 'DESC')
                    ->findAll();
    }

    public function totalRendimentosProduto(int $produtoInvestimentoId): float
    {
        $row = $this->where('ProdutoInvestimentoId', $produtoInvestimentoId)
                    ->selectSum('Valor')
                    ->first();
        return (float)($row['Valor'] ?? 0);
    }
}
