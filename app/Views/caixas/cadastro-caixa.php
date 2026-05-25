<?php

declare(strict_types=1);

$tituloPagina = 'Nova Caixa';

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

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-bold text-dark mb-1">Nova Caixa</h1>
            <p class="text-secondary mb-0">Cadastre a caixa e vincule a nota fiscal em um único passo.</p>
        </div>
    </div>

    <?php if (!empty($_SESSION['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['erro'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>?action=salvar-caixa">

        <!-- IDENTIFICAÇÃO E ROTA -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">

            <div class="card-header text-white py-3 px-4 border-0" style="background:#1e293b;">
                <h5 class="mb-0 fw-semibold">Identificação e Rota</h5>
                <small class="text-light opacity-75">Estado inicial após cadastro: <strong>Criada</strong></small>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="codigo">Código da Caixa</label>
                        <input type="text" id="codigo" name="codigo"
                               class="form-control form-control-lg"
                               placeholder="Ex: CG-2055" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="tag_nfc">ID da Tag NFC</label>
                        <input type="text" id="tag_nfc" name="tag_nfc"
                               class="form-control form-control-lg"
                               placeholder="Ex: NFC-A1B2C3D4" required>
                        <div class="form-text">Deve ser único entre caixas ativas.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="transportadora">Transportadora</label>
                        <input type="text" id="transportadora" name="transportadora"
                               class="form-control form-control-lg"
                               placeholder="Nome da transportadora" required>
                        <div class="form-text">Campo livre. Congelado após o lacre.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="filial_origem_codigo">Filial de Origem</label>
                        <select id="filial_origem_codigo" name="filial_origem_codigo"
                                class="form-select form-select-lg" required>
                            <option value="">Selecione a origem...</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option value="<?= htmlspecialchars((string) ($filial['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) ($filial['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    — <?= htmlspecialchars((string) ($filial['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="filial_destino_codigo">Filial de Destino</label>
                        <select id="filial_destino_codigo" name="filial_destino_codigo"
                                class="form-select form-select-lg" required>
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
            </div>

        </div>

        <!-- NOTA FISCAL -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">

            <div class="card-header text-white py-3 px-4 border-0" style="background:#1e293b;">
                <h5 class="mb-0 fw-semibold">Nota Fiscal</h5>
                <small class="text-light opacity-75">Obrigatória — nenhuma carga é transportada sem NF.</small>
            </div>

            <div class="card-body p-4">

                <div class="row g-4 mb-4 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="numero_nf">Número da NF</label>
                        <input type="text" id="numero_nf" name="numero_nf"
                               class="form-control form-control-lg"
                               placeholder="Ex: NF-12345" required>
                    </div>
                    <div class="col-md-4">
                        <input type="file" id="input-xml-nfe" accept=".xml" style="display:none;" aria-hidden="true">
                        <button type="button" id="btn-importar-xml"
                                class="btn btn-outline-primary d-inline-flex align-items-center gap-2 fw-semibold" style="height:48px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            Importar NF-e XML
                        </button>
                        <div id="xml-status" class="small mt-1"></div>
                    </div>
                </div>

                <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size:.75rem;letter-spacing:.05em;">
                    Cliente Destinatário
                </h6>

                <div class="row g-4 mb-4">

                    <div class="col-md-5">
                        <label class="form-label fw-semibold" for="cliente_nome">Nome / Razão Social</label>
                        <input type="text" id="cliente_nome" name="cliente_nome"
                               class="form-control form-control-lg"
                               placeholder="Distribuidora Exemplo Ltda" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="cliente_documento">CNPJ / CPF</label>
                        <input type="text" id="cliente_documento" name="cliente_documento"
                               class="form-control form-control-lg"
                               placeholder="12.345.678/0001-99" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="cliente_cep">CEP</label>
                        <input type="text" id="cliente_cep" name="cliente_cep"
                               class="form-control form-control-lg"
                               placeholder="00000-000" maxlength="9">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <div id="cep-status" class="form-control form-control-lg bg-light border-0 text-center"
                             style="min-height:48px;line-height:48px;padding:0;"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="cliente_logradouro">Logradouro</label>
                        <input type="text" id="cliente_logradouro" name="cliente_logradouro"
                               class="form-control form-control-lg"
                               placeholder="Preenchido pelo CEP">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="cliente_numero">Número</label>
                        <input type="text" id="cliente_numero" name="cliente_numero"
                               class="form-control form-control-lg" placeholder="Nº">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="cliente_bairro">Bairro</label>
                        <input type="text" id="cliente_bairro" name="cliente_bairro"
                               class="form-control form-control-lg"
                               placeholder="Preenchido pelo CEP">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="cliente_cidade">Cidade</label>
                        <input type="text" id="cliente_cidade" name="cliente_cidade"
                               class="form-control form-control-lg"
                               placeholder="Preenchida pelo CEP">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold" for="cliente_uf">UF</label>
                        <input type="text" id="cliente_uf" name="cliente_uf"
                               class="form-control form-control-lg"
                               placeholder="SP" maxlength="2">
                    </div>

                </div>

                <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size:.75rem;letter-spacing:.05em;">
                    Produtos
                </h6>

                <div id="produtos-lista"></div>

                <button type="button" id="btn-adicionar-produto" class="btn btn-outline-primary fw-semibold mt-2">
                    + Adicionar Produto
                </button>

            </div>

        </div>

        <!-- BOTÕES -->
        <div class="d-flex justify-content-end gap-3 flex-wrap">
            <a href="<?= BASE_URL ?>?action=caixas"
               class="btn btn-outline-secondary px-4 py-2">
                Cancelar
            </a>
            <button type="submit" class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">
                Cadastrar Caixa
            </button>
        </div>

    </form>

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
