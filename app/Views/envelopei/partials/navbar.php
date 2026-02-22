<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
            <i class="fa-solid fa-wallet me-2"></i> Envelopei
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navEnvelopei">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navEnvelopei">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('dashboard') ?>"><i class="fa-solid fa-chart-pie me-2"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('lancamentos') ?>"><i class="fa-solid fa-receipt me-2"></i>Lançamentos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('envelopes') ?>"><i class="fa-solid fa-inbox me-2"></i>Envelopes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('contas') ?>"><i class="fa-solid fa-building-columns me-2"></i>Contas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('cartoes') ?>"><i class="fa-solid fa-credit-card me-2"></i>Cartões</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('faturas') ?>"><i class="fa-solid fa-file-invoice me-2"></i>Faturas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('rateios') ?>">
                        <i class="fa-solid fa-percent me-2"></i>Rateio Padrão
                    </a>
                </li>

            </ul>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" id="btnLogout">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>Sair
                </button>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnLogout');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const r = await Envelopei.api('api/logout', 'POST', {});
            if (r && r.success) {
                window.location.href = "<?= base_url('login') ?>";
            } else {
                Envelopei.toast(r?.message ?? 'Falha ao sair.', 'danger');
            }
        });
    });
</script>
