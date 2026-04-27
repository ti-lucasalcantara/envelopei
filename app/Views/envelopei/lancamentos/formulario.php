<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>
<?php
$editando = !empty($lancamento);
$rotaSalvar = $tipo === 'receita'
    ? ($editando ? base_url('receitas/' . $lancamento['LancamentoId']) : base_url('receitas'))
    : ($editando ? base_url('despesas/' . $lancamento['LancamentoId']) : base_url('despesas'));
$valorAtual = $editando ? number_format((float) ($lancamento['Valor'] ?? 0), 2, ',', '.') : old('Valor');
$ehCartao = $editando && (!empty($lancamento['CartaoCreditoId']) || !empty($lancamento['FaturaId']));
?>

<form method="post" action="<?= $rotaSalvar ?>" class="card">
    <div class="card-body">
        <?php if ($editando && (int) ($lancamento['Ativo'] ?? 1) !== 1): ?>
            <div class="alert alert-warning">Este lançamento está inativo. Salvar a edição também irá reativá-lo.</div>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <label class="form-label">Descrição</label>
                <input name="Descricao" class="form-control" required value="<?= old('Descricao', $lancamento['Descricao'] ?? '') ?>">
            </div>
            <div class="col-12 col-lg-2">
                <label class="form-label">Valor</label>
                <input name="Valor" class="form-control" inputmode="decimal" required value="<?= $valorAtual ?>">
            </div>
            <div class="col-12 col-lg-2">
                <label class="form-label">Data</label>
                <input type="date" name="DataLancamento" class="form-control" required value="<?= old('DataLancamento', $lancamento['DataLancamento'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-12 col-lg-3">
                <label class="form-label">Conta</label>
                <select name="ContaId" class="form-select" <?= $ehCartao ? 'disabled' : 'required' ?>>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $conta): ?>
                        <option value="<?= $conta['ContaId'] ?>" <?= (int) old('ContaId', $lancamento['ContaId'] ?? 0) === (int) $conta['ContaId'] ? 'selected' : '' ?>>
                            <?= $conta['Nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($ehCartao): ?>
                    <div class="form-text">Despesa de cartão: a conta é definida no pagamento da fatura.</div>
                <?php endif; ?>
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label">Categoria</label>
                <select name="CategoriaId" class="form-select">
                    <option value="">Sem categoria</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <?php if ($categoria['TipoCategoria'] === 'ambos' || $categoria['TipoCategoria'] === $tipo): ?>
                            <option value="<?= $categoria['CategoriaId'] ?>" <?= (int) old('CategoriaId', $lancamento['CategoriaId'] ?? 0) === (int) $categoria['CategoriaId'] ? 'selected' : '' ?>>
                                <?= $categoria['Nome'] ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label">Envelope (opcional)</label>
                <select name="EnvelopeId" class="form-select">
                    <option value="">Sem envelope</option>
                    <?php foreach ($envelopes as $envelope): ?>
                        <option value="<?= $envelope['EnvelopeId'] ?>" <?= (int) old('EnvelopeId', $lancamento['EnvelopeId'] ?? 0) === (int) $envelope['EnvelopeId'] ? 'selected' : '' ?>>
                            <?= $envelope['Nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($editando): ?>
                    <div class="form-text">Ao deixar sem envelope, o vínculo antigo é preservado com valor zerado para não apagar histórico.</div>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label">Observação</label>
                <textarea name="Observacao" class="form-control" rows="3"><?= old('Observacao', $lancamento['Observacao'] ?? '') ?></textarea>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between">
        <a href="<?= base_url($tipo === 'receita' ? 'receitas' : 'despesas') ?>" class="btn btn-outline-secondary">Voltar</a>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i> Salvar</button>
    </div>
</form>
<?= $this->endSection() ?>
