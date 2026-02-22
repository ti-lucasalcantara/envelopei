<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<?php
$fatura = $fatura ?? [];
$lancamentos = $fatura['Lancamentos'] ?? [];
$periodo = str_pad($fatura['MesReferencia'] ?? 0, 2, '0', STR_PAD_LEFT) . '/' . ($fatura['AnoReferencia'] ?? '');
$cartaoNome = $fatura['CartaoNome'] ?? 'Cartão';
$ultimos4 = $fatura['Ultimos4Digitos'] ?? '????';
$bandeira = $fatura['Bandeira'] ?? '';
$fmtMoney = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
$fmtDate = function($d) {
    if (empty($d)) return '—';
    $t = strtotime($d);
    return $t ? date('d/m/Y', $t) : $d;
};
$vencimento = $fmtDate($fatura['DataVencimento'] ?? '') ?: '—';
$faturaPaga = !empty($fatura['Pago']);
$dataPagamento = $fmtDate($fatura['DataPagamento'] ?? '') ?: '';
$valorTotal = (float)($fatura['ValorTotal'] ?? 0);
$valorPago = (float)($fatura['ValorPago'] ?? 0);
$valorRestante = (float)($fatura['ValorRestante'] ?? max(0, $valorTotal - $valorPago));
$faturaId = (int)($fatura['FaturaId'] ?? 0);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <a href="<?= base_url('faturas') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-2"></i>Voltar às faturas
    </a>
    <div class="d-flex gap-2">
        <?php if ($faturaPaga): ?>
            <button class="btn btn-outline-warning btn-sm" id="btnDesfazer" data-fatura-id="<?= $faturaId ?>">
                <i class="fa-solid fa-rotate-left me-2"></i>Desfazer pagamento
            </button>
        <?php endif; ?>
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="fa-solid fa-print me-2"></i>Imprimir
        </button>
    </div>
</div>

