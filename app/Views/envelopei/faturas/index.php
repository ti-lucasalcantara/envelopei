<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Faturas</h4>
        <div class="text-muted">Consulte faturas e marque como pagas</div>
    </div>
    <div class="btn-group">
        <button class="btn btn-outline-primary" id="btnFiltroTodas">Todas</button>
        <button class="btn btn-outline-primary" id="btnFiltroPendentes">Pendentes</button>
    </div>
</div>

<!-- Próximas faturas a vencer -->
<div class="card shadow-sm mb-3" id="cardProximas">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fa-solid fa-clock me-2"></i>Próximas faturas a vencer</h6>
    </div>
    <div class="card-body" id="proximasBody">
        <div class="text-muted small">Carregando…</div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cartão</th>
                        <th>Período</th>
                        <th>Vencimento</th>
                        <th class="text-end">Valor</th>
                        <th>Status</th>
                        <th style="width:140px;"></th>
                    </tr>
                </thead>
                <tbody id="tbFaturas">
                    <tr><td colspan="6" class="text-center text-muted py-4">Carregando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PAGAR FATURA -->
<div class="modal fade" id="modalPagarFatura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marcar fatura como paga</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pagarFaturaId">
                <div class="mb-3">
                    <label class="form-label">Conta para débito</label>
                    <select class="form-select" id="pagarContaId"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data do pagamento</label>
                    <input type="date" class="form-control" id="pagarData">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" id="btnConfirmarPagar">
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
    let filtroPendentes = null;

    function opt(valor, texto) {
        return `<option value="${valor}">${texto}</option>`;
    }

    async function carregarContas() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) return;
        contas = r.data?.Contas ?? [];
    }

    async function carregar() {
        const tb = document.getElementById('tbFaturas');
        try {
            const url = filtroPendentes === true ? 'api/faturas?Pendentes=1' : (filtroPendentes === false ? 'api/faturas?Pendentes=0' : 'api/faturas');
            const r = await Envelopei.api(url, 'GET');
            if (!r?.success) {
                Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
                tb.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${r?.message ?? 'Erro ao carregar.'}</td></tr>`;
                return;
            }

            faturas = Array.isArray(r.data) ? r.data : (r.data ?? []);
            render();
        } catch (e) {
            console.error(e);
            Envelopei.toast('Erro ao carregar faturas.', 'danger');
            tb.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Erro ao carregar.</td></tr>';
        }
    }

    async function carregarProximas() {
        const body = document.getElementById('proximasBody');
        try {
            const r = await Envelopei.api('api/faturas/proximas?Limite=5', 'GET');
            if (!r?.success) {
                body.innerHTML = '<div class="text-muted small">Não foi possível carregar.</div>';
                return;
            }

            const proximas = Array.isArray(r.data) ? r.data : (r.data ?? []);

            if (proximas.length === 0) {
                body.innerHTML = '<div class="text-muted small">Nenhuma fatura pendente a vencer.</div>';
                return;
            }

            body.innerHTML = proximas.map(f => {
            const venc = f.DataVencimento || '-';
            const hoje = new Date().toISOString().slice(0, 10);
            const badge = venc < hoje ? 'text-bg-danger' : (venc === hoje ? 'text-bg-warning' : 'text-bg-secondary');
            return `
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <span class="fw-semibold">${f.CartaoNome ?? ''} ****${f.Ultimos4Digitos ?? '????'}</span>
                        <span class="badge ${badge} ms-2">Vence ${Envelopei.dateBR(venc)}</span>
                    </div>
                    <span class="fw-bold">${Envelopei.money(f.ValorTotal)}</span>
                </div>
            `;
        }).join('');
        } catch (e) {
            console.error(e);
            body.innerHTML = '<div class="text-muted small">Erro ao carregar.</div>';
        }
    }

    function render() {
        const tb = document.getElementById('tbFaturas');

        if (!faturas.length) {
            tb.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Nenhuma fatura.</td></tr>`;
            return;
        }

        tb.innerHTML = faturas.map(f => {
            const periodo = `${String(f.MesReferencia).padStart(2, '0')}/${f.AnoReferencia}`;
            const pago = Number(f.Pago) === 1;
            const statusBadge = pago
                ? `<span class="badge text-bg-success">Pago ${Envelopei.dateBR(f.DataPagamento)}</span>`
                : `<span class="badge text-bg-warning">Pendente</span>`;

            return `
                <tr>
                    <td>
                        <span class="fw-semibold">${f.CartaoNome ?? ''}</span>
                        ${f.Ultimos4Digitos ? '<span class="text-muted small">****' + f.Ultimos4Digitos + '</span>' : ''}
                    </td>
                    <td>${periodo}</td>
                    <td>${Envelopei.dateBR(f.DataVencimento)}</td>
                    <td class="text-end fw-semibold">${Envelopei.money(f.ValorTotal)}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <a href="<?= base_url('faturas') ?>/${f.FaturaId}" class="btn btn-sm btn-outline-primary me-1" title="Ver fatura detalhada">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        ${!pago && f.ValorTotal > 0 ? `
                            <button class="btn btn-sm btn-success" onclick="abrirModalPagar(${f.FaturaId})">
                                <i class="fa-solid fa-check me-1"></i>Pagar
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function abrirModalPagar(faturaId) {
        await carregarContas();
        document.getElementById('pagarFaturaId').value = faturaId;
        document.getElementById('pagarData').value = new Date().toISOString().slice(0, 10);

        const sel = document.getElementById('pagarContaId');
        sel.innerHTML = '<option value="">Selecione...</option>' + contas.map(c =>
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

        Envelopei.toast('Fatura marcada como paga! O valor foi debitado do envelope.', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalPagarFatura')).hide();
        carregar();
        carregarProximas();
    }

    document.addEventListener('DOMContentLoaded', () => {
        carregar();
        carregarProximas();

        document.getElementById('btnFiltroTodas').addEventListener('click', () => {
            filtroPendentes = false;
            carregar();
        });
        document.getElementById('btnFiltroPendentes').addEventListener('click', () => {
            filtroPendentes = true;
            carregar();
        });
        document.getElementById('btnConfirmarPagar').addEventListener('click', confirmarPagar);

        const params = new URLSearchParams(window.location.search);
        const cartaoId = params.get('cartao');
        if (cartaoId) {
            document.getElementById('btnFiltroPendentes').click();
        }

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
