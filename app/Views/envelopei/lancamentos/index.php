<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Lançamentos</h4>
        <div class="text-muted">Filtre e navegue pelo histórico</div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-2">
                <label class="form-label">Início</label>
                <input type="date" class="form-control" id="fInicio">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Fim</label>
                <input type="date" class="form-control" id="fFim">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Tipo</label>
                <select class="form-select" id="fTipo">
                    <option value="">Todos</option>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                    <option value="transferencia">Transferência</option>
                    <option value="ajuste">Ajuste</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Conta / Banco</label>
                <select class="form-select" id="fConta">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Envelope</label>
                <select class="form-select" id="fEnvelope">
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" id="btnFiltrar">
                    <i class="fa-solid fa-filter me-2"></i>Aplicar filtros
                </button>
                <button class="btn btn-outline-dark" id="btnLimpar">
                    <i class="fa-solid fa-broom me-2"></i>Limpar
                </button>

                <div class="ms-auto text-muted small align-self-center">
                    <span id="infoQtd">—</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px;">Data</th>
                        <th style="width:140px;">Tipo</th>
                        <th>Descrição</th>
                        <th style="width:200px;">Conta / Envelope</th>
                        <th class="text-end" style="width:140px;">Valor</th>
                        <th class="text-end" style="width:120px;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbLanc">
                    <tr><td colspan="6" class="text-center text-muted py-4">Carregando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DETALHE -->
