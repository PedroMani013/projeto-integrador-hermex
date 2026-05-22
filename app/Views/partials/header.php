<?php
$alertasAbertos = $indicadores['alertasAbertos'] ?? 0;
?>
<header class="app-header" role="banner">

    <!-- hambúrguer mobile -->
    <button class="header-burger" id="btnSidebar"
            aria-label="Abrir menu de navegação"
            aria-expanded="false"
            aria-controls="sidebar"
            type="button">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round"
             stroke-linejoin="round" aria-hidden="true">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <!-- breadcrumb fixo em Dashboard; quando outras telas existirem, tornar dinâmico com base na rota atual -->
    <nav class="header-breadcrumb" aria-label="Localização atual">
        hermeX <span aria-hidden="true"> / </span> <strong>Dashboard</strong>
    </nav>

    <!-- busca -->
    <div class="header-search">
        <form role="search" action="<?= BASE_URL ?>?action=caixas" method="get">
            <input type="search"
                   name="busca"
                   placeholder="Buscar caixa, nota fiscal, filial"
                   aria-label="Buscar caixa, nota fiscal ou filial"
                   value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
        </form>
    </div>

    <!-- ações -->
    <div class="header-actions">
        <button class="header-icon-btn" aria-label="Atualizar dados" type="button" id="btnAtualizar" onclick="location.reload()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <polyline points="23 4 23 10 17 10"/>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
            </svg>
        </button>

        <a href="/?action=alertas"
           class="header-icon-btn text-decoration-none"
           aria-label="Notificações<?= $alertasAbertos > 0 ? " ({$alertasAbertos} alertas abertos)" : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </svg>
            <?php if ($alertasAbertos > 0): ?>
                <span class="notif-dot" aria-hidden="true"></span>
            <?php endif; ?>
        </a>
    </div>

</header>
