<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\LancamentoModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;
use App\Models\Envelopei\ContaModel;
use App\Models\Envelopei\EnvelopeModel;
use App\Models\Envelopei\ProdutoInvestimentoModel;
use App\Models\Envelopei\AporteInvestimentoModel;
use App\Models\Envelopei\RendimentoInvestimentoModel;

class InvestimentoController extends BaseApiController
{
    public function resumo()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $contaModel  = new ContaModel();
        $envModel    = new EnvelopeModel();
        $prodModel   = new ProdutoInvestimentoModel();

        $contaInvestimento = $contaModel->obterOuCriarContaInvestimento($uid);
        $saldoContaInvestimento = $contaModel->saldoAtual((int)$contaInvestimento['ContaId'], null);

        $contasOrigem = $contaModel->listarContasNaoInvestimento($uid);
        $envelopes    = $envModel->listarAtivos($uid);
        $produtos     = $prodModel->listarPorUsuario($uid);
        $totais       = $prodModel->totaisPorUsuario($uid);

        return $this->ok([
            'ContaInvestimento' => [
                'ContaId' => (int)$contaInvestimento['ContaId'],
                'Nome'    => $contaInvestimento['Nome'],
                'Saldo'   => round($saldoContaInvestimento, 2),
            ],
            'ContasOrigem' => $contasOrigem,
            'Envelopes'    => $envelopes,
            'Produtos'     => $produtos,
            'Totais'       => $totais,
        ]);
    }

    /** Enviar valor do envelope + conta para a conta de investimentos (não é despesa). */
    public function enviar()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $envelopeId = (int)($p['EnvelopeId'] ?? 0);
        $contaId    = (int)($p['ContaId'] ?? 0);
        $valor      = (float)($p['Valor'] ?? 0);
        $data       = $this->normalizeDate($p['DataLancamento'] ?? null);
        $descricao  = trim($p['Descricao'] ?? 'Enviado para investimento');

        if ($envelopeId <= 0) {
            return $this->fail('Selecione o envelope de origem.', [], 422);
        }
        if ($contaId <= 0) {
            return $this->fail('Selecione a conta de origem.', [], 422);
        }
        if ($valor <= 0) {
            return $this->fail('Valor deve ser maior que zero.', [], 422);
        }

        $valor = round($valor, 2);

        $contaModel  = new ContaModel();
        $envModel    = new EnvelopeModel();

        $contaOrigem = $contaModel->find($contaId);
        if (!$contaOrigem || (int)$contaOrigem['UsuarioId'] !== $uid) {
            return $this->fail('Conta de origem inválida.', [], 422);
        }
        if (($contaOrigem['TipoConta'] ?? '') === 'investimento') {
            return $this->fail('Use a conta bancária de origem, não a conta de investimentos.', [], 422);
        }

        $env = $envModel->find($envelopeId);
        if (!$env || (int)$env['UsuarioId'] !== $uid) {
            return $this->fail('Envelope inválido.', [], 422);
        }

        $saldoEnvelope = $envModel->saldoAtual($envelopeId);
        if ($saldoEnvelope < $valor) {
            return $this->fail('Saldo insuficiente no envelope.', [], 422);
        }

        $saldoConta = $contaModel->saldoAtual($contaId, null);
        if ($saldoConta < $valor) {
            return $this->fail('Saldo insuficiente na conta.', [], 422);
        }

        $contaInvestimento = $contaModel->obterOuCriarContaInvestimento($uid);
        $contaInvestimentoId = (int)$contaInvestimento['ContaId'];

        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId'      => $uid,
            'CategoriaId'    => null,
            'TipoLancamento' => 'investimento',
            'Descricao'      => $descricao,
            'DataLancamento' => $data,
        ]);

        $itemContaModel = new ItemContaModel();
        $itemEnvModel  = new ItemEnvelopeModel();

        $itemContaModel->insert([
            'LancamentoId' => (int)$lancamentoId,
            'ContaId'      => $contaId,
            'Valor'        => -$valor,
        ]);
        $itemContaModel->insert([
            'LancamentoId' => (int)$lancamentoId,
            'ContaId'      => $contaInvestimentoId,
            'Valor'        => $valor,
        ]);

        $itemEnvModel->insert([
            'LancamentoId' => (int)$lancamentoId,
            'EnvelopeId'   => $envelopeId,
            'Valor'        => -$valor,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao registrar envio para investimento.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)$lancamentoId], 'Valor enviado para investimentos.', 201);
    }

    /** Entrada direta na conta de investimentos (sem envelope/receita). */
    public function entradaDireta()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $valor     = (float)($p['Valor'] ?? 0);
        $data      = $this->normalizeDate($p['DataLancamento'] ?? null);
        $descricao = trim($p['Descricao'] ?? 'Entrada direta em investimentos');

        if ($valor <= 0) {
            return $this->fail('Valor deve ser maior que zero.', [], 422);
        }
        $valor = round($valor, 2);

        $contaModel = new ContaModel();
        $contaInvestimento = $contaModel->obterOuCriarContaInvestimento($uid);
        $contaInvestimentoId = (int)$contaInvestimento['ContaId'];

        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId'      => $uid,
            'CategoriaId'    => null,
            'TipoLancamento' => 'investimento_entrada',
            'Descricao'      => $descricao,
            'DataLancamento' => $data,
        ]);

        (new ItemContaModel())->insert([
            'LancamentoId' => (int)$lancamentoId,
            'ContaId'      => $contaInvestimentoId,
            'Valor'        => $valor,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao registrar entrada.', [], 500);
        }

        return $this->ok(['LancamentoId' => (int)$lancamentoId], 'Entrada registrada.', 201);
    }

    public function produtosIndex()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }
        $produtos = (new ProdutoInvestimentoModel())->listarPorUsuario($uid);
        $totais   = (new ProdutoInvestimentoModel())->totaisPorUsuario($uid);
        return $this->ok(['Produtos' => $produtos, 'Totais' => $totais]);
    }

    public function produtosStore()
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $nome   = trim($p['Nome'] ?? '');
        $tipo   = trim($p['TipoProduto'] ?? 'Outros');
        $aplicado = (float)($p['ValorAplicado'] ?? 0);
        $atual  = (float)($p['ValorAtual'] ?? $aplicado);

        if ($nome === '') {
            return $this->fail('Nome do produto é obrigatório.', [], 422);
        }

        $tiposValidos = array_keys(ProdutoInvestimentoModel::TIPOS);
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'Outros';
        }

        $model = new ProdutoInvestimentoModel();
        $id = $model->insert([
            'UsuarioId'       => $uid,
            'Nome'            => $nome,
            'TipoProduto'     => $tipo,
            'ValorAplicado'   => round($aplicado, 2),
            'ValorAtual'      => round($atual, 2),
            'DataCriacao'     => date('Y-m-d H:i:s'),
            'DataAtualizacao' => null,
        ]);

        if (!$id) {
            return $this->fail('Falha ao criar produto.', [], 500);
        }

        return $this->ok(['ProdutoInvestimentoId' => (int)$id], 'Produto criado.', 201);
    }

    public function produtosUpdate($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $id = (int)$id;
        $model = new ProdutoInvestimentoModel();
        $prod = $model->find($id);
        if (!$prod || (int)$prod['UsuarioId'] !== $uid) {
            return $this->fail('Produto não encontrado.', [], 404);
        }

        $nome     = array_key_exists('Nome', $p) ? trim($p['Nome']) : $prod['Nome'];
        $tipo     = array_key_exists('TipoProduto', $p) ? trim($p['TipoProduto']) : $prod['TipoProduto'];
        $aplicado = array_key_exists('ValorAplicado', $p) ? (float)$p['ValorAplicado'] : (float)$prod['ValorAplicado'];
        $atual    = array_key_exists('ValorAtual', $p) ? (float)$p['ValorAtual'] : (float)$prod['ValorAtual'];

        if ($nome === '') {
            return $this->fail('Nome do produto é obrigatório.', [], 422);
        }

        $tiposValidos = array_keys(ProdutoInvestimentoModel::TIPOS);
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'Outros';
        }

        $model->update($id, [
            'Nome'            => $nome,
            'TipoProduto'     => $tipo,
            'ValorAplicado'   => round($aplicado, 2),
            'ValorAtual'      => round($atual, 2),
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);

        return $this->ok([], 'Produto atualizado.');
    }

    public function produtosDelete($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $id = (int)$id;
        $model = new ProdutoInvestimentoModel();
        $prod = $model->find($id);
        if (!$prod || (int)$prod['UsuarioId'] !== $uid) {
            return $this->fail('Produto não encontrado.', [], 404);
        }

        $model->delete($id);
        return $this->ok([], 'Produto removido.');
    }

    /** Histórico do produto: dados do produto + lista de aportes + lista de rendimentos + totais. */
    public function produtoHistorico($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $id = (int)$id;
        $prodModel = new ProdutoInvestimentoModel();
        $prod = $prodModel->find($id);
        if (!$prod || (int)$prod['UsuarioId'] !== $uid) {
            return $this->fail('Produto não encontrado.', [], 404);
        }

        $aporteModel    = new AporteInvestimentoModel();
        $rendimentoModel = new RendimentoInvestimentoModel();

        $aportes    = $aporteModel->listarPorProduto($id);
        $rendimentos = $rendimentoModel->listarPorProduto($id);

        $totalAportes    = $aporteModel->totalAportesProduto($id);
        $totalRendimentos = $rendimentoModel->totalRendimentosProduto($id);

        $valorAplicado = (float)$prod['ValorAplicado'];
        $valorAtual    = (float)$prod['ValorAtual'];
        $variacao      = round($valorAtual - $valorAplicado, 2);
        $percentual    = $valorAplicado != 0 ? round((($valorAtual - $valorAplicado) / $valorAplicado * 100), 2) : 0.0;

        return $this->ok([
            'Produto' => $prod,
            'Aportes' => $aportes,
            'Rendimentos' => $rendimentos,
            'Totais' => [
                'TotalAportes'     => round($totalAportes, 2),
                'TotalRendimentos' => round($totalRendimentos, 2),
                'ValorAplicado'   => round($valorAplicado, 2),
                'ValorAtual'      => round($valorAtual, 2),
                'Variacao'        => $variacao,
                'Percentual'       => $percentual,
            ],
        ]);
    }

    /** Registrar aporte no produto (atualiza ValorAplicado do produto). */
    public function produtoAporte($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $id = (int)$id;
        $prodModel = new ProdutoInvestimentoModel();
        $prod = $prodModel->find($id);
        if (!$prod || (int)$prod['UsuarioId'] !== $uid) {
            return $this->fail('Produto não encontrado.', [], 404);
        }

        $valor     = (float)($p['Valor'] ?? 0);
        $dataAporte = $this->normalizeDate($p['DataAporte'] ?? null);
        $descricao  = trim($p['Descricao'] ?? 'Aporte');

        if ($valor <= 0) {
            return $this->fail('Valor do aporte deve ser maior que zero.', [], 422);
        }
        $valor = round($valor, 2);

        $db = db_connect();
        $db->transStart();

        (new AporteInvestimentoModel())->insert([
            'ProdutoInvestimentoId' => $id,
            'UsuarioId'             => $uid,
            'Valor'                 => $valor,
            'DataAporte'            => $dataAporte,
            'Descricao'             => $descricao ?: null,
            'DataCriacao'            => date('Y-m-d H:i:s'),
        ]);

        $novoAplicado = (float)$prod['ValorAplicado'] + $valor;
        $prodModel->update($id, [
            'ValorAplicado'   => round($novoAplicado, 2),
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao registrar aporte.', [], 500);
        }

        return $this->ok([], 'Aporte registrado.', 201);
    }

    /** Registrar rendimento no produto (valor positivo = lucro, negativo = perda; atualiza ValorAtual). */
    public function produtoRendimento($id)
    {
        $p   = $this->getJson();
        $uid = $this->requireUsuarioId($p);
        if (!$uid) {
            return $this->fail('Usuário não informado.', [], 401);
        }

        $id = (int)$id;
        $prodModel = new ProdutoInvestimentoModel();
        $prod = $prodModel->find($id);
        if (!$prod || (int)$prod['UsuarioId'] !== $uid) {
            return $this->fail('Produto não encontrado.', [], 404);
        }

        $valor   = (float)($p['Valor'] ?? 0);
        $dataRend = $this->normalizeDate($p['DataRendimento'] ?? null);
        $descricao = trim($p['Descricao'] ?? 'Rendimento');

        if ($valor == 0) {
            return $this->fail('Informe um valor (positivo para lucro, negativo para perda).', [], 422);
        }
        $valor = round($valor, 2);

        $db = db_connect();
        $db->transStart();

        (new RendimentoInvestimentoModel())->insert([
            'ProdutoInvestimentoId' => $id,
            'UsuarioId'             => $uid,
            'Valor'                 => $valor,
            'DataRendimento'        => $dataRend,
            'Descricao'             => $descricao ?: null,
            'DataCriacao'            => date('Y-m-d H:i:s'),
        ]);

        $novoAtual = (float)$prod['ValorAtual'] + $valor;
        $prodModel->update($id, [
            'ValorAtual'      => round($novoAtual, 2),
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Falha ao registrar rendimento.', [], 500);
        }

        return $this->ok([], 'Rendimento registrado.', 201);
    }
}
