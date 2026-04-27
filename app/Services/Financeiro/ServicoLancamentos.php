<?php

namespace App\Services\Financeiro;

use App\Models\Envelopei\FaturaModel;
use App\Models\Envelopei\ItemContaModel;
use App\Models\Envelopei\ItemEnvelopeModel;
use App\Models\Envelopei\LancamentoModel;

class ServicoLancamentos
{
    /**
     * Registra uma receita recebida e distribui opcionalmente em envelopes.
     */
    public function registrarReceita(int $usuarioId, array $dados): int
    {
        $valor = decimal_banco($dados['Valor'] ?? $dados['ValorTotal'] ?? 0);
        if ($valor <= 0) {
            throw new \InvalidArgumentException('Informe um valor maior que zero.');
        }

        $contaId = (int) ($dados['ContaId'] ?? 0);
        if ($contaId <= 0) {
            throw new \InvalidArgumentException('Informe a conta da receita.');
        }

        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId' => $usuarioId,
            'CategoriaId' => !empty($dados['CategoriaId']) ? (int) $dados['CategoriaId'] : null,
            'TipoLancamento' => 'receita',
            'Ativo' => 1,
            'RecebidoPago' => 1,
            'Descricao' => $dados['Descricao'] ?? null,
            'Observacao' => $dados['Observacao'] ?? null,
            'DataLancamento' => $dados['DataLancamento'] ?? date('Y-m-d'),
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);

        (new ItemContaModel())->insert([
            'LancamentoId' => (int) $lancamentoId,
            'ContaId' => $contaId,
            'Valor' => $valor,
        ]);

