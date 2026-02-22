<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .page-faturas .page-header-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .page-faturas .card-fatura { border-radius: 12px; overflow: hidden; }
    .page-faturas .card-fatura .card-header-custom { padding: 1rem 1.25rem; border-bottom: 1px solid rgba(0,0,0,.06); font-weight: 600; position: relative; z-index: 0; }
    .page-faturas .card-fatura .card-body-custom { padding: 1.25rem; position: relative; z-index: 1; }
    .page-faturas .filtro-group .btn-check:checked + label { font-weight: 600; }
    .page-faturas .table-faturas { border-radius: 8px; overflow: hidden; }
    .page-faturas .table-faturas thead th { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #6c757d; padding: 0.75rem 1rem; border-bottom: 1px solid #dee2e6; }
    .page-faturas .table-faturas tbody { position: relative; z-index: 2; }
    .page-faturas .table-faturas tbody tr { position: relative; z-index: 2; pointer-events: auto; }
    .page-faturas .table-faturas tbody td { padding: 0.85rem 1rem; vertical-align: middle; }
    .page-faturas .table-faturas tbody tr:hover { background-color: rgba(13, 110, 253, 0.04); }
    .page-faturas .td-acoes { position: relative; z-index: 3; pointer-events: auto; white-space: nowrap; }
    .page-faturas .td-acoes .btn { pointer-events: auto; cursor: pointer; }
    .page-faturas .td-acoes a.btn { text-decoration: none; }
    .page-faturas .badge-status-pago { background-color: #198754; color: #fff !important; padding: 0.35em 0.65em; border-radius: 0.25rem; font-size: 0.8em; }
    .page-faturas .badge-status-pendente { background-color: #ffc107; color: #212529 !important; padding: 0.35em 0.65em; border-radius: 0.25rem; font-size: 0.8em; }
    .page-faturas .badge-venc-venceu { background-color: #dc3545; color: #fff !important; padding: 0.35em 0.65em; border-radius: 0.25rem; }
    .page-faturas .badge-venc-hoje { background-color: #ffc107; color: #212529 !important; padding: 0.35em 0.65em; border-radius: 0.25rem; }
    .page-faturas .badge-venc-futuro { background-color: #6c757d; color: #fff !important; padding: 0.35em 0.65em; border-radius: 0.25rem; }
    .page-faturas .proximas-item { padding: 0.65rem 0; border-bottom: 1px solid #f0f0f0; transition: background 0.15s; }
    .page-faturas .proximas-item:last-child { border-bottom: 0; }
    .page-faturas .proximas-item:hover { background-color: rgba(13, 110, 253, 0.05); }
    .page-faturas .empty-state { padding: 2.5rem 1rem; text-align: center; color: #6c757d; }
    .page-faturas .empty-state i { font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.6; }
    .page-faturas #modalPagarFatura .modal-content { border-radius: 12px; border: 0; box-shadow: 0 10px 40px rgba(0,0,0,.15); }
    .page-faturas #modalPagarFatura .form-label { font-weight: 500; color: #495057; }
    .page-faturas #modalPagarFatura .form-control, .page-faturas #modalPagarFatura .form-select { border-radius: 8px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-faturas">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-header-icon bg-primary bg-opacity-10">
                <i class="fa-solid fa-file-invoice text-primary"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-semibold">Faturas</h4>
                <p class="text-muted small mb-0 mt-1">Consulte faturas do cartão e marque como pagas</p>
            </div>
        </div>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar ao Dashboard
        </a>
    </div>

    <div class="mb-3">
        <label class="form-label small text-muted text-uppercase fw-semibold mb-2 d-block">Filtrar por período</label>
        <div class="btn-group flex-wrap filtro-group" role="group">
            <input type="radio" class="btn-check" name="filtroFatura" id="btnFiltroTodos" value="todos" checked>
            <label class="btn btn-outline-primary" for="btnFiltroTodos">Todos</label>
            <input type="radio" class="btn-check" name="filtroFatura" id="btnFiltroAtual" value="atual">
            <label class="btn btn-outline-primary" for="btnFiltroAtual">Fatura atual</label>
            <input type="radio" class="btn-check" name="filtroFatura" id="btnFiltroAnteriores" value="anteriores">
            <label class="btn btn-outline-primary" for="btnFiltroAnteriores">Anteriores</label>
            <input type="radio" class="btn-check" name="filtroFatura" id="btnFiltroProximas" value="proximas">
            <label class="btn btn-outline-primary" for="btnFiltroProximas">Próximas</label>
        </div>
    </div>

    <!-- Lista de faturas -->
    <div class="card card-fatura shadow-sm">
        <div class="card-header-custom bg-light d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-list me-2 text-primary"></i>Lista de faturas</span>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table table-faturas align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cartão</th>
                            <th>Período</th>
                            <th>Vencimento</th>
                            <th class="text-end">Valor</th>
                            <th>Ações</th>
                            <th style="width:50px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tbFaturas">
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fa-solid fa-spinner fa-spin d-block"></i>
                                Carregando…
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pagar Fatura -->
<div class="modal fade" id="modalPagarFatura" tabindex="-1" aria-labelledby="modalPagarTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="modalPagarTitle">Marcar fatura como paga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <input type="hidden" id="pagarFaturaId">
                <div class="mb-3">
                    <label class="form-label" for="pagarContaId">Conta para débito</label>
                    <select class="form-select" id="pagarContaId">
                        <option value="">Selecione a conta...</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label" for="pagarData">Data do pagamento</label>
                    <input type="date" class="form-control" id="pagarData">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarPagar">
                    <i class="fa-solid fa-check me-2"></i>Marcar como paga
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let faturas = [];
    let contas = [];
    let filtroTipo = 'todos';

    function opt(valor, texto) {
        return `<option value="${valor}">${texto}</option>`;
    }

    async function carregarContas() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) return;
        contas = r.data?.Contas ?? [];
    }

    function aplicarFiltroPeriodo(lista) {
        if (filtroTipo === 'todos') return lista;
        const hoje = new Date();
        const mesAtual = hoje.getMonth() + 1;
        const anoAtual = hoje.getFullYear();
        const mesProximo = mesAtual === 12 ? 1 : mesAtual + 1;
        const anoProximo = mesAtual === 12 ? anoAtual + 1 : anoAtual;
        return lista.filter(f => {
            const mes = Number(f.MesReferencia) || 0;
            const ano = Number(f.AnoReferencia) || 0;
            if (filtroTipo === 'atual') return mes === mesProximo && ano === anoProximo;
            if (filtroTipo === 'anteriores') return ano < anoProximo || (ano === anoProximo && mes < mesProximo);
            if (filtroTipo === 'proximas') return ano > anoProximo || (ano === anoProximo && mes > mesProximo);
            return true;
        });
    }

    async function carregar() {
        const tb = document.getElementById('tbFaturas');
        try {
            const r = await Envelopei.api('api/faturas', 'GET');
            if (!r?.success) {
                Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
                tb.innerHTML = `<tr><td colspan="6" class="empty-state text-danger"><i class="fa-solid fa-triangle-exclamation"></i><br>${r?.message ?? 'Erro ao carregar.'}</td></tr>`;
                return;
            }
            faturas = Array.isArray(r.data) ? r.data : (r.data ?? []);
            render();
        } catch (e) {
            console.error(e);
            Envelopei.toast('Erro ao carregar faturas.', 'danger');
            tb.innerHTML = '<tr><td colspan="6" class="empty-state text-danger"><i class="fa-solid fa-triangle-exclamation"></i><br>Erro ao carregar.</td></tr>';
        }
    }

    function render() {
        const tb = document.getElementById('tbFaturas');
        const lista = aplicarFiltroPeriodo(faturas);

        if (!lista.length) {
            const msg = filtroTipo === 'todos' ? 'Nenhuma fatura cadastrada.' : 'Nenhuma fatura neste filtro.';
            tb.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fa-solid fa-inbox"></i><br>${msg}</td></tr>`;
            return;
        }

        tb.innerHTML = lista.map(f => {
            console.log(f);
            const periodo = `${String(f.MesReferencia).padStart(2, '0')}/${f.AnoReferencia}`;
            const pago = Number(f.Pago) === 1;
            const statusBadge = pago
                ? `<span class="badge badge-status-pago">Pago ${Envelopei.dateBR(f.DataPagamento)}</span>`
                : `<span class="badge badge-status-pendente">Pendente</span>`;

            return `
                <tr>
                    <td>
                        <span class="fw-semibold">${f.CartaoNome ?? ''}</span>
                        ${f.Ultimos4Digitos ? '<span class="text-muted small ms-1">****' + f.Ultimos4Digitos + '</span>' : ''}
                    </td>
                    <td>${periodo}</td>
                    <td>${Envelopei.dateBR(f.DataVencimento)}</td>
                    <td class="text-end fw-semibold">${Envelopei.money(f.ValorTotal)}</td>
                    <td>
                        <a href="<?= base_url('faturas') ?>/${f.FaturaId}" class="btn btn-sm btn-outline-primary me-1" title="Ver detalhes">
                            <i class="fa-solid fa-eye"></i> detalhes
                        </a>
                        ${!pago && f.ValorTotal > 0 ? `
                            <button type="button" class="btn btn-sm btn-success btn-pagar-fatura" data-fatura-id="${f.FaturaId}">
                                <i class="fa-solid fa-check me-1"></i>Pagar
                            </button>
                        ` : ''}
                    </td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        }).join('');
    }

    async function abrirModalPagar(faturaId) {
        await carregarContas();
        document.getElementById('pagarFaturaId').value = faturaId;
        document.getElementById('pagarData').value = new Date().toISOString().slice(0, 10);

        const sel = document.getElementById('pagarContaId');
        sel.innerHTML = '<option value="">Selecione a conta...</option>' + contas.map(c =>
            opt(c.ContaId, `${c.Nome} (${Envelopei.money(c.SaldoAtual)})`)
        ).join('');

        new bootstrap.Modal(document.getElementById('modalPagarFatura')).show();
    }

    async function confirmarPagar() {
        const FaturaId = Number(document.getElementById('pagarFaturaId').value);
        const ContaId = Number(document.getElementById('pagarContaId').value);
        const DataPagamento = document.getElementById('pagarData').value;

        if (!ContaId) return Envelopei.toast('Selecione a conta.', 'danger');

        const r = await Envelopei.api(`api/faturas/${FaturaId}/pagar`, 'POST', { ContaId, DataPagamento });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao registrar pagamento.', 'danger');

        Envelopei.toast('Fatura marcada como paga!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalPagarFatura')).hide();
        carregar();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('tbFaturas').addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-pagar-fatura');
            if (btn) {
                e.preventDefault();
                const id = parseInt(btn.getAttribute('data-fatura-id'), 10);
                if (id) abrirModalPagar(id);
            }
        });

        carregar();

        document.querySelectorAll('input[name="filtroFatura"]').forEach(radio => {
            radio.addEventListener('change', () => {
                filtroTipo = radio.value;
                render();
            });
        });
        document.getElementById('btnConfirmarPagar').addEventListener('click', confirmarPagar);

        const params = new URLSearchParams(window.location.search);
        if (params.get('cartao')) document.getElementById('btnFiltroProximas').click();

        const pagarFaturaId = params.get('pagar');
        if (pagarFaturaId) {
            const id = parseInt(pagarFaturaId, 10);
            if (id > 0) {
                carregar().then(() => abrirModalPagar(id));
                history.replaceState(null, '', '<?= base_url('faturas') ?>');
            }
        }
    });
</script>
<?= $this->endSection() ?>
