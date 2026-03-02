<?php

namespace App\Models\Envelopei;

class AporteInvestimentoModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_aportes_investimento';
    protected $primaryKey = 'AporteId';

    protected $allowedFields = [
        'ProdutoInvestimentoId',
        'UsuarioId',
        'Valor',
        'DataAporte',
        'Descricao',
        'DataCriacao',
    ];

    public function listarPorProduto(int $produtoInvestimentoId): array
    {
        return $this->where('ProdutoInvestimentoId', $produtoInvestimentoId)
                    ->orderBy('DataAporte', 'DESC')
                    ->orderBy('DataCriacao', 'DESC')
                    ->findAll();
    }

    public function totalAportesProduto(int $produtoInvestimentoId): float
    {
        $row = $this->where('ProdutoInvestimentoId', $produtoInvestimentoId)
                    ->selectSum('Valor')
                    ->first();
        return (float)($row['Valor'] ?? 0);
    }
}
