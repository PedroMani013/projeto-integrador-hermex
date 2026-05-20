<?php

declare(strict_types=1);

$tituloPagina = 'Lacrar Caixa';

$caixaId = (string) ($caixa['_id'] ?? '');

$estilos = [
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css',
];

$scripts = [];

ob_start();
?>

<div class="container-fluid py-4 px-4">

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-bold text-dark mb-1">Lacrar Caixa</h1>
            <p class="text-secondary mb-0">
                Caixa <strong><?= htmlspecialchars((string) ($caixa['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                — <?= htmlspecialchars((string) ($caixa['filial_origem_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                → <?= htmlspecialchars((string) ($caixa['filial_destino_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
        <a href="/?action=caixas" class="btn-hermex-secondary text-decoration-none d-inline-flex align-items-center gap-2">
            Voltar
        </a>
    </div>

    <div class="row g-4">

        <!-- FORMULÁRIO DE LACRE -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-4">Dados do lacre</h5>

                    <form method="POST" action="/?action=salvar-lacre">

                        <input type="hidden" name="caixa_id" value="<?= htmlspecialchars($caixaId, ENT_QUOTES, 'UTF-8') ?>">

                        <!-- PESO BASELINE -->
                        <div class="mb-3">
                            <label class="form-label small text-uppercase fw-bold text-secondary">
                                Peso da caixa (kg) *
                            </label>
                            <input
                                type="number"
                                name="peso_baseline"
                                class="form-control rounded-4 shadow-none"
                                placeholder="Ex: 12.500"
                                min="0.001"
                                step="0.001"
                                required
                            >
                            <div class="form-text">Peso total da caixa lacrada, em quilogramas.</div>
                        </div>

                        <!-- PREVISÃO DE CHEGADA -->
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-bold text-secondary">
                                Previsão de chegada *
                            </label>
                            <input
                                type="datetime-local"
                                name="previsao_chegada"
                                class="form-control rounded-4 shadow-none"
                                required
                            >
                        </div>

                        <button type="submit" class="btn-hermex-primary w-100 d-flex justify-content-center">
                            Confirmar Lacre
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <!-- NOTAS FISCAIS VINCULADAS -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-4">
                        Notas fiscais vinculadas
                        <span class="badge bg-secondary ms-2">
                            <?= count((array) ($caixa['notas_fiscais'] ?? [])) ?>
                        </span>
                    </h5>

                    <?php
                    $nfs = (array) ($caixa['notas_fiscais'] ?? []);

                    if (empty($nfs)):
                    ?>
                        <p class="text-danger fw-bold">
                            Nenhuma nota fiscal vinculada. Vincule ao menos uma NF antes de lacrar.
                        </p>
                    <?php else: ?>

                        <?php foreach ($nfs as $i => $nf):
                            $nfArr = (array) $nf;
                            $cliente = (array) ($nfArr['cliente_destinatario'] ?? []);
                            $produtos = (array) ($nfArr['produtos'] ?? []);
                        ?>
                            <div class="mb-3 p-3 rounded-3 bg-light">

                                <div class="fw-bold mb-1">
                                    NF <?= htmlspecialchars((string) ($nfArr['numero_nf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>

                                <div class="text-secondary small mb-2">
                                    <?= htmlspecialchars((string) ($cliente['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    — <?= htmlspecialchars((string) ($cliente['documento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </div>

                                <div class="small">
                                    <?php foreach ($produtos as $produto):
                                        $prodArr = (array) $produto;
                                    ?>
                                        <span class="badge bg-secondary me-1 mb-1">
                                            <?= htmlspecialchars((string) ($prodArr['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            × <?= (int) ($prodArr['quantidade'] ?? 0) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>

                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>

</div>

<?php
$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>
