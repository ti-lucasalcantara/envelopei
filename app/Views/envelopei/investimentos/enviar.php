<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .page-investimento .card-invest { border-radius: 12px; overflow: hidden; }
    .page-investimento .card-invest .card-header-invest { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .page-investimento .card-invest .card-body-invest { padding: 1.5rem 1.5rem 1.75rem; }
    .page-investimento .invest-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .page-investimento .form-label { font-size: 0.875rem; color: #495057; margin-bottom: 0.35rem; }
    .page-investimento .form-control, .page-investimento .form-select { border-radius: 8px; }
    .page-investimento .btn-actions { border-radius: 8px; padding: 0.5rem 1.25rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-investimento">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <a href="<?= base_url('investimentos') ?>" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar aos Investimentos
            </a>

            <div class="card card-invest shadow-sm border-0 mb-4">
                <div class="card-header-invest bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="invest-icon bg-info bg-opacity-10">
                            <i class="fa-solid fa-chart-line text-info"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold">Enviar para investimento</h4>
                            <p class="text-muted small mb-0 mt-1">O valor sai do envelope e da conta e vai para a conta de investimentos (não é despesa)</p>
                        </div>
                    </div>
                </div>
                <div class="card-body-invest">
                    <div class="alert alert-info small mb-4">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Escolha o <strong>envelope</strong> e a <strong>conta</strong> de origem. O valor será debitado dos dois e creditado na sua conta de investimentos.
                    </div>

                    <form id="formEnviarInvestimento">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Envelope de origem</label>
                                <select class="form-select" id="invEnvelopeId" required>
                                    <option value="">Selecione o envelope...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Conta de origem</label>
                                <select class="form-select" id="invContaId" required>
                                    <option value="">Selecione a conta...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Data</label>
                                <input type="date" class="form-control" id="invData">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Valor</label>
                                <input type="text" inputmode="decimal" class="form-control input-money" id="invValor" placeholder="0,00" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Descrição</label>
                            <input type="text" class="form-control" id="invDesc" placeholder="Ex: Aplicação mensal">
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-4 pt-4 border-top">
                            <button type="submit" class="btn btn-info btn-actions" id="btnEnviar">
                                <i class="fa-solid fa-paper-plane me-2"></i>Enviar para investimento
                            </button>
                            <a href="<?= base_url('investimentos') ?>" class="btn btn-outline-secondary btn-actions">Cancelar</a>
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

    function opt(v, t) { return `<option value="${v}">${t}</option>`; }

    async function carregarDados() {
        const r = await Envelopei.api('api/investimentos/resumo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
            return;
        }
        cacheEnvelopes = r.data?.Envelopes ?? [];
        cacheContas = r.data?.ContasOrigem ?? [];
        document.getElementById('invEnvelopeId').innerHTML = '<option value="">Selecione o envelope...</option>' +
            cacheEnvelopes.map(e => opt(e.EnvelopeId, `${e.Nome} (${Envelopei.money(e.Saldo)})`)).join('');
        document.getElementById('invContaId').innerHTML = '<option value="">Selecione a conta...</option>' +
            cacheContas.map(c => opt(c.ContaId, c.Nome)).join('');
        document.getElementById('invData').value = new Date().toISOString().slice(0, 10);
    }

    document.getElementById('formEnviarInvestimento').addEventListener('submit', async function(e) {
        e.preventDefault();
        const EnvelopeId = Number(document.getElementById('invEnvelopeId').value || 0);
        const ContaId = Number(document.getElementById('invContaId').value || 0);
        const DataLancamento = document.getElementById('invData').value;
        const Valor = Envelopei.parseMoney(document.getElementById('invValor').value);
        const Descricao = (document.getElementById('invDesc').value || '').trim() || 'Enviado para investimento';

        if (!EnvelopeId) return Envelopei.toast('Selecione o envelope.', 'danger');
        if (!ContaId) return Envelopei.toast('Selecione a conta.', 'danger');
        if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');

        const btn = document.getElementById('btnEnviar');
        btn.disabled = true;
        const r = await Envelopei.api('api/investimentos/enviar', 'POST', { EnvelopeId, ContaId, DataLancamento, Valor, Descricao });
        btn.disabled = false;
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao enviar.', 'danger');
        Envelopei.toast('Valor enviado para investimentos!', 'success');
        window.location.href = '<?= base_url('investimentos') ?>';
    });

    document.addEventListener('DOMContentLoaded', carregarDados);
</script>
<?= $this->endSection() ?>
