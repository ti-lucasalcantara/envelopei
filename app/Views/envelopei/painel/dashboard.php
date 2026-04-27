<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Saldo total em contas', $resumo['saldoContas'], 'fa-building-columns', 'primary'],
        ['Saldo livre', $resumo['saldoLivre'], 'fa-hand-holding-dollar', 'success'],
        ['Alocado em envelopes', $resumo['saldoEnvelopes'], 'fa-envelopes-bulk', 'info'],
        ['Receitas do mês', $resumo['receitasMes'], 'fa-arrow-trend-up', 'success'],
        ['Despesas do mês', $resumo['despesasMes'], 'fa-arrow-trend-down', 'danger'],
        ['Resultado do mês', $resumo['resultadoMes'], 'fa-scale-balanced', 'dark'],
        ['Investimentos', $resumo['totalInvestimentos'], 'fa-piggy-bank', 'warning'],
        ['Faturas abertas', $resumo['faturasAbertas'], 'fa-credit-card', 'secondary'],
    ];
    ?>
    <?php foreach ($cards as $card): ?>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card card-resumo h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted fw-semibold small"><?= $card[0] ?></div>
                            <div class="h4 mt-2 mb-0"><?= moeda_br($card[1]) ?></div>
                        </div>
                        <span class="btn btn-<?= $card[3] ?> btn-sm rounded-circle"><i class="fa-solid <?= $card[2] ?>"></i></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">Receitas x despesas por mês</div>
            <div class="card-body"><canvas id="graficoMensal" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">Despesas por categoria</div>
            <div class="card-body"><canvas id="graficoCategorias" height="140"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Envelopes</strong>
                <a href="<?= base_url('envelopes') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Envelope</th><th>Conta</th><th class="text-end">Saldo</th><th>Meta</th></tr></thead>
                    <tbody>
                    <?php foreach ($resumo['envelopes'] as $envelope): ?>
                        <tr>
                            <td><span class="badge me-2" style="background: <?= $envelope['Cor'] ?? '#0d6efd' ?>">&nbsp;</span><?= $envelope['Nome'] ?></td>
                            <td><?= $envelope['ContaNome'] ?? 'Global' ?></td>
                            <td class="text-end fw-bold"><?= moeda_br($envelope['SaldoAtual']) ?></td>
                            <td style="min-width: 150px;">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: <?= $envelope['PercentualMeta'] ?? 0 ?>%; background: <?= $envelope['Cor'] ?? '#0d6efd' ?>"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header bg-white fw-bold">Próximas despesas</div>
            <div class="list-group list-group-flush">
                <?php foreach ($resumo['proximasDespesas'] as $despesa): ?>
                    <div class="list-group-item d-flex justify-content-between">
                        <div>
                            <div class="fw-semibold"><?= $despesa['Descricao'] ?: 'Despesa' ?></div>
                            <div class="text-muted small"><?= data_br($despesa['DataLancamento']) ?> · <?= $despesa['CategoriaNome'] ?? 'Sem categoria' ?></div>
                        </div>
                        <span class="text-danger fw-bold">Despesa</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mensal = <?= json_encode($resumo['graficos']['mensal'], JSON_UNESCAPED_UNICODE) ?>;
    const categorias = <?= json_encode($resumo['graficos']['categorias'], JSON_UNESCAPED_UNICODE) ?>;

    new Chart(document.getElementById('graficoMensal'), {
        type: 'bar',
        data: {
            labels: mensal.map(item => item.Mes),
            datasets: [
                { label: 'Receitas', data: mensal.map(item => Number(item.Receitas)), backgroundColor: '#198754' },
                { label: 'Despesas', data: mensal.map(item => Number(item.Despesas)), backgroundColor: '#dc3545' }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('graficoCategorias'), {
        type: 'doughnut',
        data: {
            labels: categorias.map(item => item.Categoria),
            datasets: [{ data: categorias.map(item => Number(item.Total)), backgroundColor: ['#0d6efd','#dc3545','#ffc107','#198754','#6f42c1','#20c997','#fd7e14','#6c757d'] }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
<?= $this->endSection() ?>
