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
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        <i class="fa-solid fa-circle-minus fa-lg text-danger"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">Nova Despesa</h4>
                        <p class="text-muted small mb-0">Registre uma saída à vista ou no cartão de crédito</p>
                    </div>
                </div>

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
                                <select class="form-select form-select-lg" id="desConta">
                                    <option value="">Selecione a conta...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Envelope</label>
                                <select class="form-select form-select-lg" id="desEnvelope">
                                    <option value="">Selecione o envelope...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Data</label>
                                <input type="date" class="form-control form-control-lg" id="desData">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Valor</label>
                                <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" id="desValor" placeholder="0,00">
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
                                <select class="form-select form-select-lg" id="desCartao">
                                    <option value="">Selecione o cartão...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Envelope</label>
                                <select class="form-select form-select-lg" id="desEnvelopeCartao">
                                    <option value="">Selecione o envelope...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Data</label>
                                <input type="date" class="form-control form-control-lg" id="desDataCartao">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Valor</label>
                                <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" id="desValorCartao" placeholder="0,00">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Descrição</label>
                        <input type="text" class="form-control form-control-lg" id="desDesc" placeholder="Mercado, gasolina, farmácia...">
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-danger btn-lg" id="btnSalvarDespesa">
                            <i class="fa-solid fa-check me-2"></i>Salvar Despesa
                        </button>
                    </div>
                </form>
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

        const hoje = new Date().toISOString().slice(0, 10);
        document.getElementById('desData').value = hoje;
        document.getElementById('desDataCartao').value = hoje;
    }

    document.querySelectorAll('input[name="desForma"]').forEach(el => el.addEventListener('change', togglePainel));

    document.getElementById('formDespesa').addEventListener('submit', async function(e) {
        e.preventDefault();
        const forma = document.querySelector('input[name="desForma"]:checked')?.value || 'vista';
        const Descricao = (document.getElementById('desDesc').value || '').trim();
        let payload = { Descricao };

        if (forma === 'vista') {
            const ContaId = Number(document.getElementById('desConta').value || 0);
            const EnvelopeId = Number(document.getElementById('desEnvelope').value || 0);
            const DataLancamento = document.getElementById('desData').value;
            const Valor = Number(document.getElementById('desValor').value || 0);
            if (!ContaId) return Envelopei.toast('Selecione a conta.', 'danger');
            if (!EnvelopeId) return Envelopei.toast('Selecione o envelope.', 'danger');
            if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');
            payload = { ...payload, ContaId, EnvelopeId, DataLancamento, Valor };
        } else {
            const CartaoCreditoId = Number(document.getElementById('desCartao').value || 0);
            const EnvelopeId = Number(document.getElementById('desEnvelopeCartao').value || 0);
            const DataLancamento = document.getElementById('desDataCartao').value;
            const Valor = Number(document.getElementById('desValorCartao').value || 0);
            if (!CartaoCreditoId) return Envelopei.toast('Selecione o cartão.', 'danger');
            if (!EnvelopeId) return Envelopei.toast('Selecione o envelope.', 'danger');
            if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');
            payload = { ...payload, CartaoCreditoId, EnvelopeId, DataLancamento, Valor };
        }

        const r = await Envelopei.api('api/despesas', 'POST', payload);
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');
        Envelopei.toast(forma === 'cartao' ? 'Despesa no cartão registrada!' : 'Despesa registrada!', 'success');
        window.location.href = '<?= base_url('dashboard') ?>';
    });

    document.addEventListener('DOMContentLoaded', () => {
        carregarDados();
    });
</script>
<?= $this->endSection() ?>
