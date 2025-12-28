<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Envelopei' ?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap-5.0.2/dist/css/bootstrap.min.css') ?>">

    <!-- FontAwesome (7.1.0) -->
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.1.0-web/css/fontawesome.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.1.0-web/css/brands.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.1.0-web/css/solid.css') ?>">

    <style>
        body { background: #f6f7fb; }
        .card-hover { transition: .15s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.08); }
        .navbar-brand { font-weight: 700; letter-spacing: .2px; }
        .badge-soft { background: rgba(13,110,253,.1); color: #0d6efd; border: 1px solid rgba(13,110,253,.2); }
        .text-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
        .cursor-pointer { cursor: pointer; }
    </style>

    <?= $this->renderSection('css') ?>
</head>

<body>

<?= $this->include('envelopei/partials/navbar') ?>

<main class="container py-4">
    <?= $this->renderSection('content') ?>
</main>

<?= $this->include('envelopei/partials/toast') ?>

<!-- jQuery -->
<script src="<?= base_url('assets/js/jquery-3.7.1.min.js') ?>"></script>

<!-- Bootstrap JS -->
<script src="<?= base_url('assets/bootstrap-5.0.2/dist/js/bootstrap.bundle.min.js') ?>"></script>

<script>
    // ================================
    // Helpers Envelopei (API)
    // ================================
    const Envelopei = {
        async api(path, method='GET', body=null) {
            const opts = {
                method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };

            if (body) opts.body = JSON.stringify(body);

            const res = await fetch(`<?= base_url('') ?>/${path}`.replace(/\/+$/, ''), opts);

            let json = null;
            try { json = await res.json(); } catch(e) {}

            if (res.status === 401) {
                // não autenticado -> manda pro login
                window.location.href = "<?= base_url('login') ?>";
                return { success:false, message:'Não autenticado.' };
            }

            return json ?? { success:false, message:'Resposta inválida do servidor.' };
        },

        money(v) {
            const n = Number(v ?? 0);
            return n.toLocaleString('pt-BR', { style:'currency', currency:'BRL' });
        },

        toast(msg, type='primary') {
            const el = document.getElementById('appToast');
            const title = document.getElementById('appToastTitle');
            const body  = document.getElementById('appToastBody');

            title.innerText = (type === 'danger') ? 'Atenção' : 'Envelopei';
            body.innerText  = msg;

            el.classList.remove('text-bg-primary','text-bg-danger','text-bg-success','text-bg-warning');
            el.classList.add(`text-bg-${type}`);

            const toast = new bootstrap.Toast(el, { delay: 3500 });
            toast.show();
        }
    };
</script>

<?= $this->renderSection('js') ?>
</body>
</html>
