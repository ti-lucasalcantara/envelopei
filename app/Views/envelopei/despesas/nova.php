<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .page-despesa .card-despesa { border-radius: 12px; overflow: hidden; }
    .page-despesa .card-despesa .card-header-despesa { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .page-despesa .card-despesa .card-body-despesa { padding: 1.5rem 1.5rem 1.75rem; }
    .page-despesa .despesa-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .page-despesa .form-label { font-size: 0.875rem; color: #495057; margin-bottom: 0.35rem; }
    .page-despesa .form-control, .page-despesa .form-select { border-radius: 8px; }
    .page-despesa .form-control:focus, .page-despesa .form-select:focus { border-color: #dc3545; box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15); }
    .page-despesa .btn-group .btn { border-radius: 8px; }
    .page-despesa .btn-actions { border-radius: 8px; padding: 0.5rem 1.25rem; }
    .page-despesa .divider-despesa { border: 0; height: 1px; background: linear-gradient(90deg, transparent, #e9ecef 20%, #e9ecef 80%, transparent); margin: 1.5rem 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-despesa">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar ao Dashboard
            </a>

            <div class="card card-despesa shadow-sm border-0 mb-4">
                <div class="card-header-despesa bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="despesa-icon bg-danger bg-opacity-10">
                            <i class="fa-solid fa-circle-minus text-danger"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold">Nova Despesa</h4>
                            <p class="text-muted small mb-0 mt-1">Registre uma saída à vista ou no cartão de crédito</p>
                        </div>
                    </div>
                </div>
                <div class="card-body-despesa">
                    <form id="formDespesa">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Forma de pagamento</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="desForma" id="desFormaVista" value="vista" checked>
                                <label class="btn btn-outline-primary" for="desFormaVista"><i class="fa-solid fa-money-bill me-2"></i>À vista</label>
                                <input type="radio" class="btn-check" name="desForma" id="desFormaCartao" value="cartao">
                                <label class="btn btn-outline-primary" for="desFormaCartao"><i class="fa-solid fa-credit-card me-2"></i>Cartão de crédito</label>
                            </div>
                        </div>

                        <div id="despainelVista" class="des-painel">
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Conta</label>
                                    <select class="form-select" id="desConta">
                                        <option value="">Selecione a conta...</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Envelope</label>
                                    <select class="form-select" id="desEnvelope">
                                        <option value="">Selecione o envelope...</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Data</label>
                                    <input type="date" class="form-control" id="desData">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Valor</label>
                                    <input type="text" inputmode="decimal" class="form-control input-money" id="desValor" placeholder="0,00">
                                </div>
                            </div>
                        </div>

                        <div id="despainelCartao" class="des-painel" style="display:none;">
                            <div class="alert alert-info small mb-3">
                                <i class="fa-solid fa-circle-info me-2"></i>
                                O valor será debitado do envelope quando a fatura for paga.
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Cartão de crédito</label>
                                    <select class="form-select" id="desCartao">
                                        <option value="">Selecione o cartão...</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Envelope</label>
                                    <select class="form-select" id="desEnvelopeCartao">
                                        <option value="">Selecione o envelope...</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Data</label>
                                    <input type="date" class="form-control" id="desDataCartao">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Valor</label>
                                    <input type="text" inputmode="decimal" class="form-control input-money" id="desValorCartao" placeholder="0,00">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Parcelas</label>
                                    <input type="number" min="1" class="form-control" id="desParcelas" value="1" placeholder="1">
                                </div>
                                <div class="col-12 col-md-6 d-flex align-items-end">
                                    <div class="small text-muted" id="desValorParcelaInfo">Valor de cada parcela: —</div>
                                </div>
                            </div>
                        </div>

                        <hr class="divider-despesa">

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Descrição</label>
                            <input type="text" class="form-control" id="desDesc" placeholder="Mercado, gasolina, farmácia...">
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4 pt-4 border-top">
                            <button type="button" class="btn btn-danger btn-actions" id="btnSalvarDespesa">
                                <i class="fa-solid fa-check me-2"></i>Salvar
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-actions" id="btnSalvarCriarNovaDespesa">
                                <i class="fa-solid fa-plus me-2"></i>Salvar e criar nova
                            </button>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-actions">Cancelar</a>
                        </div>
                    </form>
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
    let cacheCartoes = [];

    function opt(v, t) { return `<option value="${v}">${t}</option>`; }

    function togglePainel() {
        const forma = document.querySelector('input[name="desForma"]:checked')?.value || 'vista';
        document.getElementById('despainelVista').style.display = forma === 'vista' ? 'block' : 'none';
        document.getElementById('despainelCartao').style.display = forma === 'cartao' ? 'block' : 'none';
    }

    function setDataHoje() {
        const hoje = new Date().toISOString().slice(0, 10);
        document.getElementById('desData').value = hoje;
        document.getElementById('desDataCartao').value = hoje;
    }

    function atualizarValorParcelaInfo() {
        const valor = Envelopei.parseMoney(document.getElementById('desValorCartao').value);
        const parcelas = Math.max(1, Math.floor(Number(document.getElementById('desParcelas').value) || 1));
        const el = document.getElementById('desValorParcelaInfo');
        if (!el) return;
        if (!valor || valor <= 0) {
            el.textContent = 'Valor de cada parcela: —';
            return;
        }
        el.textContent = 'Valor de cada parcela: ' + Envelopei.money(valor / parcelas);
    }

    function resetFormDespesa() {
        document.getElementById('desFormaVista').checked = true;
        togglePainel();
        document.getElementById('desConta').value = '';
        document.getElementById('desEnvelope').value = '';
        document.getElementById('desCartao').value = '';
        document.getElementById('desEnvelopeCartao').value = '';
        document.getElementById('desParcelas').value = '1';
        document.getElementById('desDesc').value = '';
        document.getElementById('desValor').value = '';
        document.getElementById('desValorCartao').value = '';
        setDataHoje();
        atualizarValorParcelaInfo();
    }

    async function carregarDados() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
            return;
        }
        cacheContas = r.data?.Contas ?? [];
        cacheEnvelopes = r.data?.Envelopes ?? [];
        cacheCartoes = r.data?.CartoesCredito ?? [];

        const contasHtml = cacheContas.map(c => opt(c.ContaId, `${c.Nome} (${Envelopei.money(c.SaldoAtual)})`)).join('');
        document.getElementById('desConta').innerHTML = '<option value="">Selecione a conta...</option>' + contasHtml;

        const envHtml = cacheEnvelopes.map(e => opt(e.EnvelopeId, `${e.Nome} (${Envelopei.money(e.Saldo)})`)).join('');
        document.getElementById('desEnvelope').innerHTML = '<option value="">Selecione o envelope...</option>' + envHtml;
        document.getElementById('desEnvelopeCartao').innerHTML = '<option value="">Selecione o envelope...</option>' + envHtml;

        const cartoesHtml = cacheCartoes.map(c => opt(c.CartaoCreditoId, `${c.Nome}${c.Ultimos4Digitos ? ' ****' + c.Ultimos4Digitos : ''}`)).join('');
        document.getElementById('desCartao').innerHTML = '<option value="">Selecione o cartão...</option>' + cartoesHtml;

        setDataHoje();
    }

    async function salvarDespesa(criarNova) {
        const forma = document.querySelector('input[name="desForma"]:checked')?.value || 'vista';
        const Descricao = (document.getElementById('desDesc').value || '').trim();
        let payload = { Descricao };

        if (forma === 'vista') {
            const ContaId = Number(document.getElementById('desConta').value || 0);
            const EnvelopeId = Number(document.getElementById('desEnvelope').value || 0);
            const DataLancamento = document.getElementById('desData').value;
            const Valor = Envelopei.parseMoney(document.getElementById('desValor').value);
            if (!ContaId) return Envelopei.toast('Selecione a conta.', 'danger');
            if (!EnvelopeId) return Envelopei.toast('Selecione o envelope.', 'danger');
            if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');
            payload = { ...payload, ContaId, EnvelopeId, DataLancamento, Valor };
        } else {
            const CartaoCreditoId = Number(document.getElementById('desCartao').value || 0);
            const EnvelopeId = Number(document.getElementById('desEnvelopeCartao').value || 0);
            const DataLancamento = document.getElementById('desDataCartao').value;
            const Valor = Envelopei.parseMoney(document.getElementById('desValorCartao').value);
            let Parcelas = Math.floor(Number(document.getElementById('desParcelas').value) || 1);
            if (Parcelas < 1) Parcelas = 1;
            if (!CartaoCreditoId) return Envelopei.toast('Selecione o cartão.', 'danger');
            if (!EnvelopeId) return Envelopei.toast('Selecione o envelope.', 'danger');
            if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');
            payload = { ...payload, CartaoCreditoId, EnvelopeId, DataLancamento, Valor, Parcelas };
        }

        const r = await Envelopei.api('api/despesas', 'POST', payload);
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');

        Envelopei.toast(forma === 'cartao' ? 'Despesa no cartão registrada com sucesso!' : 'Despesa registrada com sucesso!', 'success');

        if (criarNova) {
            resetFormDespesa();
        } else {
            window.location.href = '<?= base_url('dashboard') ?>';
        }
    }

    document.querySelectorAll('input[name="desForma"]').forEach(el => el.addEventListener('change', togglePainel));

    document.getElementById('desValorCartao').addEventListener('input', atualizarValorParcelaInfo);
    document.getElementById('desValorCartao').addEventListener('blur', atualizarValorParcelaInfo);
    document.getElementById('desParcelas').addEventListener('input', atualizarValorParcelaInfo);
    document.getElementById('desParcelas').addEventListener('change', atualizarValorParcelaInfo);

    document.getElementById('btnSalvarDespesa').addEventListener('click', () => salvarDespesa(false));
    document.getElementById('btnSalvarCriarNovaDespesa').addEventListener('click', () => salvarDespesa(true));

    document.addEventListener('DOMContentLoaded', () => {
        carregarDados();
        atualizarValorParcelaInfo();
    });
</script>
<?= $this->endSection() ?>
