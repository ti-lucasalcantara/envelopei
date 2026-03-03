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
    .produto-page #modalExcluirAporte .modal-content,
    .produto-page #modalExcluirRendimento .modal-content {
        border-radius: 12px;
        border: 0;
        box-shadow: 0 10px 40px rgba(0,0,0,.15);
    }
    .produto-page .btn { cursor: pointer; }
    .produto-page .modal .btn { pointer-events: auto; }
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
                    <div class="invest-icon-card bg-opacity-10" id="cardTotalRendimentosIcon">
                        <i class="fa-solid fa-chart-line" id="iconTotalRendimentos"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total de rendimentos</div>
                        <div class="fs-4 fw-bold" id="cardTotalRendimentosValor">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card card-module shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fa-solid fa-chart-area me-2"></i>Evolução do investimento</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartEvolucao" height="80"></canvas>
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

<!-- Modal Excluir Aporte (estilo igual ao de lançamentos) -->
<div class="modal fade" id="modalExcluirAporte" tabindex="-1" aria-labelledby="modalExcluirAporteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-semibold" id="modalExcluirAporteTitle">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Excluir aporte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <input type="hidden" id="excluirAporteId">
                <p class="mb-2">Tem certeza que deseja excluir este aporte?</p>
                <p class="text-muted small mb-0">O valor será descontado do total aplicado e do valor atual do produto.</p>
                <div class="alert alert-warning small mt-3 mb-0 d-flex align-items-start">
                    <i class="fa-solid fa-circle-info me-2 mt-1"></i>
                    <span>Não é possível desfazer.</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnExcluirAporteAgora">
                    <i class="fa-solid fa-trash me-2"></i>Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir Rendimento (estilo igual ao de lançamentos) -->
