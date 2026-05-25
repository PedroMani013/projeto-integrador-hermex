<?php

declare(strict_types=1);

$tituloPagina = 'Nova Categoria';

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
            <h1 class="fw-bold text-dark mb-1">Nova Categoria</h1>
            <p class="text-secondary mb-0">Defina o código, nome e tolerância de variação de peso padrão.</p>
        </div>
    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="card-header text-white py-3 px-4 border-0" style="background:#1e293b;">
            <h5 class="mb-0 fw-semibold">Dados da Categoria</h5>
        </div>

        <div class="card-body p-4">

            <?php if (!empty($_SESSION['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>?action=salvar-categoria">

                <div class="row g-4">

                    <!-- CÓDIGO -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="codigo">Código</label>
                        <input type="text"
                               id="codigo"
                               name="codigo"
                               class="form-control form-control-lg"
                               placeholder="Ex: eletronica"
                               required>
                        <div class="form-text">Identificador único em minúsculas, sem espaços.</div>
                    </div>

                    <!-- NOME -->
                    <div class="col-md-5">
                        <label class="form-label fw-semibold" for="nome">Nome</label>
                        <input type="text"
                               id="nome"
                               name="nome"
                               class="form-control form-control-lg"
                               placeholder="Ex: Eletrônica"
                               required>
                    </div>

                    <!-- TOLERÂNCIA -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="tolerancia_padrao">Tolerância padrão (%)</label>
                        <input type="number"
                               id="tolerancia_padrao"
                               name="tolerancia_padrao"
                               class="form-control form-control-lg"
                               placeholder="5"
                               min="0.1"
                               max="100"
                               step="0.1"
                               required>
                    </div>

                </div>

                <!-- BOTÕES -->
                <div class="d-flex justify-content-end gap-3 mt-5 flex-wrap">

                    <a href="<?= BASE_URL ?>?action=categorias"
                       class="btn btn-outline-secondary px-4 py-2">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">
                        Salvar Categoria
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
