<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .page-transferencia .card-transferencia { border-radius: 12px; overflow: hidden; }
    .page-transferencia .card-transferencia .card-header-transferencia { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .page-transferencia .card-transferencia .card-body-transferencia { padding: 1.5rem 1.5rem 1.75rem; }
    .page-transferencia .transferencia-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .page-transferencia .form-label { font-size: 0.875rem; color: #495057; margin-bottom: 0.35rem; }
    .page-transferencia .form-control, .page-transferencia .form-select { border-radius: 8px; }
    .page-transferencia .form-control:focus, .page-transferencia .form-select:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15); }
    .page-transferencia .btn-actions { border-radius: 8px; padding: 0.5rem 1.25rem; }
    .page-transferencia .divider-transferencia { border: 0; height: 1px; background: linear-gradient(90deg, transparent, #e9ecef 20%, #e9ecef 80%, transparent); margin: 1.5rem 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-transferencia">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar ao Dashboard
            </a>

            <div class="card card-transferencia shadow-sm border-0 mb-4">
                <div class="card-header-transferencia bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="transferencia-icon bg-primary bg-opacity-10">
                            <i class="fa-solid fa-right-left text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold">Transferência entre Envelopes</h4>
                            <p class="text-muted small mb-0 mt-1">Mova valor de um envelope para outro (não altera contas)</p>
                        </div>
                    </div>
                </div>
                <div class="card-body-transferencia">
                    <div class="alert alert-info small mb-4">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        A transferência é <strong>entre envelopes</strong>. O saldo das contas bancárias não é alterado.
                    </div>

                    <form id="formTransferencia">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Envelope origem</label>
                                <select class="form-select" id="trEnvOrigem">
                                    <option value="">Selecione o envelope de origem...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Envelope destino</label>
                                <select class="form-select" id="trEnvDestino">
                                    <option value="">Selecione o envelope de destino...</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Data</label>
                                <input type="date" class="form-control" id="trData">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Valor</label>
                                <input type="text" inputmode="decimal" class="form-control input-money" id="trValor" placeholder="0,00">
                            </div>
                        </div>

                        <hr class="divider-transferencia">

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Descrição</label>
                            <input type="text" class="form-control" id="trDesc" placeholder="Ajuste, realocação...">
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4 pt-4 border-top">
                            <button type="submit" class="btn btn-primary btn-actions" id="btnSalvarTransferencia">
                                <i class="fa-solid fa-check me-2"></i>Transferir
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
        const Valor = Envelopei.parseMoney(document.getElementById('trValor').value);
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
