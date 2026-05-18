<?php

declare(strict_types=1);

$tituloPagina = 'Vincular Nota Fiscal';

$caixaId = (string) ($caixa['_id'] ?? '');

$estilos = [
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css',
];

$scripts = [
    '/assets/js/vincular-nf.js',
];

ob_start();
?>

<div class="container-fluid py-4 px-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-bold text-dark mb-1">Vincular Nota Fiscal</h1>
            <p class="text-secondary mb-0">
                Caixa <strong><?= htmlspecialchars((string) ($caixa['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                — <?= htmlspecialchars((string) ($caixa['filial_origem_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                → <?= htmlspecialchars((string) ($caixa['filial_destino_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
        <a href="<?= BASE_URL ?>?action=caixas"
           class="btn btn-outline-secondary px-4 py-2">
            Voltar
        </a>
    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="card-header text-white py-3 px-4 border-0" style="background:#1e293b;">
            <h5 class="mb-0 fw-semibold">Dados da Nota Fiscal</h5>
            <small class="text-light opacity-75">Informações congeladas no momento do lacre para fins de auditoria.</small>
        </div>

        <div class="card-body p-4">

            <?php if (!empty($_SESSION['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>

            <form method="POST" action="/?action=salvar-nf">
                <input type="hidden" name="caixa_id" value="<?= htmlspecialchars($caixaId, ENT_QUOTES, 'UTF-8') ?>">

                <!-- NÚMERO DA NF -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="numero_nf">Número da NF</label>
                        <input type="text"
                               id="numero_nf"
                               name="numero_nf"
                               class="form-control form-control-lg"
                               placeholder="Ex: NF-12345"
                               required>
                    </div>
                </div>

                <!-- CLIENTE DESTINATÁRIO -->
                <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size:.75rem;letter-spacing:.05em;">
                    Cliente Destinatário
                </h6>

                <div class="row g-4 mb-4">

                    <div class="col-md-5">
                        <label class="form-label fw-semibold" for="cliente_nome">Nome / Razão Social</label>
                        <input type="text"
                               id="cliente_nome"
                               name="cliente_nome"
                               class="form-control form-control-lg"
                               placeholder="Distribuidora Exemplo Ltda"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="cliente_documento">CNPJ / CPF</label>
                        <input type="text"
                               id="cliente_documento"
                               name="cliente_documento"
                               class="form-control form-control-lg"
                               placeholder="12.345.678/0001-99"
                               required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="cliente_cep">CEP</label>
                        <input type="text"
                               id="cliente_cep"
                               name="cliente_cep"
                               class="form-control form-control-lg"
                               placeholder="00000-000"
                               maxlength="9">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <div id="cep-status" class="form-control form-control-lg bg-light border-0 text-center"
                             style="min-height:48px;line-height:48px;padding:0;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="cliente_logradouro">Logradouro</label>
                        <input type="text"
                               id="cliente_logradouro"
                               name="cliente_logradouro"
                               class="form-control form-control-lg"
                               placeholder="Preenchido pelo CEP">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="cliente_numero">Número</label>
                        <input type="text"
                               id="cliente_numero"
                               name="cliente_numero"
                               class="form-control form-control-lg"
                               placeholder="Nº">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="cliente_bairro">Bairro</label>
                        <input type="text"
                               id="cliente_bairro"
                               name="cliente_bairro"
                               class="form-control form-control-lg"
                               placeholder="Preenchido pelo CEP">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="cliente_cidade">Cidade</label>
                        <input type="text"
                               id="cliente_cidade"
                               name="cliente_cidade"
                               class="form-control form-control-lg"
                               placeholder="Preenchida pelo CEP">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="cliente_uf">UF</label>
                        <input type="text"
                               id="cliente_uf"
                               name="cliente_uf"
                               class="form-control form-control-lg"
                               placeholder="SP"
                               maxlength="2">
                    </div>

                </div>

                <!-- PRODUTOS -->
                <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size:.75rem;letter-spacing:.05em;">
                    Produtos
                </h6>

                <div id="produtos-lista">
                    <!-- linhas de produto injetadas pelo JS -->
                </div>

                <button type="button"
                        id="btn-adicionar-produto"
                        class="btn btn-outline-secondary mt-2">
                    + Adicionar Produto
                </button>

                <!-- BOTÕES -->
                <div class="d-flex justify-content-end gap-3 mt-5 flex-wrap">
                    <a href="<?= BASE_URL ?>?action=caixas"
                       class="btn btn-outline-secondary px-4 py-2">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">
                        Salvar NF
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
const CATEGORIAS = <?= json_encode(
    array_map(fn($c) => [
        'codigo'            => (string) ($c['codigo'] ?? ''),
        'nome'              => (string) ($c['nome'] ?? ''),
        'tolerancia_padrao' => (float)  ($c['tolerancia_padrao'] ?? 0),
    ], $categorias),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP
) ?>;
</script>

<?php
$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>
