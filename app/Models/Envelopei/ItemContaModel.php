<?php

namespace App\Models\Envelopei;

class ItemContaModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_itens_conta';
    protected $primaryKey = 'ItemContaId';

    protected $allowedFields = [
        'LancamentoId',
        'ContaId',
        'Valor',
        'DataCriacao',
    ];

    public function listarPorConta(int $contaId, ?string $dataInicio=null, ?string $dataFim=null): array
    {
        $db = db_connect();

        $builder = $db->table('tb_itens_conta ic')
            ->select('ic.*, l.TipoLancamento, l.Descricao, l.DataLancamento')
            ->join('tb_lancamentos l', 'l.LancamentoId = ic.LancamentoId', 'inner')
            ->where('ic.ContaId', $contaId);

        if ($dataInicio) $builder->where('l.DataLancamento >=', $dataInicio);
        if ($dataFim)    $builder->where('l.DataLancamento <=', $dataFim);

        return $builder->orderBy('l.DataLancamento', 'DESC')
                       ->orderBy('ic.ItemContaId', 'DESC')
                       ->get()
                       ->getResultArray();
    }
}
