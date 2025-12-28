<?php

namespace App\Models\Envelopei;

class ItemEnvelopeModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_itens_envelope';
    protected $primaryKey = 'ItemEnvelopeId';

    protected $allowedFields = [
        'LancamentoId',
        'EnvelopeId',
        'Valor',
        'DataCriacao',
    ];

    public function extrato(int $envelopeId, ?string $dataInicio=null, ?string $dataFim=null): array
    {
        $db = db_connect();

        $builder = $db->table('tb_itens_envelope ie')
            ->select('ie.*, l.TipoLancamento, l.Descricao, l.DataLancamento')
            ->join('tb_lancamentos l', 'l.LancamentoId = ie.LancamentoId', 'inner')
            ->where('ie.EnvelopeId', $envelopeId);

        if ($dataInicio) $builder->where('l.DataLancamento >=', $dataInicio);
        if ($dataFim)    $builder->where('l.DataLancamento <=', $dataFim);

        return $builder->orderBy('l.DataLancamento', 'DESC')
                       ->orderBy('ie.ItemEnvelopeId', 'DESC')
                       ->get()
                       ->getResultArray();
    }
}
