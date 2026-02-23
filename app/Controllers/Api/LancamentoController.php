<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\LancamentoModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;
use App\Models\Envelopei\RateioReceitaModel;
use App\Models\Envelopei\FaturaModel;

class LancamentoController extends BaseApiController
{
    public function index()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $inicio = $this->request->getGet('inicio');
        $fim    = $this->request->getGet('fim');
        $tipo   = $this->request->getGet('tipo');

        $model = new LancamentoModel();

        $builder = $model->where('UsuarioId', $uid);

        if (!empty($inicio)) $builder->where('DataLancamento >=', $inicio);
        if (!empty($fim))    $builder->where('DataLancamento <=', $fim);
        if (!empty($tipo))   $builder->where('TipoLancamento', $tipo);

        $lista = $builder->orderBy('DataLancamento', 'DESC')
                         ->orderBy('LancamentoId', 'DESC')
                         ->findAll();

        return $this->ok($lista);
    }

    public function show($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $lm = new LancamentoModel();
        $l  = $lm->find((int)$id);

        if (!$l || (int)$l['UsuarioId'] !== $uid) {
            return $this->fail('Lançamento não encontrado.', [], 404);
        }

        $itensConta    = (new ItemContaModel())->where('LancamentoId', (int)$id)->findAll();
        $itensEnvelope = (new ItemEnvelopeModel())->where('LancamentoId', (int)$id)->findAll();
        $rateios       = (new RateioReceitaModel())->where('LancamentoId', (int)$id)->findAll();

        return $this->ok([
            'Lancamento'    => $l,
            'ItensConta'    => $itensConta,
            'ItensEnvelope' => $itensEnvelope,
            'Rateios'       => $rateios,
        ]);
    }

    public function delete($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $id = (int)$id;

        $lm = new \App\Models\Envelopei\LancamentoModel();
        $l  = $lm->find($id);

        if (!$l || (int)$l['UsuarioId'] !== $uid) {
            return $this->fail('Lançamento não encontrado.', [], 404);
        }

        $faturaId = !empty($l['FaturaId']) ? (int)$l['FaturaId'] : null;

        $db = db_connect();
        $db->transStart();

        // apaga dependências
        $db->table('tb_itens_conta')->where('LancamentoId', $id)->delete();
        $db->table('tb_itens_envelope')->where('LancamentoId', $id)->delete();
        $db->table('tb_rateios_receita')->where('LancamentoId', $id)->delete();

        // apaga o lançamento
        $db->table('tb_lancamentos')->where('LancamentoId', $id)->delete();

        if ($faturaId) {
            (new FaturaModel())->recalcularValorTotal($faturaId);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao excluir lançamento.', [], 500);
        }

        return $this->ok(['LancamentoId' => $id], 'Lançamento excluído');
    }

    /**
     * Atualiza um lançamento (receita ou despesa). Outros tipos não são editáveis.
     */
    public function update($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $id = (int)$id;
        $lm = new LancamentoModel();
        $l  = $lm->find($id);

        if (!$l || (int)$l['UsuarioId'] !== $uid) {
            return $this->fail('Lançamento não encontrado.', [], 404);
        }

        $tipo = (string)($l['TipoLancamento'] ?? '');
        if ($tipo !== 'receita' && $tipo !== 'despesa') {
            return $this->fail('Este tipo de lançamento não pode ser editado.', [], 422);
        }

        $db = db_connect();
        $db->transStart();

        $dataLancamento = $this->normalizeDate($p['DataLancamento'] ?? $l['DataLancamento']);
        $descricao      = $p['Descricao'] ?? $l['Descricao'];
        $categoriaId    = !empty($p['CategoriaId']) ? (int)$p['CategoriaId'] : null;

        $lm->update($id, [
            'Descricao'      => $descricao,
            'DataLancamento'  => $dataLancamento,
            'CategoriaId'     => $categoriaId,
        ]);

        if ($tipo === 'receita') {
            $contaId    = (int)($p['ContaId'] ?? 0);
            $valorTotal = (float)($p['ValorTotal'] ?? 0);
            if ($contaId <= 0) {
                $db->transComplete();
                return $this->fail('ContaId é obrigatório.', [], 422);
            }
            if ($valorTotal <= 0) {
                $db->transComplete();
                return $this->fail('ValorTotal deve ser maior que zero.', [], 422);
            }
            $valorTotal = round($valorTotal, 2);

            $itensConta = (new ItemContaModel())->where('LancamentoId', $id)->findAll();
            if (count($itensConta) === 1) {
                (new ItemContaModel())->update($itensConta[0]['ItemContaId'], [
                    'ContaId' => $contaId,
                    'Valor'   => $valorTotal,
                ]);
            }

            $rateios = $p['Rateios'] ?? [];
            if (!is_array($rateios) || count($rateios) === 0) {
                $db->transComplete();
                return $this->fail('Informe o rateio (Rateios).', [], 422);
            }

            $itensCalculados = [];
            foreach ($rateios as $r) {
                $envelopeId = (int)($r['EnvelopeId'] ?? 0);
                $modo       = (string)($r['ModoRateio'] ?? 'valor');
                $informado  = (float)($r['ValorInformado'] ?? 0);
                if ($envelopeId <= 0 || $informado <= 0) continue;
                $calculado = $modo === 'percentual' ? round($valorTotal * ($informado / 100), 2) : round($informado, 2);
                $itensCalculados[] = ['EnvelopeId' => $envelopeId, 'ModoRateio' => $modo === 'percentual' ? 'percentual' : 'valor', 'ValorInformado' => $informado, 'ValorCalculado' => $calculado];
            }

            $soma = round(array_sum(array_column($itensCalculados, 'ValorCalculado')), 2);
            if (abs($soma - $valorTotal) > 0.01 && count($itensCalculados) > 0) {
                $diff = round($valorTotal - $soma, 2);
                $last = count($itensCalculados) - 1;
                $itensCalculados[$last]['ValorCalculado'] = round($itensCalculados[$last]['ValorCalculado'] + $diff, 2);
            }

            $db->table('tb_itens_envelope')->where('LancamentoId', $id)->delete();
            $db->table('tb_rateios_receita')->where('LancamentoId', $id)->delete();

            $itemEnvModel = new ItemEnvelopeModel();
            $rateioModel  = new RateioReceitaModel();
            foreach ($itensCalculados as $ic) {
                $itemEnvModel->insert([
                    'LancamentoId' => $id,
                    'EnvelopeId'  => (int)$ic['EnvelopeId'],
                    'Valor'       => (float)$ic['ValorCalculado'],
                ]);
                $rateioModel->insert([
                    'LancamentoId'   => $id,
                    'EnvelopeId'     => (int)$ic['EnvelopeId'],
                    'ModoRateio'     => $ic['ModoRateio'],
                    'ValorInformado' => (float)$ic['ValorInformado'],
                    'ValorCalculado' => (float)$ic['ValorCalculado'],
                ]);
            }
        } else {
            // despesa
            $valor = (float)($p['Valor'] ?? 0);
            if ($valor <= 0) {
                $db->transComplete();
                return $this->fail('Valor deve ser maior que zero.', [], 422);
            }
            $valor = round($valor, 2);
            $envelopeId = (int)($p['EnvelopeId'] ?? 0);
            if ($envelopeId <= 0) {
                $db->transComplete();
                return $this->fail('EnvelopeId é obrigatório.', [], 422);
            }

            $cartaoId = (int)($l['CartaoCreditoId'] ?? 0);
            $faturaId = null;

            if ($cartaoId > 0) {
                $faturaId = !empty($p['FaturaId']) ? (int)$p['FaturaId'] : (int)($l['FaturaId'] ?? 0);
                if ($faturaId > 0) {
                    $fatura = (new FaturaModel())->find($faturaId);
                    if (!$fatura || (int)$fatura['CartaoCreditoId'] !== $cartaoId) {
                        $faturaId = (int)($l['FaturaId'] ?? 0);
                    }
                }
                $lm->update($id, ['FaturaId' => $faturaId ?: null]);
            } else {
                $contaId = (int)($p['ContaId'] ?? 0);
                if ($contaId <= 0) {
                    $db->transComplete();
                    return $this->fail('ContaId é obrigatório para despesa à vista.', [], 422);
                }
                $itensConta = (new ItemContaModel())->where('LancamentoId', $id)->findAll();
                if (count($itensConta) === 1) {
                    (new ItemContaModel())->update($itensConta[0]['ItemContaId'], [
                        'ContaId' => $contaId,
                        'Valor'   => -$valor,
                    ]);
                }
            }

            $itensEnv = (new ItemEnvelopeModel())->where('LancamentoId', $id)->findAll();
            $faturaAntigaId = null;
            if (count($itensEnv) >= 1) {
                $ie = $itensEnv[0];
                $faturaAntigaId = !empty($ie['FaturaId']) ? (int)$ie['FaturaId'] : null;
                $updateData = ['EnvelopeId' => $envelopeId, 'Valor' => -$valor];
                if ($faturaId !== null) {
                    $updateData['FaturaId'] = $faturaId;
                }
                (new ItemEnvelopeModel())->update($ie['ItemEnvelopeId'], $updateData);
            }

            $faturaModel = new FaturaModel();
            if ($faturaId) {
                $faturaModel->recalcularValorTotal($faturaId);
            }
            if ($faturaAntigaId && $faturaAntigaId !== $faturaId) {
                $faturaModel->recalcularValorTotal($faturaAntigaId);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao atualizar lançamento.', [], 500);
        }

        return $this->ok(['LancamentoId' => $id], 'Lançamento atualizado');
    }

}
