<?= $this->extend('envelopei/layouts/auth') ?>

<?= $this->section('content') ?>
<div class="card auth-card">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="auth-marca mb-3">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <h1 class="h4 mb-1">Envelopei</h1>
            <p class="text-muted mb-0">Entre com seu e-mail e senha.</p>
        </div>

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" class="form-control form-control-lg" id="Email" autocomplete="email" autofocus>
        </div>

        <div class="mb-4">
            <label class="form-label">Senha</label>
            <input type="password" class="form-control form-control-lg" id="Senha" autocomplete="current-password">
        </div>

        <button class="btn btn-primary btn-lg w-100" id="btnLogin">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Entrar
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnLogin');
        const email = document.getElementById('Email');
        const senha = document.getElementById('Senha');

        /**
         * Envia as credenciais para a API de login e redireciona ao painel.
         */
        async function autenticar() {
            const Email = email.value.trim();
            const Senha = senha.value;

            const resposta = await Envelopei.api('api/login', 'POST', { Email, Senha });
            if (resposta && resposta.success) {
                window.location.href = "<?= base_url('dashboard') ?>";
                return;
            }

            Envelopei.toast(resposta?.message ?? 'Falha no login.');
        }

        btn.addEventListener('click', autenticar);
        senha.addEventListener('keydown', (evento) => {
            if (evento.key === 'Enter') {
                autenticar();
            }
        });
    });
</script>
<?= $this->endSection() ?>
