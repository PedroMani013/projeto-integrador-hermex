<?php

declare(strict_types=1);

$tituloPagina = 'Caixas';

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
            <h1 class="page-title">Caixas</h1>
            <p class="page-subtitle">Gerencie as caixas criadas e lacradas aguardando despacho.</p>
        </div>

        <a href="<?= BASE_URL ?>?action=cadastro-caixa"
           class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">
            + Nova Caixa
        </a>

    </div>

    <!-- TABELA -->
    <div class="tabela-wrapper shadow-sm">

        <table class="hermex-table align-middle">

            <thead>
                <tr>
                    <th>CÓDIGO</th>
                    <th>ROTA</th>
                    <th>TRANSPORTADORA</th>
                    <th class="text-center">ESTADO</th>
                    <th class="text-end pe-4">AÇÕES</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($caixas)): ?>

                    <?php foreach ($caixas as $caixa): ?>
                        <tr>

                            <td>
                                <div class="fw-bold">
                                    <?= htmlspecialchars((string) ($caixa['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <small class="text-secondary">
                                    <?= htmlspecialchars((string) ($caixa['tag_nfc'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    <?= htmlspecialchars((string) ($caixa['filial_origem_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    →
                                    <?= htmlspecialchars((string) ($caixa['filial_destino_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) ($caixa['transportadora'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td class="text-center">
                                <?php $estado = (string) ($caixa['estado'] ?? ''); ?>
                                <span class="badge <?= match($estado) {
                                    'lacrada'     => 'bg-success',
                                    'em_transito' => 'bg-primary',
                                    default       => 'bg-secondary'
                                } ?>">
                                    <?= match($estado) {
                                        'lacrada'     => 'Lacrada',
                                        'em_transito' => 'Em trânsito',
                                        default       => 'Criada'
                                    } ?>
                                </span>
                            </td>

                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">

                                    <a href="<?= BASE_URL ?>?action=detalhe-caixa&id=<?= urlencode((string) ($caixa['_id'] ?? '')) ?>"
                                       class="btn-hermex-secondary d-inline-flex align-items-center gap-2 text-decoration-none">
                                        Ver
                                    </a>

                                    <?php if ($estado === 'criada'): ?>
                                        <a href="<?= BASE_URL ?>?action=vincular-nf&id=<?= urlencode((string) ($caixa['_id'] ?? '')) ?>"
                                           class="btn-hermex-primary d-inline-flex align-items-center gap-2 text-decoration-none">
                                            Vincular NF
                                        </a>

                                        <?php $temNf = !empty((array) ($caixa['notas_fiscais'] ?? [])); ?>
                                        <?php if ($temNf): ?>
                                            <a href="<?= BASE_URL ?>?action=lacrar-caixa&id=<?= urlencode((string) ($caixa['_id'] ?? '')) ?>"
                                               class="btn-hermex-primary d-inline-flex align-items-center gap-2 text-decoration-none"
                                               style="background:#198754;border-color:#198754;">
                                                Lacrar
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($estado === 'lacrada'): ?>
                                        <form method="POST" action="/?action=despachar-caixa" class="m-0"
                                              onsubmit="return confirm('Confirmar despacho? A caixa entrará em trânsito e o monitoramento será iniciado.')">
                                            <input type="hidden" name="caixa_id" value="<?= htmlspecialchars((string) ($caixa['_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit"
                                                    class="btn-hermex-primary d-inline-flex align-items-center gap-2"
                                                    style="background:#0d6efd;border-color:#0d6efd;">
                                                Despachar
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-secondary">
                            Nenhuma caixa encontrada.
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
