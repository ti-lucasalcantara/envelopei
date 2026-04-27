<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>
<?php
$totalAlocado = array_sum(array_column($envelopes, 'SaldoAtual'));
$semSaldo = array_filter($envelopes, fn($item) => (float) ($item['SaldoAtual'] ?? 0) == 0.0);
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-4"><div class="card card-resumo"><div class="card-body"><div class="text-muted">Total alocado</div><div class="h3"><?= moeda_br($totalAlocado) ?></div></div></div></div>
    <div class="col-12 col-lg-4"><div class="card card-resumo"><div class="card-body"><div class="text-muted">Envelopes ativos</div><div class="h3"><?= count($envelopes) ?></div></div></div></div>
    <div class="col-12 col-lg-4"><div class="card card-resumo"><div class="card-body"><div class="text-muted">Sem saldo</div><div class="h3"><?= count($semSaldo) ?></div></div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Novo envelope</strong>
        <span class="text-muted small">Campos opcionais preservam a estrutura antiga</span>
    </div>
    <form method="post" action="<?= base_url('envelopes') ?>" class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-3"><label class="form-label">Nome</label><input name="Nome" class="form-control" required></div>
            <div class="col-12 col-lg-2"><label class="form-label">Conta</label><select name="ContaId" class="form-select"><option value="">Global</option><?php foreach ($contas as $conta): ?><option value="<?= $conta['ContaId'] ?>"><?= $conta['Nome'] ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-lg-2"><label class="form-label">Meta</label><input name="MetaValor" class="form-control" inputmode="decimal" value="0,00"></div>
            <div class="col-6 col-lg-1"><label class="form-label">Cor</label><input type="color" name="Cor" class="form-control form-control-color" value="#0d6efd"></div>
            <div class="col-6 col-lg-2">
                <label class="form-label">Ícone</label>
                <input name="Icone" class="form-control" maxlength="8" value="📦">
                <div class="form-text">Use Windows + .</div>
            </div>
            <div class="col-12 col-lg-2"><button class="btn btn-primary w-100" type="submit"><i class="fa-solid fa-plus me-1"></i> Criar</button></div>
            <div class="col-12"><label class="form-label">Descrição</label><input name="Descricao" class="form-control"></div>
        </div>
    </form>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#abaCards" type="button">Cards</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#abaGrafico" type="button">Gráfico</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#abaTabela" type="button">Tabela</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="abaCards">
        <div class="row g-3">
            <?php foreach ($envelopes as $envelope): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <?php $icone = str_starts_with((string) ($envelope['Icone'] ?? ''), 'fa-') ? '📦' : (($envelope['Icone'] ?? '') ?: '📦'); ?>
                                    <div class="fw-bold"><span class="me-2" style="color: <?= $envelope['Cor'] ?? '#0d6efd' ?>"><?= $icone ?></span><?= $envelope['Nome'] ?></div>
                                    <div class="text-muted small"><?= $envelope['ContaNome'] ?? 'Global' ?></div>
                                </div>
                                <span class="badge bg-success">Ativo</span>
                            </div>
                            <div class="h4 mt-3"><?= moeda_br($envelope['SaldoAtual']) ?></div>
                            <div class="progress mb-2" style="height: 9px;"><div class="progress-bar" style="width: <?= $envelope['PercentualMeta'] ?>%; background: <?= $envelope['Cor'] ?? '#0d6efd' ?>"></div></div>
                            <div class="d-flex justify-content-between text-muted small"><span>Meta <?= moeda_br($envelope['MetaValor'] ?? 0) ?></span><span><?= $envelope['PercentualMeta'] ?>%</span></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="tab-pane fade" id="abaGrafico">
        <div class="card"><div class="card-body"><canvas id="graficoEnvelopes" height="100"></canvas></div></div>
    </div>
    <div class="tab-pane fade" id="abaTabela">
        <div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Nome</th><th>Conta</th><th class="text-end">Saldo</th><th class="text-end">Meta</th><th>Status</th></tr></thead>
            <tbody><?php foreach ($envelopes as $envelope): ?><tr><td><?= $envelope['Nome'] ?></td><td><?= $envelope['ContaNome'] ?? 'Global' ?></td><td class="text-end"><?= moeda_br($envelope['SaldoAtual']) ?></td><td class="text-end"><?= moeda_br($envelope['MetaValor'] ?? 0) ?></td><td><span class="badge bg-success">Ativo</span></td></tr><?php endforeach; ?></tbody>
        </table></div></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const envelopes = <?= json_encode($envelopes, JSON_UNESCAPED_UNICODE) ?>;
    new Chart(document.getElementById('graficoEnvelopes'), {
        type: 'pie',
        data: {
            labels: envelopes.map(item => item.Nome),
            datasets: [{ data: envelopes.map(item => Number(item.SaldoAtual)), backgroundColor: envelopes.map(item => item.Cor || '#0d6efd') }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
<?= $this->endSection() ?>
