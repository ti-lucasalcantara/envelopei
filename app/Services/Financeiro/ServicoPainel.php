<?php

namespace App\Services\Financeiro;

class ServicoPainel
{
    /**
     * Monta os indicadores principais do dashboard para o usuario informado.
     */
    public function resumo(int $usuarioId): array
    {
        $db = db_connect();
        $inicioMes = date('Y-m-01');
        $fimMes = date('Y-m-t');

        $contas = $this->listarContasComSaldo($usuarioId);
        $envelopes = $this->listarEnvelopesComSaldo($usuarioId);

        $saldoContas = array_sum(array_column($contas, 'SaldoAtual'));
        $saldoEnvelopes = array_sum(array_column($envelopes, 'SaldoAtual'));
        $saldoLivre = $saldoContas - $saldoEnvelopes;

        $movimentosMes = $db->table('tb_lancamentos l')
            ->select("
                COALESCE(SUM(CASE WHEN l.TipoLancamento = 'receita' THEN ic.Valor ELSE 0 END), 0) as Receitas,
                COALESCE(SUM(CASE WHEN l.TipoLancamento = 'despesa' THEN ABS(COALESCE(ic.Valor, ie.Valor, 0)) ELSE 0 END), 0) as Despesas
            ")
            ->join('tb_itens_conta ic', 'ic.LancamentoId = l.LancamentoId', 'left')
            ->join('tb_itens_envelope ie', 'ie.LancamentoId = l.LancamentoId', 'left')
            ->where('l.UsuarioId', $usuarioId)
            ->where('COALESCE(l.Ativo, 1) = 1', null, false)
            ->where('l.DataLancamento >=', $inicioMes)
            ->where('l.DataLancamento <=', $fimMes)
            ->get()
            ->getRowArray();

        $investimentos = $db->table('tb_produtos_investimento')
            ->select('COALESCE(SUM(ValorAtual), 0) as Total')
            ->where('UsuarioId', $usuarioId)
            ->get()
            ->getRowArray();

        $faturasAbertas = $db->table('tb_faturas f')
            ->select('COALESCE(SUM(f.ValorTotal), 0) as Total')
            ->join('tb_cartoes_credito c', 'c.CartaoCreditoId = f.CartaoCreditoId', 'inner')
            ->where('c.UsuarioId', $usuarioId)
            ->where('COALESCE(f.Pago, 0) = 0', null, false)
            ->get()
            ->getRowArray();

        return [
            'saldoContas' => round($saldoContas, 2),
            'saldoEnvelopes' => round($saldoEnvelopes, 2),
            'saldoLivre' => round($saldoLivre, 2),
            'receitasMes' => round((float) ($movimentosMes['Receitas'] ?? 0), 2),
            'despesasMes' => round((float) ($movimentosMes['Despesas'] ?? 0), 2),
            'resultadoMes' => round((float) ($movimentosMes['Receitas'] ?? 0) - (float) ($movimentosMes['Despesas'] ?? 0), 2),
            'totalInvestimentos' => round((float) ($investimentos['Total'] ?? 0), 2),
            'faturasAbertas' => round((float) ($faturasAbertas['Total'] ?? 0), 2),
            'contas' => $contas,
            'envelopes' => $envelopes,
            'graficos' => $this->dadosGraficos($usuarioId),
            'proximasDespesas' => $this->proximasDespesas($usuarioId),
        ];
    }

    /**
     * Lista contas ativas com saldo calculado pelo livro razao atual.
     */
    public function listarContasComSaldo(int $usuarioId, bool $somenteAtivas = true): array
    {
        $db = db_connect();
        $builder = $db->table('tb_contas c')
            ->select('c.*, COALESCE(c.SaldoInicial, 0) + COALESCE(SUM(CASE WHEN COALESCE(l.Ativo, 1) = 1 THEN ic.Valor ELSE 0 END), 0) as SaldoAtual')
            ->join('tb_itens_conta ic', 'ic.ContaId = c.ContaId', 'left')
            ->join('tb_lancamentos l', 'l.LancamentoId = ic.LancamentoId', 'left')
            ->where('c.UsuarioId', $usuarioId)
            ->groupBy('c.ContaId')
            ->orderBy('COALESCE(c.Ativa, 1)', 'DESC', false)
            ->orderBy('c.Nome', 'ASC');

        if ($somenteAtivas) {
            $builder->where('COALESCE(c.Ativa, 1) = 1', null, false);
        }

        $linhas = $builder->get()->getResultArray();

        foreach ($linhas as &$linha) {
            $linha['SaldoAtual'] = round((float) ($linha['SaldoAtual'] ?? 0), 2);
        }

        return $linhas;
    }

    /**
     * Lista envelopes com saldo, conta vinculada e dados de meta.
     */
    public function listarEnvelopesComSaldo(int $usuarioId): array
    {
        $db = db_connect();
        $linhas = $db->table('tb_envelopes e')
            ->select("
                e.*,
                c.Nome as ContaNome,
                COALESCE(SUM(CASE
                    WHEN ie.FaturaId IS NULL THEN ie.Valor
                    ELSE -COALESCE(ie.ValorPago, 0)
                END), 0) as SaldoAtual
            ")
            ->join('tb_contas c', 'c.ContaId = e.ContaId', 'left')
            ->join('tb_itens_envelope ie', 'ie.EnvelopeId = e.EnvelopeId', 'left')
            ->where('e.UsuarioId', $usuarioId)
            ->where('COALESCE(e.Ativo, 1) = 1', null, false)
            ->groupBy('e.EnvelopeId')
            ->orderBy('e.Ordem', 'ASC')
            ->orderBy('e.Nome', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($linhas as &$linha) {
            $saldo = (float) ($linha['SaldoAtual'] ?? 0);
            $meta = (float) ($linha['MetaValor'] ?? 0);
            $linha['SaldoAtual'] = round($saldo, 2);
            $linha['PercentualMeta'] = $meta > 0 ? min(100, round(($saldo / $meta) * 100, 2)) : 0;
        }

        return $linhas;
    }

    /**
     * Prepara series simples para os graficos do dashboard.
     */
    private function dadosGraficos(int $usuarioId): array
    {
        $db = db_connect();
        $mensal = $db->table('tb_lancamentos l')
            ->select("
                DATE_FORMAT(l.DataLancamento, '%Y-%m') as Mes,
                COALESCE(SUM(CASE WHEN l.TipoLancamento = 'receita' THEN ABS(COALESCE(ic.Valor, ie.Valor, 0)) ELSE 0 END), 0) as Receitas,
                COALESCE(SUM(CASE WHEN l.TipoLancamento = 'despesa' THEN ABS(COALESCE(ic.Valor, ie.Valor, 0)) ELSE 0 END), 0) as Despesas
            ")
            ->join('tb_itens_conta ic', 'ic.LancamentoId = l.LancamentoId', 'left')
            ->join('tb_itens_envelope ie', 'ie.LancamentoId = l.LancamentoId', 'left')
            ->where('l.UsuarioId', $usuarioId)
            ->where('COALESCE(l.Ativo, 1) = 1', null, false)
            ->groupBy("DATE_FORMAT(l.DataLancamento, '%Y-%m')")
            ->orderBy('Mes', 'ASC')
            ->limit(12)
            ->get()
            ->getResultArray();

        $categorias = $db->table('tb_lancamentos l')
            ->select('COALESCE(cat.Nome, "Sem categoria") as Categoria, COALESCE(SUM(ABS(COALESCE(ic.Valor, ie.Valor, 0))), 0) as Total')
            ->join('tb_categorias cat', 'cat.CategoriaId = l.CategoriaId', 'left')
            ->join('tb_itens_conta ic', 'ic.LancamentoId = l.LancamentoId', 'left')
            ->join('tb_itens_envelope ie', 'ie.LancamentoId = l.LancamentoId', 'left')
            ->where('l.UsuarioId', $usuarioId)
            ->where('l.TipoLancamento', 'despesa')
            ->where('COALESCE(l.Ativo, 1) = 1', null, false)
            ->groupBy('cat.CategoriaId, cat.Nome')
            ->orderBy('Total', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();

        return [
            'mensal' => $mensal,
            'categorias' => $categorias,
        ];
    }

    /**
     * Retorna despesas recentes/proximas para exibicao no painel.
     */
    private function proximasDespesas(int $usuarioId): array
    {
        return db_connect()->table('tb_lancamentos l')
            ->select('l.*, cat.Nome as CategoriaNome')
            ->join('tb_categorias cat', 'cat.CategoriaId = l.CategoriaId', 'left')
            ->where('l.UsuarioId', $usuarioId)
            ->where('l.TipoLancamento', 'despesa')
            ->where('COALESCE(l.Ativo, 1) = 1', null, false)
            ->orderBy('l.DataLancamento', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();
    }
}
