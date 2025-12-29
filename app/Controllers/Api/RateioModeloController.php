<?php

namespace App\Controllers\Api;

use App\Controllers\Api\BaseApiController;
use App\Models\Envelopei\RateioModeloModel;
use App\Models\Envelopei\RateioModeloItemModel;

class RateioModeloController extends BaseApiController
{
    protected RateioModeloModel $modelo;
    protected RateioModeloItemModel $itens;

    public function __construct()
    {
        $this->modelo = new RateioModeloModel();
        $this->itens  = new RateioModeloItemModel();
    }

    /**
     * GET /api/rateios-modelo
     */
    public function index()
    {
        $uid = $this->requireUsuarioId();
        if (!$uid) return $this->fail('Não autenticado', [], 401);

        $lista = $this->modelo
            ->where('UsuarioId', $uid)
            ->where('Ativo', 1)
            ->orderBy('Padrao', 'DESC')
            ->orderBy('Nome', 'ASC')
            ->findAll();

        return $this->ok($lista);
    }

    /**
     * GET /api/rateios-modelo/{id}
     */
    public function show($id)
    {
        $uid = $this->requireUsuarioId();
        if (!$uid) return $this->fail('Não autenticado', [], 401);

        $modelo = $this->modelo->find($id);

        if (!$modelo || (int)$modelo['UsuarioId'] !== $uid) {
            return $this->fail('Modelo não encontrado', [], 404);
        }

        $itens = $this->itens
            ->where('RateioModeloId', $id)
            ->orderBy('Ordem', 'ASC')
            ->findAll();

        return $this->ok([
            'Modelo' => $modelo,
            'Itens'  => $itens,
        ]);
    }

    /**
     * GET /api/rateios-modelo/padrao
     */
    public function padrao()
    {
        $uid = $this->requireUsuarioId();
        if (!$uid) return $this->fail('Não autenticado', [], 401);

        $modelo = $this->modelo
            ->where('UsuarioId', $uid)
            ->where('Padrao', 1)
            ->where('Ativo', 1)
            ->first();

        if (!$modelo) {
            return $this->ok(null);
        }

        $itens = $this->itens
            ->where('RateioModeloId', $modelo['RateioModeloId'])
            ->orderBy('Ordem', 'ASC')
            ->findAll();

        return $this->ok([
            'Modelo' => $modelo,
            'Itens'  => $itens,
        ]);
    }

    /**
     * POST /api/rateios-modelo
     */
    public function store()
    {
        $data = $this->getJson();
        $uid  = $this->requireUsuarioId($data);
        if (!$uid) return $this->fail('Não autenticado', [], 401);

        if (empty($data['Nome']) || empty($data['Itens'])) {
            return $this->fail('Nome e itens são obrigatórios.');
        }

        $this->validarItens($data['Itens']);

        $db = db_connect();
        $db->transStart();

        // se vier como padrão, remove padrão dos outros
        if (!empty($data['Padrao'])) {
            $this->modelo
                ->where('UsuarioId', $uid)
                ->set(['Padrao' => 0])
                ->update();
        }

        $this->modelo->insert([
            'UsuarioId'   => $uid,
            'Nome'        => $data['Nome'],
            'Padrao'      => !empty($data['Padrao']) ? 1 : 0,
            'Ativo'       => 1,
            'DataCriacao' => date('Y-m-d H:i:s'),
        ]);

        $modeloId = $this->modelo->getInsertID();

        foreach ($data['Itens'] as $ordem => $i) {
            $this->itens->insert([
                'RateioModeloId' => $modeloId,
                'EnvelopeId'     => $i['EnvelopeId'],
                'ModoRateio'     => $i['ModoRateio'],
                'Valor'          => $i['Valor'],
                'Ordem'          => $ordem + 1,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Erro ao salvar modelo.');
        }

        return $this->ok(['RateioModeloId' => $modeloId], 'Modelo criado');
    }

    /**
     * PUT /api/rateios-modelo/{id}
     */
    public function update($id)
    {
        $data = $this->getJson();
        $uid  = $this->requireUsuarioId($data);
        if (!$uid) return $this->fail('Não autenticado', [], 401);

        $modelo = $this->modelo->find($id);
        if (!$modelo || (int)$modelo['UsuarioId'] !== $uid) {
            return $this->fail('Modelo não encontrado', [], 404);
        }

        if (empty($data['Nome']) || empty($data['Itens'])) {
            return $this->fail('Nome e itens são obrigatórios.');
        }

        $this->validarItens($data['Itens']);

        $db = db_connect();
        $db->transStart();

        if (!empty($data['Padrao'])) {
            $this->modelo
                ->where('UsuarioId', $uid)
                ->set(['Padrao' => 0])
                ->update();
        }

        $this->modelo->update($id, [
            'Nome'   => $data['Nome'],
            'Padrao' => !empty($data['Padrao']) ? 1 : 0,
        ]);

        // recria itens
        $this->itens->where('RateioModeloId', $id)->delete();

        foreach ($data['Itens'] as $ordem => $i) {
            $this->itens->insert([
                'RateioModeloId' => $id,
                'EnvelopeId'     => $i['EnvelopeId'],
                'ModoRateio'     => $i['ModoRateio'],
                'Valor'          => $i['Valor'],
                'Ordem'          => $ordem + 1,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Erro ao atualizar modelo.');
        }

        return $this->ok(['RateioModeloId' => $id], 'Modelo atualizado');
    }

    /**
     * DELETE /api/rateios-modelo/{id}
     */
    public function delete($id)
    {
        $uid = $this->requireUsuarioId();
        if (!$uid) return $this->fail('Não autenticado', [], 401);

        $modelo = $this->modelo->find($id);
        if (!$modelo || (int)$modelo['UsuarioId'] !== $uid) {
            return $this->fail('Modelo não encontrado', [], 404);
        }

        $this->modelo->update($id, ['Ativo' => 0]);

        return $this->ok([], 'Modelo desativado');
    }

    /**
     * POST /api/rateios-modelo/{id}/definir-padrao
     */
    public function definirPadrao($id)
    {
        $uid = $this->requireUsuarioId();
        if (!$uid) return $this->fail('Não autenticado', [], 401);

        $modelo = $this->modelo->find($id);
        if (!$modelo || (int)$modelo['UsuarioId'] !== $uid) {
            return $this->fail('Modelo não encontrado', [], 404);
        }

        $db = db_connect();
        $db->transStart();

        $this->modelo
            ->where('UsuarioId', $uid)
            ->set(['Padrao' => 0])
            ->update();

        $this->modelo->update($id, ['Padrao' => 1]);

        $db->transComplete();

        return $this->ok([], 'Modelo definido como padrão');
    }

    /**
     * Validação dos itens do modelo
     */
    private function validarItens(array $itens)
    {
        $somaPercentual = 0;

        foreach ($itens as $i) {
            if (empty($i['EnvelopeId']) || empty($i['ModoRateio'])) {
                throw new \InvalidArgumentException('Envelope e modo são obrigatórios.');
            }

            if ($i['ModoRateio'] === 'percentual') {
                $somaPercentual += (float)$i['Valor'];
            }
        }

        if ($somaPercentual > 0 && abs($somaPercentual - 100) > 0.01) {
            throw new \InvalidArgumentException('A soma dos percentuais deve ser 100%.');
        }
    }
}
