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

    public function saldosPorUsuario(int $usuarioId): array
    {
        $db = db_connect();

        $sql = "
            SELECT e.EnvelopeId, e.Nome, e.Cor, e.Ordem,
                   COALESCE(SUM(CASE WHEN ie.FaturaId IS NULL THEN ie.Valor WHEN ie.FaturaId IS NOT NULL AND (COALESCE(ie.ValorPago, 0) > 0 OR f.Pago = 1) THEN -COALESCE(ie.ValorPago, 0) ELSE 0 END), 0) as Saldo,
                   COALESCE(SUM(CASE WHEN ie.FaturaId IS NOT NULL AND COALESCE(f.Pago, 0) = 0 THEN GREATEST(0, ABS(ie.Valor) - COALESCE(ie.ValorPago, 0)) ELSE 0 END), 0) as GastosComCartao
            FROM tb_envelopes e
            LEFT JOIN tb_itens_envelope ie ON ie.EnvelopeId = e.EnvelopeId
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
