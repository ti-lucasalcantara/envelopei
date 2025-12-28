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

        $row = $db->table('tb_itens_envelope ie')
            ->select('COALESCE(SUM(ie.Valor), 0) as Total')
            ->where('ie.EnvelopeId', $envelopeId)
            ->get()
            ->getRowArray();

        return (float)($row['Total'] ?? 0);
    }

    public function saldosPorUsuario(int $usuarioId): array
    {
        $db = db_connect();

        return $db->table('tb_envelopes e')
            ->select('e.EnvelopeId, e.Nome, e.Cor, e.Ordem, COALESCE(SUM(ie.Valor), 0) as Saldo')
            ->join('tb_itens_envelope ie', 'ie.EnvelopeId = e.EnvelopeId', 'left')
            ->where('e.UsuarioId', $usuarioId)
            ->where('e.Ativo', 1)
            ->groupBy('e.EnvelopeId')
            ->orderBy('e.Ordem', 'ASC')
            ->orderBy('e.Nome', 'ASC')
            ->get()
            ->getResultArray();
    }
}
