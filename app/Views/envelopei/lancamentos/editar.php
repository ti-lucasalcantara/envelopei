<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .page-editar-lanc .card-edit { border-radius: 12px; overflow: hidden; }
    .page-editar-lanc .card-edit .card-header-edit { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .page-editar-lanc .card-edit .card-body-edit { padding: 1.5rem; }
    .page-editar-lanc .form-label { font-size: 0.875rem; color: #495057; margin-bottom: 0.35rem; }
    .page-editar-lanc .form-control, .page-editar-lanc .form-select { border-radius: 8px; }
    .page-editar-lanc .btn-actions { border-radius: 8px; padding: 0.5rem 1.25rem; }
    .page-editar-lanc .table-rateio { border-radius: 8px; overflow: hidden; border: 1px solid #e9ecef; }
    .page-editar-lanc .table-rateio thead th { font-size: 0.8rem; font-weight: 600; }
    .page-editar-lanc .table-rateio tbody td { padding: 0.5rem 0.75rem; vertical-align: middle; }
    .page-editar-lanc #painelReceita, .page-editar-lanc #painelDespesa { display: none; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-editar-lanc">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <a href="<?= base_url('lancamentos') ?>" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar aos lançamentos
            </a>

            <div id="loadingState" class="text-center py-5">
                <i class="fa-solid fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2 text-muted">Carregando lançamento…</p>
            </div>

            <div id="errorState" style="display:none;" class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation me-2"></i><span id="errorMsg">Lançamento não encontrado ou não pode ser editado.</span>
            </div>

            <div id="formContainer" style="display:none;" class="card card-edit shadow-sm border-0 mb-4">
                <div class="card-header-edit bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div id="iconReceita" class="rounded-3 p-3 bg-success bg-opacity-10" style="display:none;">
                            <i class="fa-solid fa-circle-plus text-success"></i>
                        </div>
                        <div id="iconDespesa" class="rounded-3 p-3 bg-danger bg-opacity-10" style="display:none;">
                            <i class="fa-solid fa-circle-minus text-danger"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold" id="tituloForm">Editar lançamento</h4>
                            <p class="text-muted small mb-0 mt-1" id="subtituloForm">Altere os campos e salve</p>
                        </div>
                    </div>
                </div>
                <div class="card-body-edit">
                    <input type="hidden" id="lancamentoId" value="<?= (int)($lancamentoId ?? 0) ?>">

                    <!-- RECEITA -->
                    <div id="painelReceita">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Data</label>
                                <input type="date" class="form-control" id="recData">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold">Valor total</label>
                                <input type="text" inputmode="decimal" class="form-control input-money" id="recValor" placeholder="0,00">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Conta</label>
                                <select class="form-select" id="recConta">
                                    <option value="">Selecione...</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descrição</label>
                            <input type="text" class="form-control" id="recDesc" placeholder="Salário, extra...">
                        </div>
                        <div class="mb-3">
                            <span class="fw-semibold d-block mb-2">Rateio por envelope</span>
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
                                <span class="badge bg-light text-dark border">Soma: <strong id="rateioSoma">0,00</strong></span>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnAddRateio">
                                <i class="fa-solid fa-plus me-1"></i>Adicionar linha
                            </button>
                        </div>
                    </div>

                    <!-- DESPESA -->
                    <div id="painelDespesa">
                        <div id="despesaVista" class="des-panel">
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Conta</label>
                                    <select class="form-select" id="desConta">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Envelope</label>
                                    <select class="form-select" id="desEnvelope">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Data</label>
                                    <input type="date" class="form-control" id="desData">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Valor</label>
                                    <input type="text" inputmode="decimal" class="form-control input-money" id="desValor" placeholder="0,00">
                                </div>
                            </div>
                        </div>
                        <div id="despesaCartao" class="des-panel" style="display:none;">
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Cartão</label>
                                    <input type="text" class="form-control" id="desCartaoReadonly" readonly placeholder="—">
                                </div>
                                <div class="col-12 col-md-6" id="desFaturaWrapper" style="display:none;">
                                    <label class="form-label fw-semibold">Fatura</label>
                                    <select class="form-select" id="desFatura">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Envelope</label>
                                    <select class="form-select" id="desEnvelopeCartao">
                                        <option value="">Selecione...</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Data</label>
                                    <input type="date" class="form-control" id="desDataCartao">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Valor</label>
                                    <input type="text" inputmode="decimal" class="form-control input-money" id="desValorCartao" placeholder="0,00">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descrição</label>
                            <input type="text" class="form-control" id="desDesc" placeholder="Descrição da despesa">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary btn-actions" id="btnSalvar">
                            <i class="fa-solid fa-check me-2"></i>Salvar alterações
                        </button>
                        <a href="<?= base_url('lancamentos') ?>" class="btn btn-outline-secondary btn-actions">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    const lancamentoId = Number(document.getElementById('lancamentoId').value || 0);
    let cacheContas = [];
    let cacheEnvelopes = [];
    let cacheCartoes = [];
    let dadosLancamento = null;
    let tipoLancamento = '';

    function opt(v, t) { return `<option value="${v}">${t}</option>`; }

    function showLoading(show) {
        document.getElementById('loadingState').style.display = show ? 'block' : 'none';
    }
    function showError(show, msg) {
        const el = document.getElementById('errorState');
        el.style.display = show ? 'block' : 'none';
        if (msg) document.getElementById('errorMsg').textContent = msg;
    }
    function showForm(show) {
        document.getElementById('formContainer').style.display = show ? 'block' : 'none';
    }

    async function carregarBase() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) return false;
        cacheContas = r.data?.Contas ?? [];
        cacheEnvelopes = r.data?.Envelopes ?? [];
        cacheCartoes = r.data?.CartoesCredito ?? [];
        return true;
    }

    function preencherSelects() {
        const contasOpt = cacheContas.map(c => opt(c.ContaId, c.Nome)).join('');
        document.getElementById('recConta').innerHTML = '<option value="">Selecione...</option>' + contasOpt;
        document.getElementById('desConta').innerHTML = '<option value="">Selecione...</option>' + contasOpt;
        const envOpt = cacheEnvelopes.map(e => opt(e.EnvelopeId, e.Nome)).join('');
        document.getElementById('desEnvelope').innerHTML = '<option value="">Selecione...</option>' + envOpt;
        document.getElementById('desEnvelopeCartao').innerHTML = '<option value="">Selecione...</option>' + envOpt;
    }

    function addLinhaRateio(envId = '', modo = 'valor', valor = '') {
        const envOpts = cacheEnvelopes.map(e => `<option value="${e.EnvelopeId}" ${e.EnvelopeId == envId ? 'selected' : ''}>${e.Nome}</option>`).join('');
        const tbody = document.getElementById('tbRateio');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select class="form-select form-select-sm rateio-envelope"><option value="">Selecione...</option>${envOpts}</select></td>
            <td><select class="form-select form-select-sm rateio-modo"><option value="valor" ${modo==='percentual'?'':'selected'}>Valor</option><option value="percentual" ${modo==='percentual'?'selected':''}>%</option></select></td>
            <td><input type="text" inputmode="decimal" class="form-control form-control-sm rateio-valor input-money" value="${valor}" placeholder="0,00"></td>
            <td><button class="btn btn-sm btn-outline-danger btnRemoveRateio" type="button"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
        if (typeof Envelopei !== 'undefined' && Envelopei.applyMoneyMask) Envelopei.applyMoneyMask(tr.querySelector('.rateio-valor'));
        tr.querySelector('.btnRemoveRateio').addEventListener('click', () => { tr.remove(); calcSomaRateio(); });
        tr.querySelector('.rateio-valor').addEventListener('input', calcSomaRateio);
        tr.querySelector('.rateio-modo').addEventListener('change', calcSomaRateio);
    }

    function calcSomaRateio() {
        const total = Array.from(document.querySelectorAll('#tbRateio .rateio-valor')).reduce((a, i) => a + (Envelopei ? Envelopei.parseMoney(i.value) : 0), 0);
        const el = document.getElementById('rateioSoma');
        if (el) el.textContent = (typeof Envelopei !== 'undefined' && Envelopei.money ? Envelopei.money(total) : total.toFixed(2));
    }

    async function carregarLancamento() {
        if (!lancamentoId) {
            showLoading(false);
            showError(true, 'ID do lançamento inválido.');
            return;
        }
        const r = await Envelopei.api('api/lancamentos/' + lancamentoId, 'GET');
        showLoading(false);
        if (!r?.success) {
            showError(true, r?.message ?? 'Lançamento não encontrado.');
            return;
        }
        dadosLancamento = r.data;
        const l = dadosLancamento?.Lancamento ?? {};
        tipoLancamento = (l.TipoLancamento || '').toLowerCase();

        if (tipoLancamento !== 'receita' && tipoLancamento !== 'despesa') {
            showError(true, 'Este tipo de lançamento não pode ser editado.');
            return;
        }

        const ok = await carregarBase();
        if (!ok) {
            showError(true, 'Falha ao carregar contas e envelopes.');
            return;
        }
        preencherSelects();

        if (tipoLancamento === 'receita') {
            document.getElementById('painelReceita').style.display = 'block';
            document.getElementById('iconReceita').style.display = 'block';
            document.getElementById('tituloForm').textContent = 'Editar receita';
            document.getElementById('subtituloForm').textContent = 'Altere os campos da receita';

            document.getElementById('recData').value = (l.DataLancamento || '').toString().slice(0, 10);
            document.getElementById('recDesc').value = (l.Descricao || '');

            const itensConta = dadosLancamento?.ItensConta ?? [];
            const valorTotal = itensConta.length ? Math.abs(Number(itensConta[0].Valor || 0)) : 0;
            document.getElementById('recValor').value = typeof Envelopei !== 'undefined' && Envelopei.formatMoneyForInput ? Envelopei.formatMoneyForInput(valorTotal) : valorTotal.toFixed(2);
            if (itensConta.length) document.getElementById('recConta').value = itensConta[0].ContaId;

            const rateios = dadosLancamento?.Rateios ?? [];
            document.getElementById('tbRateio').innerHTML = '';
            rateios.forEach(rr => {
                const modo = rr.ModoRateio || 'valor';
                const val = rr.ValorInformado ?? rr.ValorCalculado ?? 0;
                addLinhaRateio(rr.EnvelopeId, modo, typeof Envelopei !== 'undefined' && Envelopei.formatMoneyForInput ? Envelopei.formatMoneyForInput(val) : val);
            });
            if (rateios.length === 0) addLinhaRateio();
            calcSomaRateio();
        } else {
            document.getElementById('painelDespesa').style.display = 'block';
            document.getElementById('iconDespesa').style.display = 'block';
            document.getElementById('tituloForm').textContent = 'Editar despesa';
            document.getElementById('subtituloForm').textContent = 'Altere os campos da despesa';

            document.getElementById('desDesc').value = (l.Descricao || '');

            const cartaoId = Number(l.CartaoCreditoId || 0);
            const itensConta = dadosLancamento?.ItensConta ?? [];
            const itensEnv = dadosLancamento?.ItensEnvelope ?? [];

            if (cartaoId > 0) {
                document.getElementById('despesaVista').style.display = 'none';
                document.getElementById('despesaCartao').style.display = 'block';
                const cartao = cacheCartoes.find(c => Number(c.CartaoCreditoId) === cartaoId);
                document.getElementById('desCartaoReadonly').value = cartao ? (cartao.Nome + (cartao.Ultimos4Digitos ? ' ****' + cartao.Ultimos4Digitos : '')) : 'Cartão';
                document.getElementById('desDataCartao').value = (l.DataLancamento || '').toString().slice(0, 10);
                const valEnv = itensEnv.length ? Math.abs(Number(itensEnv[0].Valor || 0)) : 0;
                document.getElementById('desValorCartao').value = typeof Envelopei !== 'undefined' && Envelopei.formatMoneyForInput ? Envelopei.formatMoneyForInput(valEnv) : valEnv.toFixed(2);
                if (itensEnv.length) document.getElementById('desEnvelopeCartao').value = itensEnv[0].EnvelopeId;

                const faturaId = Number(l.FaturaId || 0);
                if (faturaId > 0) {
                    const rF = await Envelopei.api('api/faturas/cartao/' + cartaoId, 'GET');
                    if (rF?.success && Array.isArray(rF.data) && rF.data.length > 0) {
                        document.getElementById('desFaturaWrapper').style.display = 'block';
                        const fatOpts = rF.data.map(f => opt(f.FaturaId, `Fatura ${String(f.MesReferencia).padStart(2,'0')}/${f.AnoReferencia}`)).join('');
                        document.getElementById('desFatura').innerHTML = '<option value="">Selecione...</option>' + fatOpts;
                        document.getElementById('desFatura').value = faturaId;
                    }
                }
            } else {
                document.getElementById('despesaVista').style.display = 'block';
                document.getElementById('despesaCartao').style.display = 'none';
                document.getElementById('desData').value = (l.DataLancamento || '').toString().slice(0, 10);
                const valEnv = itensEnv.length ? Math.abs(Number(itensEnv[0].Valor || 0)) : 0;
                document.getElementById('desValor').value = typeof Envelopei !== 'undefined' && Envelopei.formatMoneyForInput ? Envelopei.formatMoneyForInput(valEnv) : valEnv.toFixed(2);
                if (itensConta.length) document.getElementById('desConta').value = itensConta[0].ContaId;
                if (itensEnv.length) document.getElementById('desEnvelope').value = itensEnv[0].EnvelopeId;
            }
        }

        showForm(true);
    }

    async function salvar() {
        const id = lancamentoId;
        if (!id) return;

        let payload = {
            DataLancamento: null,
            Descricao: (tipoLancamento === 'receita' ? document.getElementById('recDesc') : document.getElementById('desDesc')).value.trim(),
        };

        if (tipoLancamento === 'receita') {
            payload.DataLancamento = document.getElementById('recData').value;
            payload.ContaId = Number(document.getElementById('recConta').value || 0);
            payload.ValorTotal = Envelopei ? Envelopei.parseMoney(document.getElementById('recValor').value) : parseFloat(document.getElementById('recValor').value) || 0;
            if (!payload.ContaId) return Envelopei.toast('Selecione a conta.', 'danger');
            if (!payload.ValorTotal || payload.ValorTotal <= 0) return Envelopei.toast('Informe o valor total.', 'danger');
            payload.Rateios = Array.from(document.querySelectorAll('#tbRateio tr')).map(tr => ({
                EnvelopeId: Number(tr.querySelector('.rateio-envelope')?.value || 0),
                ModoRateio: tr.querySelector('.rateio-modo')?.value || 'valor',
                ValorInformado: Envelopei ? Envelopei.parseMoney(tr.querySelector('.rateio-valor')?.value) : parseFloat(tr.querySelector('.rateio-valor')?.value) || 0,
            })).filter(r => r.EnvelopeId && r.ValorInformado > 0);
            if (payload.Rateios.length === 0) return Envelopei.toast('Informe o rateio.', 'danger');
        } else {
            const cartaoId = Number(dadosLancamento?.Lancamento?.CartaoCreditoId || 0);
            if (cartaoId > 0) {
                payload.DataLancamento = document.getElementById('desDataCartao').value;
                payload.EnvelopeId = Number(document.getElementById('desEnvelopeCartao').value || 0);
                payload.Valor = Envelopei ? Envelopei.parseMoney(document.getElementById('desValorCartao').value) : parseFloat(document.getElementById('desValorCartao').value) || 0;
                const fid = document.getElementById('desFatura').value;
                if (fid) payload.FaturaId = Number(fid);
            } else {
                payload.DataLancamento = document.getElementById('desData').value;
                payload.ContaId = Number(document.getElementById('desConta').value || 0);
                payload.EnvelopeId = Number(document.getElementById('desEnvelope').value || 0);
                payload.Valor = Envelopei ? Envelopei.parseMoney(document.getElementById('desValor').value) : parseFloat(document.getElementById('desValor').value) || 0;
            }
            if (!payload.EnvelopeId) return Envelopei.toast('Selecione o envelope.', 'danger');
            if (!payload.Valor || payload.Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');
        }

        const r = await Envelopei.api('api/lancamentos/' + id, 'PUT', payload);
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');
        Envelopei.toast('Lançamento atualizado!', 'success');
        window.location.href = '<?= base_url('lancamentos') ?>';
    }

    document.getElementById('btnAddRateio').addEventListener('click', () => { addLinhaRateio(); calcSomaRateio(); });
    document.getElementById('btnSalvar').addEventListener('click', salvar);

    document.addEventListener('DOMContentLoaded', () => {
        carregarLancamento();
    });
</script>
<?= $this->endSection() ?>
