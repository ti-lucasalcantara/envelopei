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

}
