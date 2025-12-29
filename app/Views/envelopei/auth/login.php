<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-1"><i class="fa-solid fa-wallet me-2"></i>Envelopei</h4>
                <p class="text-muted mb-4">Entre com seu e-mail e senha.</p>

                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="Email">
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" class="form-control" id="Senha">
                </div>

                <button class="btn btn-primary w-100" id="btnLogin">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Entrar
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnLogin');

        btn.addEventListener('click', async () => {
            const Email = document.getElementById('Email').value.trim();
            const Senha = document.getElementById('Senha').value;

            const r = await Envelopei.api('api/login', 'POST', { Email, Senha });

            if (r && r.success) {
                window.location.href = "<?= base_url('dashboard') ?>";
                return;
            }

            Envelopei.toast(r?.message ?? 'Falha no login.', 'danger');
        });
    });
</script>
<?= $this->endSection() ?>
