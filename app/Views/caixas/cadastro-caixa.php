<?php

declare(strict_types=1);

$tituloPagina = 'Nova Caixa';

$estilos = [
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css',
];

$scripts = [];

ob_start();
?>

<div class="container-fluid py-4 px-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-bold text-dark mb-1">Nova Caixa</h1>
            <p class="text-secondary mb-0">Cadastre a caixa com seus dados de identificação e rota.</p>
        </div>
    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="card-header text-white py-3 px-4 border-0" style="background:#1e293b;">
            <h5 class="mb-0 fw-semibold">Identificação e Rota</h5>
            <small class="text-light opacity-75">Estado inicial após cadastro: <strong>Criada</strong></small>
        </div>

        <div class="card-body p-4">

            <?php if (!empty($_SESSION['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>

            <form method="POST" action="/?action=salvar-caixa">

                <div class="row g-4">

                    <!-- CÓDIGO -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="codigo">Código da Caixa</label>
                        <input type="text"
                               id="codigo"
                               name="codigo"
                               class="form-control form-control-lg"
                               placeholder="Ex: CG-2055"
                               required>
                    </div>

                    <!-- TAG NFC -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="tag_nfc">ID da Tag NFC</label>
                        <input type="text"
                               id="tag_nfc"
                               name="tag_nfc"
                               class="form-control form-control-lg"
                               placeholder="Ex: NFC-A1B2C3D4"
                               required>
                        <div class="form-text">Deve ser único entre caixas ativas.</div>
                    </div>

                    <!-- TRANSPORTADORA -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="transportadora">Transportadora</label>
                        <input type="text"
                               id="transportadora"
                               name="transportadora"
                               class="form-control form-control-lg"
                               placeholder="Nome da transportadora"
                               required>
                        <div class="form-text">Campo livre. Congelado após o lacre.</div>
                    </div>

                    <!-- FILIAL ORIGEM -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="filial_origem_codigo">Filial de Origem</label>
                        <select id="filial_origem_codigo"
                                name="filial_origem_codigo"
                                class="form-select form-select-lg"
                                required>
                            <option value="">Selecione a origem...</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option value="<?= htmlspecialchars((string) ($filial['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) ($filial['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    — <?= htmlspecialchars((string) ($filial['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- FILIAL DESTINO -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="filial_destino_codigo">Filial de Destino</label>
                        <select id="filial_destino_codigo"
                                name="filial_destino_codigo"
                                class="form-select form-select-lg"
                                required>
                            <option value="">Selecione o destino...</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option value="<?= htmlspecialchars((string) ($filial['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) ($filial['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    — <?= htmlspecialchars((string) ($filial['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <!-- BOTÕES -->
                <div class="d-flex justify-content-end gap-3 mt-5 flex-wrap">

                    <a href="<?= BASE_URL ?>?action=caixas"
                       class="btn btn-outline-secondary px-4 py-2">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">
                        Cadastrar Caixa
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php
$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>
