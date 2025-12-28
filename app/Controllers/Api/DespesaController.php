<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\LancamentoModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;

class DespesaController extends BaseApiController
{
    public function store()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $contaId   = (int)($p['ContaId'] ?? 0);
        $envelopeId = (int)($p['EnvelopeId'] ?? 0);
        $valor     = (float)($p['Valor'] ?? 0);

        if ($contaId <= 0) return $this->fail('ContaId é obrigatório.', [], 422);
        if ($envelopeId <= 0) return $this->fail('EnvelopeId é obrigatório.', [], 422);
        if ($valor <= 0) return $this->fail('Valor deve ser maior que zero.', [], 422);

        $valor = round($valor, 2);

        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId'      => $uid,
            'CategoriaId'    => !empty($p['CategoriaId']) ? (int)$p['CategoriaId'] : null,
            'TipoLancamento' => 'despesa',
            'Descricao'      => $p['Descricao'] ?? null,
            'DataLancamento' => $this->normalizeDate($p['DataLancamento'] ?? null),
        ]);

        // conta (-)
        (new ItemContaModel())->insert([
            'LancamentoId' => (int)$lancamentoId,
            'ContaId'      => $contaId,
            'Valor'        => -$valor,
        ]);

        // envelope (-)
        (new ItemEnvelopeModel())->insert([
            'LancamentoId' => (int)$lancamentoId,
            'EnvelopeId'   => $envelopeId,
            'Valor'        => -$valor,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao salvar despesa.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)$lancamentoId], 'Despesa registrada', 201);
    }
}
