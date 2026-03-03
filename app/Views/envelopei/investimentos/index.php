<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .invest-page .card-invest-module { border-radius: 12px; overflow: hidden; }
    .invest-page .invest-icon-card { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .invest-page .prod-icon { width: 36px; height: 36px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; }
    .invest-page .pct-lucro { color: #198754; font-weight: 600; }
    .invest-page .pct-perda { color: #dc3545; font-weight: 600; }
    .invest-page .pct-zero { color: #6c757d; font-weight: 600; }
    .invest-page .table-produtos td { vertical-align: middle; }
    .invest-page #modalExcluirProduto .modal-content {
        border-radius: 12px;
        border: 0;
        box-shadow: 0 10px 40px rgba(0,0,0,.15);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="invest-page">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-0">Investimentos</h4>
            <div class="text-muted">Acompanhe seus produtos e rentabilidade</div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" class="btn btn-outline-secondary" id="btnToggleValores" title="Ocultar/mostrar valores">
                <i class="fa-solid fa-eye" id="iconToggleValores"></i>
            </button>
            <a href="<?= base_url('investimentos/enviar') ?>" class="btn btn-info">
                <i class="fa-solid fa-paper-plane me-2"></i>Enviar para investimento
            </a>
            <a href="<?= base_url('investimentos/entrada-direta') ?>" class="btn btn-success">
                <i class="fa-solid fa-wallet me-2"></i>Entrada direta
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
                <i class="fa-solid fa-plus me-2"></i>Novo produto
            </button>
        </div>
    </div>

    <!-- Resumo conta investimentos -->
    <div class="card card-invest-module shadow-sm mb-3">
        <div class="card-body">
            <h6 class="text-muted mb-2"><i class="fa-solid fa-piggy-bank me-2"></i>Saldo na conta de investimentos</h6>
            <div class="fs-3 fw-bold text-info" id="saldoContaInvestimento">—</div>
            <p class="small text-muted mb-0">Valor disponível para aplicar (saldo da conta menos o já aplicado em produtos)</p>
        </div>
    </div>

    <!-- Cards totais produtos -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="invest-icon-card bg-primary bg-opacity-10">
                        <i class="fa-solid fa-coins text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total aplicado</div>
                        <div class="fs-4 fw-bold" id="totalAplicado">—</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="invest-icon-card bg-success bg-opacity-10">
                        <i class="fa-solid fa-chart-line text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Valor atual</div>
                        <div class="fs-4 fw-bold" id="totalAtual">—</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="invest-icon-card bg-info bg-opacity-10" id="cardTotalRendimentos">
                        <i class="fa-solid fa-chart-line text-info" id="iconTotalRendimentos"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total de rendimentos</div>
                        <div class="fs-4 fw-bold" id="totalRendimentosValor">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista produtos -->
    <div class="card card-invest-module shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0"><i class="fa-solid fa-list me-2"></i>Produtos de investimento</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-produtos mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th class="text-end">Aplicado</th>
                            <th class="text-end">Atual</th>
                            <th class="text-end">Total de rendimentos</th>
                            <th style="width:180px" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyProdutos">
                        <tr><td colspan="7" class="text-center text-muted py-4">Carregando…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Produto -->
<div class="modal fade" id="modalNovoProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Novo produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoProduto">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" id="prodNome" placeholder="Ex: CDB Banco X" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="prodTipo">
                            <option value="CDB">CDB</option>
                            <option value="LCI">LCI</option>
                            <option value="LCA">LCA</option>
                            <option value="RendaVariavel">Renda variável</option>
                            <option value="Tesouro">Tesouro</option>
                            <option value="FundoImobiliario">FII</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Valor aplicado</label>
                            <input type="text" class="form-control input-money" id="prodValorAplicado" placeholder="0,00">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Valor atual</label>
                            <input type="text" class="form-control input-money" id="prodValorAtual" placeholder="0,00">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnCriarProduto"><i class="fa-solid fa-check me-2"></i>Criar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Produto -->
<div class="modal fade" id="modalEditarProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Editar produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarProduto">
                    <input type="hidden" id="editProdutoId">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" id="editProdNome" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="editProdTipo">
                            <option value="CDB">CDB</option>
                            <option value="LCI">LCI</option>
                            <option value="LCA">LCA</option>
                            <option value="RendaVariavel">Renda variável</option>
                            <option value="Tesouro">Tesouro</option>
                            <option value="FundoImobiliario">FII</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Valor aplicado</label>
                            <input type="text" class="form-control input-money" id="editProdValorAplicado">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Valor atual</label>
                            <input type="text" class="form-control input-money" id="editProdValorAtual">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarProduto"><i class="fa-solid fa-check me-2"></i>Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir Produto (estilo igual ao de lançamentos) -->
<div class="modal fade" id="modalExcluirProduto" tabindex="-1" aria-labelledby="modalExcluirProdutoTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-semibold" id="modalExcluirProdutoTitle">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Excluir produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <input type="hidden" id="excluirProdutoId">
                <p class="mb-2">Tem certeza que deseja excluir o produto <strong id="excluirProdutoNome"></strong>?</p>
                <p class="text-muted small mb-0">Todos os aportes e rendimentos vinculados serão perdidos.</p>
                <div class="alert alert-warning small mt-3 mb-0 d-flex align-items-start">
                    <i class="fa-solid fa-circle-info me-2 mt-1"></i>
                    <span>Não é possível desfazer.</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnExcluirProdutoAgora">
                    <i class="fa-solid fa-trash me-2"></i>Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    const STORAGE_OCULTAR_VALORES = 'envelopei_ocultarValores';
    let cacheConta = null;
    let cacheTotais = null;
    let cacheProdutos = [];

    function valoresOcultos() {
        return localStorage.getItem(STORAGE_OCULTAR_VALORES) === 'true';
    }

    function formatValor(v) {
        return valoresOcultos() ? 'R$ ••••••' : Envelopei.money(v ?? 0);
    }

    function formatPct(pct) {
        return valoresOcultos() ? '•••%' : (pct >= 0 ? '+' : '') + Number(pct).toFixed(2) + '%';
    }

    function atualizarIconeOlho() {
        const icon = document.getElementById('iconToggleValores');
        const btn = document.getElementById('btnToggleValores');
        if (!icon || !btn) return;
        if (valoresOcultos()) {
            icon.className = 'fa-solid fa-eye-slash';
            btn.title = 'Mostrar valores';
        } else {
            icon.className = 'fa-solid fa-eye';
            btn.title = 'Ocultar valores';
        }
    }

    const TIPO_ICONS = {
        CDB: 'fa-building-columns',
        LCI: 'fa-file-invoice',
        LCA: 'fa-seedling',
        RendaVariavel: 'fa-chart-line',
        Tesouro: 'fa-landmark',
        FundoImobiliario: 'fa-building',
        Outros: 'fa-circle-dot'
    };
    const TIPO_LABELS = {
        CDB: 'CDB',
        LCI: 'LCI',
        LCA: 'LCA',
        RendaVariavel: 'Renda variável',
        Tesouro: 'Tesouro',
        FundoImobiliario: 'FII',
        Outros: 'Outros'
    };
    const TIPO_COLORS = {
        CDB: 'primary',
        LCI: 'info',
        LCA: 'success',
        RendaVariavel: 'warning',
        Tesouro: 'secondary',
        FundoImobiliario: 'dark',
        Outros: 'secondary'
    };

    function iconTipo(tipo) {
        const icon = TIPO_ICONS[tipo] || 'fa-circle-dot';
        const color = TIPO_COLORS[tipo] || 'secondary';
        return `<span class="prod-icon bg-${color} bg-opacity-10 text-${color}"><i class="fa-solid ${icon}"></i></span>`;
    }

    function pctClass(pct) {
        if (pct > 0) return 'pct-lucro';
        if (pct < 0) return 'pct-perda';
        return 'pct-zero';
    }

    function pctIcon(pct) {
        if (pct > 0) return '<i class="fa-solid fa-arrow-trend-up me-1"></i>';
        if (pct < 0) return '<i class="fa-solid fa-arrow-trend-down me-1"></i>';
        return '<i class="fa-solid fa-minus me-1"></i>';
    }

    function aplicarVisibilidadeValores() {
        if (cacheConta == null && cacheTotais == null && cacheProdutos.length === 0) return;
        const fmt = formatValor;
        const fmtPct = formatPct;
        if (cacheConta != null) {
            const el = document.getElementById('saldoContaInvestimento');
            if (el) el.textContent = fmt(cacheConta.Saldo);
        }
        if (cacheTotais != null) {
            const elA = document.getElementById('totalAplicado');
            const elAt = document.getElementById('totalAtual');
            const elR = document.getElementById('totalRendimentosValor');
            if (elA) elA.textContent = fmt(cacheTotais.TotalAplicado);
            if (elAt) elAt.textContent = fmt(cacheTotais.TotalAtual);
            const totalRend = Number(cacheTotais.TotalRendimentos ?? 0);
            if (elR) elR.textContent = valoresOcultos() ? 'R$ ••••••' : (totalRend >= 0 ? '+' : '') + Envelopei.money(totalRend);
        }
        if (cacheProdutos.length > 0) {
            const tbody = document.getElementById('tbodyProdutos');
            if (tbody) {
                tbody.innerHTML = cacheProdutos.map(p => {
                    const aplicado = Number(p.ValorAplicado ?? 0);
                    const atual = Number(p.ValorAtual ?? 0);
                    const totalRend = Number(p.TotalRendimentos ?? 0);
                    const clsRend = totalRend > 0 ? 'pct-lucro' : totalRend < 0 ? 'pct-perda' : 'pct-zero';
                    const fmtRend = valoresOcultos() ? 'R$ ••••••' : (totalRend >= 0 ? '+' : '') + Envelopei.money(totalRend);
                    return `
                    <tr>
                        <td>${iconTipo(p.TipoProduto)}</td>
                        <td><strong>${(p.Nome || '').replace(/</g, '&lt;')}</strong></td>
                        <td>${TIPO_LABELS[p.TipoProduto] || p.TipoProduto}</td>
                        <td class="text-end">${fmt(aplicado)}</td>
                        <td class="text-end">${fmt(atual)}</td>
                        <td class="text-end ${clsRend}">${fmtRend}</td>
                        <td class="text-end">
                            <a href="<?= base_url('investimentos/produtos/') ?>${p.ProdutoInvestimentoId}" class="btn btn-sm btn-outline-info me-1" title="Ver histórico"><i class="fa-solid fa-eye"></i></a>
                            <a href="<?= base_url('investimentos/produtos/') ?>${p.ProdutoInvestimentoId}" class="btn btn-sm btn-outline-success me-1" title="Aportar"><i class="fa-solid fa-plus"></i></a>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="abrirEditar(${p.ProdutoInvestimentoId}, '${(p.Nome || '').replace(/'/g, "\\'")}', '${p.TipoProduto || 'Outros'}', ${aplicado}, ${atual})" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirProduto(${p.ProdutoInvestimentoId}, '${(p.Nome || '').replace(/'/g, "\\'")}')" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>`;
                }).join('');
            }
        }
    }

    async function carregarResumo() {
        const r = await Envelopei.api('api/investimentos/resumo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
            return;
        }

        cacheConta = r.data?.ContaInvestimento ?? {};
        cacheTotais = r.data?.Totais ?? {};
        cacheProdutos = r.data?.Produtos ?? [];

        document.getElementById('saldoContaInvestimento').textContent = formatValor(cacheConta.Saldo);
        document.getElementById('totalAplicado').textContent = formatValor(cacheTotais.TotalAplicado);
        document.getElementById('totalAtual').textContent = formatValor(cacheTotais.TotalAtual);

        const totalRend = Number(cacheTotais.TotalRendimentos ?? 0);
        document.getElementById('totalRendimentosValor').textContent = valoresOcultos() ? 'R$ ••••••' : (totalRend >= 0 ? '+' : '') + Envelopei.money(totalRend);
        const cardRend = document.getElementById('cardTotalRendimentos');
        const iconRend = document.getElementById('iconTotalRendimentos');
        cardRend.className = 'invest-icon-card bg-opacity-10 ' + (totalRend > 0 ? 'bg-success' : totalRend < 0 ? 'bg-danger' : 'bg-secondary');
        cardRend.classList.remove('bg-info');
        iconRend.className = 'fa-solid fa-chart-line ' + (totalRend > 0 ? 'text-success' : totalRend < 0 ? 'text-danger' : 'text-secondary');
        iconRend.classList.remove('text-info');

        const tbody = document.getElementById('tbodyProdutos');
        if (!cacheProdutos.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="fa-solid fa-inbox me-2"></i>Nenhum produto cadastrado. Clique em &quot;Novo produto&quot; para adicionar.</td></tr>';
        } else {
            aplicarVisibilidadeValores();
        }
    }

    window.abrirEditar = function(id, nome, tipo, aplicado, atual) {
        document.getElementById('editProdutoId').value = id;
        document.getElementById('editProdNome').value = nome;
        document.getElementById('editProdTipo').value = tipo || 'Outros';
        document.getElementById('editProdValorAplicado').value = Envelopei.formatMoneyForInput(aplicado);
        document.getElementById('editProdValorAtual').value = Envelopei.formatMoneyForInput(atual);
        new bootstrap.Modal(document.getElementById('modalEditarProduto')).show();
    };

    window.excluirProduto = function(id, nome) {
        document.getElementById('excluirProdutoId').value = id;
        document.getElementById('excluirProdutoNome').textContent = nome ? String(nome).replace(/</g, '&lt;') : '';
        new bootstrap.Modal(document.getElementById('modalExcluirProduto')).show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        atualizarIconeOlho();
        var btnExcluir = document.getElementById('btnExcluirProdutoAgora');
        if (btnExcluir) btnExcluir.addEventListener('click', async function() {
            var id = document.getElementById('excluirProdutoId').value;
            if (!id) return;
            var r = await Envelopei.api('api/investimentos/produtos/' + id, 'DELETE', {});
            if (!r || !r.success) return Envelopei.toast(r && r.message ? r.message : 'Falha ao excluir.', 'danger');
            Envelopei.toast('Produto excluído.', 'success');
            var m = document.getElementById('modalExcluirProduto');
            if (m && bootstrap.Modal.getInstance(m)) bootstrap.Modal.getInstance(m).hide();
            carregarResumo();
        });
        var btnCriar = document.getElementById('btnCriarProduto');
        if (btnCriar) btnCriar.addEventListener('click', async function() {
            var Nome = document.getElementById('prodNome').value.trim();
            var TipoProduto = document.getElementById('prodTipo').value;
            var ValorAplicado = Envelopei.parseMoney(document.getElementById('prodValorAplicado').value);
            var ValorAtual = Envelopei.parseMoney(document.getElementById('prodValorAtual').value) || ValorAplicado;
            if (!Nome) return Envelopei.toast('Informe o nome.', 'danger');
            var r = await Envelopei.api('api/investimentos/produtos', 'POST', { Nome: Nome, TipoProduto: TipoProduto, ValorAplicado: ValorAplicado, ValorAtual: ValorAtual });
            if (!r || !r.success) return Envelopei.toast(r && r.message ? r.message : 'Falha ao criar.', 'danger');
            Envelopei.toast('Produto criado!', 'success');
            var m = document.getElementById('modalNovoProduto');
            if (m && bootstrap.Modal.getInstance(m)) bootstrap.Modal.getInstance(m).hide();
            document.getElementById('formNovoProduto').reset();
            carregarResumo();
        });
        var btnSalvar = document.getElementById('btnSalvarProduto');
        if (btnSalvar) btnSalvar.addEventListener('click', async function() {
            var id = document.getElementById('editProdutoId').value;
            var Nome = document.getElementById('editProdNome').value.trim();
            var TipoProduto = document.getElementById('editProdTipo').value;
            var ValorAplicado = Envelopei.parseMoney(document.getElementById('editProdValorAplicado').value);
            var ValorAtual = Envelopei.parseMoney(document.getElementById('editProdValorAtual').value);
            if (!Nome) return Envelopei.toast('Informe o nome.', 'danger');
            var r = await Envelopei.api('api/investimentos/produtos/' + id, 'PUT', { Nome: Nome, TipoProduto: TipoProduto, ValorAplicado: ValorAplicado, ValorAtual: ValorAtual });
            if (!r || !r.success) return Envelopei.toast(r && r.message ? r.message : 'Falha ao salvar.', 'danger');
            Envelopei.toast('Produto atualizado!', 'success');
            var m = document.getElementById('modalEditarProduto');
            if (m && bootstrap.Modal.getInstance(m)) bootstrap.Modal.getInstance(m).hide();
            carregarResumo();
        });
        var btnToggle = document.getElementById('btnToggleValores');
        if (btnToggle) btnToggle.addEventListener('click', function() {
            var atual = valoresOcultos();
            localStorage.setItem(STORAGE_OCULTAR_VALORES, (!atual).toString());
            atualizarIconeOlho();
            aplicarVisibilidadeValores();
        });
        carregarResumo();
    });
</script>
<?= $this->endSection() ?>
