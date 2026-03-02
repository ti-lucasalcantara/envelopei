<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\EnvelopeModel;
use App\Models\Envelopei\ContaModel;
use App\Models\Envelopei\CartaoCreditoModel;
use App\Models\Envelopei\FaturaModel;

class DashboardController extends BaseApiController
{
    public function resumo()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $mes = (int)$this->request->getGet('mes') ?: (int)($p['mes'] ?? 0);
        $ano = (int)$this->request->getGet('ano') ?: (int)($p['ano'] ?? 0);
        $dataFim = null;
        $dataInicio = null;
        $dataFimMes = null;
        if ($mes >= 1 && $mes <= 12 && $ano >= 2000 && $ano <= 2100) {
            $dataInicio = "{$ano}-{$mes}-01";
            $dataFim = date('Y-m-t', strtotime($dataInicio));
            $dataFimMes = $dataFim;
        } else {
            // "Todo o período": saldo sem filtro; Receitas/Despesas do mês = mês atual
            $dataInicio = date('Y-m-01');
            $dataFimMes = date('Y-m-t');
        }

        $envModel   = new EnvelopeModel();
        $contaModel = new ContaModel();
        $cartaoModel = new CartaoCreditoModel();
        $faturaModel = new FaturaModel();

        $envelopes = $envModel->saldosPorUsuario($uid, $dataFim, $dataInicio, $dataFimMes);

        $totalEnvelopes = 0.0;
        foreach ($envelopes as $e) {
            $totalEnvelopes += (float)$e['Saldo'];
        }
        $totalEnvelopes = round($totalEnvelopes, 2);

        $contas = $contaModel->listarAtivas($uid);
        $totalContas = 0.0;

        foreach ($contas as &$c) {
            $c['SaldoAtual'] = $contaModel->saldoAtual((int)$c['ContaId'], $dataFim);
            $totalContas += (float)$c['SaldoAtual'];
        }
        unset($c);

        $totalContas = round($totalContas, 2);

        $cartoes = $cartaoModel->listarAtivos($uid);
        $faturasProximas = $faturaModel->proximasAVencer($uid, 10);

        $mesFaturas = $mes >= 1 && $mes <= 12 && $ano >= 2000 && $ano <= 2100 ? $mes : (int)date('n');
        $anoFaturas = $mes >= 1 && $mes <= 12 && $ano >= 2000 && $ano <= 2100 ? $ano : (int)date('Y');
        $faturasDoMes = $faturaModel->totalFaturasDoMes($uid, $mesFaturas, $anoFaturas);

        return $this->ok([
            'Envelopes'       => $envelopes,
            'Contas'          => $contas,
            'CartoesCredito'  => $cartoes,
            'FaturasProximas' => $faturasProximas,
            'Totais'          => [
                'TotalEnvelopes'  => $totalEnvelopes,
                'TotalContas'     => $totalContas,
                'FaturasDoMes'   => round($faturasDoMes, 2),
            ],
            'FiltroPeriodo'   => $dataFim ? ['mes' => $mes, 'ano' => $ano, 'dataFim' => $dataFim] : null,
        ]);
    }
}
