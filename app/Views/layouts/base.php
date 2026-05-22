<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="hermeX — Monitoramento de cadeia de custódia de cargas em trânsito">
    <title><?= htmlspecialchars($tituloPagina ?? 'Dashboard') ?> — hermeX</title>

    <!-- bootstrap 5 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <!-- design tokens -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/hermex_pages.css">

    <!-- CSS específico da página -->
    <?php foreach ($estilos ?? [] as $css): ?>
        <?php
        $cssPath = BASE_PATH . '/public/' . ltrim($css, '/');
        $v = file_exists($cssPath) ? filemtime($cssPath) : time();
        ?>
        <link rel="stylesheet" href="<?= BASE_URL . ltrim(htmlspecialchars($css), '/') ?>?v=<?= $v ?>">
    <?php endforeach; ?>
</head>
<body>

<div class="app-shell">
    <!-- overlay mobile para fechar sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-wrapper">
        <?php require_once __DIR__ . '/../partials/header.php'; ?>

        <main class="main-content" id="conteudo-principal" tabindex="-1">
            <?= $conteudo ?? '' ?>
        </main>
    </div>
</div>

<!-- bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmFo9SZHcAHqMHkjMgOmNPKXAeg"
        crossorigin="anonymous"></script>

<!-- chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<!-- scripts específicos da página -->
<?php foreach ($scripts ?? [] as $js): ?>
    <?php
    $jsPath = BASE_PATH . '/public/' . ltrim($js, '/');
    $v = file_exists($jsPath) ? filemtime($jsPath) : time();
    ?>
    <script src="<?= BASE_URL . ltrim(htmlspecialchars($js), '/') ?>?v=<?= $v ?>"></script>
<?php endforeach; ?>

</body>
</html>
