<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .page-receita .card-receita { border-radius: 12px; overflow: hidden; }
    .page-receita .card-receita .card-header-receita { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .page-receita .card-receita .card-body-receita { padding: 1.5rem 1.5rem 1.75rem; }
    .page-receita .receita-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .page-receita .form-label { font-size: 0.875rem; color: #495057; margin-bottom: 0.35rem; }
    .page-receita .form-control, .page-receita .form-select { border-radius: 8px; }
    .page-receita .form-control:focus, .page-receita .form-select:focus { border-color: #198754; box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15); }
    .page-receita .form-select-rateio { height: calc(1.5em + 0.75rem); min-height: 38px; }
    .page-receita .btn-match-select { height: calc(1.5em + 0.75rem); min-height: 38px; padding-top: 0; padding-bottom: 0; display: inline-flex; align-items: center; }
    .page-receita .section-title { font-size: 0.95rem; font-weight: 600; color: #212529; margin-bottom: 0.75rem; }
    .page-receita .table-rateio { border-radius: 8px; overflow: hidden; border: 1px solid #e9ecef; }
    .page-receita .table-rateio thead th { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; color: #6c757d; padding: 0.65rem 0.75rem; }
    .page-receita .table-rateio tbody td { padding: 0.5rem 0.75rem; vertical-align: middle; }
    .page-receita .table-rateio .form-control-sm, .page-receita .table-rateio .form-select-sm { border-radius: 6px; }
    .page-receita .soma-rateio { font-size: 0.95rem; padding: 0.5rem 1rem; border-radius: 8px; }
    .page-receita .btn-actions { border-radius: 8px; padding: 0.5rem 1.25rem; }
    .page-receita .divider-receita { border: 0; height: 1px; background: linear-gradient(90deg, transparent, #e9ecef 20%, #e9ecef 80%, transparent); margin: 1.5rem 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-receita">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar ao Dashboard
            </a>

            <div class="card card-receita shadow-sm border-0 mb-4">
                <div class="card-header-receita bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="receita-icon bg-success bg-opacity-10">
                            <i class="fa-solid fa-circle-plus text-success"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold">Nova Receita</h4>
                            <p class="text-muted small mb-0 mt-1">Registre uma entrada e distribua entre os envelopes</p>
                        </div>
                    </div>
                </div>
                <div class="card-body-receita">
                    <form id="formReceita" class="needs-validation" novalidate>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Data</label>
                                <input type="date" class="form-control" id="recData" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Valor</label>
                                <input type="text" inputmode="decimal" class="form-control input-money" id="recValor" placeholder="0,00" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Conta</label>
                                <select class="form-select" id="recConta" required>
                                    <option value="" selected disabled>- Selecione -</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descrição</label>
                                <input type="text" class="form-control" id="recDesc" placeholder="Salário, extra...">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rateio pré-definido</label>
                            <div class="d-flex flex-column flex-md-row gap-2 align-items-md-stretch">
                                <select class="form-select form-select-rateio flex-grow-1" id="recRateioModelo">
                                    <option value="" selected disabled>- Selecione -</option>
                                </select>
                                <button class="btn btn-outline-primary btn-match-select" type="button" id="btnAplicarRateio">
                                    <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Aplicar
                                </button>
                            </div>
                            <div class="form-text mt-1">Selecione um modelo para preencher o rateio automaticamente.</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="section-title">Rateio por envelope</span>
                            <button class="btn btn-outline-primary btn-sm btn-actions" type="button" id="btnAddRateio">
                                <i class="fa-solid fa-plus me-1"></i>Adicionar linha
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-rateio table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Envelope</th>
                                        <th style="width:120px;">Modo</th>
                                        <th style="width:130px;">Valor</th>
                                        <th style="width:52px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbRateio"></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-2">
                            <span class="badge bg-light text-dark border soma-rateio">Soma: <strong id="rateioSoma">0,00</strong></span>
                        </div>
                    </form>

                    <div class="d-flex flex-wrap gap-2 mt-4 pt-4 border-top">
                        <button type="button" class="btn btn-success btn-actions" id="btnSalvarReceita">
                            <i class="fa-solid fa-check me-2"></i>Salvar
                        </button>
                        <button type="button" class="btn btn-outline-success btn-actions" id="btnSalvarCriarNovaReceita">
                            <i class="fa-solid fa-plus me-2"></i>Salvar e criar nova
                        </button>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-actions">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let cacheEnvelopes = [];
    let cacheContas = [];
    let cacheRateiosModelo = [];

    function opt(v, t) { return `<option value="${v}">${t}</option>`; }

    async function carregarDados() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
            return;
        }
        cacheContas = r.data?.Contas ?? [];
        cacheEnvelopes = r.data?.Envelopes ?? [];

        const contasHtml = cacheContas.map(c => opt(c.ContaId, `${c.Nome} (${Envelopei.money(c.SaldoAtual)})`)).join('');
        document.getElementById('recConta').innerHTML = '<option value="" selected disabled>- Selecione - </option>' + contasHtml;

        document.getElementById('recData').value = new Date().toISOString().slice(0, 10);
        addLinhaRateio();
        calcSomaRateio();
    }

    async function carregarRateios() {
        const r = await Envelopei.api('api/rateios-modelo', 'GET');
        if (!r?.success) return;
        cacheRateiosModelo = r.data ?? [];
        const sel = document.getElementById('recRateioModelo');
        sel.innerHTML = '<option value="" selected disabled >- Selecione -</option>' + cacheRateiosModelo.map(m =>
            `<option value="${m.RateioModeloId}">${m.Nome}${Number(m.Padrao) === 1 ? ' (padrão)' : ''}</option>`
        ).join('');
    }

    function addLinhaRateio() {
        const envOpts = cacheEnvelopes.map(e => opt(e.EnvelopeId, e.Nome)).join('');
        const tbody = document.getElementById('tbRateio');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select class="form-select form-select-sm rateio-envelope"><option value="">Selecione...</option>${envOpts}</select></td>
            <td><select class="form-select form-select-sm rateio-modo"><option value="valor">Valor</option><option value="percentual">%</option></select></td>
            <td><input type="text" inputmode="decimal" class="form-control form-control-sm rateio-valor input-money" value="" placeholder="0,00"></td>
            <td><button class="btn btn-sm btn-outline-danger btnRemoveRateio" type="button" title="Remover"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        Envelopei.applyMoneyMask(tr.querySelector('.rateio-valor'));
        tr.querySelector('.btnRemoveRateio').addEventListener('click', () => { tr.remove(); calcSomaRateio(); });
        tr.querySelector('.rateio-valor').addEventListener('input', calcSomaRateio);
        tr.querySelector('.rateio-modo').addEventListener('change', calcSomaRateio);
    }

    async function aplicarRateioModelo() {
        const id = Number(document.getElementById('recRateioModelo').value || 0);
        if (!id) return Envelopei.toast('Selecione um modelo.', 'warning');
        const r = await Envelopei.api(`api/rateios-modelo/${id}`, 'GET');
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha.', 'danger');
        const itens = r.data?.Itens ?? [];
        if (!itens.length) return Envelopei.toast('Modelo sem itens.', 'warning');
        document.getElementById('tbRateio').innerHTML = '';
        itens.forEach(i => {
            const envOpts = cacheEnvelopes.map(e => `<option value="${e.EnvelopeId}" ${e.EnvelopeId == i.EnvelopeId ? 'selected' : ''}>${e.Nome}</option>`).join('');
            const tbody = document.getElementById('tbRateio');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><select class="form-select form-select-sm rateio-envelope">${envOpts}</select></td>
                <td><select class="form-select form-select-sm rateio-modo"><option value="valor" ${i.ModoRateio === 'valor' ? 'selected' : ''}>Valor</option><option value="percentual" ${i.ModoRateio === 'percentual' ? 'selected' : ''}>%</option></select></td>
                <td><input type="text" inputmode="decimal" class="form-control form-control-sm rateio-valor input-money" value="${Envelopei.formatMoneyForInput(i.Valor)}" placeholder="0,00"></td>
                <td><button class="btn btn-sm btn-outline-danger btnRemoveRateio" type="button"><i class="fa-solid fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            Envelopei.applyMoneyMask(tr.querySelector('.rateio-valor'));
            tr.querySelector('.btnRemoveRateio').addEventListener('click', () => { tr.remove(); calcSomaRateio(); });
            tr.querySelector('.rateio-valor').addEventListener('input', calcSomaRateio);
            tr.querySelector('.rateio-modo').addEventListener('change', calcSomaRateio);
        });
        calcSomaRateio();
    }

    function calcSomaRateio() {
        const total = Array.from(document.querySelectorAll('.rateio-valor')).reduce((a, i) => a + Envelopei.parseMoney(i.value), 0);
        document.getElementById('rateioSoma').textContent = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setDataHojeReceita() {
        document.getElementById('recData').value = new Date().toISOString().slice(0, 10);
    }

    function resetFormReceita() {
        setDataHojeReceita();
        document.getElementById('recValor').value = '';
        document.getElementById('recDesc').value = '';
        document.getElementById('recConta').value = '';
        document.getElementById('recRateioModelo').value = '';
        document.getElementById('tbRateio').innerHTML = '';
        addLinhaRateio();
        calcSomaRateio();
    }

    async function salvarReceita(criarNova) {
        const ContaId = Number(document.getElementById('recConta').value || 0);
        const DataLancamento = document.getElementById('recData').value;
        const ValorTotal = Envelopei.parseMoney(document.getElementById('recValor').value);
        const Descricao = (document.getElementById('recDesc').value || '').trim();
        const RateioModeloId = Number(document.getElementById('recRateioModelo').value || 0);

        if (!ContaId) return Envelopei.toast('Selecione a conta.', 'danger');
        if (!ValorTotal || ValorTotal <= 0) return Envelopei.toast('Informe o valor total.', 'danger');

        let Rateios = [];
        if (!RateioModeloId) {
            Rateios = Array.from(document.querySelectorAll('#tbRateio tr')).map(tr => ({
                EnvelopeId: Number(tr.querySelector('.rateio-envelope')?.value || 0),
                ModoRateio: tr.querySelector('.rateio-modo')?.value || 'valor',
                ValorInformado: Envelopei.parseMoney(tr.querySelector('.rateio-valor')?.value)
            })).filter(r => r.EnvelopeId && r.ValorInformado > 0);
            if (Rateios.length === 0) return Envelopei.toast('Informe o rateio ou selecione um modelo.', 'danger');
        }

        const payload = { ContaId, DataLancamento, ValorTotal, Descricao };
        if (RateioModeloId) payload.RateioModeloId = RateioModeloId;
        else payload.Rateios = Rateios;

        const r = await Envelopei.api('api/receitas', 'POST', payload);
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');

        Envelopei.toast('Receita registrada com sucesso!', 'success');

        if (criarNova) {
            resetFormReceita();
        } else {
            window.location.href = '<?= base_url('dashboard') ?>';
        }
    }

    document.getElementById('btnAddRateio').addEventListener('click', addLinhaRateio);
    document.getElementById('btnAplicarRateio').addEventListener('click', aplicarRateioModelo);
    document.getElementById('btnSalvarReceita').addEventListener('click', () => salvarReceita(false));
    document.getElementById('btnSalvarCriarNovaReceita').addEventListener('click', () => salvarReceita(true));

    document.getElementById('formReceita').addEventListener('submit', function(e) {
        e.preventDefault();
        salvarReceita(false);
    });

    document.addEventListener('DOMContentLoaded', () => {
        carregarDados();
        carregarRateios();
    });
</script>
<?= $this->endSection() ?>
