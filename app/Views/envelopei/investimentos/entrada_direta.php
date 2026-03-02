<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .page-investimento .card-invest { border-radius: 12px; overflow: hidden; }
    .page-investimento .card-invest .card-header-invest { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,.06); }
    .page-investimento .card-invest .card-body-invest { padding: 1.5rem 1.5rem 1.75rem; }
    .page-investimento .invest-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .page-investimento .btn-actions { border-radius: 8px; padding: 0.5rem 1.25rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-investimento">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <a href="<?= base_url('investimentos') ?>" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar aos Investimentos
            </a>

            <div class="card card-invest shadow-sm border-0 mb-4">
                <div class="card-header-invest bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="invest-icon bg-success bg-opacity-10">
                            <i class="fa-solid fa-wallet text-success"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold">Entrada direta em investimentos</h4>
                            <p class="text-muted small mb-0 mt-1">Adicione saldo na conta de investimentos sem passar por receita ou envelope</p>
                        </div>
                    </div>
                </div>
                <div class="card-body-invest">
                    <form id="formEntradaDireta">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Data</label>
                                <input type="date" class="form-control" id="edData">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Valor</label>
                                <input type="text" inputmode="decimal" class="form-control input-money" id="edValor" placeholder="0,00" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descrição</label>
                                <input type="text" class="form-control" id="edDesc" placeholder="Ex: Aporte direto">
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-4 pt-4 border-top">
                            <button type="submit" class="btn btn-success btn-actions" id="btnSalvar">
                                <i class="fa-solid fa-plus me-2"></i>Registrar entrada
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
    document.getElementById('edData').value = new Date().toISOString().slice(0, 10);

    document.getElementById('formEntradaDireta').addEventListener('submit', async function(e) {
        e.preventDefault();
        const DataLancamento = document.getElementById('edData').value;
        const Valor = Envelopei.parseMoney(document.getElementById('edValor').value);
        const Descricao = (document.getElementById('edDesc').value || '').trim() || 'Entrada direta em investimentos';

        if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');

        const btn = document.getElementById('btnSalvar');
        btn.disabled = true;
        const r = await Envelopei.api('api/investimentos/entrada-direta', 'POST', { DataLancamento, Valor, Descricao });
        btn.disabled = false;
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao registrar.', 'danger');
        Envelopei.toast('Entrada registrada!', 'success');
        window.location.href = '<?= base_url('investimentos') ?>';
    });
</script>
<?= $this->endSection() ?>
