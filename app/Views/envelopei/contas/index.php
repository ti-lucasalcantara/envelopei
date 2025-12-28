<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Contas</h4>
        <div class="text-muted">Onde o dinheiro está de verdade</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConta">
        <i class="fa-solid fa-plus me-2"></i>Nova Conta
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th class="text-end">Saldo Inicial</th>
                        <th class="text-end">Saldo Atual</th>
                        <th style="width:160px;"></th>
                    </tr>
                </thead>
                <tbody id="tbContas">
                    <tr><td colspan="5" class="text-center text-muted py-4">Carregando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalConta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ctaModalTitle">Nova Conta</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="ctaId">

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" class="form-control" id="ctaNome" placeholder="Nubank, Carteira...">
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="ctaTipo">
                            <option value="banco">Banco</option>
                            <option value="carteira">Carteira</option>
                            <option value="poupanca">Poupança</option>
                            <option value="investimento">Investimento</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Saldo Inicial</label>
                        <input type="number" step="0.01" class="form-control" id="ctaSaldoInicial" value="0">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnSalvarConta">
                    <i class="fa-solid fa-check me-2"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let contas = [];

    async function carregar() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET'); // já vem contas + saldo
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');

        contas = r.data?.Contas ?? [];
        render();
    }

    function render() {
        const tb = document.getElementById('tbContas');

        if (!contas.length) {
            tb.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Nenhuma conta.</td></tr>`;
            return;
        }

        tb.innerHTML = contas.map(c => {
            return `
                <tr>
                    <td class="fw-semibold">${c.Nome}</td>
                    <td><span class="badge bg-light text-dark border">${c.TipoConta}</span></td>
                    <td class="text-end">${Envelopei.money(c.SaldoInicial)}</td>
                    <td class="text-end fw-semibold">${Envelopei.money(c.SaldoAtual)}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editar(${c.ContaId})">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="desativar(${c.ContaId})">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function editar(id) {
        const c = contas.find(x => Number(x.ContaId) === Number(id));
        if (!c) return;

        $('#ctaModalTitle').text('Editar Conta');
        $('#ctaId').val(c.ContaId);
        $('#ctaNome').val(c.Nome);
        $('#ctaTipo').val(c.TipoConta);
        $('#ctaSaldoInicial').val(c.SaldoInicial);

        new bootstrap.Modal(document.getElementById('modalConta')).show();
    }

    async function desativar(id) {
        if (!confirm('Desativar esta conta?')) return;
        const r = await Envelopei.api(`api/contas/${id}`, 'DELETE', {});
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao desativar.', 'danger');

        Envelopei.toast('Conta desativada.', 'success');
        carregar();
    }

    async function salvar() {
        const ContaId = Number($('#ctaId').val() || 0);
        const Nome = $('#ctaNome').val().trim();
        const TipoConta = $('#ctaTipo').val();
        const SaldoInicial = Number($('#ctaSaldoInicial').val() || 0);

        if (!Nome) return Envelopei.toast('Informe o nome.', 'danger');

        let r;
        if (!ContaId) {
            r = await Envelopei.api('api/contas', 'POST', { Nome, TipoConta, SaldoInicial });
        } else {
            r = await Envelopei.api(`api/contas/${ContaId}`, 'PUT', { Nome, TipoConta, SaldoInicial });
        }

        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');

        bootstrap.Modal.getInstance(document.getElementById('modalConta')).hide();
        Envelopei.toast('Salvo com sucesso!', 'success');

        $('#ctaId').val('');
        $('#ctaNome').val('');
        $('#ctaTipo').val('banco');
        $('#ctaSaldoInicial').val('0');

        carregar();
    }

    document.addEventListener('DOMContentLoaded', () => {
        carregar();
        document.getElementById('btnSalvarConta').addEventListener('click', salvar);

        document.getElementById('modalConta').addEventListener('hidden.bs.modal', () => {
            $('#ctaModalTitle').text('Nova Conta');
            $('#ctaId').val('');
            $('#ctaNome').val('');
            $('#ctaTipo').val('banco');
            $('#ctaSaldoInicial').val('0');
        });
    });
</script>
<?= $this->endSection() ?>
