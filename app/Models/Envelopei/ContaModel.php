<?php

namespace App\Models\Envelopei;

class ContaModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_contas';
    protected $primaryKey = 'ContaId';

    protected $allowedFields = [
        'UsuarioId',
        'Nome',
        'TipoConta',
        'SaldoInicial',
        'Ativa',
        'DataCriacao',
    ];

    public function listarAtivas(int $usuarioId): array
    {
        return $this->where('UsuarioId', $usuarioId)
                    ->where('Ativa', 1)
                    ->orderBy('Nome', 'ASC')
                    ->findAll();
    }

    public function saldoAtual(int $contaId, ?string $dataFim = null): float
    {
        $conta = $this->select('SaldoInicial')->find($contaId);

        if (!$conta) {
            return 0.0;
        }

        $db = db_connect();

        $builder = $db->table('tb_itens_conta ic')
            ->select('COALESCE(SUM(ic.Valor), 0) as Total')
            ->where('ic.ContaId', $contaId);

        if ($dataFim !== null && $dataFim !== '') {
            $builder->join('tb_lancamentos l', 'l.LancamentoId = ic.LancamentoId', 'inner')
                    ->where('l.DataLancamento <=', $dataFim);
        }

        $row = $builder->get()->getRowArray();

        return (float)$conta['SaldoInicial'] + (float)($row['Total'] ?? 0);
    }
}
