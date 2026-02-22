<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<?php
$env = $envelope ?? [];
$envId = (int)($env['EnvelopeId'] ?? 0);
$envNome = esc($env['Nome'] ?? 'Envelope');
$saldoAtual = (float)($env['SaldoAtual'] ?? 0);
$cor = $env['Cor'] ?? '';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-2"></i>Voltar ao Dashboard
    </a>
    <div class="d-flex gap-2">
        <input type="date" class="form-control form-control-sm" id="filtroInicio" title="Data início">
        <input type="date" class="form-control form-control-sm" id="filtroFim" title="Data fim">
        <button class="btn btn-outline-primary btn-sm" id="btnFiltrar">
            <i class="fa-solid fa-filter me-2"></i>Filtrar
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" <?= $cor ? "style='border-left: 6px solid {$cor}'" : '' ?>>
        <div>
            <h5 class="mb-0">Extrato: <?= $envNome ?></h5>
            <div class="text-muted small">Saldo atual: <span id="saldoAtual" class="fw-bold"><?= 'R$ ' . number_format($saldoAtual, 2, ',', '.') ?></span></div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px;">Data</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th class="text-end" style="width:130px;">Valor</th>
                    </tr>
                </thead>
                <tbody id="extratoBody">
                    <tr><td colspan="4" class="text-center text-muted py-4">Carregando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    const envelopeId = <?= $envId ?>;

    function money(v) {
        const n = Number(v ?? 0);
        return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    async function carregarExtrato() {
        const inicio = document.getElementById('filtroInicio').value || null;
        const fim = document.getElementById('filtroFim').value || null;

        const qs = new URLSearchParams();
        if (inicio) qs.set('inicio', inicio);
        if (fim) qs.set('fim', fim);

        const r = await Envelopei.api(`api/envelopes/${envelopeId}/extrato?${qs.toString()}`, 'GET');

        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar extrato.', 'danger');
            document.getElementById('extratoBody').innerHTML =
                '<tr><td colspan="4" class="text-center text-danger py-4">Erro ao carregar.</td></tr>';
            return;
        }

        const env = r.data?.Envelope ?? {};
        const itens = r.data?.Itens ?? [];

        document.getElementById('saldoAtual').innerText = money(env.SaldoAtual);

        if (!itens.length) {
            document.getElementById('extratoBody').innerHTML =
                '<tr><td colspan="4" class="text-center text-muted py-4">Nenhum lançamento.</td></tr>';
            return;
        }

        document.getElementById('extratoBody').innerHTML = itens.map(i => {
            const v = Number(i.Valor ?? 0);
            const badge = v < 0 ? 'bg-light border border-danger text-danger' : 'bg-light border border-success text-success';
            const valorTotal = Math.abs(Number(i.Valor) || 0);
            const valorPago = Number(i.ValorPago) || 0;
            const pendente = i.FaturaId && valorPago < valorTotal;
            const pendenteLabel = pendente ? ' <span class="badge bg-light border border-warning text-warning small">cartão pendente</span>' : '';
            return `
                <tr class="${pendente ? 'tr-marker-warning' : ''}">
                    <td class="text-mono">${Envelopei.dateBR(i.DataLancamento)}</td>
                    <td><span class="badge ${badge}">${i.TipoLancamento ?? '-'}</span>${pendenteLabel}</td>
                    <td>${i.Descricao ?? '-'}</td>
                    <td class="text-end fw-semibold">${money(v)}</td>
                </tr>
            `;
        }).join('');
    }

    function setFiltrosPadrao() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        document.getElementById('filtroInicio').value = `${y}-${m}-01`;
        document.getElementById('filtroFim').value = now.toISOString().slice(0, 10);
    }

    document.addEventListener('DOMContentLoaded', () => {
        setFiltrosPadrao();
        carregarExtrato();
        document.getElementById('btnFiltrar').addEventListener('click', carregarExtrato);
    });
</script>
<?= $this->endSection() ?>
