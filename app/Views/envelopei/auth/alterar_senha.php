<?= $this->extend('envelopei/layouts/app') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-1"><i class="fa-solid fa-key me-2"></i>Alterar senha</h4>
                <p class="text-muted mb-4">Informe a senha atual e a nova senha.</p>

                <div class="mb-3">
                    <label class="form-label">Senha atual</label>
                    <input type="password" class="form-control" id="SenhaAtual" autocomplete="current-password">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nova senha</label>
                    <input type="password" class="form-control" id="SenhaNova" autocomplete="new-password" minlength="6">
                    <div class="form-text">Mínimo de 6 caracteres.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirmar nova senha</label>
                    <input type="password" class="form-control" id="SenhaNovaConfirmar" autocomplete="new-password" minlength="6">
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-primary" id="btnAlterarSenha">
                        <i class="fa-solid fa-check me-2"></i>Alterar senha
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnAlterarSenha');
        const senhaAtual = document.getElementById('SenhaAtual');
        const senhaNova = document.getElementById('SenhaNova');
        const senhaNovaConfirmar = document.getElementById('SenhaNovaConfirmar');

        btn.addEventListener('click', async () => {
            const atual = senhaAtual.value;
            const nova = senhaNova.value;
            const confirmar = senhaNovaConfirmar.value;

            if (!atual.trim()) {
                Envelopei.toast('Informe a senha atual.', 'danger');
                senhaAtual.focus();
                return;
            }
            if (nova.length < 6) {
                Envelopei.toast('A nova senha deve ter no mínimo 6 caracteres.', 'danger');
                senhaNova.focus();
                return;
            }
            if (nova !== confirmar) {
                Envelopei.toast('A confirmação da nova senha não confere.', 'danger');
                senhaNovaConfirmar.focus();
                return;
            }

            btn.disabled = true;
            const r = await Envelopei.api('api/alterar-senha', 'POST', {
                SenhaAtual: atual,
                SenhaNova: nova
            });
            btn.disabled = false;

            if (r && r.success) {
                Envelopei.toast(r.message || 'Senha alterada com sucesso.', 'success');
                senhaAtual.value = '';
                senhaNova.value = '';
                senhaNovaConfirmar.value = '';
                setTimeout(() => {
                    window.location.href = "<?= base_url('dashboard') ?>";
                }, 1500);
                return;
            }

            Envelopei.toast(r?.message ?? 'Não foi possível alterar a senha.', 'danger');
        });
    });
</script>
<?= $this->endSection() ?>
