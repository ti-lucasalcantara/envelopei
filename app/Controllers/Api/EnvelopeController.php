<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\EnvelopeModel;
use App\Models\Envelopei\ItemEnvelopeModel;

class EnvelopeController extends BaseApiController
{
    public function index()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new EnvelopeModel();
        return $this->ok($model->saldosPorUsuario($uid));
    }

    public function show($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new EnvelopeModel();
        $env = $model->find((int)$id);

        if (!$env || (int)$env['UsuarioId'] !== $uid) {
            return $this->fail('Envelope não encontrado.', [], 404);
        }

        $env['SaldoAtual'] = $model->saldoAtual((int)$id);

        return $this->ok($env);
    }

    public function store()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $nome = trim($p['Nome'] ?? '');
        if ($nome === '') return $this->fail('Nome é obrigatório.', [], 422);

        $data = [
            'UsuarioId' => $uid,
            'Nome'      => $nome,
            'Cor'       => $p['Cor'] ?? null,
            'Ordem'     => isset($p['Ordem']) ? (int)$p['Ordem'] : null,
            'Ativo'     => 1,
        ];

        $model = new EnvelopeModel();
        $id = $model->insert($data);

        return $this->ok(['EnvelopeId' => (int)$id], 'Envelope criado', 201);
    }

    public function update($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new EnvelopeModel();
        $env = $model->find((int)$id);

        if (!$env || (int)$env['UsuarioId'] !== $uid) {
            return $this->fail('Envelope não encontrado.', [], 404);
        }

        $data = [];
        if (isset($p['Nome']))  $data['Nome'] = trim((string)$p['Nome']);
        if (array_key_exists('Cor', $p))   $data['Cor'] = $p['Cor'];
        if (array_key_exists('Ordem', $p)) $data['Ordem'] = $p['Ordem'] !== null ? (int)$p['Ordem'] : null;
        if (isset($p['Ativo'])) $data['Ativo'] = (int)$p['Ativo'];

        if (empty($data)) return $this->ok([], 'Nada para atualizar.');

        $model->update((int)$id, $data);

        return $this->ok([], 'Envelope atualizado');
    }

    public function delete($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new EnvelopeModel();
        $env = $model->find((int)$id);

        if (!$env || (int)$env['UsuarioId'] !== $uid) {
            return $this->fail('Envelope não encontrado.', [], 404);
        }

        $model->update((int)$id, ['Ativo' => 0]);

        return $this->ok([], 'Envelope desativado');
    }

    public function extrato($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $envModel = new EnvelopeModel();
        $env = $envModel->find((int)$id);

        if (!$env || (int)$env['UsuarioId'] !== $uid) {
            return $this->fail('Envelope não encontrado.', [], 404);
        }

        $inicio = $this->request->getGet('inicio');
        $fim    = $this->request->getGet('fim');

        $itens = (new ItemEnvelopeModel())->extrato((int)$id, $inicio ?: null, $fim ?: null);

        $saldo = $envModel->saldoAtual((int)$id);

        return $this->ok([
            'Envelope' => [
                'EnvelopeId' => (int)$env['EnvelopeId'],
                'Nome'       => $env['Nome'],
                'SaldoAtual' => $saldo,
            ],
            'Itens' => $itens,
        ]);
    }
}
