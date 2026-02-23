<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('css') ?>
<style>
    .page-lancamentos .page-header-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .page-lancamentos .card-lanc { border-radius: 12px; overflow: hidden; }
    .page-lancamentos .card-lanc .card-header-custom { padding: 1rem 1.25rem; border-bottom: 1px solid rgba(0,0,0,.06); font-weight: 600; }
    .page-lancamentos .card-lanc .card-body-custom { padding: 1.25rem; }
    .page-lancamentos .form-label { font-size: 0.875rem; color: #495057; font-weight: 500; }
    .page-lancamentos .form-control, .page-lancamentos .form-select { border-radius: 8px; }
    .page-lancamentos .table-lanc { border-radius: 8px; overflow: hidden; }
    .page-lancamentos .table-lanc thead th { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #6c757d; padding: 0.75rem 1rem; border-bottom: 1px solid #dee2e6; }
    .page-lancamentos .table-lanc tbody td { padding: 0.85rem 1rem; vertical-align: middle; }
    .page-lancamentos .table-lanc tbody tr:hover { background-color: rgba(13, 110, 253, 0.04); }
    .page-lancamentos .td-acoes { position: relative; z-index: 2; white-space: nowrap; }
    .page-lancamentos .badge-tipo { padding: 0.35em 0.6em; border-radius: 0.25rem; font-size: 0.8em; font-weight: 500; color: #fff !important; border: 0; }
    .page-lancamentos .badge-tipo i { color: inherit; }
    .page-lancamentos .badge-tipo-receita { background-color: #198754 !important; }
    .page-lancamentos .badge-tipo-despesa { background-color: #dc3545 !important; }
    .page-lancamentos .badge-tipo-transferencia { background-color: #6c757d !important; }
    .page-lancamentos .badge-tipo-pgto { background-color: #0dcaf0 !important; }
    .page-lancamentos .badge-tipo-ajuste { background-color: #212529 !important; }
    .page-lancamentos .empty-state { padding: 2.5rem 1rem; text-align: center; color: #6c757d; }
    .page-lancamentos .empty-state i { font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.6; }
    .page-lancamentos #modalLanc .modal-content, .page-lancamentos #modalExcluir .modal-content { border-radius: 12px; border: 0; box-shadow: 0 10px 40px rgba(0,0,0,.15); }
    .page-lancamentos #modalLanc pre { border-radius: 8px; font-size: 0.8rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-lancamentos">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-header-icon bg-secondary bg-opacity-10">
                <i class="fa-solid fa-receipt text-secondary"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-semibold">Lançamentos</h4>
                <p class="text-muted small mb-0 mt-1">Filtre e navegue pelo histórico</p>
            </div>
        </div>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar ao Dashboard
        </a>
    </div>

    <!-- Filtros -->
    <div class="card card-lanc shadow-sm mb-4">
        <div class="card-header-custom bg-light">
            <i class="fa-solid fa-filter me-2 text-primary"></i>Filtros
        </div>
        <div class="card-body-custom">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label">Início</label>
                    <input type="date" class="form-control" id="fInicio">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label">Fim</label>
                    <input type="date" class="form-control" id="fFim">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" id="fTipo">
                        <option value="">Todos</option>
                        <option value="receita">Receita</option>
                        <option value="despesa">Despesa</option>
                        <option value="transferencia">Transferência</option>
                        <option value="pgto_fatura">Pagamento fatura</option>
                        <option value="pgto_parcial">Pagamento parcial</option>
                        <option value="ajuste">Ajuste</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label">Conta</label>
                    <select class="form-select" id="fConta">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label">Envelope</label>
                    <select class="form-select" id="fEnvelope">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex flex-md-column gap-2 align-items-start justify-content-md-end">
                    <button type="button" class="btn btn-primary" id="btnFiltrar">
                        <i class="fa-solid fa-search me-2"></i>Aplicar
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnLimpar">
                        <i class="fa-solid fa-broom me-2"></i>Limpar
                    </button>
                </div>
            </div>
            <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between">
                <span class="text-muted small" id="infoQtd">—</span>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card card-lanc shadow-sm">
        <div class="card-header-custom bg-light d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-list me-2 text-primary"></i>Lista de lançamentos</span>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table table-lanc align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:110px;">Data</th>
                            <th style="width:140px;">Tipo</th>
                            <th>Descrição</th>
                            <th style="width:200px;">Conta / Envelope</th>
                            <th class="text-end" style="width:120px;">Valor</th>
                            <th class="text-end" style="width:120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbLanc">
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

<!-- Modal Detalhe -->
<div class="modal fade" id="modalLanc" tabindex="-1" aria-labelledby="modalLancTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-semibold" id="modalLancTitle">Detalhes do lançamento</h5>
                    <div class="text-muted small mt-1" id="lSub">—</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <pre class="bg-light border rounded p-3 mb-0" id="lJson" style="max-height:420px; overflow:auto; font-size:0.8rem;"></pre>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger fw-semibold" id="modalExcluirTitle">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Excluir lançamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="excluirId">
                <p class="mb-2">Tem certeza que deseja excluir este lançamento?</p>
                <p class="text-muted small mb-0">Essa ação remove também os itens de conta/envelope e o rateio vinculado.</p>
                <div class="alert alert-warning small mt-3 mb-0">
                    <i class="fa-solid fa-circle-info me-2"></i>Não é possível desfazer.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnExcluirAgora">
                    <i class="fa-solid fa-trash me-2"></i>Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let cacheContas = [];
    let cacheEnvelopes = [];
    let cacheCartoes = [];
    let listaFull = [];
    let listaFiltrada = [];

    function setDefaultRange() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        document.getElementById('fInicio').value = `${y}-${m}-01`;
        document.getElementById('fFim').value = now.toISOString().slice(0, 10);
    }

    function badgeTipo(tipo) {
        const t = (tipo || '').toLowerCase();
        const b = 'badge badge-tipo';
        if (t === 'receita') return `<span class="${b} badge-tipo-receita"><i class="fa-solid fa-arrow-up me-1"></i>Receita</span>`;
        if (t === 'despesa') return `<span class="${b} badge-tipo-despesa"><i class="fa-solid fa-arrow-down me-1"></i>Despesa</span>`;
        if (t === 'transferencia') return `<span class="${b} badge-tipo-transferencia"><i class="fa-solid fa-right-left me-1"></i>Transferência</span>`;
        if (t === 'pgto_fatura') return `<span class="${b} badge-tipo-pgto"><i class="fa-solid fa-credit-card me-1"></i>Pgto. fatura</span>`;
        if (t === 'pgto_parcial') return `<span class="${b} badge-tipo-pgto"><i class="fa-solid fa-credit-card me-1"></i>Pgto. parc.</span>`;
        if (t === 'ajuste') return `<span class="${b} badge-tipo-ajuste"><i class="fa-solid fa-wrench me-1"></i>Ajuste</span>`;
        return `<span class="${b} badge-tipo-transferencia">${tipo || '-'}</span>`;
    }

    function fmtMoneyColored(v) {
        const n = Number(v || 0);
        const cls = n < 0 ? 'text-danger' : (n > 0 ? 'text-success' : 'text-muted');
        return `<span class="fw-semibold ${cls}">${Envelopei.money(n)}</span>`;
    }

    function getNomeConta(contaId) {
        const c = cacheContas.find(x => Number(x.ContaId) === Number(contaId));
        return c ? c.Nome : `Conta ${contaId}`;
    }

    function getNomeEnvelope(envelopeId) {
        const e = cacheEnvelopes.find(x => Number(x.EnvelopeId) === Number(envelopeId));
        return e ? e.Nome : `Envelope ${envelopeId}`;
    }

    function getNomeCartao(cartaoId) {
        const c = cacheCartoes.find(x => Number(x.CartaoCreditoId) === Number(cartaoId));
        if (!c) return `Cartão ${cartaoId}`;
        return c.Ultimos4Digitos ? `${c.Nome} ****${c.Ultimos4Digitos}` : c.Nome;
    }

    function preencherSelects() {
        const htmlContas = cacheContas.map(c => `<option value="${c.ContaId}">${c.Nome}</option>`).join('');
        document.getElementById('fConta').innerHTML = `<option value="">Todas</option>${htmlContas}`;
        const htmlEnv = cacheEnvelopes.map(e => `<option value="${e.EnvelopeId}">${e.Nome}</option>`).join('');
        document.getElementById('fEnvelope').innerHTML = `<option value="">Todos</option>${htmlEnv}`;
    }

    async function carregarBaseSelects() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar dados.', 'danger');
            return false;
        }
        cacheContas = r.data?.Contas ?? [];
        cacheEnvelopes = r.data?.Envelopes ?? [];
        cacheCartoes = r.data?.CartoesCredito ?? [];
        preencherSelects();
        return true;
    }

    async function carregarLancamentosAPI() {
        const inicio = document.getElementById('fInicio').value;
        const fim = document.getElementById('fFim').value;
        const tipo = document.getElementById('fTipo').value;
        const qs = new URLSearchParams();
        if (inicio) qs.set('inicio', inicio);
        if (fim) qs.set('fim', fim);
        if (tipo) qs.set('tipo', tipo);
        const r = await Envelopei.api(`api/lancamentos?${qs.toString()}`, 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
            return [];
        }
        const lista = r.data ?? [];
        lista.sort((a, b) => {
            const da = (a.DataLancamento || '').toString();
            const db = (b.DataLancamento || '').toString();
            if (da < db) return -1;
            if (da > db) return 1;
            return Number(a.LancamentoId) - Number(b.LancamentoId);
        });
        return lista;
    }

    async function enriquecerLancamentos(listaBasica) {
        const out = [];
        for (const l of listaBasica) {
            const det = await Envelopei.api(`api/lancamentos/${l.LancamentoId}`, 'GET');
            if (!det?.success) continue;
            const data = det.data ?? {};
            const itensConta = data.ItensConta ?? [];
            const itensEnvelope = data.ItensEnvelope ?? [];
            const contasIds = [...new Set(itensConta.map(x => Number(x.ContaId)).filter(Boolean))];
            const envelopesIds = [...new Set(itensEnvelope.map(x => Number(x.EnvelopeId)).filter(Boolean))];
            const valorConta = itensConta.reduce((sum, x) => sum + Number(x.Valor || 0), 0);
            const valorEnvelope = itensEnvelope.reduce((sum, x) => sum + Number(x.Valor || 0), 0);
            out.push({
                ...l,
                _contasIds: contasIds,
                _envelopesIds: envelopesIds,
                _valorConta: valorConta,
                _valorEnvelope: valorEnvelope,
            });
        }
        return out;
    }

    function aplicarFiltrosFront() {
        const contaId = Number(document.getElementById('fConta').value || 0);
        const envId = Number(document.getElementById('fEnvelope').value || 0);
        listaFiltrada = listaFull.filter(l => {
            if (contaId && !(l._contasIds || []).includes(contaId)) return false;
            if (envId && !(l._envelopesIds || []).includes(envId)) return false;
            return true;
        });
        renderTabela();
    }

    function renderTabela() {
        const tb = document.getElementById('tbLanc');
        const info = document.getElementById('infoQtd');

        if (!listaFiltrada.length) {
            tb.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fa-solid fa-inbox"></i><br>Nenhum lançamento no período.</td></tr>`;
            info.innerText = '0 lançamentos';
            return;
        }

        info.innerText = `${listaFiltrada.length} lançamento(s)`;

        tb.innerHTML = listaFiltrada.map(l => {
            const tipo = (l.TipoLancamento || '').toLowerCase();
            const contas = (l._contasIds || []);
            const envs = (l._envelopesIds || []);
            const cartaoId = Number(l.CartaoCreditoId || 0);

            let contaLabel = contas.length === 0 ? '-' : (contas.length === 1 ? getNomeConta(contas[0]) : `${getNomeConta(contas[0])} +${contas.length - 1}`);
            if (cartaoId && contaLabel === '-') contaLabel = `<span class="text-primary"><i class="fa-solid fa-credit-card me-1"></i>${getNomeCartao(cartaoId)}</span>`;
            const envLabel = envs.length === 0 ? '-' : (envs.length === 1 ? getNomeEnvelope(envs[0]) : `${getNomeEnvelope(envs[0])} +${envs.length - 1}`);

            let valor = Number(l._valorConta || 0);
            if (valor === 0 && cartaoId && tipo === 'despesa') valor = Number(l._valorEnvelope || 0);
            const valorHtml = (valor !== 0) ? fmtMoneyColored(valor) : `<span class="text-muted">—</span>`;

            const trClass = tipo === 'receita' ? 'tr-marker-success' : (tipo === 'despesa' || tipo === 'pgto_fatura' ? 'tr-marker-danger' : '');

            return `
                <tr class="${trClass}">
                    <td class="text-mono">${Envelopei.dateBR(l.DataLancamento)}</td>
                    <td>${badgeTipo(l.TipoLancamento)}</td>
                    <td>${(l.Descricao || '-').toString().substring(0, 60)}${(l.Descricao || '').length > 60 ? '…' : ''}</td>
                    <td>
                        <div class="small">
                            <div><i class="fa-solid fa-building-columns me-1 text-muted"></i>${contaLabel}</div>
                            <div><i class="fa-solid fa-inbox me-1 text-muted"></i>${envLabel}</div>
                        </div>
                    </td>
                    <td class="text-end">${valorHtml}</td>
                    <td class="text-end td-acoes">
                        <div class="btn-group btn-group-sm">
                            ${(tipo === 'receita' || tipo === 'despesa') ? `
                            <a href="<?= base_url('lancamentos/editar/') ?>${l.LancamentoId}" class="btn btn-outline-primary" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            ` : ''}
                            <button type="button" class="btn btn-outline-danger btn-excluir" data-id="${l.LancamentoId}" title="Excluir">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function detalhar(id) {
        const r = await Envelopei.api(`api/lancamentos/${id}`, 'GET');
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao carregar.', 'danger');
        document.getElementById('lSub').innerText = `ID: ${id}`;
        document.getElementById('lJson').textContent = JSON.stringify(r.data, null, 2);
        new bootstrap.Modal(document.getElementById('modalLanc')).show();
    }

    function confirmarExcluir(id) {
        document.getElementById('excluirId').value = id;
        new bootstrap.Modal(document.getElementById('modalExcluir')).show();
    }

    async function excluirLancamento() {
        const id = Number(document.getElementById('excluirId').value || 0);
        if (!id) return;
        const r = await Envelopei.api(`api/lancamentos/${id}`, 'DELETE', {});
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao excluir.', 'danger');
            return;
        }
        Envelopei.toast('Lançamento excluído!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalExcluir')).hide();
        listaFull = listaFull.filter(x => Number(x.LancamentoId) !== id);
        aplicarFiltrosFront();
    }

    async function aplicarFiltros() {
        const tb = document.getElementById('tbLanc');
        tb.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fa-solid fa-spinner fa-spin d-block"></i>Carregando…</td></tr>`;
        document.getElementById('infoQtd').innerText = 'Carregando…';
        const basica = await carregarLancamentosAPI();
        listaFull = await enriquecerLancamentos(basica);
        aplicarFiltrosFront();
    }

    function limparFiltros() {
        setDefaultRange();
        document.getElementById('fTipo').value = '';
        document.getElementById('fConta').value = '';
        document.getElementById('fEnvelope').value = '';
        aplicarFiltros();
    }

    document.addEventListener('DOMContentLoaded', async () => {
        setDefaultRange();

        document.getElementById('tbLanc').addEventListener('click', function(e) {
            const btnDet = e.target.closest('.btn-detalhar');
            const btnExc = e.target.closest('.btn-excluir');
            if (btnDet) {
                e.preventDefault();
                const id = parseInt(btnDet.getAttribute('data-id'), 10);
                if (id) detalhar(id);
            }
            if (btnExc) {
                e.preventDefault();
                const id = parseInt(btnExc.getAttribute('data-id'), 10);
                if (id) confirmarExcluir(id);
            }
            // Editar: link direto, não interceptar
        });

        const ok = await carregarBaseSelects();
        if (!ok) return;

        document.getElementById('btnFiltrar').addEventListener('click', aplicarFiltros);
        document.getElementById('btnLimpar').addEventListener('click', limparFiltros);
        document.getElementById('btnExcluirAgora').addEventListener('click', excluirLancamento);

        aplicarFiltros();
    });
</script>
<?= $this->endSection() ?>
