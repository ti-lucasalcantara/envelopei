<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Envelopei' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap-5.0.2/dist/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.1.0-web/css/fontawesome.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.1.0-web/css/solid.css') ?>">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eef4ff 0%, #f7f9fc 45%, #e8f7f0 100%);
        }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            border: 1px solid #e3e9f2;
            border-radius: 14px;
            box-shadow: 0 18px 45px rgba(24, 37, 54, .10);
        }
        .auth-marca {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0d6efd;
            color: #fff;
        }
        .form-label { font-weight: 700; color: #3d4b59; }
    </style>
    <?= $this->renderSection('css') ?>
</head>
<body>
<main class="auth-wrapper">
    <?= $this->renderSection('content') ?>
</main>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div id="toastEnvelopei" class="toast border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastEnvelopeiTitulo">Envelopei</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
        <div class="toast-body" id="toastEnvelopeiMensagem"></div>
    </div>
</div>

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

            return json ?? { success: false, message: 'Resposta inválida do servidor.' };
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
        }
    };
</script>
<?= $this->renderSection('js') ?>
</body>
</html>
