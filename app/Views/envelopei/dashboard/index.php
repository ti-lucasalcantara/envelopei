<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-3">
    <div>
        <h4 class="mb-0">Dashboard</h4>
        <div class="text-muted">Visão geral e conciliação</div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <label class="d-flex align-items-center gap-2 mb-0">
            <span class="text-muted small">Período:</span>
            <select class="form-select form-select-sm" id="filtroPeriodo" style="width:auto; min-width:140px;">
                <option value="">Todo o período</option>
            </select>
        </label>
        <button type="button" class="btn btn-outline-secondary" id="btnToggleValores" title="Ocultar/mostrar valores">
            <i class="fa-solid fa-eye" id="iconToggleValores"></i>
        </button>
        <a href="<?= base_url('receitas/nova') ?>" class="btn btn-success">
            <i class="fa-solid fa-circle-plus me-2"></i>Receita
        </a>
        <a href="<?= base_url('despesas/nova') ?>" class="btn btn-danger">
            <i class="fa-solid fa-circle-minus me-2"></i>Despesa
        </a>
        <a href="<?= base_url('transferencias/nova') ?>" class="btn btn-outline-primary">
            <i class="fa-solid fa-right-left me-2"></i>Transferir
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Total Envelopes</div>
                <div class="fs-3 fw-bold" id="totalEnvelopes">—</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Total Contas</div>
                <div class="fs-3 fw-bold" id="totalContas">—</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Diferença (Conciliação)</div>
                <div class="fs-3 fw-bold" id="difConcil">—</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-3">
        <div class="card shadow-sm border-warning">
            <div class="card-body">
                <div class="text-muted"><i class="fa-solid fa-credit-card me-1"></i>Faturas em aberto</div>
                <div class="fs-3 fw-bold text-warning" id="faturasEmAberto">—</div>
            </div>
        </div>
    </div>
</div>

<!-- Próximas faturas -->
<div class="card shadow-sm mb-3" id="cardFaturas" style="display:none;">
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="mb-0"><i class="fa-solid fa-credit-card me-2"></i>Próximas faturas a vencer</h6>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label class="mb-0 d-flex align-items-center gap-1">
                <span class="text-muted small">Ver fatura:</span>
                <select class="form-select form-select-sm" id="filtroFaturaProxima" style="width:auto; min-width:180px;">
                    <option value="">Todas</option>
                </select>
            </label>
            <a href="<?= base_url('faturas') ?>" class="btn btn-sm btn-outline-primary">Ver todas</a>
        </div>
    </div>
    <div class="card-body py-2" id="faturasProximasBody">
        <div class="text-muted small">Carregando…</div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-2">
    <h5 class="mb-0">Envelopes</h5>
    <a href="<?= base_url('envelopes') ?>" class="btn btn-sm btn-outline-dark">
        Gerenciar <i class="fa-solid fa-chevron-right ms-1"></i>
    </a>
</div>

