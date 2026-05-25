<?php
/** variáveis esperadas: $indicadores['alertasAbertos'] */
$alertasAbertos = $indicadores['alertasAbertos'] ?? 0;
$actionAtual    = $_GET['action'] ?? 'dashboard';
?>
<aside class="sidebar" id="sidebar" aria-label="Navegação principal">

    <!-- logo simplificada -->
    <a href="<?= BASE_URL ?>" class="sidebar-logo" aria-label="HermeX — ir para o dashboard">
        <img src="<?= BASE_URL ?>assets/img/logo-hermex.svg"
             alt="Logo HermeX — ícone de caixa com escudo"
             width="40" height="40">
        <span class="sidebar-logo-text">
            <span class="sidebar-logo-name">HermeX</span>
            <span class="sidebar-logo-sub">Chain of Custody</span>
        </span>
    </a>

    <nav class="sidebar-nav" aria-label="Menu de navegação">

        <!-- OPERAÇÃO -->
        <span class="sidebar-section" aria-hidden="true">Operação</span>

        <a href="<?= BASE_URL ?>?action=dashboard"
           class="sidebar-link <?= $actionAtual === 'dashboard' ? 'ativo' : '' ?>"
           aria-current="<?= $actionAtual === 'dashboard' ? 'page' : 'false' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Dashboard
        </a>

        <a href="<?= BASE_URL ?>?action=caixas"
           class="sidebar-link <?= $actionAtual === 'caixas' ? 'ativo' : '' ?>"
           aria-current="<?= $actionAtual === 'caixas' ? 'page' : 'false' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
            </svg>
            Caixas
        </a>

        <a href="<?= BASE_URL ?>?action=recepcao-nfc"
           class="sidebar-link <?= $actionAtual === 'recepcao-nfc' ? 'ativo' : '' ?>"
           aria-current="<?= $actionAtual === 'recepcao-nfc' ? 'page' : 'false' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M20 7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3"/>
                <path d="m16 3 4 4-4 4"/>
            </svg>
            Recepção NFC
        </a>

        <a href="<?= BASE_URL ?>?action=alertas"
           class="sidebar-link <?= $actionAtual === 'alertas' ? 'ativo' : '' ?>"
           aria-current="<?= $actionAtual === 'alertas' ? 'page' : 'false' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </svg>
            Alertas
            <?php // HU10/HU17: contador inclui apenas caixas 'violada'. Quando desvios de rota (HU17) existirem, incluir esse tipo aqui também. ?>
            <?php if ($alertasAbertos > 0): ?>
                <span class="sidebar-badge" aria-label="<?= $alertasAbertos ?> alertas abertos">
                    <?= $alertasAbertos ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- CADASTROS -->
        <span class="sidebar-section" aria-hidden="true">Cadastros</span>

        <a href="<?= BASE_URL ?>?action=filiais"
           class="sidebar-link <?= $actionAtual === 'filiais' ? 'ativo' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Filiais
        </a>

        <a href="<?= BASE_URL ?>?action=categorias"
           class="sidebar-link <?= $actionAtual === 'categorias' ? 'ativo' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            Categorias
        </a>

        <!-- DEMO -->
        <span class="sidebar-section" aria-hidden="true">Demo</span>

        <a href="<?= BASE_URL ?>?action=simulador"
           class="sidebar-link <?= $actionAtual === 'simulador' ? 'ativo' : '' ?>"
           aria-current="<?= $actionAtual === 'simulador' ? 'page' : 'false' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <polygon points="5 3 19 12 5 21 5 3"/>
            </svg>
            Simulador
        </a>

        <!-- ANÁLISE -->
        <span class="sidebar-section" aria-hidden="true">Análise</span>

        <a href="<?= BASE_URL ?>?action=relatorios"
           class="sidebar-link <?= $actionAtual === 'relatorios' ? 'ativo' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
            </svg>
            Operacional
        </a>

        <a href="<?= BASE_URL ?>?action=relatorio-custodia"
           class="sidebar-link <?= $actionAtual === 'relatorio-custodia' ? 'ativo' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M9 11l3 3L22 4"/>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            Custódia
        </a>

    </nav>

    <!-- HU11: substituir dados fictícios pelo usuário autenticado via $_SESSION após implementar login -->
    <div class="sidebar-footer">
        <div class="sidebar-avatar" aria-hidden="true">AC</div>
        <span class="sidebar-user-name">Ana Costa</span>
        <button class="sidebar-config" aria-label="Configurações do usuário" type="button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
        </button>
    </div>

</aside>
