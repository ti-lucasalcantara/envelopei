<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>
<form method="post" action="<?= $conta ? base_url('contas/' . $conta['ContaId']) : base_url('contas') ?>" class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label">Nome</label>
                <input name="Nome" class="form-control" required value="<?= old('Nome', $conta['Nome'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Tipo</label>
                <select name="TipoConta" class="form-select" required>
                    <?php foreach (['banco' => 'Banco', 'carteira' => 'Carteira', 'poupanca' => 'Poupança', 'investimento' => 'Investimento'] as $valor => $rotulo): ?>
                        <option value="<?= $valor ?>" <?= old('TipoConta', $conta['TipoConta'] ?? '') === $valor ? 'selected' : '' ?>><?= $rotulo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Banco/Instituição</label>
                <input name="Banco" class="form-control" value="<?= old('Banco', $conta['Banco'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Saldo inicial</label>
                <input name="SaldoInicial" class="form-control" inputmode="decimal" value="<?= old('SaldoInicial', isset($conta['SaldoInicial']) ? number_format((float) $conta['SaldoInicial'], 2, ',', '.') : '0,00') ?>">
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between">
        <a href="<?= base_url('contas') ?>" class="btn btn-outline-secondary">Voltar</a>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i> Salvar</button>
    </div>
</form>
<?= $this->endSection() ?>
