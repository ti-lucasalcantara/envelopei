<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\LancamentoModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;

class TransferenciaController extends BaseApiController
{
    public function entreEnvelopes()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $origemId  = (int)($p['EnvelopeOrigemId'] ?? 0);
        $destinoId = (int)($p['EnvelopeDestinoId'] ?? 0);
        $valor     = (float)($p['Valor'] ?? 0);

        if ($origemId <= 0 || $destinoId <= 0) return $this->fail('EnvelopeOrigemId e EnvelopeDestinoId são obrigatórios.', [], 422);
        if ($origemId === $destinoId) return $this->fail('Origem e destino não podem ser iguais.', [], 422);
        if ($valor <= 0) return $this->fail('Valor deve ser maior que zero.', [], 422);

        $valor = round($valor, 2);

        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId'      => $uid,
            'CategoriaId'    => null,
            'TipoLancamento' => 'transferencia',
            'Descricao'      => $p['Descricao'] ?? 'Transferência entre envelopes',
            'DataLancamento' => $this->normalizeDate($p['DataLancamento'] ?? null),
        ]);

        $itemEnvModel = new ItemEnvelopeModel();

        $itemEnvModel->insert([
            'LancamentoId' => (int)$lancamentoId,
            'EnvelopeId'   => $origemId,
            'Valor'        => -$valor,
        ]);

        $itemEnvModel->insert([
            'LancamentoId' => (int)$lancamentoId,
            'EnvelopeId'   => $destinoId,
            'Valor'        => $valor,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao salvar transferência.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)$lancamentoId], 'Transferência realizada', 201);
    }

    public function entreContas()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $origemId  = (int)($p['ContaOrigemId'] ?? 0);
        $destinoId = (int)($p['ContaDestinoId'] ?? 0);
        $valor     = (float)($p['Valor'] ?? 0);

        if ($origemId <= 0 || $destinoId <= 0) return $this->fail('ContaOrigemId e ContaDestinoId são obrigatórios.', [], 422);
        if ($origemId === $destinoId) return $this->fail('Origem e destino não podem ser iguais.', [], 422);
        if ($valor <= 0) return $this->fail('Valor deve ser maior que zero.', [], 422);

        $valor = round($valor, 2);

        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId'      => $uid,
            'CategoriaId'    => null,
            'TipoLancamento' => 'transferencia',
            'Descricao'      => $p['Descricao'] ?? 'Transferência entre contas',
            'DataLancamento' => $this->normalizeDate($p['DataLancamento'] ?? null),
        ]);

        $itemContaModel = new ItemContaModel();

        $itemContaModel->insert([
            'LancamentoId' => (int)$lancamentoId,
            'ContaId'      => $origemId,
            'Valor'        => -$valor,
        ]);

        $itemContaModel->insert([
            'LancamentoId' => (int)$lancamentoId,
            'ContaId'      => $destinoId,
            'Valor'        => $valor,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao salvar transferência.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)$lancamentoId], 'Transferência realizada', 201);
    }
}
