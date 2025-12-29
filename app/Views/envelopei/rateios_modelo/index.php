<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0">Rateio Pré-definido</h4>
        <div class="text-muted">Crie modelos de rateio e defina um como padrão para reaproveitar nas receitas.</div>
    </div>

    <button class="btn btn-primary" id="btnNovo">
        <i class="fa-solid fa-plus me-2"></i>Novo modelo
    </button>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-light text-dark border">
                <i class="fa-solid fa-circle-info me-1"></i>
                Dica: mantenha o modelo em <strong>percentual</strong> somando 100%.
            </span>

            <span class="badge bg-light text-dark border">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                Você pode criar modelos diferentes: “Padrão”, “Freelas”, “13º”, etc.
            </span>

            <div class="ms-auto text-muted small">
                <span id="infoQtd">—</span>
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
                        <th style="width:60px;">#</th>
                        <th>Nome</th>
                        <th style="width:120px;">Padrão</th>
                        <th style="width:140px;">Itens</th>
                        <th class="text-end" style="width:220px;">Ações</th>
                    </tr>
                </thead>
                <tbody id="tbModelos">
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Carregando…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- MODAL CRIAR/EDITAR -->
<div class="modal fade" id="modalModelo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="mTitulo">Novo modelo</h5>
                    <div class="text-muted small" id="mSub">Configure os percentuais por envelope</div>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="mId">

                <div class="row g-3">
                    <div class="col-12 col-lg-8">
                        <label class="form-label">Nome do modelo</label>
                        <input type="text" class="form-control" id="mNome" placeholder="Ex: Padrão Mensal">
                    </div>

                    <div class="col-12 col-lg-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="mPadrao">
                            <label class="form-check-label" for="mPadrao">
                                Definir como padrão
                            </label>
                            <div class="text-muted small">Somente um modelo pode ser padrão.</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge bg-light text-dark border">
                                Total: <strong><span id="mSoma">0</span>%</strong>
                            </span>
                            <span class="badge bg-light text-dark border">
                                Restante: <strong><span id="mRestante">100</span>%</strong>
                            </span>

                            <div class="ms-auto">
                                <button class="btn btn-sm btn-outline-dark" id="btnAuto100">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                                    Ajustar último para fechar 100%
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive border rounded">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Envelope</th>
                                        <th style="width:160px;">Modo</th>
                                        <th style="width:160px;">Valor (%)</th>
                                        <th class="text-end" style="width:80px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="mItens">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Carregando envelopes…</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-muted small mt-2">
                            Por enquanto o recomendado é usar <strong>percentual</strong>. (Valor fixo pode ser habilitado depois.)
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnSalvar">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Salvar
                </button>
            </div>

        </div>
    </div>
</div>


