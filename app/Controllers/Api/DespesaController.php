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
        $parcelas = $ehCartao ? max(1, min(24, (int)($p['Parcelas'] ?? 1))) : 1;
        $valorParcela = $ehCartao && $parcelas > 1 ? round($valor / $parcelas, 2) : $valor;

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
        $descricao = $p['Descricao'] ?? null;

        $lancamentoModel = new LancamentoModel();
        $itemEnvelopeModel = new ItemEnvelopeModel();
        $faturaModel = new FaturaModel();
        $faturasRecalc = [];
        $primeiroLancamentoId = null;

        if ($ehCartao && $parcelas > 1) {
            $ref = FaturaModel::mesAnoParaDespesa($dataLancamento, $diaFechamento);
            for ($i = 1; $i <= $parcelas; $i++) {
                $mesAno = FaturaModel::mesAnoParaParcela($ref['Mes'], $ref['Ano'], $i);
                $fatura = $faturaModel->obterOuCriar($cartaoId, $mesAno['Mes'], $mesAno['Ano'], $diaVencimento);
                $faturaId = (int)$fatura['FaturaId'];
                $faturasRecalc[$faturaId] = true;

                // Data da parcela = dia 1 do mês correspondente (1/4 = 01/mar, 2/4 = 01/abr, etc.)
                $dataParcela = sprintf('%04d-%02d-01', $mesAno['Ano'], $mesAno['Mes']);

                $descParcela = $parcelas > 1 ? trim(($descricao ?? '') . ' (' . $i . '/' . $parcelas . ')') : $descricao;
                $lid = $lancamentoModel->insert([
                    'UsuarioId'       => $uid,
                    'CategoriaId'     => !empty($p['CategoriaId']) ? (int)$p['CategoriaId'] : null,
                    'CartaoCreditoId' => $cartaoId,
                    'FaturaId'        => $faturaId,
                    'TipoLancamento'  => 'despesa',
                    'Descricao'       => $descParcela ?: null,
                    'DataLancamento'  => $dataParcela,
                ]);

                $itemEnvelopeModel->insert([
                    'LancamentoId' => (int)$lid,
                    'EnvelopeId'   => $envelopeId,
                    'FaturaId'     => $faturaId,
                    'Valor'        => -$valorParcela,
                ]);
                if ($primeiroLancamentoId === null) $primeiroLancamentoId = (int)$lid;
            }
        } else {
            $lancamentoData = [
                'UsuarioId'       => $uid,
                'CategoriaId'     => !empty($p['CategoriaId']) ? (int)$p['CategoriaId'] : null,
                'CartaoCreditoId'  => $ehCartao ? $cartaoId : null,
                'FaturaId'        => null,
                'TipoLancamento'  => 'despesa',
                'Descricao'       => $descricao,
                'DataLancamento'  => $dataLancamento,
            ];

            $faturaId = null;
            if ($ehCartao) {
                $faturaInformada = !empty($p['FaturaId']) ? (int)$p['FaturaId'] : null;
                if ($faturaInformada > 0) {
                    $fatura = $faturaModel->find($faturaInformada);
                    if ($fatura && (int)$fatura['CartaoCreditoId'] === $cartaoId) {
                        $faturaId = (int)$fatura['FaturaId'];
                    }
                }
                if ($faturaId === null) {
                    $ref = FaturaModel::mesAnoParaDespesa($dataLancamento, $diaFechamento);
                    $fatura = $faturaModel->obterOuCriar($cartaoId, $ref['Mes'], $ref['Ano'], $diaVencimento);
                    $faturaId = (int)$fatura['FaturaId'];
                }
                $lancamentoData['FaturaId'] = $faturaId;
            }

            $lancamentoId = $lancamentoModel->insert($lancamentoData);

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
                $faturasRecalc[$faturaId] = true;
            }

            $itemEnvelopeModel->insert($itemEnvelope);
        }

        foreach (array_keys($faturasRecalc) as $fid) {
            $faturaModel->recalcularValorTotal($fid);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao salvar despesa.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)($primeiroLancamentoId ?? $lancamentoId ?? 0)], 'Despesa registrada', 201);
    }
}
