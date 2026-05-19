<?php

/**
 * hermeX - Cadastro de Produtos
 * View: app/Views/produtos/cadastro-produto.php
 */

$tituloPagina = 'Novo Produto';

$estilos = [
    '/assets/css/dashboard.css',
    '/assets/css/produtos.css'
];

$scripts = [
    '/assets/js/produtos.js'
];

ob_start();
?>

<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="page-header">

        <div>
            <h1 class="page-title">
                Novo Produto
            </h1>

            <p class="page-subtitle">
                Cadastre um novo item custodiado no sistema hermeX.
            </p>
        </div>
    </div>

    <!-- FORM -->
    <form id="formProduto"
          method="POST"
          action="/?action=salvar-produto"
          enctype="multipart/form-data">

        <div class="row g-4">

            <!-- IDENTIFICAÇÃO -->
            <div class="col-12 col-lg-8">

                <div class="card-indicador h-100">

                    <div class="d-flex align-items-center justify-content-between mb-4">

                        <div>
                            <h5 class="fw-bold mb-1">
                                Identificação do Produto
                            </h5>

                            <p class="text-muted mb-0 small">
                                Informações principais do item
                            </p>
                        </div>

                        <span class="badge-status">
                            Etapa 1
                        </span>
                    </div>

                    <div class="row g-3">

                        <!-- NOME -->
                        <div class="col-12">

                            <label class="form-label fw-semibold">
                                Nome do Produto
                            </label>

                            <input type="text"
                                   name="nome"
                                   class="form-control"
                                   placeholder="Ex: Monitor Industrial 4K"
                                   required>
                        </div>

                        <!-- SKU -->
                        <div class="col-12 col-md-6">

                            <label class="form-label fw-semibold">
                                SKU
                            </label>

                            <input type="text"
                                   name="sku"
                                   class="form-control"
                                   placeholder="HER-0001"
                                   required>
                        </div>

                        <!-- CATEGORIA -->
                        <div class="col-12 col-md-6">

                            <label class="form-label fw-semibold">
                                Categoria
                            </label>

                            <select name="categoria"
                                    class="form-select"
                                    required>

                                <option value="">
                                    Selecione...
                                </option>

                                <option value="Eletrônicos">
                                    Eletrônicos
                                </option>

                                <option value="Insumos Médicos">
                                    Insumos Médicos
                                </option>

                                <option value="Ferramentas">
                                    Ferramentas
                                </option>

                                <option value="Produtos Químicos">
                                    Produtos Químicos
                                </option>

                            </select>
                        </div>

                    </div>
                </div>
            </div>

            <!-- LOGÍSTICA -->
            <div class="col-12 col-lg-4">

                <div class="card-indicador h-100">

                    <div class="d-flex align-items-center justify-content-between mb-4">

                        <div>
                            <h5 class="fw-bold mb-1">
                                Logística
                            </h5>

                            <p class="text-muted mb-0 small">
                                Dados de rastreio
                            </p>
                        </div>

                        <span class="badge-status">
                            Etapa 2
                        </span>
                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            NFC Tag ID
                        </label>

                        <input type="text"
                               name="codigoNfc"
                               class="form-control"
                               placeholder="NFC-001">
                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Peso Unitário (KG)
                        </label>

                        <input type="number"
                               step="0.01"
                               name="pesoUnitario"
                               class="form-control"
                               placeholder="0.00">
                    </div>

                    <div>

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <label class="form-label fw-semibold mb-0">
                                Tolerância de Peso
                            </label>

                            <span id="valorTolerancia"
                                  class="fw-bold">
                                5%
                            </span>
                        </div>

                        <input type="range"
                               id="toleranciaInput"
                               name="toleranciaPeso"
                               min="0"
                               max="20"
                               value="5"
                               class="form-range">

                        <div class="d-flex justify-content-between small text-muted">

                            <span>0%</span>

                            <span>20%</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- DESCRIÇÃO -->
            <div class="col-12">

                <div class="card-indicador">

                    <div class="d-flex align-items-center justify-content-between mb-4">

                        <div>
                            <h5 class="fw-bold mb-1">
                                Descrição e Arquivos
                            </h5>

                            <p class="text-muted mb-0 small">
                                Informações complementares do produto
                            </p>
                        </div>

                        <span class="badge-status">
                            Etapa 3
                        </span>
                    </div>

                    <div class="row g-4">

                        <!-- DESCRIÇÃO -->
                        <div class="col-12 col-lg-8">

                            <label class="form-label fw-semibold">
                                Descrição
                            </label>

                            <textarea name="descricao"
                                      rows="8"
                                      class="form-control"
                                      placeholder="Descreva o produto..."></textarea>
                        </div>

                        <!-- IMAGEM -->
                        <div class="col-12 col-lg-4">

                            <label class="form-label fw-semibold">
                                Foto do Produto
                            </label>

                            <input type="file"
                                   name="imagem"
                                   accept=".jpg,.jpeg,.png"
                                   class="form-control">

                            <div class="form-text">
                                JPG ou PNG até 5MB.
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- RODAPÉ -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4 pt-4 border-top">

            <div class="text-muted small">
                Este produto será salvo no banco de dados da aplicação.
            </div>

            <div class="d-flex gap-2 flex-wrap">

                <a href="<?= BASE_URL ?>?action=produtos"
                   class="btn btn-outline-secondary">
                    Cancelar
                </a>

                <button type="submit"
                        class="btn-hermex-primary">
                    Salvar Produto
                </button>
            </div>
        </div>

    </form>

</div>

<script>

const toleranciaInput = document.getElementById('toleranciaInput');

const valorTolerancia = document.getElementById('valorTolerancia');

if (toleranciaInput && valorTolerancia) {

    toleranciaInput.addEventListener('input', function () {

        valorTolerancia.textContent = this.value + '%';

    });

}

</script>

<?php

$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>