<div class="modal fade" id="modalExcluirRendimento" tabindex="-1" aria-labelledby="modalExcluirRendimentoTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-semibold" id="modalExcluirRendimentoTitle">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Excluir rendimento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <input type="hidden" id="excluirRendimentoId">
                <p class="mb-2">Tem certeza que deseja excluir este rendimento?</p>
                <p class="text-muted small mb-0">O valor será revertido no valor atual do produto.</p>
                <div class="alert alert-warning small mt-3 mb-0 d-flex align-items-start">
                    <i class="fa-solid fa-circle-info me-2 mt-1"></i>
                    <span>Não é possível desfazer.</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnExcluirRendimentoAgora">
                    <i class="fa-solid fa-trash me-2"></i>Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
        window._cacheTotais = totais;

        document.getElementById('produtoNome').textContent = prod.Nome || 'Produto';
        document.getElementById('produtoTipo').textContent = TIPO_LABELS[prod.TipoProduto] || prod.TipoProduto;

        document.getElementById('cardAplicado').textContent = Envelopei.money(totais.ValorAplicado);
        document.getElementById('cardAtual').textContent = Envelopei.money(totais.ValorAtual);
        const totalRend = Number(totais.TotalRendimentos ?? 0);
        document.getElementById('cardTotalRendimentosValor').textContent = (totalRend >= 0 ? '+' : '') + Envelopei.money(totalRend);
        const cardIcon = document.getElementById('cardTotalRendimentosIcon');
        const iconEl = document.getElementById('iconTotalRendimentos');
        cardIcon.className = 'invest-icon-card bg-opacity-10 ' + (totalRend > 0 ? 'bg-success' : totalRend < 0 ? 'bg-danger' : 'bg-secondary');
        iconEl.className = 'fa-solid fa-chart-line ' + (totalRend > 0 ? 'text-success' : totalRend < 0 ? 'text-danger' : 'text-secondary');

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

        desenharGraficoEvolucao(aportes, rendimentos);
    }

    function desenharGraficoEvolucao(aportes, rendimentos) {
        if (window._chartEvolucao) {
            window._chartEvolucao.destroy();
            window._chartEvolucao = null;
        }

        const eventos = [];
        (aportes || []).forEach(function(a) {
            eventos.push({ data: (a.DataAporte || '').toString().slice(0, 10), aplicado: Number(a.Valor), rendimento: 0 });
        });
        (rendimentos || []).forEach(function(r) {
            eventos.push({ data: (r.DataRendimento || '').toString().slice(0, 10), aplicado: 0, rendimento: Number(r.Valor) });
        });
        eventos.sort(function(x, y) { return x.data.localeCompare(y.data); });

        const labels = [];
        const dadosAplicado = [];
        const dadosAtual = [];
        let acumAplicado = 0;
        let acumRendimento = 0;

        if (eventos.length === 0) {
            const hoje = new Date().toISOString().slice(0, 10);
            const tot = window._cacheTotais || {};
            labels.push(Envelopei.dateBR(hoje));
            dadosAplicado.push(Number(tot.ValorAplicado) || 0);
            dadosAtual.push(Number(tot.ValorAtual) || 0);
        } else {
            eventos.forEach(function(ev) {
                acumAplicado += ev.aplicado;
                acumRendimento += ev.rendimento;
                labels.push(Envelopei.dateBR(ev.data));
                dadosAplicado.push(acumAplicado);
                dadosAtual.push(acumAplicado + acumRendimento);
            });
        }

        const ctx = document.getElementById('chartEvolucao');
        if (!ctx || typeof Chart === 'undefined') return;

        window._chartEvolucao = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Valor aplicado',
                        data: dadosAplicado,
                        borderColor: 'rgb(13, 110, 253)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.2
                    },
                    {
                        label: 'Valor atual',
                        data: dadosAtual,
                        borderColor: 'rgb(25, 135, 84)',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        fill: true,
                        tension: 0.2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + Envelopei.money(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); }
                        }
                    }
                }
            }
        });
    }

    function formatMoneySigned(num) {
        const n = Number(num);
        if (isNaN(n)) return '';
        const neg = n < 0;
        const s = Envelopei.formatMoneyForInput(Math.abs(n));
        return neg ? '-' + s : s;
    }

    function initProdutoPage() {
        var elAporteData = document.getElementById('aporteData');
        var elRendData = document.getElementById('rendData');
        if (elAporteData) elAporteData.value = new Date().toISOString().slice(0, 10);
        if (elRendData) elRendData.value = new Date().toISOString().slice(0, 10);

        document.querySelectorAll('.input-valor-assinado').forEach(applySignedMoneyMask);

        var btnSalvarAporte = document.getElementById('btnSalvarAporte');
        if (btnSalvarAporte) btnSalvarAporte.addEventListener('click', async function() {
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

        var btnSalvarRendimento = document.getElementById('btnSalvarRendimento');
        if (btnSalvarRendimento) btnSalvarRendimento.addEventListener('click', async function() {
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

        var btnExcluirAporte = document.getElementById('btnExcluirAporteAgora');
        if (btnExcluirAporte) btnExcluirAporte.addEventListener('click', async function() {
            var aporteId = document.getElementById('excluirAporteId').value;
            if (!aporteId) return;
            var r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/aportes/' + aporteId, 'DELETE', {});
            if (!r || !r.success) return Envelopei.toast(r && r.message ? r.message : 'Falha ao excluir.', 'danger');
            Envelopei.toast('Aporte excluído.', 'success');
            var modal = document.getElementById('modalExcluirAporte');
            if (modal && bootstrap.Modal.getInstance(modal)) bootstrap.Modal.getInstance(modal).hide();
            carregarHistorico();
        });

        var btnExcluirRendimento = document.getElementById('btnExcluirRendimentoAgora');
        if (btnExcluirRendimento) btnExcluirRendimento.addEventListener('click', async function() {
            var rendimentoId = document.getElementById('excluirRendimentoId').value;
            if (!rendimentoId) return;
            var r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/rendimentos/' + rendimentoId, 'DELETE', {});
            if (!r || !r.success) return Envelopei.toast(r && r.message ? r.message : 'Falha ao excluir.', 'danger');
            Envelopei.toast('Rendimento excluído.', 'success');
            var modal = document.getElementById('modalExcluirRendimento');
            if (modal && bootstrap.Modal.getInstance(modal)) bootstrap.Modal.getInstance(modal).hide();
            carregarHistorico();
        });

        var btnAtualizarAporte = document.getElementById('btnAtualizarAporte');
        if (btnAtualizarAporte) btnAtualizarAporte.addEventListener('click', async function() {
            var aporteId = document.getElementById('editAporteId').value;
            var DataAporte = document.getElementById('editAporteData').value;
            var Valor = Envelopei.parseMoney(document.getElementById('editAporteValor').value);
            var Descricao = document.getElementById('editAporteDesc').value.trim();
            if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor do aporte.', 'danger');
            var r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/aportes/' + aporteId, 'PUT', { DataAporte: DataAporte, Valor: Valor, Descricao: Descricao });
            if (!r || !r.success) return Envelopei.toast(r && r.message ? r.message : 'Falha ao atualizar.', 'danger');
            Envelopei.toast('Aporte atualizado!', 'success');
            var modal = document.getElementById('modalEditarAporte');
            if (modal && bootstrap.Modal.getInstance(modal)) bootstrap.Modal.getInstance(modal).hide();
            carregarHistorico();
        });

        var btnAtualizarRendimento = document.getElementById('btnAtualizarRendimento');
        if (btnAtualizarRendimento) btnAtualizarRendimento.addEventListener('click', async function() {
            var rendimentoId = document.getElementById('editRendimentoId').value;
            var DataRendimento = document.getElementById('editRendimentoData').value;
            var Valor = parseMoneySigned(document.getElementById('editRendimentoValor').value);
            var Descricao = document.getElementById('editRendimentoDesc').value.trim();
            if (Valor === 0) return Envelopei.toast('Informe um valor (positivo ou negativo).', 'danger');
            var r = await Envelopei.api('api/investimentos/produtos/' + PRODUTO_ID + '/rendimentos/' + rendimentoId, 'PUT', { DataRendimento: DataRendimento, Valor: Valor, Descricao: Descricao });
            if (!r || !r.success) return Envelopei.toast(r && r.message ? r.message : 'Falha ao atualizar.', 'danger');
            Envelopei.toast('Rendimento atualizado!', 'success');
            var modal = document.getElementById('modalEditarRendimento');
            if (modal && bootstrap.Modal.getInstance(modal)) bootstrap.Modal.getInstance(modal).hide();
            carregarHistorico();
        });

        carregarHistorico();
    }

    /** Máscara para valor com sinal (permite negativo). */
    function applySignedMoneyMask(input) {
        if (!input || input._signedMask) return;
        input._signedMask = true;
        input.setAttribute('inputmode', 'decimal');
        input.addEventListener('input', function() {
            var raw = this.value;
            var neg = raw.trim().indexOf('-') === 0;
            var v = raw.replace(/^-/, '').replace(/\D/g, '');
            if (v.length > 12) v = v.slice(0, 12);
            if (v.length === 0) { this.value = neg ? '-' : ''; return; }
            var intRaw = v.length <= 2 ? '0' : v.slice(0, -2);
            var intPart = intRaw.replace(/^0+/, '') || '0';
            var formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + v.slice(-2).padStart(2, '0');
            this.value = (neg ? '-' : '') + formatted;
        });
    }

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

    window.excluirAporte = function(aporteId) {
        document.getElementById('excluirAporteId').value = aporteId;
        new bootstrap.Modal(document.getElementById('modalExcluirAporte')).show();
    };

    window.excluirRendimento = function(rendimentoId) {
        document.getElementById('excluirRendimentoId').value = rendimentoId;
        new bootstrap.Modal(document.getElementById('modalExcluirRendimento')).show();
    };

    document.addEventListener('DOMContentLoaded', initProdutoPage);
</script>
<?= $this->endSection() ?>
