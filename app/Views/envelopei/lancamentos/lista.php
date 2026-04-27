<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0"><?= $tipo === 'receita' ? 'Receitas cadastradas' : 'Despesas cadastradas' ?></h2>
    <a href="<?= base_url($tipo === 'receita' ? 'receitas/nova' : 'despesas/nova') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Novo lançamento
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Conta</th>
                <th>Envelope</th>
                <th>Status</th>
                <th class="text-end">Valor</th>
                <th class="text-end">Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($lancamentos as $lancamento): ?>
                <?php
                $ativo = (int) ($lancamento['Ativo'] ?? 1) === 1;
                $valor = $lancamento['ValorConta'] ?? $lancamento['ValorEnvelope'] ?? 0;
                $rotaEditar = $tipo === 'receita'
                    ? base_url('receitas/' . $lancamento['LancamentoId'] . '/editar')
                    : base_url('despesas/' . $lancamento['LancamentoId'] . '/editar');
                ?>
                <tr class="<?= $ativo ? '' : 'table-light text-muted' ?>">
                    <td><?= data_br($lancamento['DataLancamento']) ?></td>
                    <td class="fw-semibold"><?= $lancamento['Descricao'] ?: 'Sem descrição' ?></td>
                    <td><?= $lancamento['CategoriaNome'] ?? '-' ?></td>
                    <td><?= $lancamento['ContaNome'] ?? '-' ?></td>
                    <td><?= abs((float) ($lancamento['ValorEnvelope'] ?? 0)) > 0 ? ($lancamento['EnvelopeNome'] ?? '-') : '-' ?></td>
                    <td><?= $ativo ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>' ?></td>
                    <td class="text-end fw-bold <?= $tipo === 'despesa' ? 'text-danger' : 'text-success' ?>"><?= moeda_br(abs((float) $valor)) ?></td>
                    <td class="text-end">
                        <a href="<?= $rotaEditar ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <?php if ($ativo): ?>
                            <form class="d-inline" method="post" action="<?= base_url('lancamentos/' . $lancamento['LancamentoId'] . '/inativar') ?>" data-confirmacao="Inativar este lançamento? O histórico será preservado.">
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Inativar">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <form class="d-inline" method="post" action="<?= base_url('lancamentos/' . $lancamento['LancamentoId'] . '/reativar') ?>" data-confirmacao="Reativar este lançamento?">
                                <button class="btn btn-sm btn-outline-success" type="submit" title="Reativar">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($lancamentos)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Nenhum lançamento encontrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
