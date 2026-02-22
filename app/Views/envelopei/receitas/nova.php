<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-10 col-xl-8">
        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar ao Dashboard
        </a>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fa-solid fa-circle-plus fa-lg text-success"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">Nova Receita</h4>
                        <p class="text-muted small mb-0">Registre uma entrada de dinheiro e distribua entre os envelopes</p>
                    </div>
                </div>

                <form id="formReceita" class="needs-validation" novalidate>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Conta</label>
                            <select class="form-select form-select-lg" id="recConta" required>
                                <option value="">Selecione a conta...</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Data</label>
                            <input type="date" class="form-control form-control-lg" id="recData" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Valor Total</label>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" id="recValor" placeholder="0,00" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descrição</label>
                            <input type="text" class="form-control form-control-lg" id="recDesc" placeholder="Salário, extra, bico...">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rateio pré-definido</label>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <select class="form-select form-select-lg" id="recRateioModelo">
                                <option value="">— Rateio manual —</option>
                            </select>
                            <button class="btn btn-outline-primary btn-lg" type="button" id="btnAplicarRateio">
                                <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Aplicar
                            </button>
                        </div>
                        <div class="form-text">Selecione um modelo para preencher o rateio automaticamente.</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-semibold mb-0">Rateio por envelope</label>
                        <button class="btn btn-outline-primary btn-sm" type="button" id="btnAddRateio">
                            <i class="fa-solid fa-plus me-1"></i>Adicionar linha
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Envelope</th>
                                    <th style="width:140px;">Modo</th>
                                    <th style="width:140px;">Valor</th>
                                    <th style="width:56px;"></th>
                                </tr>
                            </thead>
                            <tbody id="tbRateio"></tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <span class="badge bg-light text-dark border px-3 py-2">Soma: <strong id="rateioSoma">0,00</strong></span>
                    </div>
                </form>

                <div class="d-flex gap-2 mt-4 pt-4 border-top">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" form="formReceita" class="btn btn-success btn-lg" id="btnSalvarReceita">
                        <i class="fa-solid fa-check me-2"></i>Salvar Receita
                    </button>
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
        document.getElementById('recConta').innerHTML = '<option value="">Selecione a conta...</option>' + contasHtml;

        document.getElementById('recData').value = new Date().toISOString().slice(0, 10);
        addLinhaRateio();
        calcSomaRateio();
    }

    async function carregarRateios() {
        const r = await Envelopei.api('api/rateios-modelo', 'GET');
        if (!r?.success) return;
        cacheRateiosModelo = r.data ?? [];
        const sel = document.getElementById('recRateioModelo');
        sel.innerHTML = '<option value="">— Rateio manual —</option>' + cacheRateiosModelo.map(m =>
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
            <td><input type="number" step="0.01" class="form-control form-control-sm rateio-valor" value=""></td>
            <td><button class="btn btn-sm btn-outline-danger btnRemoveRateio" type="button" title="Remover"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
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
                <td><input type="number" step="0.01" class="form-control form-control-sm rateio-valor" value="${i.Valor}"></td>
                <td><button class="btn btn-sm btn-outline-danger btnRemoveRateio" type="button"><i class="fa-solid fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            tr.querySelector('.btnRemoveRateio').addEventListener('click', () => { tr.remove(); calcSomaRateio(); });
            tr.querySelector('.rateio-valor').addEventListener('input', calcSomaRateio);
            tr.querySelector('.rateio-modo').addEventListener('change', calcSomaRateio);
        });
        calcSomaRateio();
    }

    function calcSomaRateio() {
        const total = Array.from(document.querySelectorAll('.rateio-valor')).reduce((a, i) => a + (Number(i.value) || 0), 0);
        document.getElementById('rateioSoma').textContent = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.getElementById('btnAddRateio').addEventListener('click', addLinhaRateio);
    document.getElementById('btnAplicarRateio').addEventListener('click', aplicarRateioModelo);

    document.getElementById('formReceita').addEventListener('submit', async function(e) {
        e.preventDefault();
        const ContaId = Number(document.getElementById('recConta').value || 0);
        const DataLancamento = document.getElementById('recData').value;
        const ValorTotal = Number(document.getElementById('recValor').value || 0);
        const Descricao = (document.getElementById('recDesc').value || '').trim();
        const RateioModeloId = Number(document.getElementById('recRateioModelo').value || 0);

        if (!ContaId) return Envelopei.toast('Selecione a conta.', 'danger');
        if (!ValorTotal || ValorTotal <= 0) return Envelopei.toast('Informe o valor total.', 'danger');

        let Rateios = [];
        if (!RateioModeloId) {
            Rateios = Array.from(document.querySelectorAll('#tbRateio tr')).map(tr => ({
                EnvelopeId: Number(tr.querySelector('.rateio-envelope')?.value || 0),
                ModoRateio: tr.querySelector('.rateio-modo')?.value || 'valor',
                ValorInformado: Number(tr.querySelector('.rateio-valor')?.value || 0)
            })).filter(r => r.EnvelopeId && r.ValorInformado > 0);
            if (Rateios.length === 0) return Envelopei.toast('Informe o rateio ou selecione um modelo.', 'danger');
        }

        const payload = { ContaId, DataLancamento, ValorTotal, Descricao };
        if (RateioModeloId) payload.RateioModeloId = RateioModeloId;
        else payload.Rateios = Rateios;

        const r = await Envelopei.api('api/receitas', 'POST', payload);
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');
        Envelopei.toast('Receita registrada!', 'success');
        window.location.href = '<?= base_url('dashboard') ?>';
    });

    document.addEventListener('DOMContentLoaded', () => {
        carregarDados();
        carregarRateios();
    });
</script>
<?= $this->endSection() ?>
