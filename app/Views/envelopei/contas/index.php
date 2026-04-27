<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Contas cadastradas</h2>
    <a href="<?= base_url('contas/nova') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Nova conta</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Banco</th>
                <th class="text-end">Saldo inicial</th>
                <th class="text-end">Saldo atual</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($contas as $conta): ?>
                <?php $ativa = (int) ($conta['Ativa'] ?? 1) === 1; ?>
                <tr class="<?= $ativa ? '' : 'table-light text-muted' ?>">
                    <td class="fw-bold"><?= $conta['Nome'] ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $conta['TipoConta'] ?></span></td>
                    <td><?= $conta['Banco'] ?? '-' ?></td>
                    <td class="text-end"><?= moeda_br($conta['SaldoInicial']) ?></td>
                    <td class="text-end fw-bold"><?= moeda_br($conta['SaldoAtual']) ?></td>
                    <td><?= $ativa ? '<span class="badge bg-success">Ativa</span>' : '<span class="badge bg-secondary">Inativa</span>' ?></td>
                    <td class="text-end">
                        <a href="<?= base_url('contas/' . $conta['ContaId'] . '/editar') ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <?php if ($ativa): ?>
                            <form class="d-inline" method="post" action="<?= base_url('contas/' . $conta['ContaId'] . '/inativar') ?>" data-confirmacao="Inativar esta conta? Os dados antigos serão preservados.">
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Inativar">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <form class="d-inline" method="post" action="<?= base_url('contas/' . $conta['ContaId'] . '/reativar') ?>" data-confirmacao="Reativar esta conta?">
                                <button class="btn btn-sm btn-outline-success" type="submit" title="Reativar">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($contas)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma conta cadastrada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
