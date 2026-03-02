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

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" <?= $cor ? "style='border-left: 6px solid {$cor}'" : '' ?>>
        <div>
            <h5 class="mb-0">Extrato: <?= $envNome ?></h5>
            <div class="text-muted small">Saldo atual: <span id="saldoAtual" class="fw-bold"><?= 'R$ ' . number_format($saldoAtual, 2, ',', '.') ?></span></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success bg-opacity-10 border-success border-opacity-25">
                <h6 class="mb-0"><i class="fa-solid fa-arrow-up me-2"></i>Receitas</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:100px;">Data</th>
                                <th>Descrição</th>
                                <th class="text-end" style="width:120px;">Valor</th>
                                <th class="text-center" style="width:120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="extratoReceitasBody">
                            <tr><td colspan="4" class="text-center text-muted py-4">Carregando…</td></tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Total receitas:</th>
                                <th class="text-end text-success" id="totalReceitas">R$ 0,00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-danger bg-opacity-10 border-danger border-opacity-25">
                <h6 class="mb-0"><i class="fa-solid fa-arrow-down me-2"></i>Despesas</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:100px;">Data</th>
                                <th>Descrição</th>
                                <th class="text-end" style="width:120px;">Valor</th>
                                <th class="text-center" style="width:120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="extratoDespesasBody">
                            <tr><td colspan="4" class="text-center text-muted py-4">Carregando…</td></tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Total despesas:</th>
                                <th class="text-end text-danger" id="totalDespesas">R$ 0,00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    const envelopeId = <?= $envId ?>;
    const baseUrlLancamentosEditar = '<?= base_url('lancamentos/editar') ?>';

    function money(v) {
        var n = (v != null && v !== '') ? Number(v) : 0;
        if (isNaN(n)) n = 0;
        return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function isReceita(tipo, valor) {
        var t = (tipo || '').toLowerCase();
        if (t === 'receita') return true;
        if (t === 'despesa' || t === 'pgto_fatura' || t === 'pgto_parcial') return false;
        return Number(valor || 0) > 0;
    }

    function isDespesa(tipo, valor) {
        var t = (tipo || '').toLowerCase();
        if (t === 'despesa' || t === 'pgto_fatura' || t === 'pgto_parcial') return true;
        if (t === 'receita') return false;
        return Number(valor || 0) < 0;
    }

    async function carregarExtrato() {
        var inicio = document.getElementById('filtroInicio').value || null;
        var fim = document.getElementById('filtroFim').value || null;

        var qs = new URLSearchParams();
        if (inicio) qs.set('inicio', inicio);
        if (fim) qs.set('fim', fim);

        var r = await Envelopei.api('api/envelopes/' + envelopeId + '/extrato?' + qs.toString(), 'GET');

        var tbodyRec = document.getElementById('extratoReceitasBody');
        var tbodyDes = document.getElementById('extratoDespesasBody');
        var elTotalRec = document.getElementById('totalReceitas');
        var elTotalDes = document.getElementById('totalDespesas');

        if (!r || !r.success) {
            Envelopei.toast((r && r.message) ? r.message : 'Falha ao carregar extrato.', 'danger');
            tbodyRec.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Erro ao carregar.</td></tr>';
            tbodyDes.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Erro ao carregar.</td></tr>';
            return;
        }

        var env = (r.data && r.data.Envelope) ? r.data.Envelope : {};
        var itens = (r.data && r.data.Itens) ? r.data.Itens : [];

        document.getElementById('saldoAtual').innerText = money(env.SaldoAtual);

        var receitas = itens.filter(function(i) { return isReceita(i.TipoLancamento, i.Valor); });
        var despesas = itens.filter(function(i) { return isDespesa(i.TipoLancamento, i.Valor); });

        var totalReceitas = 0;
        receitas.forEach(function(i) { totalReceitas += Number(i.Valor) || 0; });
        var totalDespesas = 0;
        despesas.forEach(function(i) { totalDespesas += Math.abs(Number(i.Valor) || 0); });

        if (receitas.length === 0) {
            tbodyRec.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Nenhuma receita.</td></tr>';
        } else {
            tbodyRec.innerHTML = receitas.map(function(i) {
                var v = Number(i.Valor != null ? i.Valor : 0);
                var desc = (i.Descricao != null && i.Descricao !== '') ? i.Descricao : '-';
                var lid = (i.LancamentoId != null) ? i.LancamentoId : '';
                var acoes = '<a href="' + baseUrlLancamentosEditar + '/' + lid + '" class="btn btn-sm btn-outline-primary me-1" title="Editar"><i class="fa-solid fa-pen"></i></a>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-excluir-lanc" data-id="' + lid + '" title="Excluir"><i class="fa-solid fa-trash-can"></i></button>';
                return '<tr><td class="text-mono">' + Envelopei.dateBR(i.DataLancamento) + '</td><td>' + desc + '</td><td class="text-end fw-semibold text-success">' + money(v) + '</td><td class="text-center">' + acoes + '</td></tr>';
            }).join('');
        }

        if (despesas.length === 0) {
            tbodyDes.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Nenhuma despesa.</td></tr>';
        } else {
            tbodyDes.innerHTML = despesas.map(function(i) {
                var v = Number(i.Valor != null ? i.Valor : 0);
                var valorAbs = Math.abs(v);
                var valorTotal = Math.abs(Number(i.Valor) || 0);
                var valorPago = Number(i.ValorPago) || 0;
                var pendente = i.FaturaId && valorPago < valorTotal;
                var pendenteLabel = pendente ? ' <span class="badge bg-warning text-dark small">cartão pendente</span>' : '';
                var tipoLabel = (i.TipoLancamento || 'despesa') === 'pgto_fatura' ? 'Pagamento fatura' : ((i.TipoLancamento || '') === 'pgto_parcial' ? 'Pagamento parcial' : '');
                var descPart = (i.Descricao != null && i.Descricao !== '') ? i.Descricao : tipoLabel;
                if (descPart == null || descPart === '') descPart = '-';
                var desc = descPart + pendenteLabel;
                var trClass = pendente ? 'tr-marker-warning' : '';
                var lid = (i.LancamentoId != null) ? i.LancamentoId : '';
                var acoes = '<a href="' + baseUrlLancamentosEditar + '/' + lid + '" class="btn btn-sm btn-outline-primary me-1" title="Editar"><i class="fa-solid fa-pen"></i></a>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-excluir-lanc" data-id="' + lid + '" title="Excluir"><i class="fa-solid fa-trash-can"></i></button>';
                return '<tr class="' + trClass + '"><td class="text-mono">' + Envelopei.dateBR(i.DataLancamento) + '</td><td>' + desc + '</td><td class="text-end fw-semibold text-danger">' + money(valorAbs) + '</td><td class="text-center">' + acoes + '</td></tr>';
            }).join('');
        }
        document.querySelectorAll('.btn-excluir-lanc').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.getAttribute('data-id');
                if (!id) return;
                if (!confirm('Excluir este lançamento? Esta ação não pode ser desfeita.')) return;
                excluirLancamento(id);
            });
        });

        elTotalRec.textContent = money(totalReceitas);
        elTotalDes.textContent = money(totalDespesas);
    }

    async function excluirLancamento(lancamentoId) {
        var r = await Envelopei.api('api/lancamentos/' + lancamentoId, 'DELETE');
        if (r && r.success) {
            Envelopei.toast('Lançamento excluído.', 'success');
            carregarExtrato();
        } else {
            Envelopei.toast((r && r.message) ? r.message : 'Erro ao excluir.', 'danger');
        }
    }

    function setFiltrosPadrao() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        document.getElementById('filtroInicio').value = `${y}-${m}-01`;
        document.getElementById('filtroFim').value = now.toISOString().slice(0, 10);
    }

    function init() {
        setFiltrosPadrao();
        carregarExtrato();
        document.getElementById('btnFiltrar').addEventListener('click', carregarExtrato);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
</script>
<?= $this->endSection() ?>
