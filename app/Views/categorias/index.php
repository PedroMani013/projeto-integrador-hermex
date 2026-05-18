<?php

declare(strict_types=1);

$tituloPagina = 'Categorias';

$estilos = [
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css',
];

$scripts = [];

ob_start();
?>

<div class="container-fluid py-4 px-4">

    <?php if (!empty($_SESSION['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['sucesso'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="page-header">

        <div>
            <h1 class="page-title">Categorias de Produto</h1>
            <p class="page-subtitle">Configure as categorias e tolerâncias de variação de peso padrão.</p>
        </div>

        <a href="<?= BASE_URL ?>?action=cadastro-categoria"
           class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">
            + Nova Categoria
        </a>

    </div>

    <!-- TABELA -->
    <div class="tabela-wrapper shadow-sm">

        <table class="hermex-table align-middle">

            <thead>
                <tr>
                    <th>CÓDIGO</th>
                    <th>NOME</th>
                    <th class="text-center">TOLERÂNCIA PADRÃO</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($categorias)): ?>

                    <?php foreach ($categorias as $cat): ?>
                        <tr>

                            <td>
                                <span class="badge-status">
                                    <?= htmlspecialchars((string) ($cat['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>

                            <td class="fw-semibold">
                                <?= htmlspecialchars((string) ($cat['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td class="text-center">
                                <?= (float) ($cat['tolerancia_padrao'] ?? 0) ?> %
                            </td>

                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-5 text-secondary">
                            Nenhuma categoria cadastrada.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php
$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>
