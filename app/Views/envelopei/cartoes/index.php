<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Cartões de Crédito</h4>
        <div class="text-muted">Cadastre e gerencie seus cartões</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCartao">
        <i class="fa-solid fa-plus me-2"></i>Novo Cartão
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Bandeira</th>
                        <th>Final</th>
                        <th>Fechamento</th>
                        <th>Vencimento</th>
                        <th class="text-end">Limite</th>
                        <th style="width:140px;"></th>
                    </tr>
                </thead>
                <tbody id="tbCartoes">
                    <tr><td colspan="7" class="text-center text-muted py-4">Carregando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CRIAR/EDITAR -->
<div class="modal fade" id="modalCartao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartaoModalTitle">Novo Cartão</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cartaoId">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" class="form-control" id="cartaoNome" placeholder="Nubank, Itaú...">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Bandeira</label>
                        <input type="text" class="form-control" id="cartaoBandeira" placeholder="Visa, Master...">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Últimos 4 dígitos</label>
                        <input type="text" class="form-control" id="cartaoFinal" placeholder="1234" maxlength="4" inputmode="numeric" pattern="[0-9]*">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Dia fechamento</label>
                        <input type="number" class="form-control" id="cartaoDiaFech" min="1" max="28" value="10" title="Dia do mês em que a fatura fecha">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Dia vencimento</label>
                        <input type="number" class="form-control" id="cartaoDiaVenc" min="1" max="28" value="17">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Limite (opcional)</label>
                        <input type="text" inputmode="decimal" class="form-control input-money" id="cartaoLimite" placeholder="0,00">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Cor (opcional)</label>
                        <input type="color" class="form-control" id="cartaoCor" placeholder="#0d6efd">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnSalvarCartao">
                    <i class="fa-solid fa-check me-2"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let cartoes = [];

    async function carregar() {
        try {
            const r = await Envelopei.api('api/cartoes-credito?Ativos=0', 'GET');
            if (!r?.success) {
                Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
                document.getElementById('tbCartoes').innerHTML =
                    `<tr><td colspan="7" class="text-center text-danger py-4">${r?.message ?? 'Erro ao carregar. Tente novamente.'}</td></tr>`;
                return;
            }

            cartoes = Array.isArray(r.data) ? r.data : (r.data ?? []);
            render();
        } catch (e) {
            console.error(e);
            Envelopei.toast('Erro ao carregar cartões.', 'danger');
            document.getElementById('tbCartoes').innerHTML =
                '<tr><td colspan="7" class="text-center text-danger py-4">Erro ao carregar. Verifique o console.</td></tr>';
        }
    }

    function render() {
        const tb = document.getElementById('tbCartoes');

        if (!cartoes.length) {
            tb.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Nenhum cartão cadastrado.</td></tr>`;
            return;
        }

        tb.innerHTML = cartoes.map(c => {
            const ativo = Number(c.Ativo) === 1;
            const rowClass = ativo ? '' : 'tr-marker-secondary';
            const limite = c.Limite != null ? Envelopei.money(c.Limite) : '—';
            return `
                <tr class="${rowClass}">
                    <td class="fw-semibold">${c.Nome}</td>
                    <td>${c.Bandeira || '—'}</td>
                    <td>${c.Ultimos4Digitos ? '****' + c.Ultimos4Digitos : '—'}</td>
                    <td>${c.DiaFechamento ?? 10}</td>
                    <td>${c.DiaVencimento ?? 17}</td>
                    <td class="text-end">${limite}</td>
                    <td class="text-end">
                        <a href="<?= base_url('faturas') ?>?cartao=${c.CartaoCreditoId}" class="btn btn-sm btn-outline-info me-1" title="Ver faturas">
                            <i class="fa-solid fa-file-invoice"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editar(${c.CartaoCreditoId})">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="desativar(${c.CartaoCreditoId}, ${ativo})">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function editar(id) {
        const c = cartoes.find(x => Number(x.CartaoCreditoId) === Number(id));
        if (!c) return;

        $('#cartaoModalTitle').text('Editar Cartão');
        $('#cartaoId').val(c.CartaoCreditoId);
        $('#cartaoNome').val(c.Nome);
        $('#cartaoBandeira').val(c.Bandeira ?? '');
        $('#cartaoFinal').val(c.Ultimos4Digitos ?? '');
        $('#cartaoDiaFech').val(c.DiaFechamento ?? 10);
        $('#cartaoDiaVenc').val(c.DiaVencimento ?? 17);
        $('#cartaoLimite').val(c.Limite != null ? Envelopei.formatMoneyForInput(c.Limite) : '');
        $('#cartaoCor').val(c.Cor ?? '');

        new bootstrap.Modal(document.getElementById('modalCartao')).show();
    }

    async function desativar(id, ativo) {
        const acao = ativo ? 'Desativar' : 'Reativar';
        if (!confirm(`${acao} este cartão?`)) return;

        const r = await Envelopei.api(`api/cartoes-credito/${id}`, 'PUT', { Ativo: ativo ? 0 : 1 });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha.', 'danger');

        Envelopei.toast(ativo ? 'Cartão desativado.' : 'Cartão reativado.', 'success');
        carregar();
    }

    async function salvar() {
        const CartaoCreditoId = Number($('#cartaoId').val() || 0);
        const Nome = $('#cartaoNome').val().trim();
        const Bandeira = $('#cartaoBandeira').val().trim() || null;
        const Ultimos4Digitos = $('#cartaoFinal').val().replace(/\D/g, '').slice(0, 4) || null;
        const DiaFechamento = Number($('#cartaoDiaFech').val()) || 10;
        const DiaVencimento = Number($('#cartaoDiaVenc').val()) || 17;
        const Limite = $('#cartaoLimite').val() ? Envelopei.parseMoney($('#cartaoLimite').val()) : null;
        const Cor = $('#cartaoCor').val().trim() || null;

        if (!Nome) return Envelopei.toast('Informe o nome.', 'danger');

        const payload = { Nome, Bandeira, Ultimos4Digitos, DiaFechamento, DiaVencimento, Limite, Cor };

        let r;
        if (!CartaoCreditoId) {
            r = await Envelopei.api('api/cartoes-credito', 'POST', payload);
        } else {
            r = await Envelopei.api(`api/cartoes-credito/${CartaoCreditoId}`, 'PUT', payload);
        }

        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');

        bootstrap.Modal.getInstance(document.getElementById('modalCartao')).hide();
        Envelopei.toast('Salvo com sucesso!', 'success');

        $('#cartaoId').val('');
        $('#cartaoNome').val('');
        $('#cartaoBandeira').val('');
        $('#cartaoFinal').val('');
        $('#cartaoDiaFech').val(10);
        $('#cartaoDiaVenc').val(17);
        $('#cartaoLimite').val('');
        $('#cartaoCor').val('');

        carregar();
    }

    document.addEventListener('DOMContentLoaded', () => {
        carregar();
        document.getElementById('btnSalvarCartao').addEventListener('click', salvar);

        document.getElementById('modalCartao').addEventListener('hidden.bs.modal', () => {
            $('#cartaoModalTitle').text('Novo Cartão');
            $('#cartaoId').val('');
            $('#cartaoNome').val('');
            $('#cartaoBandeira').val('');
            $('#cartaoFinal').val('');
            $('#cartaoDiaFech').val(10);
            $('#cartaoDiaVenc').val(17);
            $('#cartaoLimite').val('');
            $('#cartaoCor').val('');
        });
    });
</script>
<?= $this->endSection() ?>
