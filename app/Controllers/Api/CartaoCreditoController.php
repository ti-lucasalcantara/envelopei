<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\CartaoCreditoModel;

class CartaoCreditoController extends BaseApiController
{
    public function index()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new CartaoCreditoModel();
        $ativosParam = $this->request->getGet('Ativos') ?? $p['Ativos'] ?? true;
        $ativos = filter_var($ativosParam, FILTER_VALIDATE_BOOLEAN);
        $lista = $ativos ? $model->listarAtivos($uid) : $model->listarTodos($uid);

        return $this->ok($lista);
    }

    public function show($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new CartaoCreditoModel();
        $cartao = $model->find((int)$id);

        if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
            return $this->fail('Cartão não encontrado.', [], 404);
        }

        return $this->ok($cartao);
    }

    public function store()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $nome = trim($p['Nome'] ?? '');
        if ($nome === '') return $this->fail('Nome é obrigatório.', [], 422);

        $data = [
            'UsuarioId'       => $uid,
            'Nome'            => $nome,
            'Bandeira'        => trim($p['Bandeira'] ?? ''),
            'Ultimos4Digitos' => preg_replace('/\D/', '', $p['Ultimos4Digitos'] ?? '') ?: null,
            'DiaFechamento'   => min(28, max(1, (int)($p['DiaFechamento'] ?? 10))),
            'DiaVencimento'   => min(28, max(1, (int)($p['DiaVencimento'] ?? 17))),
            'Limite'          => !empty($p['Limite']) ? round((float)$p['Limite'], 2) : null,
            'Cor'             => trim($p['Cor'] ?? '') ?: null,
            'Ativo'           => 1,
        ];

        $model = new CartaoCreditoModel();
        $id = $model->insert($data);

        return $this->ok(['CartaoCreditoId' => (int)$id], 'Cartão cadastrado', 201);
    }

    public function update($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new CartaoCreditoModel();
        $cartao = $model->find((int)$id);

        if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
            return $this->fail('Cartão não encontrado.', [], 404);
        }

        $data = [];
        if (isset($p['Nome'])) $data['Nome'] = trim((string)$p['Nome']);
        if (isset($p['Bandeira'])) $data['Bandeira'] = trim((string)$p['Bandeira']);
        if (isset($p['Ultimos4Digitos'])) $data['Ultimos4Digitos'] = preg_replace('/\D/', '', $p['Ultimos4Digitos']) ?: null;
        if (isset($p['DiaFechamento'])) $data['DiaFechamento'] = min(28, max(1, (int)$p['DiaFechamento']));
        if (isset($p['DiaVencimento'])) $data['DiaVencimento'] = min(28, max(1, (int)$p['DiaVencimento']));
        if (array_key_exists('Limite', $p)) $data['Limite'] = $p['Limite'] !== '' && $p['Limite'] !== null ? round((float)$p['Limite'], 2) : null;
        if (isset($p['Cor'])) $data['Cor'] = trim((string)$p['Cor']) ?: null;
        if (isset($p['Ativo'])) $data['Ativo'] = (int)$p['Ativo'];

        if (empty($data)) return $this->ok([], 'Nada para atualizar.');

        $model->update((int)$id, $data);

        return $this->ok([], 'Cartão atualizado');
    }

    public function delete($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new CartaoCreditoModel();
        $cartao = $model->find((int)$id);

        if (!$cartao || (int)$cartao['UsuarioId'] !== $uid) {
            return $this->fail('Cartão não encontrado.', [], 404);
        }

        $model->update((int)$id, ['Ativo' => 0]);

        return $this->ok([], 'Cartão desativado');
    }
}
