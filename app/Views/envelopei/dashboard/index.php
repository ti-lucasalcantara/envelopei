<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-3">
    <div>
        <h4 class="mb-0">Dashboard</h4>
        <div class="text-muted">Visão geral e conciliação</div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalReceita">
            <i class="fa-solid fa-circle-plus me-2"></i>Receita
        </button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalDespesa">
            <i class="fa-solid fa-circle-minus me-2"></i>Despesa
        </button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTransferencia">
            <i class="fa-solid fa-right-left me-2"></i>Transferir
        </button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Total Envelopes</div>
                <div class="fs-3 fw-bold" id="totalEnvelopes">—</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Total Contas</div>
                <div class="fs-3 fw-bold" id="totalContas">—</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Diferença (Conciliação)</div>
                <div class="fs-3 fw-bold" id="difConcil">—</div>
                <small class="text-muted">Ideal: 0,00</small>
            </div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-2">
    <h5 class="mb-0">Envelopes</h5>
    <a href="<?= base_url('envelopes') ?>" class="btn btn-sm btn-outline-dark">
        Gerenciar <i class="fa-solid fa-chevron-right ms-1"></i>
    </a>
</div>

<div class="row g-3" id="gridEnvelopes"></div>

<!-- MODAL: EXTRATO ENVELOPE -->
<div class="modal fade" id="modalExtrato" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="extratoTitulo">Extrato</h5>
                    <div class="text-muted small" id="extratoSub">—</div>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody id="extratoBody">
                            <tr><td colspan="4" class="text-center text-muted py-4">Carregando…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="fw-semibold">Saldo: <span id="extratoSaldo">—</span></div>
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: RECEITA -->
<div class="modal fade" id="modalReceita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Receita</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Conta</label>
                        <select class="form-select" id="recConta"></select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" id="recData">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Valor Total</label>
                        <input type="number" step="0.01" class="form-control" id="recValor">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="recDesc" placeholder="Salário, extra, etc.">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Rateio pré-definido</label>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <select class="form-select" id="recRateioModelo">
                                <option value="">— Rateio manual —</option>
                            </select>

                            <button class="btn btn-outline-primary" type="button" id="btnAplicarRateio">
                                <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Aplicar
                            </button>
                        </div>
                        <div class="text-muted small mt-1">
                            Selecione um modelo para preencher automaticamente o rateio por envelope.
                        </div>
                    </div>
                </div>



                <hr class="my-3">

                <div class="d-flex align-items-center justify-content-between">
                    <div class="fw-semibold">Rateio por envelope</div>
                    <button class="btn btn-sm btn-outline-primary" id="btnAddRateio">
                        <i class="fa-solid fa-plus me-1"></i>Adicionar linha
                    </button>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Envelope</th>
                                <th style="width:160px;">Modo</th>
                                <th style="width:160px;">Valor</th>
                                <th style="width:60px;"></th>
                            </tr>
                        </thead>
                        <tbody id="tbRateio"></tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    <div class="badge badge-soft px-3 py-2">
                        Soma do rateio: <span id="rateioSoma">0,00</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" id="btnSalvarReceita">
                    <i class="fa-solid fa-check me-2"></i>Salvar Receita
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: DESPESA -->
<div class="modal fade" id="modalDespesa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Despesa</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Conta</label>
                        <select class="form-select" id="desConta"></select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Envelope</label>
                        <select class="form-select" id="desEnvelope"></select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" id="desData">
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Valor</label>
                        <input type="number" step="0.01" class="form-control" id="desValor">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="desDesc" placeholder="Mercado, gasolina, etc.">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="btnSalvarDespesa">
                    <i class="fa-solid fa-check me-2"></i>Salvar Despesa
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: TRANSFERÊNCIA ENTRE ENVELOPES -->
<div class="modal fade" id="modalTransferencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transferência</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    Aqui é <b>transferência entre envelopes</b>. (Não mexe no saldo das contas.)
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Envelope Origem</label>
                        <select class="form-select" id="trEnvOrigem"></select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Envelope Destino</label>
                        <select class="form-select" id="trEnvDestino"></select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" id="trData">
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Valor</label>
                        <input type="number" step="0.01" class="form-control" id="trValor">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="trDesc" placeholder="Ajuste, realocação, etc.">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnSalvarTransferencia">
                    <i class="fa-solid fa-check me-2"></i>Transferir
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let cacheEnvelopes = [];
    let cacheContas = [];
    let cacheRateiosModelo = [];

    function opt(valor, texto) {
        return `<option value="${valor}">${texto}</option>`;
    }

    function setHoje() {
        const hoje = new Date().toISOString().slice(0,10);
        ['recData','desData','trData'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = hoje;
        });
    }

    function renderEnvelopesCards(envelopes) {
        const grid = document.getElementById('gridEnvelopes');
        if (!envelopes || envelopes.length === 0) {
            grid.innerHTML = `<div class="col-12"><div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>Nenhum envelope cadastrado.
            </div></div>`;
            return;
        }

        grid.innerHTML = envelopes.map(e => {
            const saldo = Number(e.Saldo ?? 0);
            const cor = e.Cor ? `style="border-left:6px solid ${e.Cor};"` : '';
            const badge = saldo < 0 ? 'text-bg-danger' : 'text-bg-success';

            return `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-hover shadow-sm cursor-pointer" ${cor} onclick="abrirExtrato(${e.EnvelopeId}, '${(e.Nome ?? '').replace(/'/g, "\\'")}')">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="fw-semibold">${e.Nome}</div>
                                <div class="text-muted small">Saldo atual</div>
                            </div>
                            <span class="badge ${badge}">•</span>
                        </div>
                        <div class="mt-2 fs-4 fw-bold">${Envelopei.money(saldo)}</div>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    async function carregarResumo() {
        const r = await Envelopei.api('api/dashboard/resumo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar dashboard.', 'danger');
            return;
        }

        const totais = r.data?.Totais ?? {};
        document.getElementById('totalEnvelopes').innerText = Envelopei.money(totais.TotalEnvelopes);
        document.getElementById('totalContas').innerText    = Envelopei.money(totais.TotalContas);
        document.getElementById('difConcil').innerText      = Envelopei.money(totais.Diferenca);

        cacheEnvelopes = r.data?.Envelopes ?? [];
        cacheContas    = r.data?.Contas ?? [];

        renderEnvelopesCards(cacheEnvelopes);
        preencherSelects();
    }

    async function carregarRateiosModelo() {
        const r = await Envelopei.api('api/rateios-modelo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar rateios.', 'danger');
            return;
        }

        cacheRateiosModelo = r.data ?? [];

        const opts = [
            `<option value="">— Rateio manual —</option>`,
            ...cacheRateiosModelo.map(m =>
                `<option value="${m.RateioModeloId}">
                    ${m.Nome}${Number(m.Padrao) === 1 ? ' (padrão)' : ''}
                </option>`
            )
        ];

        document.getElementById('recRateioModelo').innerHTML = opts.join('');
    }

    async function aplicarRateioModelo() {
        const id = Number($('#recRateioModelo').val() || 0);

        if (!id) {
            Envelopei.toast('Selecione um modelo de rateio.', 'warning');
            return;
        }

        const r = await Envelopei.api(`api/rateios-modelo/${id}`, 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar modelo.', 'danger');
            return;
        }

        const itens = r.data?.Itens ?? [];
        if (itens.length === 0) {
            Envelopei.toast('Modelo não possui itens.', 'warning');
            return;
        }

        // limpa rateio atual
        $('#tbRateio').html('');

        itens.forEach(i => {
            addLinhaRateioComDados(
                i.EnvelopeId,
                i.ModoRateio,
                i.Valor
            );
        });

        calcSomaRateio();
    }



    function preencherSelects() {
        // contas
        const contasHtml = cacheContas.map(c => opt(c.ContaId, `${c.Nome} (${Envelopei.money(c.SaldoAtual)})`)).join('');
        $('#recConta').html(`<option value="">Selecione...</option>${contasHtml}`);
        $('#desConta').html(`<option value="">Selecione...</option>${contasHtml}`);

        // envelopes
        const envHtml = cacheEnvelopes.map(e => opt(e.EnvelopeId, `${e.Nome} (${Envelopei.money(e.Saldo)})`)).join('');
        $('#desEnvelope').html(`<option value="">Selecione...</option>${envHtml}`);
        $('#trEnvOrigem').html(`<option value="">Selecione...</option>${envHtml}`);
        $('#trEnvDestino').html(`<option value="">Selecione...</option>${envHtml}`);

        // rateio table começa com 1 linha
        $('#tbRateio').html('');
        addLinhaRateio();
        calcSomaRateio();
    }

    function addLinhaRateio() {
        const envOptions = cacheEnvelopes.map(e => opt(e.EnvelopeId, e.Nome)).join('');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select class="form-select form-select-sm rateio-envelope">
                    <option value="">Selecione...</option>
                    ${envOptions}
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm rateio-modo">
                    <option value="valor">Valor</option>
                    <option value="percentual">%</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm rateio-valor" value="">
            </td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-danger btnRemoveRateio" title="Remover">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        document.getElementById('tbRateio').appendChild(row);

        row.querySelector('.btnRemoveRateio').addEventListener('click', () => {
            row.remove();
            calcSomaRateio();
        });

        row.querySelector('.rateio-valor').addEventListener('input', calcSomaRateio);
        row.querySelector('.rateio-modo').addEventListener('change', calcSomaRateio);
    }

    function addLinhaRateioComDados(envelopeId, modo, valor) {
        const envOptions = cacheEnvelopes.map(e =>
            `<option value="${e.EnvelopeId}" ${e.EnvelopeId == envelopeId ? 'selected' : ''}>${e.Nome}</option>`
        ).join('');

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select class="form-select form-select-sm rateio-envelope">
                    ${envOptions}
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm rateio-modo">
                    <option value="valor" ${modo === 'valor' ? 'selected' : ''}>Valor</option>
                    <option value="percentual" ${modo === 'percentual' ? 'selected' : ''}>%</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm rateio-valor" value="${valor}">
            </td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-danger btnRemoveRateio">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;

        row.querySelector('.btnRemoveRateio').addEventListener('click', () => {
            row.remove();
            calcSomaRateio();
        });

        row.querySelector('.rateio-valor').addEventListener('input', calcSomaRateio);
        row.querySelector('.rateio-modo').addEventListener('change', calcSomaRateio);

        document.getElementById('tbRateio').appendChild(row);
    }


    function calcSomaRateio() {
        const total = Array.from(document.querySelectorAll('.rateio-valor'))
            .map(i => Number(i.value || 0))
            .reduce((a,b) => a + b, 0);

        document.getElementById('rateioSoma').innerText = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    async function abrirExtrato(envelopeId, nome) {
        const modal = new bootstrap.Modal(document.getElementById('modalExtrato'));
        document.getElementById('extratoTitulo').innerText = `Extrato: ${nome}`;
        document.getElementById('extratoSub').innerText = 'Carregando…';
        document.getElementById('extratoBody').innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Carregando…</td></tr>`;
        document.getElementById('extratoSaldo').innerText = '—';

        modal.show();

        const r = await Envelopei.api(`api/envelopes/${envelopeId}/extrato`, 'GET');

        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar extrato.', 'danger');
            return;
        }

        const env = r.data?.Envelope ?? {};
        const itens = r.data?.Itens ?? [];

        document.getElementById('extratoSub').innerText = `Saldo atual: ${Envelopei.money(env.SaldoAtual)}`;
        document.getElementById('extratoSaldo').innerText = Envelopei.money(env.SaldoAtual);

        if (itens.length === 0) {
            document.getElementById('extratoBody').innerHTML =
                `<tr><td colspan="4" class="text-center text-muted py-4">Nenhum lançamento.</td></tr>`;
            return;
        }

        document.getElementById('extratoBody').innerHTML = itens.map(i => {
            const v = Number(i.Valor ?? 0);
            const badge = v < 0 ? 'text-bg-danger' : 'text-bg-success';
            return `
                <tr>
                    <td class="text-mono">${i.DataLancamento ?? '-'}</td>
                    <td><span class="badge ${badge}">${i.TipoLancamento ?? '-'}</span></td>
                    <td>${i.Descricao ?? '-'}</td>
                    <td class="text-end fw-semibold">${Envelopei.money(v)}</td>
                </tr>
            `;
        }).join('');
    }

    async function salvarReceita() {
        const ContaId        = Number($('#recConta').val() || 0);
        const DataLancamento = $('#recData').val();
        const ValorTotal     = Number($('#recValor').val() || 0);
        const Descricao      = ($('#recDesc').val() || '').trim();

        // ✅ novo: modelo de rateio (opcional)
        const RateioModeloId = Number($('#recRateioModelo').val() || 0);

        if (!ContaId) return Envelopei.toast('Selecione a conta.', 'danger');
        if (!ValorTotal || ValorTotal <= 0) return Envelopei.toast('Informe o valor total.', 'danger');

        // Se NÃO escolheu modelo, exige rateio manual (como era antes)
        let Rateios = [];
        if (!RateioModeloId) {
            Rateios = Array.from(document.querySelectorAll('#tbRateio tr')).map(tr => {
                const EnvelopeId     = Number(tr.querySelector('.rateio-envelope')?.value || 0);
                const ModoRateio     = tr.querySelector('.rateio-modo')?.value || 'valor';
                const ValorInformado = Number(tr.querySelector('.rateio-valor')?.value || 0);

                return { EnvelopeId, ModoRateio, ValorInformado };
            }).filter(r => r.EnvelopeId && r.ValorInformado > 0);

            if (Rateios.length === 0) return Envelopei.toast('Informe o rateio ou selecione um modelo.', 'danger');
        }

        const payload = {
            ContaId,
            DataLancamento,
            ValorTotal,
            Descricao
        };

        if (RateioModeloId) {
            payload.RateioModeloId = RateioModeloId; // ✅ backend busca e aplica o modelo
        } else {
            payload.Rateios = Rateios; // ✅ rateio manual
        }

        const r = await Envelopei.api('api/receitas', 'POST', payload);

        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar receita.', 'danger');

        Envelopei.toast('Receita registrada!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalReceita')).hide();
        await carregarResumo();
    }


    async function salvarDespesa() {
        const ContaId = Number($('#desConta').val() || 0);
        const EnvelopeId = Number($('#desEnvelope').val() || 0);
        const DataLancamento = $('#desData').val();
        const Valor = Number($('#desValor').val() || 0);
        const Descricao = $('#desDesc').val();

        if (!ContaId) return Envelopei.toast('Selecione a conta.', 'danger');
        if (!EnvelopeId) return Envelopei.toast('Selecione o envelope.', 'danger');
        if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');

        const r = await Envelopei.api('api/despesas', 'POST', { ContaId, EnvelopeId, DataLancamento, Valor, Descricao });

        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar despesa.', 'danger');

        Envelopei.toast('Despesa registrada!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalDespesa')).hide();
        await carregarResumo();
    }

    async function salvarTransferencia() {
        const EnvelopeOrigemId = Number($('#trEnvOrigem').val() || 0);
        const EnvelopeDestinoId = Number($('#trEnvDestino').val() || 0);
        const DataLancamento = $('#trData').val();
        const Valor = Number($('#trValor').val() || 0);
        const Descricao = $('#trDesc').val();

        if (!EnvelopeOrigemId) return Envelopei.toast('Selecione o envelope origem.', 'danger');
        if (!EnvelopeDestinoId) return Envelopei.toast('Selecione o envelope destino.', 'danger');
        if (EnvelopeOrigemId === EnvelopeDestinoId) return Envelopei.toast('Origem e destino não podem ser iguais.', 'danger');
        if (!Valor || Valor <= 0) return Envelopei.toast('Informe o valor.', 'danger');

        const r = await Envelopei.api('api/transferencias/envelopes', 'POST', { EnvelopeOrigemId, EnvelopeDestinoId, DataLancamento, Valor, Descricao });

        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao transferir.', 'danger');

        Envelopei.toast('Transferência realizada!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalTransferencia')).hide();
        await carregarResumo();
    }

    document.addEventListener('DOMContentLoaded', () => {
        setHoje();
        carregarResumo();
        carregarRateiosModelo();

        document.getElementById('btnAddRateio').addEventListener('click', addLinhaRateio);
        document.getElementById('btnSalvarReceita').addEventListener('click', salvarReceita);
        document.getElementById('btnSalvarDespesa').addEventListener('click', salvarDespesa);
        document.getElementById('btnSalvarTransferencia').addEventListener('click', salvarTransferencia);
        document.getElementById('btnAplicarRateio').addEventListener('click', aplicarRateioModelo);

        $('#recValor').on('input', calcSomaRateio);
    });
</script>
<?= $this->endSection() ?>
