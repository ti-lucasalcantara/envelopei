<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\LancamentoModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;
use App\Models\Envelopei\RateioReceitaModel;
use App\Models\Envelopei\RateioModeloModel;
use App\Models\Envelopei\RateioModeloItemModel;

class ReceitaController extends BaseApiController
{
    public function store()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) return $this->fail('Usuário não informado.', [], 401);

        $contaId     = (int)($p['ContaId'] ?? 0);
        $valorTotal  = (float)($p['ValorTotal'] ?? 0);

        // ✅ Novos campos
        $usarPadrao     = !empty($p['UsarRateioPadrao']);
        $rateioModeloId = (int)($p['RateioModeloId'] ?? 0);

        $rateios = $p['Rateios'] ?? [];

        if ($contaId <= 0) return $this->fail('ContaId é obrigatório.', [], 422);
        if ($valorTotal <= 0) return $this->fail('ValorTotal deve ser maior que zero.', [], 422);

        // -------------------------------------------------------
        // ✅ Se não veio rateio manual, tenta rateio pré-definido
        // -------------------------------------------------------
        if (!is_array($rateios) || count($rateios) === 0) {
            if ($usarPadrao || $rateioModeloId > 0) {
                $rateios = $this->rateiosDoModelo($uid, $rateioModeloId); // retorna no formato padrão do store()
            } else {
                return $this->fail('Informe Rateios ou use UsarRateioPadrao/RateioModeloId.', [], 422);
            }
        }

        // -------------------------------------------------------
        // calcula rateios
        // -------------------------------------------------------
        $itensCalculados = [];
        $soma = 0.0;

        foreach ($rateios as $r) {
            $envelopeId = (int)($r['EnvelopeId'] ?? 0);
            $modo       = (string)($r['ModoRateio'] ?? 'valor'); // valor | percentual
            $informado  = (float)($r['ValorInformado'] ?? 0);

            if ($envelopeId <= 0) return $this->fail('Rateio inválido: EnvelopeId.', $r, 422);
            if ($informado <= 0) return $this->fail('Rateio inválido: ValorInformado.', $r, 422);

            $calculado = 0.0;

            if ($modo === 'percentual') {
                $calculado = round($valorTotal * ($informado / 100), 2);
            } else {
                $calculado = round($informado, 2);
                $modo = 'valor';
            }

            $soma += $calculado;

            $itensCalculados[] = [
                'EnvelopeId'      => $envelopeId,
                'ModoRateio'      => $modo,
                'ValorInformado'  => $informado,
                'ValorCalculado'  => $calculado,
            ];
        }

        // -------------------------------------------------------
        // ✅ Ajuste fino: fecha centavos no percentual
        // Se o rateio for percentual, pode sobrar/faltar centavos.
        // Ajusta o último item para fechar no ValorTotal.
        // -------------------------------------------------------
        $soma = round($soma, 2);
        $valorTotal = round($valorTotal, 2);

        $temPercentual = false;
        foreach ($itensCalculados as $ic) {
            if ($ic['ModoRateio'] === 'percentual') {
                $temPercentual = true;
                break;
            }
        }

        if ($temPercentual && count($itensCalculados) > 0 && $soma !== $valorTotal) {
            $diff = round($valorTotal - $soma, 2);
            $last = count($itensCalculados) - 1;

            $itensCalculados[$last]['ValorCalculado'] = round($itensCalculados[$last]['ValorCalculado'] + $diff, 2);

            // recalcula soma
            $soma2 = 0.0;
            foreach ($itensCalculados as $ic) $soma2 += (float)$ic['ValorCalculado'];
            $soma = round($soma2, 2);
        }

        // valida soma final (agora tem que bater)
        if ($soma !== $valorTotal) {
            return $this->fail(
                'A soma do rateio não bate com o ValorTotal.',
                ['SomaRateio' => $soma, 'ValorTotal' => $valorTotal],
                422
            );
        }

        // -------------------------------------------------------
        // gravação
        // -------------------------------------------------------
        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId'      => $uid,
            'CategoriaId'    => !empty($p['CategoriaId']) ? (int)$p['CategoriaId'] : null,
            'TipoLancamento' => 'receita',
            'Descricao'      => $p['Descricao'] ?? null,
            'DataLancamento' => $this->normalizeDate($p['DataLancamento'] ?? null),
        ]);

        // movimento na conta (+)
        (new ItemContaModel())->insert([
            'LancamentoId' => (int)$lancamentoId,
            'ContaId'      => $contaId,
            'Valor'        => $valorTotal,
        ]);

        $itemEnvModel = new ItemEnvelopeModel();
        $rateioModel  = new RateioReceitaModel();

        foreach ($itensCalculados as $ic) {
            // movimento no envelope (+)
            $itemEnvModel->insert([
                'LancamentoId' => (int)$lancamentoId,
                'EnvelopeId'   => (int)$ic['EnvelopeId'],
                'Valor'        => (float)$ic['ValorCalculado'],
            ]);

            // memória do rateio da receita
            $rateioModel->insert([
                'LancamentoId'   => (int)$lancamentoId,
                'EnvelopeId'     => (int)$ic['EnvelopeId'],
                'ModoRateio'     => $ic['ModoRateio'],
                'ValorInformado' => (float)$ic['ValorInformado'],
                'ValorCalculado' => (float)$ic['ValorCalculado'],
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao salvar receita.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)$lancamentoId], 'Receita registrada', 201);
    }

    /**
     * Retorna rateios (EnvelopeId, ModoRateio, ValorInformado) baseados no modelo:
     * - se $rateioModeloId > 0 usa esse modelo
     * - senão usa o modelo padrão do usuário
     */
    private function rateiosDoModelo(int $usuarioId, int $rateioModeloId = 0): array
    {
        $modeloModel = new RateioModeloModel();
        $itensModel  = new RateioModeloItemModel();

        if ($rateioModeloId > 0) {
            $modelo = $modeloModel->find($rateioModeloId);
            if (!$modelo || (int)$modelo['UsuarioId'] !== $usuarioId || (int)$modelo['Ativo'] !== 1) {
                throw new \RuntimeException('RateioModeloId inválido para este usuário.');
            }
        } else {
            $modelo = $modeloModel->getPadraoDoUsuario($usuarioId);
            if (!$modelo) {
                throw new \RuntimeException('Nenhum rateio padrão definido.');
            }
            $rateioModeloId = (int)$modelo['RateioModeloId'];
        }

        $itens = $itensModel->listarPorModelo($rateioModeloId);

        if (!$itens || count($itens) === 0) {
            throw new \RuntimeException('Modelo de rateio não possui itens.');
        }

        // devolve no formato que seu store já entende
        $rateios = [];
        foreach ($itens as $i) {
            $rateios[] = [
                'EnvelopeId'     => (int)$i['EnvelopeId'],
                'ModoRateio'     => (string)$i['ModoRateio'], // percentual|valor
                'ValorInformado' => (float)$i['Valor'],       // aqui é o percentual ou valor fixo do modelo
            ];
        }

        return $rateios;
    }
}