        $this->registrarRateiosDaReceita((int) $lancamentoId, $dados);

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new \RuntimeException('Não foi possível registrar a receita.');
        }

        return (int) $lancamentoId;
    }

    /**
     * Registra uma despesa paga, com envelope opcional.
     */
    public function registrarDespesa(int $usuarioId, array $dados): int
    {
        $valor = decimal_banco($dados['Valor'] ?? 0);
        if ($valor <= 0) {
            throw new \InvalidArgumentException('Informe um valor maior que zero.');
        }

        $contaId = (int) ($dados['ContaId'] ?? 0);
        if ($contaId <= 0) {
            throw new \InvalidArgumentException('Informe a conta da despesa.');
        }

        $db = db_connect();
        $db->transStart();

        $lancamentoId = (new LancamentoModel())->insert([
            'UsuarioId' => $usuarioId,
            'CategoriaId' => !empty($dados['CategoriaId']) ? (int) $dados['CategoriaId'] : null,
            'TipoLancamento' => 'despesa',
            'Ativo' => 1,
            'RecebidoPago' => 1,
            'Descricao' => $dados['Descricao'] ?? null,
            'Observacao' => $dados['Observacao'] ?? null,
            'DataLancamento' => $dados['DataLancamento'] ?? date('Y-m-d'),
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);

        (new ItemContaModel())->insert([
            'LancamentoId' => (int) $lancamentoId,
            'ContaId' => $contaId,
            'Valor' => -$valor,
        ]);

        $envelopeId = (int) ($dados['EnvelopeId'] ?? 0);
        if ($envelopeId > 0) {
            (new ItemEnvelopeModel())->insert([
                'LancamentoId' => (int) $lancamentoId,
                'EnvelopeId' => $envelopeId,
                'Valor' => -$valor,
            ]);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new \RuntimeException('Não foi possível registrar a despesa.');
        }

        return (int) $lancamentoId;
    }

    /**
     * Inativa um lançamento mantendo todos os registros para auditoria.
     */
    public function inativarLancamento(int $usuarioId, int $lancamentoId): void
    {
        $model = new LancamentoModel();
        $lancamento = $model->find($lancamentoId);
        if (! $lancamento || (int) $lancamento['UsuarioId'] !== $usuarioId) {
            throw new \RuntimeException('Lançamento não encontrado.');
        }

        $model->update($lancamentoId, [
            'Ativo' => 0,
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Reativa um lançamento inativo sem recriar seus itens financeiros.
     */
    public function reativarLancamento(int $usuarioId, int $lancamentoId): void
    {
        $model = new LancamentoModel();
        $lancamento = $model->find($lancamentoId);
        if (! $lancamento || (int) $lancamento['UsuarioId'] !== $usuarioId) {
            throw new \RuntimeException('Lançamento não encontrado.');
        }

        $model->update($lancamentoId, [
            'Ativo' => 1,
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Atualiza uma receita ou despesa preservando o mesmo LancamentoId.
     */
    public function atualizarLancamento(int $usuarioId, int $lancamentoId, array $dados): void
    {
        $lancamentoModel = new LancamentoModel();
        $lancamento = $lancamentoModel->find($lancamentoId);
        if (! $lancamento || (int) $lancamento['UsuarioId'] !== $usuarioId) {
            throw new \RuntimeException('Lançamento não encontrado.');
        }

        $tipo = (string) ($lancamento['TipoLancamento'] ?? '');
        if (! in_array($tipo, ['receita', 'despesa'], true)) {
            throw new \RuntimeException('Somente receitas e despesas podem ser editadas nesta tela.');
        }

        $ehCartao = !empty($lancamento['CartaoCreditoId']) || !empty($lancamento['FaturaId']);
        $valor = decimal_banco($dados['Valor'] ?? 0);
        if ($valor <= 0) {
            throw new \InvalidArgumentException('Informe um valor maior que zero.');
        }

        $contaId = (int) ($dados['ContaId'] ?? 0);
        if (! $ehCartao && $contaId <= 0) {
            throw new \InvalidArgumentException('Informe a conta do lançamento.');
        }

        $db = db_connect();
        $db->transStart();

        $lancamentoModel->update($lancamentoId, [
            'CategoriaId' => !empty($dados['CategoriaId']) ? (int) $dados['CategoriaId'] : null,
            'Descricao' => $dados['Descricao'] ?? null,
            'Observacao' => $dados['Observacao'] ?? null,
            'DataLancamento' => $dados['DataLancamento'] ?? date('Y-m-d'),
            'Ativo' => 1,
            'RecebidoPago' => 1,
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);

        $valorLancamento = $tipo === 'despesa' ? -$valor : $valor;
        if (! $ehCartao) {
            $this->atualizarOuCriarItemConta($lancamentoId, $contaId, $valorLancamento);
        }

        $this->atualizarItemEnvelopeDoLancamento($lancamentoId, (int) ($dados['EnvelopeId'] ?? 0), $valorLancamento);

        if (!empty($lancamento['FaturaId'])) {
            (new FaturaModel())->recalcularValorTotal((int) $lancamento['FaturaId']);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new \RuntimeException('Não foi possível atualizar o lançamento.');
        }
    }

    /**
     * Registra rateios manuais enviados no formulário de receita.
     */
    private function registrarRateiosDaReceita(int $lancamentoId, array $dados): void
    {
        if (!empty($dados['EnvelopeId'])) {
            $valor = decimal_banco($dados['Valor'] ?? $dados['ValorTotal'] ?? 0);
            (new ItemEnvelopeModel())->insert([
                'LancamentoId' => $lancamentoId,
                'EnvelopeId' => (int) $dados['EnvelopeId'],
                'Valor' => $valor,
            ]);
            return;
        }

        $rateios = $dados['Rateios'] ?? [];
        if (! is_array($rateios) || $rateios === []) {
            return;
        }

        $itemEnvelopeModel = new ItemEnvelopeModel();
        foreach ($rateios as $rateio) {
            $envelopeId = (int) ($rateio['EnvelopeId'] ?? 0);
            $valor = decimal_banco($rateio['Valor'] ?? 0);

            if ($envelopeId <= 0 || $valor <= 0) {
                continue;
            }

            $itemEnvelopeModel->insert([
                'LancamentoId' => $lancamentoId,
                'EnvelopeId' => $envelopeId,
                'Valor' => $valor,
            ]);
        }
    }

    /**
     * Atualiza o primeiro item de conta do lançamento ou cria um novo se não existir.
     */
    private function atualizarOuCriarItemConta(int $lancamentoId, int $contaId, float $valor): void
    {
        $model = new ItemContaModel();
        $item = $model->where('LancamentoId', $lancamentoId)->orderBy('ItemContaId', 'ASC')->first();

        if ($item) {
            $model->update((int) $item['ItemContaId'], [
                'ContaId' => $contaId,
                'Valor' => $valor,
            ]);
            return;
        }

        $model->insert([
            'LancamentoId' => $lancamentoId,
            'ContaId' => $contaId,
            'Valor' => $valor,
        ]);
    }

    /**
     * Atualiza o item de envelope sem apagar registros antigos.
     */
    private function atualizarItemEnvelopeDoLancamento(int $lancamentoId, int $envelopeId, float $valor): void
    {
        $model = new ItemEnvelopeModel();
        $item = $model->where('LancamentoId', $lancamentoId)->orderBy('ItemEnvelopeId', 'ASC')->first();

        if ($item && $envelopeId > 0) {
            $model->update((int) $item['ItemEnvelopeId'], [
                'EnvelopeId' => $envelopeId,
                'Valor' => $valor,
            ]);
            $this->zerarDemaisItensEnvelope($lancamentoId, (int) $item['ItemEnvelopeId']);
            return;
        }

        if ($item && $envelopeId <= 0) {
            $model->update((int) $item['ItemEnvelopeId'], [
                'Valor' => 0,
            ]);
            $this->zerarDemaisItensEnvelope($lancamentoId, (int) $item['ItemEnvelopeId']);
            return;
        }

        if ($envelopeId > 0) {
            $model->insert([
                'LancamentoId' => $lancamentoId,
                'EnvelopeId' => $envelopeId,
                'Valor' => $valor,
            ]);
        }
    }

    /**
     * Zera itens extras de envelope para evitar saldos duplicados sem apagar histórico.
     */
    private function zerarDemaisItensEnvelope(int $lancamentoId, int $itemMantidoId): void
    {
        db_connect()->table('tb_itens_envelope')
            ->where('LancamentoId', $lancamentoId)
            ->where('ItemEnvelopeId !=', $itemMantidoId)
            ->update(['Valor' => 0]);
    }
}
