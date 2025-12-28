<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\CategoriaModel;

class CategoriaController extends BaseApiController
{
    public function index()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        return $this->ok((new CategoriaModel())->listarAtivas($uid));
    }

    public function store()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $nome = trim($p['Nome'] ?? '');
        if ($nome === '') return $this->fail('Nome é obrigatório.', [], 422);

        $id = (new CategoriaModel())->insert([
            'UsuarioId'      => $uid,
            'Nome'           => $nome,
            'TipoCategoria'  => $p['TipoCategoria'] ?? 'ambos',
            'Ativa'          => 1,
        ]);

        return $this->ok(['CategoriaId' => (int)$id], 'Categoria criada', 201);
    }

    public function update($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new CategoriaModel();
        $cat = $model->find((int)$id);

        if (!$cat || (int)$cat['UsuarioId'] !== $uid) {
            return $this->fail('Categoria não encontrada.', [], 404);
        }

        $data = [];
        if (isset($p['Nome']))         $data['Nome'] = trim((string)$p['Nome']);
        if (isset($p['TipoCategoria'])) $data['TipoCategoria'] = (string)$p['TipoCategoria'];
        if (isset($p['Ativa']))        $data['Ativa'] = (int)$p['Ativa'];

        if (empty($data)) return $this->ok([], 'Nada para atualizar.');

        $model->update((int)$id, $data);

        return $this->ok([], 'Categoria atualizada');
    }

    public function delete($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $model = new CategoriaModel();
        $cat = $model->find((int)$id);

        if (!$cat || (int)$cat['UsuarioId'] !== $uid) {
            return $this->fail('Categoria não encontrada.', [], 404);
        }

        $model->update((int)$id, ['Ativa' => 0]);

        return $this->ok([], 'Categoria desativada');
    }
}
