<?php

namespace App\Models\Envelopei;

class EnvelopeModel extends BaseEnvelopeiModel
{
    protected $table      = 'tb_envelopes';
    protected $primaryKey = 'EnvelopeId';

    protected $allowedFields = [
        'UsuarioId',
        'Nome',
        'Cor',
        'Ordem',
        'Ativo',
        'DataCriacao',
    ];

    public function listarAtivos(int $usuarioId): array
    {
        return $this->where('UsuarioId', $usuarioId)
                    ->where('Ativo', 1)
                    ->orderBy('Ordem', 'ASC')
                    ->orderBy('Nome', 'ASC')
                    ->findAll();
    }

    public function saldoAtual(int $envelopeId): float
    {
        $db = db_connect();

        $row = $db->query("
            SELECT COALESCE(SUM(
                CASE
                    WHEN ie.FaturaId IS NULL THEN ie.Valor
                    ELSE -COALESCE(ie.ValorPago, 0)
                END
            ), 0) as Total
            FROM tb_itens_envelope ie
            LEFT JOIN tb_faturas f ON f.FaturaId = ie.FaturaId
            WHERE ie.EnvelopeId = ?
            AND (ie.FaturaId IS NULL OR COALESCE(ie.ValorPago, 0) > 0 OR f.Pago = 1)
        ", [$envelopeId])->getRowArray();

        return (float)($row['Total'] ?? 0);
    }

    /**
     * @param int $usuarioId
     * @param string|null $dataFim Data final para saldo (ex: último dia do mês)
     * @param string|null $dataInicio Data inicial para ReceitasMes/DespesasMes (ex: primeiro dia do mês). Se null, ReceitasMes e DespesasMes ficam 0.
     */
    public function saldosPorUsuario(int $usuarioId, ?string $dataFim = null, ?string $dataInicio = null): array
    {
        $db = db_connect();

        $ateData = ($dataFim !== null && $dataFim !== '') ? ' AND l.DataLancamento <= ' . $db->escape($dataFim) : '';
        $noMes = '';
        if ($dataInicio !== null && $dataInicio !== '' && $dataFim !== null && $dataFim !== '') {
            $noMes = ' AND l.DataLancamento >= ' . $db->escape($dataInicio) . ' AND l.DataLancamento <= ' . $db->escape($dataFim);
        }

        $receitasMesSel = $noMes !== '' ? ", COALESCE(SUM(CASE WHEN l.TipoLancamento = 'receita' {$noMes} THEN ie.Valor ELSE 0 END), 0) as ReceitasMes" : ", 0 as ReceitasMes";
        $despesasMesSel = $noMes !== '' ? ", COALESCE(SUM(CASE WHEN l.TipoLancamento = 'despesa' {$noMes} THEN ABS(ie.Valor) ELSE 0 END), 0) as DespesasMes" : ", 0 as DespesasMes";

        $sql = "
            SELECT e.EnvelopeId, e.Nome, e.Cor, e.Ordem,
                   COALESCE(SUM(CASE WHEN l.LancamentoId IS NOT NULL {$ateData} THEN CASE WHEN ie.FaturaId IS NULL THEN ie.Valor WHEN ie.FaturaId IS NOT NULL AND (COALESCE(ie.ValorPago, 0) > 0 OR f.Pago = 1) THEN -COALESCE(ie.ValorPago, 0) ELSE 0 END ELSE 0 END), 0) as Saldo,
                   COALESCE(SUM(CASE WHEN l.LancamentoId IS NOT NULL {$ateData} AND ie.FaturaId IS NOT NULL AND COALESCE(f.Pago, 0) = 0 THEN GREATEST(0, ABS(ie.Valor) - COALESCE(ie.ValorPago, 0)) ELSE 0 END), 0) as GastosComCartao
                   {$receitasMesSel}
                   {$despesasMesSel}
            FROM tb_envelopes e
            LEFT JOIN tb_itens_envelope ie ON ie.EnvelopeId = e.EnvelopeId
            LEFT JOIN tb_lancamentos l ON l.LancamentoId = ie.LancamentoId
            LEFT JOIN tb_faturas f ON f.FaturaId = ie.FaturaId
            WHERE e.UsuarioId = ? AND e.Ativo = 1
            GROUP BY e.EnvelopeId
            ORDER BY e.Ordem ASC, e.Nome ASC
        ";

        $rows = $db->query($sql, [$usuarioId])->getResultArray();

        foreach ($rows as &$r) {
            $saldo = (float)($r['Saldo'] ?? 0);
            $gastosCartao = (float)($r['GastosComCartao'] ?? 0);
            $r['SaldoAposPagamento'] = round($saldo - $gastosCartao, 2);
            $r['ReceitasMes'] = round((float)($r['ReceitasMes'] ?? 0), 2);
            $r['DespesasMes'] = round((float)($r['DespesasMes'] ?? 0), 2);
        }
        unset($r);

        return $rows;
    }

    /**
     * Lista todos os envelopes do usuário (ativos e inativos) com saldos.
     * Usado na página de CRUD para permitir reativar.
     */
    public function listarTodosComSaldos(int $usuarioId): array
    {
        $db = db_connect();

        $sql = "
            SELECT e.EnvelopeId, e.Nome, e.Cor, e.Ordem, e.Ativo,
                   COALESCE(SUM(CASE WHEN ie.FaturaId IS NULL THEN ie.Valor WHEN ie.FaturaId IS NOT NULL AND (COALESCE(ie.ValorPago, 0) > 0 OR f.Pago = 1) THEN -COALESCE(ie.ValorPago, 0) ELSE 0 END), 0) as Saldo,
                   COALESCE(SUM(CASE WHEN ie.FaturaId IS NOT NULL AND COALESCE(f.Pago, 0) = 0 THEN GREATEST(0, ABS(ie.Valor) - COALESCE(ie.ValorPago, 0)) ELSE 0 END), 0) as GastosComCartao
            FROM tb_envelopes e
            LEFT JOIN tb_itens_envelope ie ON ie.EnvelopeId = e.EnvelopeId
            LEFT JOIN tb_faturas f ON f.FaturaId = ie.FaturaId
            WHERE e.UsuarioId = ?
            GROUP BY e.EnvelopeId
            ORDER BY e.Ativo DESC, e.Ordem ASC, e.Nome ASC
        ";

        $rows = $db->query($sql, [$usuarioId])->getResultArray();

        foreach ($rows as &$r) {
            $saldo = (float)($r['Saldo'] ?? 0);
            $gastosCartao = (float)($r['GastosComCartao'] ?? 0);
            $r['SaldoAposPagamento'] = round($saldo - $gastosCartao, 2);
            $r['Ativo'] = (int)($r['Ativo'] ?? 1);
        }
        unset($r);

        return $rows;
    }
}