<div class="card shadow-sm" id="faturaDetalhada">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4 pb-4 border-bottom">
            <h4 class="mb-1">Envelopei</h4>
            <p class="text-muted small mb-0">Controle de despesas</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-2">Fatura do Cartão de Crédito</h5>
                <div class="text-muted small">
                    <div><strong><?= esc($cartaoNome) ?></strong></div>
                    <div><?= esc($bandeira) ? esc($bandeira) . ' • ' : '' ?>****<?= esc($ultimos4) ?></div>
                    <div>Período: <?= esc($periodo) ?></div>
                    <div>Vencimento: <?= esc($vencimento) ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-2 text-end">
                    <div class="col-12">
                        <?php if ($faturaPaga): ?>
                            <span class="badge text-bg-success fs-6">Pago em <?= esc($dataPagamento) ?></span>
                        <?php else: ?>
                            <span class="badge text-bg-warning fs-6">Pendente</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-4">
                            <div>
                                <div class="text-muted small">Valor pago</div>
                                <div class="fw-bold text-success"><?= $fmtMoney($valorPago) ?></div>
                            </div>
                            <div>
                                <div class="text-muted small">Total da fatura</div>
                                <div class="fw-bold"><?= $fmtMoney($valorTotal) ?></div>
                            </div>
                            <div>
                                <div class="text-muted small">Valor restante</div>
                                <div class="fw-bold <?= $valorRestante > 0 ? 'text-warning' : 'text-success' ?>"><?= $fmtMoney($valorRestante) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:100px;">Data</th>
                        <th>Descrição</th>
                        <th>Envelope</th>
                        <th class="text-end" style="width:100px;">Valor</th>
                        <th class="text-end" style="width:100px;">Pago</th>
                        <th class="text-end" style="width:100px;">Restante</th>
                        <th style="width:100px;" class="no-print">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lancamentos)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhum lançamento nesta fatura.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lancamentos as $l): ?>
                            <?php
                            $valorItem = abs((float)($l['Valor'] ?? 0));
                            $valorPagoItem = (float)($l['ValorPago'] ?? 0);
                            $restanteItem = $valorItem - $valorPagoItem;
                            $itemCompleto = $valorPagoItem >= $valorItem;
                            ?>
                            <tr class="<?= $itemCompleto ? 'tr-marker-success' : '' ?>">
                                <td class="text-mono"><?= $fmtDate($l['DataLancamento'] ?? '') ?></td>
                                <td>
                                    <?= esc($l['Descricao'] ?? '—') ?>
                                    <?php if (!empty($l['Pagamentos']) && is_array($l['Pagamentos'])): ?>
                                        <div class="small mt-1">
                                            <?php foreach ($l['Pagamentos'] as $pg): ?>
                                                <div class="text-muted">
                                                    <?= $fmtMoney($pg['Valor']) ?> em <?= $fmtDate($pg['DataPagamento'] ?? '') ?>
                                                    <?php if (!empty($pg['Descricao'])): ?> – <?= esc($pg['Descricao']) ?><?php endif; ?>
                                                    <button class="btn btn-link btn-sm p-0 ms-1 text-danger desfazer-pgto no-print" data-pag-id="<?= (int)($pg['PagamentoItemId'] ?? 0) ?>" title="Desfazer">×</button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($l['EnvelopeNome'] ?? '—') ?></td>
                                <td class="text-end fw-semibold"><?= $fmtMoney($valorItem) ?></td>
                                <td class="text-end text-success"><?= $fmtMoney($valorPagoItem) ?></td>
                                <td class="text-end <?= $restanteItem > 0 ? 'text-warning fw-semibold' : '' ?>"><?= $fmtMoney($restanteItem) ?></td>
                                <td class="no-print">
                                    <?php if ($restanteItem > 0): ?>
                                        <button class="btn btn-sm btn-success btn-pagar-item" data-item-id="<?= (int)($l['ItemEnvelopeId'] ?? 0) ?>" data-restante="<?= $restanteItem ?>" data-descricao="<?= esc($l['Descricao'] ?? '') ?>">
                                            <i class="fa-solid fa-check me-1"></i>Pagar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-4 pt-4 border-top gap-4">
            <div class="text-end">
                <div class="text-muted small">Valor pago</div>
                <div class="fw-bold text-success"><?= $fmtMoney($valorPago) ?></div>
            </div>
            <div class="text-end">
                <div class="text-muted small">Total</div>
                <div class="fw-bold"><?= $fmtMoney($valorTotal) ?></div>
            </div>
            <div class="text-end">
                <div class="text-muted small">Restante</div>
                <div class="fs-4 fw-bold <?= $valorRestante > 0 ? 'text-warning' : 'text-success' ?>"><?= $fmtMoney($valorRestante) ?></div>
            </div>
        </div>

        <?php if (!$faturaPaga && $valorRestante > 0): ?>
            <div class="mt-4 pt-4 border-top text-center no-print">
                <a href="<?= base_url('faturas') ?>?pagar=<?= $faturaId ?>" class="btn btn-success">
                    <i class="fa-solid fa-check me-2"></i>Pagar fatura inteira (<?= $fmtMoney($valorRestante) ?>)
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pagar item (parcial ou total) -->
<div class="modal fade no-print" id="modalPagarItem" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pagar lançamento</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pagarItemId">
                <div class="alert alert-info small mb-3">
                    <i class="fa-solid fa-info-circle me-2"></i>
                    Valor restante: <strong id="pagarRestanteInfo">—</strong>. Informe o valor a pagar (pode ser parcial).
                </div>
                <div class="mb-3">
                    <label class="form-label">Valor a pagar</label>
                    <input type="number" step="0.01" class="form-control" id="pagarItemValor" placeholder="0,00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição (opcional)</label>
                    <input type="text" class="form-control" id="pagarItemDescricao" placeholder="Ex: 1ª parcela">
                </div>
                <div class="mb-3">
                    <label class="form-label">Conta para débito</label>
                    <select class="form-select" id="pagarItemConta"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data do pagamento</label>
                    <input type="date" class="form-control" id="pagarItemData">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" id="btnConfirmarPagarItem">
                    <i class="fa-solid fa-check me-2"></i>Registrar pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let contas = [];

    async function carregarContas() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) return;
        contas = r.data?.Contas ?? [];
    }

    function opt(val, txt) { return `<option value="${val}">${txt}</option>`; }

    document.querySelectorAll('.btn-pagar-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            const itemId = this.dataset.itemId;
            const restante = Number(this.dataset.restante || 0);
            const descricao = this.dataset.descricao || '';

            await carregarContas();
            document.getElementById('pagarItemId').value = itemId;
            document.getElementById('pagarRestanteInfo').innerText = Envelopei.money(restante);
            document.getElementById('pagarItemValor').value = restante.toFixed(2);
            document.getElementById('pagarItemValor').max = restante;
            document.getElementById('pagarItemDescricao').value = '';
            document.getElementById('pagarItemData').value = new Date().toISOString().slice(0, 10);
            document.getElementById('pagarItemConta').innerHTML =
                '<option value="">Selecione...</option>' + contas.map(c =>
                    opt(c.ContaId, c.Nome + ' (' + Envelopei.money(c.SaldoAtual) + ')')
                ).join('');
            new bootstrap.Modal(document.getElementById('modalPagarItem')).show();
        });
    });

    document.querySelectorAll('.desfazer-pgto').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const pagId = this.dataset.pagId;
            if (!pagId || !confirm('Desfazer este pagamento? O valor será devolvido à conta.')) return;

            const r = await Envelopei.api('api/faturas/item/pagamento/' + pagId + '/desfazer', 'POST', {});
            if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao desfazer.', 'danger');

            Envelopei.toast('Pagamento desfeito!', 'success');
            window.location.reload();
        });
    });

    document.getElementById('btnConfirmarPagarItem')?.addEventListener('click', async function() {
        const itemId = document.getElementById('pagarItemId').value;
        const valor = Number(document.getElementById('pagarItemValor').value || 0);
        const descricao = document.getElementById('pagarItemDescricao').value.trim();
        const contaId = Number(document.getElementById('pagarItemConta').value);
        const dataPag = document.getElementById('pagarItemData').value;

        if (!contaId) return Envelopei.toast('Selecione a conta.', 'danger');
        if (!valor || valor <= 0) return Envelopei.toast('Informe o valor a pagar.', 'danger');

        const r = await Envelopei.api('api/faturas/item/' + itemId + '/pagar', 'POST', {
            ContaId: contaId,
            Valor: valor,
            Descricao: descricao || null,
            DataPagamento: dataPag
        });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao pagar.', 'danger');

        Envelopei.toast('Pagamento registrado!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalPagarItem')).hide();
        window.location.reload();
    });

    document.getElementById('btnDesfazer')?.addEventListener('click', async function() {
        if (!confirm('Desfazer o pagamento desta fatura? Os valores serão devolvidos às contas que debitaram.')) return;

        const faturaId = this.dataset.faturaId;
        const r = await Envelopei.api('api/faturas/' + faturaId + '/desfazer', 'POST', {});
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao desfazer.', 'danger');

        Envelopei.toast('Pagamento desfeito!', 'success');
        window.location.reload();
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<style>
@media print {
    .navbar, .btn, .no-print, main .d-flex.mb-4 { display: none !important; }
    #faturaDetalhada { box-shadow: none !important; border: 1px solid #dee2e6; }
}
</style>
<?= $this->endSection() ?>
