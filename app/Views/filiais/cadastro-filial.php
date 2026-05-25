<?php

declare(strict_types=1);

$tituloPagina = 'Cadastro de Filial';

$estilos = [
    '/assets/css/bootstrap.min.css',
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css'
];

$scripts = [
    '/assets/js/bootstrap.bundle.min.js',
    '/assets/js/cadastro-filial.js'
];

ob_start();
?>

<div class="container-fluid py-4 px-4 filial-page">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h1 class="fw-bold text-dark mb-1">
                Cadastro de Filial
            </h1>

            <p class="text-secondary mb-0">
                Cadastre as unidades logísticas da HermeX
            </p>
        </div>

    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <!-- CARD HEADER -->
        <div class="card-header text-white py-3 px-4 border-0"
        style="background:#1e293b;">

            <div class="d-flex align-items-center gap-2" >
                <span class="fs-4">🏢</span>

                <div>
                    <h5 class="mb-0 fw-semibold">
                        Nova Filial
                    </h5>

                    <small class="text-light opacity-75">
                        Preencha os dados da unidade
                    </small>
                </div>
            </div>

        </div>

        <!-- FORM -->
        <div class="card-body p-4">

            <?php if (!empty($_SESSION['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>?action=salvar-filial">

                <div class="row g-4">

                    <!-- NOME -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Nome da Filial
                        </label>

                        <input type="text"
                               name="nome"
                               class="form-control form-control-lg"
                               placeholder="Digite o nome da filial"
                               required>
                    </div>

                    <!-- CÓDIGO -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Código
                        </label>

                        <input type="text"
                               name="codigo"
                               class="form-control form-control-lg"
                               placeholder="Ex: SP01"
                               required>
                    </div>

                    <!-- CEP -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="cep">
                            CEP
                        </label>

                        <div class="input-group">
                            <input type="text"
                                   id="cep"
                                   name="cep"
                                   class="form-control form-control-lg"
                                   placeholder="00000-000"
                                   maxlength="9"
                                   required>
                            <span class="input-group-text" id="cep-status" style="min-width:2.5rem;">
                            </span>
                        </div>
                        <div id="cep-erro" class="form-text text-danger d-none"></div>
                    </div>

                    <!-- ENDEREÇO (logradouro) -->
                    <div class="col-md-8">
                        <label class="form-label fw-semibold" for="endereco">
                            Logradouro
                        </label>

                        <input type="text"
                               id="endereco"
                               name="endereco"
                               class="form-control form-control-lg"
                               placeholder="Preenchido pelo CEP"
                               required>
                    </div>

                    <!-- NÚMERO -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="numero">
                            Número
                        </label>

                        <input type="text"
                               id="numero"
                               name="numero"
                               class="form-control form-control-lg"
                               placeholder="Nº">
                    </div>

                    <!-- COMPLEMENTO -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="complemento">
                            Complemento
                        </label>

                        <input type="text"
                               id="complemento"
                               name="complemento"
                               class="form-control form-control-lg"
                               placeholder="Apto, sala, bloco...">
                    </div>

                    <!-- BAIRRO -->
                    <div class="col-md-5">
                        <label class="form-label fw-semibold" for="bairro">
                            Bairro
                        </label>

                        <input type="text"
                               id="bairro"
                               name="bairro"
                               class="form-control form-control-lg"
                               placeholder="Preenchido pelo CEP">
                    </div>

                    <!-- CIDADE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="cidade">
                            Cidade
                        </label>

                        <input type="text"
                               id="cidade"
                               name="cidade"
                               class="form-control form-control-lg"
                               placeholder="Preenchida pelo CEP"
                               required>
                    </div>

                    <!-- ESTADO (UF) -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="uf">
                            UF
                        </label>

                        <input type="text"
                               id="uf"
                               name="uf"
                               class="form-control form-control-lg"
                               placeholder="SP"
                               maxlength="2"
                               required>
                    </div>

                </div>

                <!-- BOTÕES -->
                <div class="d-flex justify-content-end gap-3 mt-5 flex-wrap">

                    <a href="<?= BASE_URL ?>?action=filiais"
                       class="btn btn-outline-secondary px-4 py-2">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">
                        Salvar Filial
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