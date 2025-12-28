<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\EnvelopeModel;
use App\Models\Envelopei\ContaModel;

class DashboardController extends BaseApiController
{
    public function resumo()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $envModel   = new EnvelopeModel();
        $contaModel = new ContaModel();

        $envelopes = $envModel->saldosPorUsuario($uid);

        $totalEnvelopes = 0.0;
        foreach ($envelopes as $e) {
            $totalEnvelopes += (float)$e['Saldo'];
        }
        $totalEnvelopes = round($totalEnvelopes, 2);

        $contas = $contaModel->listarAtivas($uid);
        $totalContas = 0.0;

        foreach ($contas as &$c) {
            $c['SaldoAtual'] = $contaModel->saldoAtual((int)$c['ContaId']);
            $totalContas += (float)$c['SaldoAtual'];
        }
        unset($c);

        $totalContas = round($totalContas, 2);

        return $this->ok([
            'Envelopes' => $envelopes,
            'Contas'    => $contas,
            'Totais'    => [
                'TotalEnvelopes' => $totalEnvelopes,
                'TotalContas'    => $totalContas,
                'Diferenca'      => round($totalContas - $totalEnvelopes, 2),
            ],
        ]);
    }
}
