<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .produto-page .card-module { border-radius: 12px; overflow: hidden; }
    .produto-page .invest-icon-card { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .produto-page .pct-lucro { color: #198754; font-weight: 600; }
    .produto-page .pct-perda { color: #dc3545; font-weight: 600; }
    .produto-page .pct-zero { color: #6c757d; font-weight: 600; }
    .produto-page .table-hist th { font-weight: 600; }
    .produto-page .rend-positivo { color: #198754; }
    .produto-page .rend-negativo { color: #dc3545; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="produto-page">
    <a href="<?= base_url('investimentos') ?>" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="fa-solid fa-arrow-left me-2"></i>Voltar aos Investimentos
    </a>

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-0" id="produtoNome">—</h4>
            <div class="text-muted" id="produtoTipo">—</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAporte">
                <i class="fa-solid fa-plus me-2"></i>Fazer aporte
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRendimento">
                <i class="fa-solid fa-chart-line me-2"></i>Registrar rendimento
            </button>
        </div>
    </div>

    <!-- Resumo do produto -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="invest-icon-card bg-primary bg-opacity-10">
                        <i class="fa-solid fa-coins text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total aplicado</div>
                        <div class="fs-4 fw-bold" id="cardAplicado">—</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="invest-icon-card bg-success bg-opacity-10">
                        <i class="fa-solid fa-wallet text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Valor atual</div>
                        <div class="fs-4 fw-bold" id="cardAtual">—</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="invest-icon-card bg-opacity-10" id="cardVariacaoIcon">
                        <i class="fa-solid fa-percent" id="iconVariacao"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Variação</div>
                        <div class="fs-4 fw-bold" id="cardVariacaoValor">—</div>
                        <div class="small fw-semibold" id="cardVariacaoPct">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela Aportes -->
    <div class="card card-module shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fa-solid fa-plus-circle me-2"></i>Histórico de aportes</h6>
            <span class="badge bg-primary" id="totalAportesBadge">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-hist mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th class="text-end">Valor</th>
                            <th class="text-end" style="width:100px">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyAportes">
                        <tr><td colspan="4" class="text-center text-muted py-3">Carregando…</td></tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total aportado</th>
                            <th class="text-end" id="footTotalAportes">—</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabela Rendimentos -->
    <div class="card card-module shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fa-solid fa-chart-line me-2"></i>Histórico de rendimentos</h6>
            <span class="badge bg-success" id="totalRendimentosBadge">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-hist mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th class="text-end">Valor</th>
                            <th class="text-end" style="width:100px">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyRendimentos">
                        <tr><td colspan="4" class="text-center text-muted py-3">Carregando…</td></tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total rendimentos</th>
                            <th class="text-end" id="footTotalRendimentos">—</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aporte -->
<div class="modal fade" id="modalAporte" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Fazer aporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAporte">
                    <div class="mb-3">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" id="aporteData">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor</label>
                        <input type="text" class="form-control input-money" id="aporteValor" placeholder="0,00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="aporteDesc" placeholder="Ex: Aporte mensal">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarAporte"><i class="fa-solid fa-check me-2"></i>Registrar aporte</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rendimento -->
<div class="modal fade" id="modalRendimento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-chart-line me-2"></i>Registrar rendimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    <i class="fa-solid fa-info-circle me-2"></i>Use valor <strong>positivo</strong> para lucro e <strong>negativo</strong> para perda (ex: -150,00).
                </div>
                <form id="formRendimento">
                    <div class="mb-3">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" id="rendData">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor (lucro ou perda)</label>
                        <input type="text" class="form-control input-valor-assinado" id="rendValor" placeholder="Ex: 100,00 ou -50,00 (perda)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="rendDesc" placeholder="Ex: Rendimento mensal">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnSalvarRendimento"><i class="fa-solid fa-check me-2"></i>Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Aporte -->
<div class="modal fade" id="modalEditarAporte" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Editar aporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editAporteId">
                <div class="mb-3">
                    <label class="form-label">Data</label>
                    <input type="date" class="form-control" id="editAporteData">
                </div>
                <div class="mb-3">
                    <label class="form-label">Valor</label>
                    <input type="text" class="form-control input-money" id="editAporteValor" placeholder="0,00" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="editAporteDesc">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAtualizarAporte"><i class="fa-solid fa-check me-2"></i>Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Rendimento -->
<div class="modal fade" id="modalEditarRendimento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Editar rendimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    <i class="fa-solid fa-info-circle me-2"></i>Valor <strong>positivo</strong> = lucro, <strong>negativo</strong> = perda.
                </div>
                <input type="hidden" id="editRendimentoId">
                <div class="mb-3">
                    <label class="form-label">Data</label>
                    <input type="date" class="form-control" id="editRendimentoData">
                </div>
                <div class="mb-3">
                    <label class="form-label">Valor (lucro ou perda)</label>
                    <input type="text" class="form-control input-valor-assinado" id="editRendimentoValor" placeholder="0,00 ou -0,00" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="editRendimentoDesc">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnAtualizarRendimento"><i class="fa-solid fa-check me-2"></i>Salvar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    const PRODUTO_ID = <?= (int)($produtoId ?? 0) ?>;

    const TIPO_LABELS = {
        CDB: 'CDB', LCI: 'LCI', LCA: 'LCA',
        RendaVariavel: 'Renda variável', Tesouro: 'Tesouro',
        FundoImobiliario: 'FII', Outros: 'Outros'
    };

    function pctClass(pct) {
        if (pct > 0) return 'pct-lucro';
        if (pct < 0) return 'pct-perda';
        return 'pct-zero';
    }

    function pctIcon(pct) {
        if (pct > 0) return 'fa-arrow-trend-up text-success';
        if (pct < 0) return 'fa-arrow-trend-down text-danger';
        return 'fa-minus text-secondary';
    }

    /** Parse money allowing minus (e.g. -150,00) */
    function parseMoneySigned(str) {
        const s = String(str || '').trim();
        const neg = /^\-/.test(s);
        const n = Envelopei.parseMoney(s.replace(/^\-/, ''));
        return neg ? -n : n;
    }

    async function carregarHistorico() {
        const r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/historico', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
            return;
        }

        const prod = r.data?.Produto ?? {};
        const aportes = r.data?.Aportes ?? [];
        const rendimentos = r.data?.Rendimentos ?? [];
        const totais = r.data?.Totais ?? {};
        window._cacheAportes = aportes;
        window._cacheRendimentos = rendimentos;

        document.getElementById('produtoNome').textContent = prod.Nome || 'Produto';
        document.getElementById('produtoTipo').textContent = TIPO_LABELS[prod.TipoProduto] || prod.TipoProduto;

        document.getElementById('cardAplicado').textContent = Envelopei.money(totais.ValorAplicado);
        document.getElementById('cardAtual').textContent = Envelopei.money(totais.ValorAtual);
        document.getElementById('cardVariacaoValor').textContent = (totais.Variacao >= 0 ? '+' : '') + Envelopei.money(totais.Variacao);
        const pct = Number(totais.Percentual ?? 0);
        document.getElementById('cardVariacaoPct').innerHTML = '<i class="fa-solid ' + pctIcon(pct) + ' me-1"></i><span class="' + pctClass(pct) + '">' + (pct >= 0 ? '+' : '') + pct + '%</span>';
        const cardIcon = document.getElementById('cardVariacaoIcon');
        const iconEl = document.getElementById('iconVariacao');
        cardIcon.className = 'invest-icon-card bg-opacity-10 ' + (pct > 0 ? 'bg-success' : pct < 0 ? 'bg-danger' : 'bg-secondary');
        iconEl.className = 'fa-solid fa-percent ' + (pct > 0 ? 'text-success' : pct < 0 ? 'text-danger' : 'text-secondary');

        // Aportes
        document.getElementById('totalAportesBadge').textContent = aportes.length;
        document.getElementById('footTotalAportes').textContent = Envelopei.money(totais.TotalAportes);
        const tbodyA = document.getElementById('tbodyAportes');
        if (aportes.length === 0) {
            tbodyA.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3"><i class="fa-solid fa-inbox me-2"></i>Nenhum aporte registrado.</td></tr>';
        } else {
            tbodyA.innerHTML = aportes.map(a => `
                <tr>
                    <td>${Envelopei.dateBR(a.DataAporte)}</td>
                    <td>${(a.Descricao || '—').replace(/</g, '&lt;')}</td>
                    <td class="text-end">${Envelopei.money(a.Valor)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="abrirEditarAporte(${a.AporteId})" title="Editar"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirAporte(${a.AporteId})" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>`).join('');
        }

        // Rendimentos
        document.getElementById('totalRendimentosBadge').textContent = rendimentos.length;
        const totalRend = Number(totais.TotalRendimentos ?? 0);
        const footRend = document.getElementById('footTotalRendimentos');
        footRend.textContent = (totalRend >= 0 ? '+' : '') + Envelopei.money(totalRend);
        footRend.className = 'text-end ' + (totalRend >= 0 ? 'text-success' : 'text-danger');
        const tbodyR = document.getElementById('tbodyRendimentos');
        if (rendimentos.length === 0) {
            tbodyR.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3"><i class="fa-solid fa-inbox me-2"></i>Nenhum rendimento registrado.</td></tr>';
        } else {
            tbodyR.innerHTML = rendimentos.map(r => {
                const v = Number(r.Valor);
                const cls = v >= 0 ? 'rend-positivo' : 'rend-negativo';
                return `<tr>
                    <td>${Envelopei.dateBR(r.DataRendimento)}</td>
                    <td>${(r.Descricao || '—').replace(/</g, '&lt;')}</td>
                    <td class="text-end ${cls}">${v >= 0 ? '+' : ''}${Envelopei.money(v)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="abrirEditarRendimento(${r.RendimentoId})" title="Editar"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirRendimento(${r.RendimentoId})" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>`;
            }).join('');
        }
    }

    function formatMoneySigned(num) {
        const n = Number(num);
        if (isNaN(n)) return '';
        const neg = n < 0;
        const s = Envelopei.formatMoneyForInput(Math.abs(n));
        return neg ? '-' + s : s;
    }

    document.getElementById('aporteData').value = new Date().toISOString().slice(0, 10);
    document.getElementById('rendData').value = new Date().toISOString().slice(0, 10);

    document.getElementById('btnSalvarAporte').addEventListener('click', async function() {
        const DataAporte = document.getElementById('aporteData').value;
        const Valor = Envelopei.parseMoney(document.getElementById('aporteValor').value);
        const Descricao = document.getElementById('aporteDesc').value.trim();
        if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor do aporte.', 'danger');
        const r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/aportes', 'POST', { DataAporte, Valor, Descricao });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao registrar.', 'danger');
        Envelopei.toast('Aporte registrado!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalAporte')).hide();
        document.getElementById('formAporte').reset();
        document.getElementById('aporteData').value = new Date().toISOString().slice(0, 10);
        carregarHistorico();
    });

    document.getElementById('btnSalvarRendimento').addEventListener('click', async function() {
        const DataRendimento = document.getElementById('rendData').value;
        const Valor = parseMoneySigned(document.getElementById('rendValor').value);
        const Descricao = document.getElementById('rendDesc').value.trim();
        if (Valor === 0) return Envelopei.toast('Informe um valor (positivo ou negativo).', 'danger');
        const r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/rendimentos', 'POST', { DataRendimento, Valor, Descricao });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao registrar.', 'danger');
        Envelopei.toast('Rendimento registrado!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalRendimento')).hide();
        document.getElementById('formRendimento').reset();
        document.getElementById('rendData').value = new Date().toISOString().slice(0, 10);
        carregarHistorico();
    });

    /** Máscara para valor com sinal (permite negativo). Detectar sinal ANTES de remover não-dígitos. */
    function applySignedMoneyMask(input) {
        if (!input || input._signedMask) return;
        input._signedMask = true;
        input.setAttribute('inputmode', 'decimal');
        input.addEventListener('input', function() {
            const raw = this.value;
            const neg = raw.trim().startsWith('-');
            let v = raw.replace(/^-/, '').replace(/\D/g, '');
            if (v.length > 12) v = v.slice(0, 12);
            if (v.length === 0) { this.value = neg ? '-' : ''; return; }
            const intRaw = v.length <= 2 ? '0' : v.slice(0, -2);
            const intPart = intRaw.replace(/^0+/, '') || '0';
            const formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + v.slice(-2).padStart(2, '0');
            this.value = (neg ? '-' : '') + formatted;
        });
    }

    document.querySelectorAll('.input-valor-assinado').forEach(applySignedMoneyMask);

    window.abrirEditarAporte = function(aporteId) {
        const a = (window._cacheAportes || []).find(function(x) { return x.AporteId == aporteId; });
        if (!a) return;
        document.getElementById('editAporteId').value = a.AporteId;
        document.getElementById('editAporteData').value = (a.DataAporte || '').toString().slice(0, 10);
        document.getElementById('editAporteValor').value = Envelopei.formatMoneyForInput(Number(a.Valor));
        document.getElementById('editAporteDesc').value = a.Descricao || '';
        new bootstrap.Modal(document.getElementById('modalEditarAporte')).show();
    };

    window.abrirEditarRendimento = function(rendimentoId) {
        const r = (window._cacheRendimentos || []).find(function(x) { return x.RendimentoId == rendimentoId; });
        if (!r) return;
        document.getElementById('editRendimentoId').value = r.RendimentoId;
        document.getElementById('editRendimentoData').value = (r.DataRendimento || '').toString().slice(0, 10);
        document.getElementById('editRendimentoValor').value = formatMoneySigned(Number(r.Valor));
        document.getElementById('editRendimentoDesc').value = r.Descricao || '';
        new bootstrap.Modal(document.getElementById('modalEditarRendimento')).show();
    };

    window.excluirAporte = async function(aporteId) {
        if (!confirm('Excluir este aporte? O valor será descontado do total aplicado.')) return;
        const r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/aportes/' + aporteId, 'DELETE', {});
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao excluir.', 'danger');
        Envelopei.toast('Aporte excluído.', 'success');
        carregarHistorico();
    };

    window.excluirRendimento = async function(rendimentoId) {
        if (!confirm('Excluir este rendimento? O valor será revertido no valor atual.')) return;
        const r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/rendimentos/' + rendimentoId, 'DELETE', {});
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao excluir.', 'danger');
        Envelopei.toast('Rendimento excluído.', 'success');
        carregarHistorico();
    };

    document.getElementById('btnAtualizarAporte').addEventListener('click', async function() {
        const aporteId = document.getElementById('editAporteId').value;
        const DataAporte = document.getElementById('editAporteData').value;
        const Valor = Envelopei.parseMoney(document.getElementById('editAporteValor').value);
        const Descricao = document.getElementById('editAporteDesc').value.trim();
        if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor do aporte.', 'danger');
        const r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/aportes/' + aporteId, 'PUT', { DataAporte, Valor, Descricao });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao atualizar.', 'danger');
        Envelopei.toast('Aporte atualizado!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalEditarAporte')).hide();
        carregarHistorico();
    });

    document.getElementById('btnAtualizarRendimento').addEventListener('click', async function() {
        const rendimentoId = document.getElementById('editRendimentoId').value;
        const DataRendimento = document.getElementById('editRendimentoData').value;
        const Valor = parseMoneySigned(document.getElementById('editRendimentoValor').value);
        const Descricao = document.getElementById('editRendimentoDesc').value.trim();
        if (Valor === 0) return Envelopei.toast('Informe um valor (positivo ou negativo).', 'danger');
        const r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/rendimentos/' + rendimentoId, 'PUT', { DataRendimento, Valor, Descricao });
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao atualizar.', 'danger');
        Envelopei.toast('Rendimento atualizado!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalEditarRendimento')).hide();
        carregarHistorico();
    });

    document.addEventListener('DOMContentLoaded', carregarHistorico);
</script>
<?= $this->endSection() ?>