<!-- MODAL CONFIRMAÇÃO DESATIVAR -->
<div class="modal fade" id="modalDesativar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Desativar modelo
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="dId">
                <div>Tem certeza que deseja desativar este modelo?</div>
                <div class="text-muted small mt-1">Ele não aparecerá mais para uso, mas pode ser recriado depois.</div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="btnDesativarAgora">
                    <i class="fa-solid fa-ban me-2"></i>Desativar
                </button>
            </div>

        </div>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    let envelopes = [];
    let modelos = [];

    function toNum(v) {
        const n = Number(String(v ?? '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function atualizarSoma() {
        let soma = 0;

        $('#mItens tr[data-env]').each(function(){
            const modo = $(this).find('.modo').val();
            const val  = toNum($(this).find('.valor').val());

            if (modo === 'percentual') soma += val;
        });

        soma = Math.round(soma * 100) / 100;
        const restante = Math.round((100 - soma) * 100) / 100;

        $('#mSoma').text(soma.toFixed(2).replace('.', ','));
        $('#mRestante').text(restante.toFixed(2).replace('.', ','));

        if (Math.abs(restante) < 0.01) {
            $('#mRestante').closest('.badge').removeClass('border').addClass('border border-success');
        } else {
            $('#mRestante').closest('.badge').removeClass('border-success').addClass('border');
        }
    }

    function montarItensModal(itensExistentes = []) {
        const map = {};
        (itensExistentes || []).forEach(i => {
            map[Number(i.EnvelopeId)] = i;
        });

        const rows = envelopes.map(e => {
            const ex = map[Number(e.EnvelopeId)] ?? null;
            const modo = ex?.ModoRateio ?? 'percentual';
            const valor = ex?.Valor ?? 0;

            return `
                <tr data-env="${e.EnvelopeId}">
                    <td>
                        <div class="fw-semibold">${e.Nome}</div>
                        <div class="text-muted small">${e.Descricao ?? ''}</div>
                    </td>
                    <td>
                        <select class="form-select form-select-sm modo">
                            <option value="percentual" ${modo === 'percentual' ? 'selected' : ''}>percentual</option>
                            <option value="valor" ${modo === 'valor' ? 'selected' : ''}>valor</option>
                        </select>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control valor" value="${String(valor).replace('.', ',')}">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-dark btnZerar" title="Zerar">
                            <i class="fa-solid fa-eraser"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        $('#mItens').html(rows);

        // eventos
        $('#mItens .valor, #mItens .modo').on('input change', atualizarSoma);
        $('#mItens .btnZerar').on('click', function(){
            const tr = $(this).closest('tr');
            tr.find('.valor').val('0');
            tr.find('.modo').val('percentual');
            atualizarSoma();
        });

        atualizarSoma();
    }

    function payloadModelo() {
        const Nome = ($('#mNome').val() || '').trim();
        const Padrao = $('#mPadrao').is(':checked') ? 1 : 0;

        const Itens = [];
        $('#mItens tr[data-env]').each(function(idx){
            const EnvelopeId = Number($(this).attr('data-env'));
            const ModoRateio = $(this).find('.modo').val();
            const Valor = toNum($(this).find('.valor').val());

            // ignora itens zerados
            if (Valor <= 0) return;

            Itens.push({
                EnvelopeId,
                ModoRateio,
                Valor,
                Ordem: idx + 1
            });
        });

        return { Nome, Padrao, Itens };
    }

    function validarModelo(p) {
        if (!p.Nome) return 'Informe o nome do modelo.';
        if (!Array.isArray(p.Itens) || p.Itens.length === 0) return 'Informe ao menos 1 envelope no rateio.';

        // valida soma percentual (somente itens com modo percentual)
        let soma = 0;
        p.Itens.forEach(i => {
            if (i.ModoRateio === 'percentual') soma += Number(i.Valor || 0);
        });

        soma = Math.round(soma * 100) / 100;
        if (Math.abs(soma - 100) > 0.01) return 'A soma dos percentuais deve ser 100%.';

        return null;
    }

    async function carregarEnvelopes() {
        const r = await Envelopei.api('api/envelopes', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar envelopes.', 'danger');
            return false;
        }
        envelopes = r.data ?? [];
        return true;
    }

    async function carregarModelos() {
        const r = await Envelopei.api('api/rateios-modelo', 'GET');
        if (!r?.success) {
            Envelopei.toast(r?.message ?? 'Falha ao carregar modelos.', 'danger');
            return;
        }

        modelos = r.data ?? [];
        renderTabela();
    }

    function renderTabela() {
        const tb = $('#tbModelos');

        $('#infoQtd').text(`${modelos.length} modelo(s)`);

        if (!modelos.length) {
            tb.html(`<tr><td colspan="5" class="text-center text-muted py-4">Nenhum modelo cadastrado.</td></tr>`);
            return;
        }

        tb.html(modelos.map(m => {
            const badgePadrao = Number(m.Padrao) === 1
                ? `<span class="badge text-bg-success"><i class="fa-solid fa-star me-1"></i>padrão</span>`
                : `<span class="badge bg-light text-dark border">—</span>`;

            return `
                <tr>
                    <td class="text-muted">${m.RateioModeloId}</td>
                    <td class="fw-semibold">${m.Nome}</td>
                    <td>${badgePadrao}</td>
                    <td class="text-muted">—</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-dark" onclick="editar(${m.RateioModeloId})" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn btn-outline-success" onclick="definirPadrao(${m.RateioModeloId})" title="Definir como padrão">
                                <i class="fa-solid fa-star"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="abrirDesativar(${m.RateioModeloId})" title="Desativar">
                                <i class="fa-solid fa-ban"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join(''));

        // opcional: mostrar contagem de itens sem N chamadas
        // quando editar, buscamos os itens e aí o usuário vê.
    }

    function abrirNovo() {
        $('#mId').val('');
        $('#mTitulo').text('Novo modelo');
        $('#mSub').text('Configure os percentuais por envelope');
        $('#mNome').val('Padrão Mensal');
        $('#mPadrao').prop('checked', true);

        montarItensModal([]);
        new bootstrap.Modal(document.getElementById('modalModelo')).show();
    }

    async function editar(id) {
        const r = await Envelopei.api(`api/rateios-modelo/${id}`, 'GET');
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao carregar modelo.', 'danger');

        const modelo = r.data?.Modelo ?? null;
        const itens  = r.data?.Itens ?? [];

        if (!modelo) return Envelopei.toast('Modelo inválido.', 'danger');

        $('#mId').val(modelo.RateioModeloId);
        $('#mTitulo').text(`Editar modelo #${modelo.RateioModeloId}`);
        $('#mSub').text('Ajuste os percentuais e salve');
        $('#mNome').val(modelo.Nome);
        $('#mPadrao').prop('checked', Number(modelo.Padrao) === 1);

        montarItensModal(itens);

        new bootstrap.Modal(document.getElementById('modalModelo')).show();
    }

    async function salvar() {
        const id = Number($('#mId').val() || 0);
        const p = payloadModelo();

        const erro = validarModelo(p);
        if (erro) return Envelopei.toast(erro, 'danger');

        try {
            let r;
            if (id > 0) {
                r = await Envelopei.api(`api/rateios-modelo/${id}`, 'PUT', p);
            } else {
                r = await Envelopei.api(`api/rateios-modelo`, 'POST', p);
            }

            if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao salvar.', 'danger');

            Envelopei.toast('Modelo salvo!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalModelo')).hide();

            carregarModelos();
        } catch (e) {
            Envelopei.toast('Erro inesperado ao salvar.', 'danger');
        }
    }

    async function definirPadrao(id) {
        const r = await Envelopei.api(`api/rateios-modelo/${id}/definir-padrao`, 'POST', {});
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao definir padrão.', 'danger');

        Envelopei.toast('Modelo definido como padrão!', 'success');
        carregarModelos();
    }

    function abrirDesativar(id) {
        $('#dId').val(id);
        new bootstrap.Modal(document.getElementById('modalDesativar')).show();
    }

    async function desativarAgora() {
        const id = Number($('#dId').val() || 0);
        if (!id) return;

        const r = await Envelopei.api(`api/rateios-modelo/${id}`, 'DELETE', {});
        if (!r?.success) return Envelopei.toast(r?.message ?? 'Falha ao desativar.', 'danger');

        Envelopei.toast('Modelo desativado!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalDesativar')).hide();
        carregarModelos();
    }

    function ajustarUltimo() {
        // Ajusta o último item (em percentual) para fechar 100%
        let soma = 0;
        const rows = $('#mItens tr[data-env]');

        // soma todos menos o último "percentual" com valor > 0
        let lastRow = null;

        rows.each(function(){
            const modo = $(this).find('.modo').val();
            const val  = toNum($(this).find('.valor').val());
            if (modo === 'percentual' && val > 0) lastRow = this;
        });

        if (!lastRow) return Envelopei.toast('Nenhum item percentual para ajustar.', 'warning');

        rows.each(function(){
            if (this === lastRow) return;
            const modo = $(this).find('.modo').val();
            const val  = toNum($(this).find('.valor').val());
            if (modo === 'percentual') soma += val;
        });

        soma = Math.round(soma * 100) / 100;
        const resto = Math.round((100 - soma) * 100) / 100;

        $(lastRow).find('.valor').val(String(resto).replace('.', ','));
        atualizarSoma();
    }

    document.addEventListener('DOMContentLoaded', async () => {
        $('#btnNovo').on('click', abrirNovo);
        $('#btnSalvar').on('click', salvar);
        $('#btnDesativarAgora').on('click', desativarAgora);
        $('#btnAuto100').on('click', ajustarUltimo);

        const okEnv = await carregarEnvelopes();
        if (!okEnv) return;

        carregarModelos();
    });
</script>
<?= $this->endSection() ?>
