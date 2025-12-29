<?php

namespace App\Models\Envelopei;

use CodeIgniter\Model;

class RateioModeloItemModel extends Model
{
    protected $table            = 'tb_rateios_modelo_itens';
    protected $primaryKey       = 'RateioModeloItemId';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'RateioModeloId',
        'EnvelopeId',
        'ModoRateio',
        'Valor',
        'Ordem',
    ];

    protected $useTimestamps    = false;

    // ---------------------------------------------
    // Helpers
    // ---------------------------------------------

    public function listarPorModelo(int $rateioModeloId): array
    {
        return $this->where('RateioModeloId', $rateioModeloId)
            ->orderBy('Ordem', 'ASC')
            ->findAll();
    }

    public function substituirItens(int $rateioModeloId, array $itens): void
    {
        // Remove existentes
        $this->where('RateioModeloId', $rateioModeloId)->delete();

        // Insere novos
        foreach ($itens as $idx => $i) {
            $this->insert([
                'RateioModeloId' => $rateioModeloId,
                'EnvelopeId'     => (int)($i['EnvelopeId'] ?? 0),
                'ModoRateio'     => (string)($i['ModoRateio'] ?? 'percentual'),
                'Valor'          => (float)($i['Valor'] ?? 0),
                'Ordem'          => (int)($i['Ordem'] ?? ($idx + 1)),
            ]);
        }
    }
}
