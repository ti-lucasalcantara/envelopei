<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\ContaModel;

class ContaController extends BaseApiController
{
    public function index()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new ContaModel();
        return $this->ok($model->listarAtivas($uid));
    }

    public function show($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new ContaModel();
        $conta = $model->find((int)$id);

        if (!$conta || (int)$conta['UsuarioId'] !== $uid) {
            return $this->fail('Conta não encontrada.', [], 404);
        }

        $conta['SaldoAtual'] = $model->saldoAtual((int)$id);

        return $this->ok($conta);
    }

    public function store()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $nome = trim($p['Nome'] ?? '');
        if ($nome === '') return $this->fail('Nome é obrigatório.', [], 422);

        $data = [
            'UsuarioId'    => $uid,
            'Nome'         => $nome,
            'TipoConta'    => $p['TipoConta'] ?? 'banco',
            'SaldoInicial' => (float)($p['SaldoInicial'] ?? 0),
            'Ativa'        => 1,
        ];

        $model = new ContaModel();
        $id = $model->insert($data);

        return $this->ok(['ContaId' => (int)$id], 'Conta criada', 201);
    }

    public function update($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new ContaModel();
        $conta = $model->find((int)$id);

        if (!$conta || (int)$conta['UsuarioId'] !== $uid) {
            return $this->fail('Conta não encontrada.', [], 404);
        }

        $data = [];
        if (isset($p['Nome']))         $data['Nome'] = trim((string)$p['Nome']);
        if (isset($p['TipoConta']))    $data['TipoConta'] = (string)$p['TipoConta'];
        if (isset($p['SaldoInicial'])) $data['SaldoInicial'] = (float)$p['SaldoInicial'];
        if (isset($p['Ativa']))        $data['Ativa'] = (int)$p['Ativa'];

        if (empty($data)) return $this->ok([], 'Nada para atualizar.');

        $model->update((int)$id, $data);

        return $this->ok([], 'Conta atualizada');
    }

    public function delete($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new ContaModel();
        $conta = $model->find((int)$id);

        if (!$conta || (int)$conta['UsuarioId'] !== $uid) {
            return $this->fail('Conta não encontrada.', [], 404);
        }

        // soft delete
        $model->update((int)$id, ['Ativa' => 0]);

        return $this->ok([], 'Conta desativada');
    }

    public function saldo($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new ContaModel();
        $conta = $model->find((int)$id);

        if (!$conta || (int)$conta['UsuarioId'] !== $uid) {
            return $this->fail('Conta não encontrada.', [], 404);
        }

        return $this->ok([
            'ContaId'    => (int)$id,
            'SaldoAtual' => $model->saldoAtual((int)$id),
        ]);
    }
}
