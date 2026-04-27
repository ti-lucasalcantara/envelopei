<aside class="app-sidebar">
    <div class="app-brand">
        <i class="fa-solid fa-wallet"></i>
        <span>Envelopei</span>
    </div>
    <nav class="app-menu">
        <a class="<?= classe_menu_ativo('dashboard') ?>" href="<?= base_url('dashboard') ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a class="<?= classe_menu_ativo('contas') ?>" href="<?= base_url('contas') ?>"><i class="fa-solid fa-building-columns"></i> Contas</a>
        <a class="<?= classe_menu_ativo('receitas') ?>" href="<?= base_url('receitas') ?>"><i class="fa-solid fa-arrow-trend-up"></i> Receitas</a>
        <a class="<?= classe_menu_ativo('despesas') ?>" href="<?= base_url('despesas') ?>"><i class="fa-solid fa-arrow-trend-down"></i> Despesas</a>
        <a class="<?= classe_menu_ativo('envelopes') ?>" href="<?= base_url('envelopes') ?>"><i class="fa-solid fa-envelopes-bulk"></i> Envelopes</a>
        <a class="<?= classe_menu_ativo('rateios') ?>" href="<?= base_url('rateios') ?>"><i class="fa-solid fa-percent"></i> Regras de Rateio</a>
        <a class="<?= classe_menu_ativo('cartoes') ?>" href="<?= base_url('cartoes') ?>"><i class="fa-solid fa-credit-card"></i> Cartões de Crédito</a>
        <a class="<?= classe_menu_ativo('faturas') ?>" href="<?= base_url('faturas') ?>"><i class="fa-solid fa-file-invoice-dollar"></i> Faturas</a>
        <a class="<?= classe_menu_ativo('investimentos') ?>" href="<?= base_url('investimentos') ?>"><i class="fa-solid fa-piggy-bank"></i> Investimentos</a>
        <a class="<?= classe_menu_ativo('categorias') ?>" href="<?= base_url('categorias') ?>"><i class="fa-solid fa-tags"></i> Categorias</a>
        <a class="<?= classe_menu_ativo('relatorios') ?>" href="<?= base_url('relatorios') ?>"><i class="fa-solid fa-chart-pie"></i> Relatórios</a>
        <a class="<?= classe_menu_ativo('configuracoes') ?>" href="<?= base_url('configuracoes') ?>"><i class="fa-solid fa-gear"></i> Configurações</a>
        <a href="<?= base_url('sair') ?>"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
    </nav>
</aside>
