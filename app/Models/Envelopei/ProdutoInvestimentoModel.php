<?php

namespace App\Models\Envelopei;

class ProdutoInvestimentoModel extends BaseEnvelopeiModel
{
    protected $table         = 'tb_produtos_investimento';
    protected $primaryKey    = 'ProdutoInvestimentoId';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'UsuarioId',
        'Nome',
        'TipoProduto',
        'ValorAplicado',
        'ValorAtual',
        'DataCriacao',
        'DataAtualizacao',
    ];

    public const TIPOS = [
        'CDB'             => 'CDB',
        'LCI'             => 'LCI',
        'LCA'             => 'LCA',
        'RendaVariavel'   => 'Renda variável',
        'Tesouro'         => 'Tesouro',
        'FundoImobiliario'=> 'FII',
        'Outros'          => 'Outros',
    ];

    public function listarPorUsuario(int $usuarioId): array
    {
        return $this->where('UsuarioId', $usuarioId)
                    ->orderBy('Nome', 'ASC')
                    ->findAll();
    }

    public function totaisPorUsuario(int $usuarioId): array
    {
        $rows = $this->where('UsuarioId', $usuarioId)->findAll();
        $aplicado = 0.0;
        $atual = 0.0;
        foreach ($rows as $r) {
            $aplicado += (float)($r['ValorAplicado'] ?? 0);
            $atual += (float)($r['ValorAtual'] ?? 0);
        }
        return [
            'TotalAplicado' => round($aplicado, 2),
            'TotalAtual'    => round($atual, 2),
            'Variacao'      => round($atual - $aplicado, 2),
            'Percentual'    => $aplicado != 0 ? round((($atual - $aplicado) / $aplicado * 100), 2) : 0.0,
        ];
    }
}
