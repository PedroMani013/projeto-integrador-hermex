<?php

declare(strict_types=1);

$tituloPagina = 'Relatórios';

$estilos = [
    '/assets/css/bootstrap.min.css',
    '/assets/css/dashboard.css',
    '/assets/css/hermex_pages.css'
];

$scripts = [
    '/assets/js/bootstrap.bundle.min.js'
];

ob_start();
?>

<div class="container-fluid py-4 px-4">

    <!-- FILTROS -->
    <form method="GET" class="row g-3 mb-4">

        <input type="hidden"
               name="action"
               value="relatorios">

        <div class="col-md-3">

            <select name="periodo"
                    class="form-select rounded-3">

                <option value="dia"
                    <?= ($_GET['periodo'] ?? '') === 'dia' ? 'selected' : '' ?>>

                    Último dia

                </option>

                <option value="semana"
                    <?= ($_GET['periodo'] ?? 'semana') === 'semana' ? 'selected' : '' ?>>

                    Última semana

                </option>

                <option value="mes"
                    <?= ($_GET['periodo'] ?? '') === 'mes' ? 'selected' : '' ?>>

                    Último mês

                </option>

                <option value="personalizado"
                    <?= ($_GET['periodo'] ?? '') === 'personalizado' ? 'selected' : '' ?>>

                    Personalizado

                </option>

            </select>

        </div>

        <div class="col-md-3">

            <input type="date"
                   name="data_inicio"
                   value="<?= $_GET['data_inicio'] ?? '' ?>"
                   class="form-control rounded-3">

        </div>

        <div class="col-md-3">

            <input type="date"
                   name="data_fim"
                   value="<?= $_GET['data_fim'] ?? '' ?>"
                   class="form-control rounded-3">

        </div>

        <div class="col-md-3">

            <button class="btn-hermex-primary border-0 px-4 h-100 w-100">

                Filtrar

            </button>

        </div>

    </form>

    <!-- HEADER -->
    <div class="page-header">

        <div>

            <h1 class="page-title">
                Relatórios Operacionais
            </h1>

            <p class="page-subtitle">
                Monitoramento logístico inteligente Hermex
            </p>

        </div>

        <a href="<?= BASE_URL ?>?action=exportar-relatorio&periodo=<?= $_GET['periodo'] ?? 'semana' ?>&data_inicio=<?= $_GET['data_inicio'] ?? '' ?>&data_fim=<?= $_GET['data_fim'] ?? '' ?>"
           class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">

            Exportar PDF

        </a>

    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <div class="card-indicador h-100">

                <p class="card-indicador-label">
                    TOTAL MOVIMENTAÇÕES
                </p>

                <p class="card-indicador-valor">
                    <?= $totalMovimentacoes ?>
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card-indicador h-100">

                <p class="card-indicador-label">
                    ALERTAS
                </p>

                <p class="card-indicador-valor text-danger">
                    <?= $alertas ?>
                </p>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card-indicador h-100">

                <p class="card-indicador-label">
                    INTEGRIDADE
                </p>

                <p class="card-indicador-valor">
                    <?= $integridade ?>%
                </p>

            </div>

        </div>

    </div>

    <!-- TABELA -->
    <div class="tabela-wrapper shadow-sm">

        <table class="hermex-table align-middle">

            <thead>

                <tr>

                    <th>Código</th>
                    <th>Status</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Total Itens</th>
                    <th>Transportadora</th>
                    <th>Data</th>

                </tr>

            </thead>

            <tbody>

                <?php if (empty($relatorios)): ?>

                    <tr>

                        <td colspan="7"
                            class="text-center py-5">

                            Nenhum relatório encontrado

                        </td>

                    </tr>

                <?php endif; ?>

                <?php foreach ($relatorios as $relatorio): ?>

                    <tr>

                        <td>

                            <?= $relatorio['codigo'] ?? '-' ?>

                        </td>

                        <td>

                            <?= ucfirst($relatorio['estado'] ?? '-') ?>

                        </td>

                        <td>

                            <?= $relatorio['filial_origem_nome'] ?? '-' ?>

                        </td>

                        <td>

                            <?= $relatorio['filial_destino_nome'] ?? '-' ?>

                        </td>

                        <td>

                            <?= $relatorio['total_itens'] ?? 0 ?>

                        </td>

                        <td>

                            <?= $relatorio['transportadora'] ?? '-' ?>

                        </td>

                        <td>

                            <?php if (!empty($relatorio['criado_em'])): ?>

                                <?= $relatorio['criado_em']
                                    ->toDateTime()
                                    ->format('d/m/Y H:i') ?>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php

$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>