<div class="modal fade" id="modalLanc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Detalhes do Lançamento</h5>
                    <div class="text-muted small" id="lSub">—</div>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre class="bg-light border rounded p-3 mb-0" id="lJson" style="max-height:420px; overflow:auto;"></pre>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EXCLUIR -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Excluir lançamento
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="excluirId">
                <div class="mb-2">Tem certeza que deseja excluir este lançamento?</div>
                <div class="text-muted small">
                    Essa ação remove também os itens de conta/envelope e o rateio vinculado.
                </div>

                <div class="alert alert-warning small mt-3 mb-0">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    Dica: use com cuidado — não dá pra desfazer.
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="btnExcluirAgora">
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

    // lista completa (enriquecida) e lista filtrada
    let listaFull = [];
    let listaFiltrada = [];

    function setDefaultRange() {
        // por padrão: mês atual
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth()+1).padStart(2,'0');
        document.getElementById('fInicio').value = `${y}-${m}-01`;
        document.getElementById('fFim').value = now.toISOString().slice(0,10);
    }

    function badgeTipo(tipo) {
        const t = (tipo || '').toLowerCase();
        if (t === 'receita') return `<span class="badge text-bg-success"><i class="fa-solid fa-arrow-up me-1"></i>receita</span>`;
        if (t === 'despesa') return `<span class="badge text-bg-danger"><i class="fa-solid fa-arrow-down me-1"></i>despesa</span>`;
        if (t === 'transferencia') return `<span class="badge text-bg-secondary"><i class="fa-solid fa-right-left me-1"></i>transferência</span>`;
        if (t === 'ajuste') return `<span class="badge text-bg-dark"><i class="fa-solid fa-wrench me-1"></i>ajuste</span>`;
        return `<span class="badge bg-light text-dark border">${tipo || '-'}</span>`;
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

    function preencherSelects() {
        // contas (dashboard/resumo já retorna SaldoAtual)
        const htmlContas = cacheContas.map(c => `<option value="${c.ContaId}">${c.Nome}</option>`).join('');
        document.getElementById('fConta').innerHTML = `<option value="">Todas</option>${htmlContas}`;

        // envelopes
        const htmlEnv = cacheEnvelopes.map(e => `<option value="${e.EnvelopeId}">${e.Nome}</option>`).join('');
        document.getElementById('fEnvelope').innerHTML = `<option value="">Todos</option>${htmlEnv}`;
    }

    async function carregarBaseSelects() {
        // pega envelopes + contas num endpoint só
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar dados base.', 'danger');
            return false;
        }

        cacheContas = r.data?.Contas ?? [];
        cacheEnvelopes = r.data?.Envelopes ?? [];

        preencherSelects();
        return true;
    }

    async function carregarLancamentosAPI() {
        const inicio = document.getElementById('fInicio').value;
        const fim    = document.getElementById('fFim').value;
        const tipo   = document.getElementById('fTipo').value;

        const qs = new URLSearchParams();
        if (inicio) qs.set('inicio', inicio);
        if (fim)    qs.set('fim', fim);
        if (tipo)   qs.set('tipo', tipo);

        // sua API hoje ordena DESC, mas vamos reordenar no front para "mais antigo primeiro"
        const r = await Envelopei.api(`api/lancamentos?${qs.toString()}`, 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar lançamentos.', 'danger');
            return [];
        }

        const lista = r.data ?? [];

        // Ordena mais antigo primeiro (DataLancamento, depois ID)
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
        // Para permitir filtro por conta/envelope e mostrar valores/nomes
        // buscamos os detalhes do lançamento.
        const out = [];

        for (const l of listaBasica) {
            const id = l.LancamentoId;
            const det = await Envelopei.api(`api/lancamentos/${id}`, 'GET');

            if (!det?.success) continue;

            const data = det.data ?? {};
            const itensConta = data.ItensConta ?? [];
            const itensEnvelope = data.ItensEnvelope ?? [];

            const contasIds = [...new Set(itensConta.map(x => Number(x.ContaId)).filter(Boolean))];
            const envelopesIds = [...new Set(itensEnvelope.map(x => Number(x.EnvelopeId)).filter(Boolean))];

            // valor total na conta (soma dos itens de conta)
            const valorConta = itensConta.reduce((sum, x) => sum + Number(x.Valor || 0), 0);

            out.push({
                ...l,
                _contasIds: contasIds,
                _envelopesIds: envelopesIds,
                _valorConta: valorConta,
            });
        }

        return out;
    }

    function aplicarFiltrosFront() {
        const contaId = Number(document.getElementById('fConta').value || 0);
        const envId   = Number(document.getElementById('fEnvelope').value || 0);

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
            tb.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Nenhum lançamento.</td></tr>`;
            info.innerText = '0 lançamentos';
            return;
        }

        info.innerText = `${listaFiltrada.length} lançamento(s)`;

        tb.innerHTML = listaFiltrada.map(l => {
            const tipo = (l.TipoLancamento || '').toLowerCase();

            // nome(s) conta/envelope (exibe 1 ou "múltiplos")
            const contas = (l._contasIds || []);
            const envs   = (l._envelopesIds || []);

            const contaLabel = contas.length === 0 ? '-' : (contas.length === 1 ? getNomeConta(contas[0]) : `${getNomeConta(contas[0])} +${contas.length-1}`);
            const envLabel   = envs.length === 0 ? '-' : (envs.length === 1 ? getNomeEnvelope(envs[0]) : `${getNomeEnvelope(envs[0])} +${envs.length-1}`);

            // valor: para receita/despesa fica bem; para transferência entre envelopes pode vir 0 em conta
            // então mostramos:
            // - se tiver valorConta != 0 -> usa ele
            // - senão, mostra "—" (ou 0)
            const valor = Number(l._valorConta || 0);
            const valorHtml = (valor !== 0) ? fmtMoneyColored(valor) : `<span class="text-muted">—</span>`;

            // linha levemente colorida (receita/despesa)
            const trClass = tipo === 'receita' ? 'table-success' : (tipo === 'despesa' ? 'table-danger' : '');

            return `
                <tr class="${trClass}">
                    <td class="text-mono">${l.DataLancamento ?? '-'}</td>
                    <td>${badgeTipo(l.TipoLancamento)}</td>
                    <td>${l.Descricao ?? '-'}</td>
                    <td>
                        <div class="small">
                            <div><i class="fa-solid fa-building-columns me-1 text-muted"></i>${contaLabel}</div>
                            <div><i class="fa-solid fa-inbox me-1 text-muted"></i>${envLabel}</div>
                        </div>
                    </td>
                    <td class="text-end">${valorHtml}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-dark" onclick="detalhar(${l.LancamentoId})" title="Ver detalhes">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="confirmarExcluir(${l.LancamentoId})" title="Excluir lançamento">
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
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao carregar detalhe.', 'danger');

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

        // remove do cache local pra não precisar recarregar tudo
        listaFull = listaFull.filter(x => Number(x.LancamentoId) !== id);
        aplicarFiltrosFront();
    }


    async function aplicarFiltros() {
        const tb = document.getElementById('tbLanc');
        tb.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">Carregando…</td></tr>`;
        document.getElementById('infoQtd').innerText = 'Carregando…';

        // 1) lista por período/tipo via API
        const basica = await carregarLancamentosAPI();

        // 2) enriquece pra ter conta/envelope/valor
        listaFull = await enriquecerLancamentos(basica);

        // 3) aplica filtro de conta/envelope no front
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

        const ok = await carregarBaseSelects();
        if (!ok) return;

        document.getElementById('btnFiltrar').addEventListener('click', aplicarFiltros);
        document.getElementById('btnLimpar').addEventListener('click', limparFiltros);
        document.getElementById('btnExcluirAgora').addEventListener('click', excluirLancamento);

        // carrega inicial
        aplicarFiltros();
    });
</script>
<?= $this->endSection() ?>
