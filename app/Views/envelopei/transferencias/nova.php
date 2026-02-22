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
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="fa-solid fa-right-left fa-lg text-primary"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">Transferência entre Envelopes</h4>
                        <p class="text-muted small mb-0">Mova valor de um envelope para outro (não altera contas)</p>
                    </div>
                </div>

                <div class="alert alert-info small mb-4">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    A transferência é <strong>entre envelopes</strong>. O saldo das contas bancárias não é alterado.
                </div>

                <form id="formTransferencia">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Envelope origem</label>
                            <select class="form-select form-select-lg" id="trEnvOrigem">
                                <option value="">Selecione o envelope de origem...</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Envelope destino</label>
                            <select class="form-select form-select-lg" id="trEnvDestino">
                                <option value="">Selecione o envelope de destino...</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Data</label>
                            <input type="date" class="form-control form-control-lg" id="trData">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Valor</label>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" id="trValor" placeholder="0,00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descrição</label>
                            <input type="text" class="form-control form-control-lg" id="trDesc" placeholder="Ajuste, realocação...">
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2 border-top">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-lg" id="btnSalvarTransferencia">
                            <i class="fa-solid fa-check me-2"></i>Transferir
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

    function opt(v, t) { return `<option value="${v}">${t}</option>`; }

    async function carregarDados() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
            return;
        }
        cacheEnvelopes = r.data?.Envelopes ?? [];
        const envHtml = cacheEnvelopes.map(e => opt(e.EnvelopeId, `${e.Nome} (${Envelopei.money(e.Saldo)})`)).join('');
        document.getElementById('trEnvOrigem').innerHTML = '<option value="">Selecione o envelope de origem...</option>' + envHtml;
        document.getElementById('trEnvDestino').innerHTML = '<option value="">Selecione o envelope de destino...</option>' + envHtml;
        document.getElementById('trData').value = new Date().toISOString().slice(0, 10);
    }

    document.getElementById('formTransferencia').addEventListener('submit', async function(e) {
        e.preventDefault();
        const EnvelopeOrigemId = Number(document.getElementById('trEnvOrigem').value || 0);
        const EnvelopeDestinoId = Number(document.getElementById('trEnvDestino').value || 0);
        const DataLancamento = document.getElementById('trData').value;
        const Valor = Number(document.getElementById('trValor').value || 0);
        const Descricao = (document.getElementById('trDesc').value || '').trim();

        if (!EnvelopeOrigemId) return Envelopei.toast('Selecione o envelope origem.', 'danger');
        if (!EnvelopeDestinoId) return Envelopei.toast('Selecione o envelope destino.', 'danger');
        if (EnvelopeOrigemId === EnvelopeDestinoId) return Envelopei.toast('Origem e destino não podem ser iguais.', 'danger');
        if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');

        const r = await Envelopei.api('api/transferencias/envelopes', 'POST', { EnvelopeOrigemId, EnvelopeDestinoId, DataLancamento, Valor, Descricao });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao transferir.', 'danger');
        Envelopei.toast('Transferência realizada!', 'success');
        window.location.href = '<?= base_url('dashboard') ?>';
    });

    document.addEventListener('DOMContentLoaded', carregarDados);
</script>
<?= $this->endSection() ?>
