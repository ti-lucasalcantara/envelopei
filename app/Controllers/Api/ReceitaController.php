<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\LancamentoModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;
use App\Models\Envelopei\RateioReceitaModel;

class ReceitaController extends BaseApiController
{
    public function store()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $contaId   = (int)($p['ContaId'] ?? 0);
        $valorTotal = (float)($p['ValorTotal'] ?? 0);
        $rateios   = $p['Rateios'] ?? [];

        if ($contaId <= 0) return $this->fail('ContaId é obrigatório.', [], 422);
        if ($valorTotal <= 0) return $this->fail('ValorTotal deve ser maior que zero.', [], 422);
        if (!is_array($rateios) || count($rateios) === 0) return $this->fail('Informe Rateios.', [], 422);

        // calcula rateios
        $itensCalculados = [];
        $soma = 0.0;

        foreach ($rateios as $r) {
            $envelopeId = (int)($r['EnvelopeId'] ?? 0);
            $modo       = (string)($r['ModoRateio'] ?? 'valor'); // valor | percentual
            $informado  = (float)($r['ValorInformado'] ?? 0);

            if ($envelopeId <= 0) return $this->fail('Rateio inválido: EnvelopeId.', $r, 422);
            if ($informado <= 0) return $this->fail('Rateio inválido: ValorInformado.', $r, 422);

            $calculado = 0.0;
            if ($modo === 'percentual') {
                $calculado = round($valorTotal * ($informado / 100), 2);
            } else {
                $calculado = round($informado, 2);
                $modo = 'valor';
            }

            $soma += $calculado;

            $itensCalculados[] = [
                'EnvelopeId'      => $envelopeId,
                'ModoRateio'      => $modo,
                'ValorInformado'  => $informado,
                'ValorCalculado'  => $calculado,
            ];
        }

        // valida soma
        $soma = round($soma, 2);
        $valorTotal = round($valorTotal, 2);

        if ($soma !== $valorTotal) {
            return $this->fail(
                'A soma do rateio não bate com o ValorTotal.',
                ['SomaRateio' => $soma, 'ValorTotal' => $valorTotal],
                422
            );
        }

        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId'      => $uid,
            'CategoriaId'    => !empty($p['CategoriaId']) ? (int)$p['CategoriaId'] : null,
            'TipoLancamento' => 'receita',
            'Descricao'      => $p['Descricao'] ?? null,
            'DataLancamento' => $this->normalizeDate($p['DataLancamento'] ?? null),
        ]);

        // movimento na conta (+)
        (new ItemContaModel())->insert([
            'LancamentoId' => (int)$lancamentoId,
            'ContaId'      => $contaId,
            'Valor'        => $valorTotal,
        ]);

        $itemEnvModel = new ItemEnvelopeModel();
        $rateioModel  = new RateioReceitaModel();

        foreach ($itensCalculados as $ic) {
            // movimento no envelope (+)
            $itemEnvModel->insert([
                'LancamentoId' => (int)$lancamentoId,
                'EnvelopeId'   => (int)$ic['EnvelopeId'],
                'Valor'        => (float)$ic['ValorCalculado'],
            ]);

            // guarda memória do rateio
            $rateioModel->insert([
                'LancamentoId'   => (int)$lancamentoId,
                'EnvelopeId'     => (int)$ic['EnvelopeId'],
                'ModoRateio'     => $ic['ModoRateio'],
                'ValorInformado' => (float)$ic['ValorInformado'],
                'ValorCalculado' => (float)$ic['ValorCalculado'],
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao salvar receita.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)$lancamentoId], 'Receita registrada', 201);
    }
}
