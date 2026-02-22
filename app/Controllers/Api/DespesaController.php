<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\LancamentoModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;
use App\Models\Envelopei\CartaoCreditoModel;
use App\Models\Envelopei\FaturaModel;

class DespesaController extends BaseApiController
{
    public function store()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $contaId     = (int)($p['ContaId'] ?? 0);
        $cartaoId    = (int)($p['CartaoCreditoId'] ?? 0);
        $envelopeId  = (int)($p['EnvelopeId'] ?? 0);
        $valor       = (float)($p['Valor'] ?? 0);

        if ($envelopeId <= 0) return $this->fail('EnvelopeId é obrigatório.', [], 422);
        if ($valor <= 0) return $this->fail('Valor deve ser maior que zero.', [], 422);

        $valor = round($valor, 2);

        $ehCartao = $cartaoId > 0;

        if ($ehCartao) {
            $cartaoModel = new CartaoCreditoModel();
            $cartao = $cartaoModel->find($cartaoId);
            if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
                return $this->fail('Cartão não encontrado.', [], 404);
            }
            $diaFechamento = (int)($cartao['DiaFechamento'] ?? 10);
            $diaVencimento = (int)($cartao['DiaVencimento'] ?? 17);
        } else {
            if ($contaId <= 0) return $this->fail('ContaId ou CartaoCreditoId é obrigatório.', [], 422);
        }

        $db = db_connect();
        $db->transStart();

        $dataLancamento = $this->normalizeDate($p['DataLancamento'] ?? null);

        $lancamentoData = [
            'UsuarioId'      => $uid,
            'CategoriaId'    => !empty($p['CategoriaId']) ? (int)$p['CategoriaId'] : null,
            'CartaoCreditoId' => $ehCartao ? $cartaoId : null,
            'FaturaId'       => null,
            'TipoLancamento' => 'despesa',
            'Descricao'      => $p['Descricao'] ?? null,
            'DataLancamento' => $dataLancamento,
        ];

        $faturaId = null;
        if ($ehCartao) {
            $ref = FaturaModel::mesAnoParaDespesa($dataLancamento, $diaFechamento);
            $faturaModel = new FaturaModel();
            $fatura = $faturaModel->obterOuCriar($cartaoId, $ref['Mes'], $ref['Ano'], $diaVencimento);
            $faturaId = (int)$fatura['FaturaId'];
            $lancamentoData['FaturaId'] = $faturaId;
        }

        $lancamentoId = (new LancamentoModel())->insert($lancamentoData);

        if (!$ehCartao) {
            (new ItemContaModel())->insert([
                'LancamentoId' => (int)$lancamentoId,
                'ContaId'      => $contaId,
                'Valor'        => -$valor,
            ]);
        }

        $itemEnvelope = [
            'LancamentoId' => (int)$lancamentoId,
            'EnvelopeId'   => $envelopeId,
            'Valor'        => -$valor,
        ];
        if ($faturaId) {
            $itemEnvelope['FaturaId'] = $faturaId;
        }

        (new ItemEnvelopeModel())->insert($itemEnvelope);

        if ($ehCartao && $faturaId) {
            $faturaModel = new FaturaModel();
            $faturaModel->recalcularValorTotal($faturaId);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao salvar despesa.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)$lancamentoId], 'Despesa registrada', 201);
    }
}
