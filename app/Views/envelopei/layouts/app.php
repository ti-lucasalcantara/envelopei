<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Envelopei' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap-5.0.2/dist/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.1.0-web/css/fontawesome.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.1.0-web/css/brands.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.1.0-web/css/solid.css') ?>">
    <style>
        body { background: #f4f6f9; color: #263238; }
        .app-shell { min-height: 100vh; display: flex; }
        .app-sidebar { width: 278px; background: #182536; color: #dbe7f3; position: fixed; inset: 0 auto 0 0; overflow-y: auto; z-index: 1020; }
        .app-brand { height: 72px; display: flex; align-items: center; gap: 12px; padding: 0 22px; border-bottom: 1px solid rgba(255,255,255,.08); font-weight: 800; font-size: 1.15rem; }
        .app-menu { padding: 16px 12px 28px; }
        .app-menu a { color: #cbd7e3; text-decoration: none; display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 8px; margin-bottom: 4px; font-weight: 600; }
        .app-menu a:hover, .app-menu a.active { background: #0d6efd; color: #fff; }
        .app-main { margin-left: 278px; width: calc(100% - 278px); min-height: 100vh; }
        .app-topo { background: #fff; border-bottom: 1px solid #e7ebf0; padding: 18px 28px; display: flex; align-items: center;  gap: 16px; }
        .app-conteudo { padding: 28px; }
        .card { border: 1px solid #e6ebf1; border-radius: 10px; }
        .card-resumo { box-shadow: 0 10px 24px rgba(24,37,54,.06); }
        .table thead th { background: #f8fafc; color: #52616f; font-size: .82rem; text-transform: uppercase; letter-spacing: .02em; }
        .btn i { pointer-events: none; }
        .form-label { font-weight: 700; color: #3d4b59; }
        @media (max-width: 991.98px) {
            .app-sidebar { position: static; width: 100%; min-height: auto; }
            .app-shell { display: block; }
            .app-main { margin-left: 0; width: 100%; }
            .app-menu { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .app-topo, .app-conteudo { padding: 18px; }
        }
    </style>
    <?= $this->renderSection('css') ?>
</head>
<body>
<div class="app-shell">
    <?= $this->include('envelopei/partials/navbar') ?>

    <main class="app-main">
        <header class="app-topo">
            <div>
                <h1 class="h4 mb-1"><?= $tituloPagina ?? $titulo ?? 'Envelopei' ?></h1>
                <div class="text-muted"><?= $subtituloPagina ?? 'Controle financeiro pessoal por envelopes.' ?></div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('receitas/nova') ?>" class="btn btn-success">
                    <i class="fa-solid fa-plus me-1"></i> Receita
                </a>
                <a href="<?= site_url('despesas/nova') ?>" class="btn btn-danger">
                    <i class="fa-solid fa-minus me-1"></i> Despesa
                </a>
            </div>
        </header>

        <section class="app-conteudo">
            <?php if (session('sucesso')): ?>
                <div class="alert alert-success"><?= session('sucesso') ?></div>
            <?php endif; ?>
            <?php if (session('erro')): ?>
                <div class="alert alert-danger"><?= session('erro') ?></div>
            <?php endif; ?>
            <?php if (session('errors')): ?>
                <div class="alert alert-danger">
                    <?php foreach ((array) session('errors') as $erro): ?>
                        <div><?= $erro ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </section>
    </main>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div id="toastEnvelopei" class="toast border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastEnvelopeiTitulo">Envelopei</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
        <div class="toast-body" id="toastEnvelopeiMensagem"></div>
    </div>
</div>

<div class="modal fade" id="modalConfirmacaoEnvelopei" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmacaoTitulo">Confirmar ação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" id="modalConfirmacaoMensagem"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="modalConfirmacaoBotao">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= base_url('assets/bootstrap-5.0.2/dist/js/bootstrap.bundle.min.js') ?>"></script>
<script>
    const Envelopei = {
        async api(path, method = 'GET', body = null) {
            const opcoes = {
                method: method,
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
            };
            if (body) {
                opcoes.body = JSON.stringify(body);
            }

            const resposta = await fetch(`<?= base_url('') ?>/${path}`.replace(/\/+$/, ''), opcoes);
            let json = null;
            try { json = await resposta.json(); } catch (erro) {}

            if (resposta.status === 401) {
                window.location.href = "<?= base_url('login') ?>";
                return { success: false, message: 'Não autenticado.' };
            }

            return json ?? { success: false, message: 'Resposta inválida do servidor.' };
        },
        money(valor) {
            return Number(valor ?? 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        },
        dateBR(valor) {
            if (!valor) return '-';
            const partes = String(valor).slice(0, 10).split('-');
            return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : valor;
        },
        parseMoney(valor) {
            if (!valor) return 0;
            return Number(String(valor).replace(/R\$/g, '').replace(/\./g, '').replace(',', '.').trim()) || 0;
        },
        formatMoneyForInput(valor) {
            const numero = Number(valor ?? 0);
            if (Number.isNaN(numero)) {
                return '';
            }

            return numero.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        applyMoneyMask(input) {
            if (!input || input.dataset.mascaraDinheiro === '1') {
                return;
            }

            input.dataset.mascaraDinheiro = '1';
            input.setAttribute('inputmode', 'decimal');
            input.addEventListener('blur', function () {
                const valor = Envelopei.parseMoney(this.value);
                this.value = valor ? Envelopei.formatMoneyForInput(valor) : '';
            });
        },
        toast(mensagem, tipo = 'primary') {
            const toastEl = document.getElementById('toastEnvelopei');
            const tituloEl = document.getElementById('toastEnvelopeiTitulo');
            const mensagemEl = document.getElementById('toastEnvelopeiMensagem');
            if (!toastEl || !mensagemEl) {
                return;
            }

            const mapaTitulos = {
                success: 'Sucesso',
                danger: 'Atenção',
                warning: 'Atenção',
                info: 'Informação',
                primary: 'Envelopei'
            };

            toastEl.classList.remove('text-bg-primary', 'text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info');
            toastEl.classList.add('text-bg-' + (tipo || 'primary'));
            tituloEl.textContent = mapaTitulos[tipo] || 'Envelopei';
            mensagemEl.textContent = mensagem || '';

            bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4200 }).show();
        },
        confirmar(mensagem, titulo = 'Confirmar ação', textoBotao = 'Confirmar') {
            return new Promise((resolve) => {
                const modalEl = document.getElementById('modalConfirmacaoEnvelopei');
                const tituloEl = document.getElementById('modalConfirmacaoTitulo');
                const mensagemEl = document.getElementById('modalConfirmacaoMensagem');
                const botaoEl = document.getElementById('modalConfirmacaoBotao');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

                tituloEl.textContent = titulo;
                mensagemEl.textContent = mensagem;
                botaoEl.textContent = textoBotao;

                const limpar = () => {
                    botaoEl.removeEventListener('click', confirmarAcao);
                    modalEl.removeEventListener('hidden.bs.modal', cancelarAcao);
                };
                const confirmarAcao = () => {
                    limpar();
                    modal.hide();
                    resolve(true);
                };
                const cancelarAcao = () => {
                    limpar();
                    resolve(false);
                };

                botaoEl.addEventListener('click', confirmarAcao);
                modalEl.addEventListener('hidden.bs.modal', cancelarAcao);
                modal.show();
            });
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.input-money').forEach(function (input) {
            Envelopei.applyMoneyMask(input);
        });

        document.querySelectorAll('form[data-confirmacao]').forEach(function (formulario) {
            formulario.addEventListener('submit', async function (evento) {
                if (formulario.dataset.confirmado === '1') {
                    return;
                }

                evento.preventDefault();
                const confirmado = await Envelopei.confirmar(formulario.dataset.confirmacao);
                if (confirmado) {
                    formulario.dataset.confirmado = '1';
                    formulario.submit();
                }
            });
        });
    });
</script>
<?= $this->renderSection('js') ?>
</body>
</html>
