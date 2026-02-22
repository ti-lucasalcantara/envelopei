<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\FaturaModel;
use App\Models\Envelopei\LancamentoModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;
use App\Models\Envelopei\PagamentoItemModel;

class FaturaController extends BaseApiController
{
    public function index()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new FaturaModel();
        $pendentesParam = $this->request->getGet('Pendentes') ?? $p['Pendentes'] ?? null;
        $apenasPendentes = $pendentesParam !== null ? filter_var($pendentesParam, FILTER_VALIDATE_BOOLEAN) : null;
        $lista = $model->listarPorUsuario($uid, $apenasPendentes);

        return $this->ok($lista);
    }

    public function show($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new FaturaModel();
        $fatura = $model->find((int)$id);

        if (!$fatura) return $this->fail('Fatura não encontrada.', [], 404);

        $db = db_connect();
        $cartao = $db->table('tb_cartoes_credito')->where('CartaoCreditoId', $fatura['CartaoCreditoId'])->get()->getRowArray();
        if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
            return $this->fail('Fatura não encontrada.', [], 404);
        }

        $fatura['CartaoNome'] = $cartao['Nome'] ?? '';
        $fatura['Ultimos4Digitos'] = $cartao['Ultimos4Digitos'] ?? '';
        $fatura['Cor'] = $cartao['Cor'] ?? '';
        $fatura['Lancamentos'] = $model->lancamentosDaFatura((int)$id);
        $fatura['ValorPago'] = $model->valorPagoFatura((int)$id);
        $fatura['ValorRestante'] = max(0, (float)($fatura['ValorTotal'] ?? 0) - (float)($fatura['ValorPago'] ?? 0));

        return $this->ok($fatura);
    }

    public function proximasAVencer()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new FaturaModel();
        $limiteParam = $this->request->getGet('Limite') ?? $p['Limite'] ?? 5;
        $limite = max(1, min(20, (int)$limiteParam));
        $lista = $model->proximasAVencer($uid, $limite);

        return $this->ok($lista);
    }

    public function pagar($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $contaId = (int)($p['ContaId'] ?? 0);
        if ($contaId <= 0) return $this->fail('ContaId é obrigatório para marcar fatura como paga.', [], 422);

        $model = new FaturaModel();
        $fatura = $model->find((int)$id);

        if (!$fatura) return $this->fail('Fatura não encontrada.', [], 404);

        $db = db_connect();
        $cartao = $db->table('tb_cartoes_credito')->where('CartaoCreditoId', $fatura['CartaoCreditoId'])->get()->getRowArray();
        if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
            return $this->fail('Fatura não encontrada.', [], 404);
        }

        if ((int)$fatura['Pago'] === 1) {
            return $this->fail('Esta fatura já está paga.', [], 422);
        }

        $valorTotal = (float)($fatura['ValorTotal'] ?? 0);
        if ($valorTotal <= 0) {
            return $this->fail('Fatura com valor zero não pode ser paga.', [], 422);
        }

        $dataPagamento = $this->normalizeDate($p['DataPagamento'] ?? null);

        $db->transStart();

        $itens = $db->table('tb_itens_envelope')
            ->select('ItemEnvelopeId, Valor, ValorPago')
            ->where('FaturaId', (int)$id)
            ->get()
            ->getResultArray();

        $valorRestante = 0.0;
        $pagamentosCriar = [];
        foreach ($itens as $item) {
            $valorItem = abs((float)($item['Valor'] ?? 0));
            $valorPago = (float)($item['ValorPago'] ?? 0);
            $restante = $valorItem - $valorPago;
            if ($restante > 0) {
                $valorRestante += $restante;
                $pagamentosCriar[] = [
                    'ItemEnvelopeId' => $item['ItemEnvelopeId'],
                    'Valor' => $restante,
                ];
            }
        }

        $model->update((int)$id, [
            'Pago'             => 1,
            'DataPagamento'    => $dataPagamento,
            'ContaIdPagamento' => $contaId,
        ]);

        if ($valorRestante > 0) {
            $descricao = sprintf('Pagamento fatura %s ****%s - %02d/%d',
                $cartao['Nome'] ?? 'Cartão',
                $cartao['Ultimos4Digitos'] ?? '????',
                (int)$fatura['MesReferencia'],
                (int)$fatura['AnoReferencia']
            );

            $lancamentoId = (new LancamentoModel())->insert([
                'UsuarioId'       => $uid,
                'CategoriaId'     => null,
                'FaturaId'        => (int)$id,
                'TipoLancamento'  => 'pgto_fatura',
                'Descricao'       => $descricao,
                'DataLancamento'  => $dataPagamento,
            ]);

            (new ItemContaModel())->insert([
                'LancamentoId' => (int)$lancamentoId,
                'ContaId'      => $contaId,
                'Valor'        => -$valorRestante,
            ]);

            $ieModel = new ItemEnvelopeModel();
            $pagItemModel = new PagamentoItemModel();
            foreach ($pagamentosCriar as $pc) {
                $ie = $ieModel->find($pc['ItemEnvelopeId']);
                $novoValorPago = (float)($ie['ValorPago'] ?? 0) + $pc['Valor'];
                $ieModel->update($pc['ItemEnvelopeId'], ['ValorPago' => $novoValorPago]);
                $pagItemModel->insert([
                    'ItemEnvelopeId'   => $pc['ItemEnvelopeId'],
                    'Valor'            => $pc['Valor'],
                    'Descricao'        => $descricao,
                    'DataPagamento'    => $dataPagamento,
                    'ContaIdPagamento' => $contaId,
                    'LancamentoId'     => $lancamentoId,
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao registrar pagamento.', [], 500);
        }

        return $this->ok([], 'Fatura marcada como paga');
    }

    /**
     * Desfaz o pagamento de uma fatura (volta ao estado pendente).
     */
    public function desfazerPagar($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new FaturaModel();
        $fatura = $model->find((int)$id);

        if (!$fatura) return $this->fail('Fatura não encontrada.', [], 404);

        $db = db_connect();
        $cartao = $db->table('tb_cartoes_credito')->where('CartaoCreditoId', $fatura['CartaoCreditoId'])->get()->getRowArray();
        if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
            return $this->fail('Fatura não encontrada.', [], 404);
        }

        if ((int)$fatura['Pago'] !== 1) {
            return $this->fail('Esta fatura não está paga.', [], 422);
        }

        $valorTotal = (float)($fatura['ValorTotal'] ?? 0);

        $db->transStart();

        $model->update((int)$id, [
            'Pago'             => 0,
            'DataPagamento'    => null,
            'ContaIdPagamento' => null,
        ]);

        $db->table('tb_itens_envelope')
            ->where('FaturaId', (int)$id)
            ->update([
                'Pago'             => 0,
                'DataPagamento'    => null,
                'ContaIdPagamento' => null,
                'ValorPago'        => 0,
            ]);

        $itensIds = $db->table('tb_itens_envelope')->select('ItemEnvelopeId')->where('FaturaId', (int)$id)->get()->getResultArray();
        foreach ($itensIds as $row) {
            $db->table('tb_pagamentos_item')->where('ItemEnvelopeId', $row['ItemEnvelopeId'])->delete();
        }

        $pgtoLancamentos = $db->table('tb_lancamentos')
            ->where('FaturaId', (int)$id)
            ->whereIn('TipoLancamento', ['pgto_fatura', 'pgto_parcial'])
            ->get()
            ->getResultArray();

        foreach ($pgtoLancamentos as $pgto) {
            $db->table('tb_itens_conta')->where('LancamentoId', $pgto['LancamentoId'])->delete();
            $db->table('tb_lancamentos')->where('LancamentoId', $pgto['LancamentoId'])->delete();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao desfazer pagamento.', [], 500);
        }

        return $this->ok([], 'Pagamento desfeito');
    }

    /**
     * Pagamento parcial ou total de um item da fatura.
     * Params: ContaId, Valor, Descricao (opcional), DataPagamento
     */
    public function pagarItem($itemEnvelopeId)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $contaId = (int)($p['ContaId'] ?? 0);
        if ($contaId <= 0) return $this->fail('ContaId é obrigatório.', [], 422);

        $valorPagar = (float)($p['Valor'] ?? 0);
        if ($valorPagar <= 0) return $this->fail('Valor deve ser maior que zero.', [], 422);

        $descricao = trim($p['Descricao'] ?? '') ?: null;

        $ieModel = new ItemEnvelopeModel();
        $item = $ieModel->find((int)$itemEnvelopeId);

        if (!$item || empty($item['FaturaId'])) return $this->fail('Item não encontrado.', [], 404);

        $fatura = (new FaturaModel())->find((int)$item['FaturaId']);
        if (!$fatura) return $this->fail('Fatura não encontrada.', [], 404);

        $db = db_connect();
        $cartao = $db->table('tb_cartoes_credito')->where('CartaoCreditoId', $fatura['CartaoCreditoId'])->get()->getRowArray();
        if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
            return $this->fail('Item não encontrado.', [], 404);
        }

        $valorTotalItem = abs((float)($item['Valor'] ?? 0));
        $valorPagoItem = (float)($item['ValorPago'] ?? 0);
        $valorRestante = $valorTotalItem - $valorPagoItem;

        if ($valorRestante <= 0) return $this->fail('Este item já está pago.', [], 422);
        if ($valorPagar > $valorRestante) return $this->fail("Valor informado (R$ " . number_format($valorPagar, 2, ',', '.') . ") excede o restante (R$ " . number_format($valorRestante, 2, ',', '.') . ").", [], 422);

        $dataPagamento = $this->normalizeDate($p['DataPagamento'] ?? null);

        $descLanc = $db->table('tb_lancamentos')->select('Descricao')->where('LancamentoId', $item['LancamentoId'])->get()->getRowArray();
        $descricaoFinal = mb_substr(($descricao ?: ($descLanc['Descricao'] ?? 'Despesa')) . ' - pgto. parc.', 0, 190);

        $db->transStart();
        $erro = null;

        try {
            $novoValorPago = $valorPagoItem + $valorPagar;
            $ieModel->update((int)$itemEnvelopeId, [
                'Pago'      => ($novoValorPago >= $valorTotalItem) ? 1 : 0,
                'ValorPago' => $novoValorPago,
            ]);

            $lancamentoId = (new LancamentoModel())->insert([
                'UsuarioId'       => $uid,
                'CategoriaId'     => null,
                'FaturaId'        => (int)$item['FaturaId'],
                'TipoLancamento'  => 'pgto_parcial',
                'Descricao'       => $descricaoFinal,
                'DataLancamento'  => $dataPagamento,
            ]);

            if ($lancamentoId === false || (int)$lancamentoId <= 0) {
                $erro = 'Falha ao criar lançamento de pagamento.';
                throw new \RuntimeException($erro);
            }

            $lancamentoId = (int)$lancamentoId;

            (new ItemContaModel())->insert([
                'LancamentoId' => $lancamentoId,
                'ContaId'      => $contaId,
                'Valor'        => -$valorPagar,
            ]);

            (new PagamentoItemModel())->insert([
                'ItemEnvelopeId'   => (int)$itemEnvelopeId,
                'Valor'            => $valorPagar,
                'Descricao'        => $descricao !== null ? mb_substr($descricao, 0, 190) : null,
                'DataPagamento'    => $dataPagamento,
                'ContaIdPagamento' => $contaId,
                'LancamentoId'     => $lancamentoId,
            ]);
        } catch (\Throwable $e) {
            $erro = $erro ?? $e->getMessage();
            log_message('error', 'FaturaController::pagarItem - ' . $erro);
        }

        if ($erro !== null) {
            if ($db->transStatus()) {
                $db->transRollback();
            }
            $db->transComplete();
            return $this->fail('Falha ao registrar pagamento.', [], 500);
        }

        $valorPagoFatura = (new FaturaModel())->valorPagoFatura((int)$item['FaturaId']);
        $valorTotalFatura = (float)($fatura['ValorTotal'] ?? 0);
        if ($valorPagoFatura >= $valorTotalFatura) {
            $model = new FaturaModel();
            $model->update((int)$item['FaturaId'], [
                'Pago'             => 1,
                'DataPagamento'    => $dataPagamento,
                'ContaIdPagamento' => $contaId,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao registrar pagamento.', [], 500);
        }

        return $this->ok([], 'Pagamento registrado');
    }

    /**
     * Desfaz um pagamento individual de item (dinheiro volta para a conta).
     */
    public function desfazerItemPagamento($pagamentoItemId)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $pagItemModel = new PagamentoItemModel();
        $pag = $pagItemModel->find((int)$pagamentoItemId);

        if (!$pag) return $this->fail('Pagamento não encontrado.', [], 404);

        $ieModel = new ItemEnvelopeModel();
        $item = $ieModel->find((int)$pag['ItemEnvelopeId']);
        if (!$item || empty($item['FaturaId'])) return $this->fail('Item não encontrado.', [], 404);

        $fatura = (new FaturaModel())->find((int)$item['FaturaId']);
        $db = db_connect();
        $cartao = $db->table('tb_cartoes_credito')->where('CartaoCreditoId', $fatura['CartaoCreditoId'])->get()->getRowArray();
        if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
            return $this->fail('Pagamento não encontrado.', [], 404);
        }

        $valor = (float)($pag['Valor'] ?? 0);
        $lancamentoId = (int)($pag['LancamentoId'] ?? 0);

        $db->transStart();

        // Excluir pagamento_item antes do lançamento (FK de tb_pagamentos_item -> tb_lancamentos)
        $pagItemModel->delete((int)$pagamentoItemId);

        $db->table('tb_itens_conta')->where('LancamentoId', $lancamentoId)->delete();
        $db->table('tb_lancamentos')->where('LancamentoId', $lancamentoId)->delete();

        $novoValorPago = (float)($item['ValorPago'] ?? 0) - $valor;
        $ieModel->update((int)$pag['ItemEnvelopeId'], [
            'ValorPago' => max(0, $novoValorPago),
            'Pago'      => 0,
        ]);

        $faturaModel = new FaturaModel();
        $faturaModel->update((int)$item['FaturaId'], [
            'Pago'             => 0,
            'DataPagamento'    => null,
            'ContaIdPagamento' => null,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao desfazer pagamento.', [], 500);
        }

        return $this->ok([], 'Pagamento desfeito');
    }
}