<div class="row g-3" id="gridEnvelopes"></div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    const STORAGE_OCULTAR_VALORES = 'envelopei_ocultarValores';

    let cacheEnvelopes = [];
    let cacheContas = [];
    let cacheCartoes = [];
    let cacheTotais = {};
    let cacheFaturasProximas = [];

    function valoresOcultos() {
        return localStorage.getItem(STORAGE_OCULTAR_VALORES) === 'true';
    }

    function formatValor(v) {
        return valoresOcultos() ? 'R$ ••••••' : Envelopei.money(v ?? 0);
    }

    function atualizarIconeOlho() {
        const icon = document.getElementById('iconToggleValores');
        const btn = document.getElementById('btnToggleValores');
        if (!icon || !btn) return;
        if (valoresOcultos()) {
            icon.className = 'fa-solid fa-eye-slash';
            btn.title = 'Mostrar valores';
        } else {
            icon.className = 'fa-solid fa-eye';
            btn.title = 'Ocultar valores';
        }
    }

    function opt(valor, texto) {
        return `<option value="${valor}">${texto}</option>`;
    }

    function renderEnvelopesCards(envelopes) {
        const grid = document.getElementById('gridEnvelopes');
        if (!envelopes || envelopes.length === 0) {
            grid.innerHTML = `<div class="col-12"><div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>Nenhum envelope cadastrado.
            </div></div>`;
            return;
        }

        const ocultar = valoresOcultos();
        grid.innerHTML = envelopes.map(e => {
            const saldo = Number(e.Saldo ?? 0);
            const receitasMes = Number(e.ReceitasMes ?? 0);
            const despesasMes = Number(e.DespesasMes ?? 0);
            const cor = e.Cor ? `style="border-left:6px solid ${e.Cor};"` : '';
            const badge = saldo < 0 ? 'text-bg-danger' : 'text-bg-success';
            const saldoClasse = saldo < 0 ? 'text-danger' : 'text-dark';

            return `
            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= base_url('envelopes') ?>/${e.EnvelopeId}/extrato" class="text-decoration-none text-dark">
                    <div class="card card-hover shadow-sm cursor-pointer" ${cor}>
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="fw-semibold">${e.Nome}</div>
                                    <div class="text-muted small">Saldo atual</div>
                                </div>
                                <span class="badge ${badge}">•</span>
                            </div>
                            <div class="mt-2 fs-4 fw-bold ${saldoClasse}">${formatValor(saldo)}</div>
                            <div class="mt-2 pt-2 border-top small">
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Receitas do mês</span>
                                    <span>${formatValor(receitasMes)}</span>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <span class="text-muted">Despesas do mês</span>
                                    <span>${formatValor(despesasMes)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>`;
        }).join('');
    }

    function aplicarVisibilidadeValores() {
        const ocultar = valoresOcultos();
        const fmt = (v) => ocultar ? 'R$ ••••••' : Envelopei.money(v ?? 0);

        const elTotalEnv = document.getElementById('totalEnvelopes');
        const elTotalContas = document.getElementById('totalContas');
        const elDif = document.getElementById('difConcil');
        const elFaturas = document.getElementById('faturasEmAberto');
        if (elTotalEnv && cacheTotais.TotalEnvelopes !== undefined) elTotalEnv.innerText = fmt(cacheTotais.TotalEnvelopes);
        if (elTotalContas && cacheTotais.TotalContas !== undefined) elTotalContas.innerText = fmt(cacheTotais.TotalContas);
        if (elDif && cacheTotais.Diferenca !== undefined) elDif.innerText = fmt(cacheTotais.Diferenca);
        if (elFaturas && cacheTotais.FaturasEmAberto !== undefined) elFaturas.innerText = fmt(cacheTotais.FaturasEmAberto);

        renderEnvelopesCards(cacheEnvelopes);
        renderFaturasProximas(cacheFaturasProximas);
    }

    function buildPeriodoOptions() {
        const sel = document.getElementById('filtroPeriodo');
        if (!sel) return;
        const opts = sel.querySelectorAll('option');
        for (let i = 1; i < opts.length; i++) opts[i].remove();
        const hoje = new Date();
        const nomesMes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        const anoAtual = hoje.getFullYear();
        const mesAtual = hoje.getMonth() + 1;
        for (let ano = 2026; ano <= anoAtual; ano++) {
            const mesFim = ano === anoAtual ? mesAtual : 12;
            for (let mes = 1; mes <= mesFim; mes++) {
                const opt = document.createElement('option');
                opt.value = ano + '-' + mes;
                opt.textContent = nomesMes[mes - 1] + '/' + ano;
                sel.appendChild(opt);
            }
        }
    }

    async function carregarResumo() {
        const periodo = document.getElementById('filtroPeriodo')?.value || '';
        let path = 'api/dashboard/resumo';
        if (periodo) {
            const [ano, mes] = periodo.split('-').map(Number);
            if (mes && ano) path += '?mes=' + mes + '&ano=' + ano;
        }
        const r = await Envelopei.api(path, 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar dashboard.', 'danger');
            return;
        }

        cacheTotais = r.data?.Totais ?? {};
        cacheEnvelopes = r.data?.Envelopes ?? [];
        cacheContas = r.data?.Contas ?? [];
        cacheCartoes = r.data?.CartoesCredito ?? [];
        cacheFaturasProximas = r.data?.FaturasProximas ?? [];

        aplicarVisibilidadeValores();
        atualizarIconeOlho();
    }

    const baseUrlFaturas = '<?= base_url('faturas') ?>';

    function renderFaturasProximas(proximas) {
        const card = document.getElementById('cardFaturas');
        const body = document.getElementById('faturasProximasBody');
        const selFatura = document.getElementById('filtroFaturaProxima');

        if (!proximas || proximas.length === 0) {
            card.style.display = 'none';
            return;
        }

        card.style.display = 'block';

        var filtroId = selFatura ? selFatura.value : '';
        var listar = filtroId ? proximas.filter(function(f) { return String(f.FaturaId) === String(filtroId); }) : proximas;

        if (selFatura) {
            selFatura.innerHTML = '<option value="">Todas</option>' + proximas.map(function(f) {
                var label = (f.CartaoNome || '') + ' ****' + (f.Ultimos4Digitos || '????') + ' - Vence ' + Envelopei.dateBR(f.DataVencimento);
                return '<option value="' + (f.FaturaId || '') + '">' + label + '</option>';
            }).join('');
            if (filtroId) selFatura.value = filtroId;
        }

        const ocultar = valoresOcultos();
        const fmt = function(v) { return ocultar ? 'R$ ••••••' : Envelopei.money(v != null ? v : 0); };
        if (listar.length === 0) {
            body.innerHTML = '<div class="text-muted small py-2">Nenhuma fatura selecionada.</div>';
            return;
        }
        body.innerHTML = listar.map(function(f) {
            var venc = f.DataVencimento || '-';
            var hoje = new Date().toISOString().slice(0, 10);
            var badge = venc < hoje ? 'text-bg-danger' : (venc === hoje ? 'text-bg-warning' : 'text-bg-secondary');
            var linkFatura = baseUrlFaturas + '/' + (f.FaturaId || '');
            return '<div class="d-flex justify-content-between align-items-center py-2 border-bottom" data-fatura-id="' + (f.FaturaId || '') + '">' +
                '<div><span class="fw-semibold">' + (f.CartaoNome || '') + ' ****' + (f.Ultimos4Digitos || '????') + '</span>' +
                ' <span class="badge ' + badge + ' ms-2">Vence ' + Envelopei.dateBR(venc) + '</span></div>' +
                '<div class="d-flex align-items-center gap-2">' +
                '<span class="fw-bold">' + fmt(f.ValorTotal) + '</span>' +
                '<a href="' + linkFatura + '" class="btn btn-sm btn-outline-primary" title="Ver detalhes da fatura">Ver fatura <i class="fa-solid fa-chevron-right ms-1"></i></a>' +
                '</div></div>';
        }).join('');
    }

    document.getElementById('filtroPeriodo').addEventListener('change', function() { carregarResumo(); });

    var elFiltroFatura = document.getElementById('filtroFaturaProxima');
    if (elFiltroFatura) {
        elFiltroFatura.addEventListener('change', function() {
            renderFaturasProximas(cacheFaturasProximas);
        });
    }

    document.getElementById('btnToggleValores').addEventListener('click', () => {
        const atual = valoresOcultos();
        localStorage.setItem(STORAGE_OCULTAR_VALORES, (!atual).toString());
        atualizarIconeOlho();
        aplicarVisibilidadeValores();
    });

    document.addEventListener('DOMContentLoaded', () => {
        buildPeriodoOptions();
        atualizarIconeOlho();
        carregarResumo();
    });
</script>
<?= $this->endSection() ?>
