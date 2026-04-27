<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-12 col-xl-4">
        <form method="post" action="<?= base_url('categorias') ?>" class="card">
            <div class="card-header bg-white fw-bold">Nova categoria</div>
            <div class="card-body">
                <label class="form-label">Nome</label>
                <input name="Nome" class="form-control mb-3" required>
                <label class="form-label">Tipo</label>
                <select name="TipoCategoria" class="form-select mb-3">
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                    <option value="ambos">Ambos</option>
                </select>
                <label class="form-label">Cor</label>
                <input type="color" name="Cor" class="form-control form-control-color mb-3" value="#0d6efd">
                <label class="form-label">Ícone</label>
                <input name="Icone" class="form-control form-control-lg" maxlength="8" value="🏷️">
                <div class="form-text">Clique no campo e pressione Windows + . para escolher um símbolo.</div>
            </div>
            <div class="card-footer bg-white"><button class="btn btn-primary w-100" type="submit">Salvar</button></div>
        </form>
    </div>
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Categoria</th><th>Tipo</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td>
                                <?php $icone = str_starts_with((string) ($categoria['Icone'] ?? ''), 'fa-') ? '🏷️' : (($categoria['Icone'] ?? '') ?: '🏷️'); ?>
                                <span class="me-2" style="color: <?= $categoria['Cor'] ?? '#0d6efd' ?>"><?= $icone ?></span><?= $categoria['Nome'] ?>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= $categoria['TipoCategoria'] ?></span></td>
                            <td><?= (int) ($categoria['Ativa'] ?? 1) === 1 ? '<span class="badge bg-success">Ativa</span>' : '<span class="badge bg-secondary">Inativa</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categorias)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
