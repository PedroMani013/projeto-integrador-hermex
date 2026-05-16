<?php

declare(strict_types=1);

$tituloPagina = 'Cadastro de Filial';

$estilos = [
    '/assets/css/bootstrap.min.css',
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css',
    '/assets/css/filiais.css'
];

$scripts = [
    '/assets/js/bootstrap.bundle.min.js'
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
                Cadastre as unidades logísticas da hermeX
            </p>
        </div>

    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <!-- CARD HEADER -->
        <div class="card-header bg-dark text-white py-3 px-4 border-0">

            <div class="d-flex align-items-center gap-2">
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

            <form method="POST" action="/?action=salvar-filial">

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

                    <!-- CIDADE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Cidade
                        </label>

                        <input type="text"
                               name="cidade"
                               class="form-control form-control-lg"
                               placeholder="Cidade"
                               required>
                    </div>

                    <!-- ESTADO -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Estado
                        </label>

                        <input type="text"
                               name="estado"
                               class="form-control form-control-lg"
                               placeholder="UF"
                               required>
                    </div>

                    <!-- CEP -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            CEP
                        </label>

                        <input type="text"
                               name="cep"
                               class="form-control form-control-lg"
                               placeholder="00000-000">
                    </div>

                    <!-- ENDEREÇO -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Endereço
                        </label>

                        <input type="text"
                               name="endereco"
                               class="form-control form-control-lg"
                               placeholder="Rua, avenida, número..."
                               required>
                    </div>

                    <!-- RESPONSÁVEL -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Responsável
                        </label>

                        <input type="text"
                               name="responsavel"
                               class="form-control form-control-lg"
                               placeholder="Nome do responsável">
                    </div>

                    <!-- TELEFONE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Telefone
                        </label>

                        <input type="text"
                               name="telefone"
                               class="form-control form-control-lg"
                               placeholder="(00) 00000-0000">
                    </div>

                </div>

                <!-- BOTÕES -->
                <div class="d-flex justify-content-end gap-3 mt-5 flex-wrap">

                    <a href="<?= BASE_URL ?>?action=filiais"
                       class="btn btn-outline-secondary px-4 py-2">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn btn-dark px-5 py-2 fw-semibold">
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