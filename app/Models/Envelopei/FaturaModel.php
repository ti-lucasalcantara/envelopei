<?php

namespace App\Models\Envelopei;

class FaturaModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_faturas';
    protected $primaryKey = 'FaturaId';

    protected $allowedFields = [
        'CartaoCreditoId',
        'MesReferencia',
        'AnoReferencia',
        'DataVencimento',
        'ValorTotal',
        'Pago',
        'DataPagamento',
        'ContaIdPagamento',
        'DataCriacao',
    ];

    /**
     * Retorna a fatura para um cartão em determinado mês/ano.
     * Cria se não existir.
     */
    public function obterOuCriar(int $cartaoCreditoId, int $mesRef, int $anoRef, int $diaVencimento): array
    {
        $fatura = $this->where('CartaoCreditoId', $cartaoCreditoId)
                       ->where('MesReferencia', $mesRef)
                       ->where('AnoReferencia', $anoRef)
                       ->first();

        if ($fatura) {
            return $fatura;
        }

        $dataVencimento = sprintf('%04d-%02d-%02d', $anoRef, $mesRef, min($diaVencimento, 28));
        if ($diaVencimento > 28) {
            $dataVencimento = date('Y-m-t', strtotime("$anoRef-$mesRef-01"));
        }

        $id = $this->insert([
            'CartaoCreditoId'   => $cartaoCreditoId,
            'MesReferencia'     => $mesRef,
            'AnoReferencia'     => $anoRef,
            'DataVencimento'    => $dataVencimento,
            'ValorTotal'        => 0,
            'Pago'              => 0,
            'DataPagamento'     => null,
            'ContaIdPagamento'  => null,
        ]);

        return $this->find((int)$id);
    }

    /**
     * Calcula mes/ano da fatura para uma despesa baseado no dia de fechamento.
     */
    public static function mesAnoParaDespesa(string $dataLancamento, int $diaFechamento): array
    {
        $d = (int)date('d', strtotime($dataLancamento));
        $mes = (int)date('m', strtotime($dataLancamento));
        $ano = (int)date('Y', strtotime($dataLancamento));

        if ($d <= $diaFechamento) {
            return ['Mes' => $mes, 'Ano' => $ano];
        }

        if ($mes === 12) {
            return ['Mes' => 1, 'Ano' => $ano + 1];
        }
        return ['Mes' => $mes + 1, 'Ano' => $ano];
    }

    /**
     * Mes/ano da fatura para a parcela N (1-based).
     * Parcela 1 = ref; parcela 2 = ref + 1 mês; etc.
     */
    public static function mesAnoParaParcela(int $mesRef, int $anoRef, int $numeroParcela): array
    {
        $mes = $mesRef;
        $ano = $anoRef;
        $add = $numeroParcela - 1;
        if ($add > 0) {
            $mes += $add;
            while ($mes > 12) {
                $mes -= 12;
                $ano++;
            }
        }
        return ['Mes' => $mes, 'Ano' => $ano];
    }

    /**
     * Lista faturas de um usuário (via cartões).
     */
    public function listarPorUsuario(int $usuarioId, ?bool $apenasPendentes = null): array
    {
        $db = db_connect();
        $builder = $db->table('tb_faturas f')
            ->select('f.*, cc.Nome as CartaoNome, cc.Ultimos4Digitos, cc.Cor')
            ->join('tb_cartoes_credito cc', 'cc.CartaoCreditoId = f.CartaoCreditoId', 'inner')
            ->where('cc.UsuarioId', $usuarioId)
            ->orderBy('f.AnoReferencia', 'DESC')
            ->orderBy('f.MesReferencia', 'DESC');

        if ($apenasPendentes === true) {
            $builder->where('f.Pago', 0);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Lista faturas de um cartão (pendentes por padrão), ordenadas por período.
     */
    public function listarPorCartao(int $cartaoCreditoId, bool $apenasPendentes = true): array
    {
        $builder = $this->where('CartaoCreditoId', $cartaoCreditoId)
            ->orderBy('AnoReferencia', 'DESC')
            ->orderBy('MesReferencia', 'DESC');

        if ($apenasPendentes) {
            $builder->where('Pago', 0);
        }

        return $builder->findAll();
    }

    /**
     * Lançamentos (despesas) vinculados à fatura.
     */
    public function lancamentosDaFatura(int $faturaId): array
    {
        $db = db_connect();
        $itens = $db->table('tb_lancamentos l')
            ->select('l.*, ie.ItemEnvelopeId, ie.EnvelopeId, ie.Valor, ie.ValorPago, ie.Pago as ItemPago, e.Nome as EnvelopeNome')
            ->join('tb_itens_envelope ie', 'ie.LancamentoId = l.LancamentoId', 'inner')
            ->join('tb_envelopes e', 'e.EnvelopeId = ie.EnvelopeId', 'inner')
            ->where('ie.FaturaId', $faturaId)
            ->orderBy('l.DataLancamento', 'ASC')
            ->get()
            ->getResultArray();

        $pagamentoModel = new PagamentoItemModel();
        foreach ($itens as &$item) {
            $item['Pagamentos'] = $pagamentoModel->listarPorItemEnvelope((int)$item['ItemEnvelopeId']);
        }
        unset($item);

        return $itens;
    }

    public function valorPagoFatura(int $faturaId): float
    {
        $db = db_connect();
        $row = $db->table('tb_itens_envelope')
            ->select('COALESCE(SUM(ValorPago), 0) as Total')
            ->where('FaturaId', $faturaId)
            ->get()
            ->getRowArray();
        return (float)($row['Total'] ?? 0);
    }

    /**
     * Atualiza valor total da fatura.
     */
    public function recalcularValorTotal(int $faturaId): void
    {
        $db = db_connect();
        $row = $db->table('tb_itens_envelope')
            ->select('COALESCE(SUM(ABS(Valor)), 0) as Total')
            ->where('FaturaId', $faturaId)
            ->get()
            ->getRowArray();

        $total = (float)($row['Total'] ?? 0);
        $this->update($faturaId, ['ValorTotal' => $total]);
    }

    /**
     * Total de faturas em aberto (pendentes) do usuário.
     */
    public function totalFaturasEmAberto(int $usuarioId): float
    {
        $db = db_connect();
        $row = $db->table('tb_faturas f')
            ->select('COALESCE(SUM(f.ValorTotal), 0) as Total')
            ->join('tb_cartoes_credito cc', 'cc.CartaoCreditoId = f.CartaoCreditoId', 'inner')
            ->where('cc.UsuarioId', $usuarioId)
            ->where('cc.Ativo', 1)
            ->where('f.Pago', 0)
            ->get()
            ->getRowArray();

        return (float)($row['Total'] ?? 0);
    }

    /**
     * Soma do ValorTotal das faturas com vencimento no mês/ano informado (do usuário).
     */
    public function totalFaturasDoMes(int $usuarioId, int $mes, int $ano): float
    {
        $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
        $dataFim = date('Y-m-t', strtotime($dataInicio));

        $db = db_connect();
        $row = $db->table('tb_faturas f')
            ->select('COALESCE(SUM(f.ValorTotal), 0) as Total')
            ->join('tb_cartoes_credito cc', 'cc.CartaoCreditoId = f.CartaoCreditoId', 'inner')
            ->where('cc.UsuarioId', $usuarioId)
            ->where('cc.Ativo', 1)
            ->where('f.DataVencimento >=', $dataInicio)
            ->where('f.DataVencimento <=', $dataFim)
            ->get()
            ->getRowArray();

        return (float)($row['Total'] ?? 0);
    }

    /**
     * Próximas faturas a vencer (pendentes).
     */
    public function proximasAVencer(int $usuarioId, int $limite = 5): array
    {
        $db = db_connect();
        return $db->table('tb_faturas f')
            ->select('f.*, cc.Nome as CartaoNome, cc.Ultimos4Digitos')
            ->join('tb_cartoes_credito cc', 'cc.CartaoCreditoId = f.CartaoCreditoId', 'inner')
            ->where('cc.UsuarioId', $usuarioId)
            ->where('cc.Ativo', 1)
            ->where('f.Pago', 0)
            ->where('f.DataVencimento >=', date('Y-m-d'))
            ->orderBy('f.DataVencimento', 'ASC')
            ->limit($limite)
            ->get()
            ->getResultArray();
    }

    /**
     * Faturas pendentes com vencimento somente no próximo mês (para o dashboard).
     */
    public function proximasAVencerProximoMes(int $usuarioId, int $limite = 10): array
    {
        $inicioProximoMes = date('Y-m-01', strtotime('first day of next month'));
        $fimProximoMes   = date('Y-m-t', strtotime('last day of next month'));

        $db = db_connect();
        return $db->table('tb_faturas f')
            ->select('f.*, cc.Nome as CartaoNome, cc.Ultimos4Digitos')
            ->join('tb_cartoes_credito cc', 'cc.CartaoCreditoId = f.CartaoCreditoId', 'inner')
            ->where('cc.UsuarioId', $usuarioId)
            ->where('cc.Ativo', 1)
            ->where('f.Pago', 0)
            ->where('f.DataVencimento >=', $inicioProximoMes)
            ->where('f.DataVencimento <=', $fimProximoMes)
            ->orderBy('f.DataVencimento', 'ASC')
            ->limit($limite)
            ->get()
            ->getResultArray();
    }
}
