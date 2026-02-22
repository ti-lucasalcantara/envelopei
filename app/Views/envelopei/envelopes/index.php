<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Envelopes</h4>
        <div class="text-muted">Crie, edite e organize seus envelopes</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEnvelope">
        <i class="fa-solid fa-plus me-2"></i>Novo Envelope
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Cor</th>
                        <th>Status</th>
                        <th class="text-end">Saldo</th>
                        <th style="width:200px;"></th>
                    </tr>
                </thead>
                <tbody id="tbEnvelopes">
                    <tr><td colspan="5" class="text-center text-muted py-4">Carregando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CRIAR/EDITAR -->
<div class="modal fade" id="modalEnvelope" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="envModalTitle">Novo Envelope</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="envId">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" class="form-control" id="envNome">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Cor</label>
                        <input type="color" class="form-control" id="envCor" placeholder="#0d6efd">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Ordem</label>
                        <input type="number" class="form-control" id="envOrdem" placeholder="1">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnSalvarEnv">
                    <i class="fa-solid fa-check me-2"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let envelopes = [];

    async function carregar() {
        const r = await Envelopei.api('api/envelopes?IncluirInativos=1', 'GET');
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');

        envelopes = r.data ?? [];
        render();
    }

    function render() {
        const tb = document.getElementById('tbEnvelopes');

        if (!envelopes.length) {
            tb.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Nenhum envelope.</td></tr>`;
            return;
        }

        tb.innerHTML = envelopes.map(e => {
            const ativo = Number(e.Ativo) === 1;
            const trClass = ativo ? '' : 'tr-marker-danger';
            const cor = e.Cor ? `<span class="badge" style="background:${e.Cor};"> </span> <span class="text-mono small">${e.Cor}</span>` : '-';
            const statusBadge = ativo
                ? '<span class="badge bg-light border border-success text-success small">Ativo</span>'
                : '<span class="badge bg-light border border-danger text-danger small">Inativo</span>';
            const botoes = ativo
                ? `
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editar(${e.EnvelopeId})" title="Editar">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="desativar(${e.EnvelopeId})" title="Desativar">
                        <i class="fa-solid fa-ban"></i>
                    </button>
                `
                : `
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editar(${e.EnvelopeId})" title="Editar">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="reativar(${e.EnvelopeId})" title="Reativar">
                        <i class="fa-solid fa-rotate-right me-1"></i>Reativar
                    </button>
                `;
            return `
                <tr class="${trClass}">
                    <td class="fw-semibold">${e.Nome}</td>
                    <td>${cor}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end fw-semibold">${Envelopei.money(e.Saldo)}</td>
                    <td class="text-end">${botoes}</td>
                </tr>
            `;
        }).join('');
    }

    function editar(id) {
        const e = envelopes.find(x => Number(x.EnvelopeId) === Number(id));
        if (!e) return;

        $('#envModalTitle').text('Editar Envelope');
        $('#envId').val(e.EnvelopeId);
        $('#envNome').val(e.Nome);
        $('#envCor').val(e.Cor ?? '');
        $('#envOrdem').val(e.Ordem ?? '');

        new bootstrap.Modal(document.getElementById('modalEnvelope')).show();
    }

    async function desativar(id) {
        if (!confirm('Desativar este envelope? Ele deixará de aparecer no dashboard.')) return;
        const r = await Envelopei.api(`api/envelopes/${id}`, 'DELETE', {});
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao desativar.', 'danger');

        Envelopei.toast('Envelope desativado.', 'success');
        carregar();
    }

    async function reativar(id) {
        const r = await Envelopei.api(`api/envelopes/${id}`, 'PUT', { Ativo: 1 });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao reativar.', 'danger');

        Envelopei.toast('Envelope reativado!', 'success');
        carregar();
    }

    async function salvar() {
        const EnvelopeId = Number($('#envId').val() || 0);
        const Nome = $('#envNome').val().trim();
        const Cor = $('#envCor').val().trim() || null;
        const Ordem = $('#envOrdem').val() !== '' ? Number($('#envOrdem').val()) : null;

        if (!Nome) return Envelopei.toast('Informe o nome.', 'danger');

        let r;
        if (!EnvelopeId) {
            r = await Envelopei.api('api/envelopes', 'POST', { Nome, Cor, Ordem });
        } else {
            r = await Envelopei.api(`api/envelopes/${EnvelopeId}`, 'PUT', { Nome, Cor, Ordem });
        }

        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');

        bootstrap.Modal.getInstance(document.getElementById('modalEnvelope')).hide();
        Envelopei.toast('Salvo com sucesso!', 'success');

        // reset
        $('#envId').val('');
        $('#envNome').val('');
        $('#envCor').val('');
        $('#envOrdem').val('');

        carregar();
    }

    document.addEventListener('DOMContentLoaded', () => {
        carregar();

        document.getElementById('btnSalvarEnv').addEventListener('click', salvar);

        document.getElementById('modalEnvelope').addEventListener('hidden.bs.modal', () => {
            $('#envModalTitle').text('Novo Envelope');
            $('#envId').val('');
            $('#envNome').val('');
            $('#envCor').val('');
            $('#envOrdem').val('');
        });
    });
</script>
<?= $this->endSection() ?>
