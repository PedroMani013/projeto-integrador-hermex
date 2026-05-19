<?php
/**
 * hermeX - Gestão de Produtos
 * View: app/Views/produtos/index.php
 */

use App\Models\Produto;

$tituloPagina = 'Produtos';

$estilos = [
    '/assets/css/dashboard.css',
    '/assets/css/hermex_pages.css'
    
];

$scripts = [
    '/assets/js/produtos.js'
];

$produtos = $produtos ?? [];

$indicadores = $indicadores ?? [
    'totalSkus'         => count($produtos),
    'insumosMedicos'    => 0,
    'toleranciaCritica' => 0,
    'tagsNfc'           => 0,
];

$paginacao = $paginacao ?? [
    'pagina'    => 1,
    'porPagina' => 10,
    'total'     => count($produtos),
];

$pagina       = (int) ($paginacao['pagina'] ?? 1);
$porPagina    = (int) ($paginacao['porPagina'] ?? 10);
$total        = (int) ($paginacao['total'] ?? 0);
$totalPaginas = (int) ceil($total / max(1, $porPagina));

$agora = new \DateTimeImmutable(
    'now',
    new \DateTimeZone('America/Sao_Paulo')
);

ob_start();
?>

<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="page-header">

        <div class="page-title-block">

            <h1 class="page-title">
                Gestão de Produtos
            </h1>

            <p class="page-subtitle mb-0">

                Gerencie os itens custodiados monitorados pela hermeX

                <span class="bullet-sep" aria-hidden="true">
                    •
                </span>

                <time datetime="<?= $agora->format('Y-m-d\TH:i') ?>">

                    <?= $agora->format('d/m/Y H:i') ?>

                </time>
            </p>
        </div>

        <div class="page-header-actions">

            <a href="<?= BASE_URL ?>?action=cadastro-produto"
               class="btn-hermex-primary text-decoration-none d-inline-flex align-items-center gap-2">

                <span style="font-size:20px;">
                    +
                </span>

                Novo Produto
            </a>
        </div>
    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <!-- TOTAL -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card-indicador h-100">

                <p class="card-indicador-label">
                    TOTAL DE SKUS
                </p>

                <p class="card-indicador-valor">
                    <?= $indicadores['totalSkus'] ?>
                </p>

                <small class="text-muted">
                    Produtos cadastrados
                </small>
            </div>
        </div>

        <!-- MÉDICOS -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card-indicador h-100">

                <p class="card-indicador-label">
                    INSUMOS MÉDICOS
                </p>

                <p class="card-indicador-valor">
                    <?= $indicadores['insumosMedicos'] ?>
                </p>

                <small class="text-muted">
                    Produtos críticos
                </small>
            </div>
        </div>

        <!-- TOLERÂNCIA -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card-indicador h-100">

                <p class="card-indicador-label">
                    TOLERÂNCIA CRÍTICA
                </p>

                <p class="card-indicador-valor">
                    <?= $indicadores['toleranciaCritica'] ?>
                </p>

                <small class="text-muted">
                    Margem ≤ 1%
                </small>
            </div>
        </div>

        <!-- NFC -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card-indicador h-100">

                <p class="card-indicador-label">
                    TAGS NFC
                </p>

                <p class="card-indicador-valor">
                    <?= $indicadores['tagsNfc'] ?>
                </p>

                <small class="text-muted">
                    Dispositivos ativos
                </small>
            </div>
        </div>

    </div>

    <!-- TABELA -->
    <section class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <!-- HEADER TABELA -->
        <div class="card-header bg-white border-bottom py-3 px-4">

            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">

                <div>

                    <h2 class="h4 fw-bold mb-1">
                        Catálogo de Produtos
                    </h2>

                    <p class="text-muted mb-0">

                        <?= $total ?> produtos cadastrados

                    </p>
                </div>

                <!-- BUSCA -->
                <form method="GET"
                      class="d-flex flex-column flex-sm-row gap-2">

                    <input type="hidden"
                           name="action"
                           value="produtos">

                    <input type="text"
                           name="busca"
                           value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>"
                           placeholder="Buscar produto..."
                           class="form-control">

                    <button type="submit"
                            class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">

                        Buscar
                    </button>
                </form>
            </div>
        </div>

        <?php if (empty($produtos)): ?>

            <!-- VAZIO -->
            <div class="card-body text-center py-5">

                <div class="mb-3"
                     style="font-size:48px;">

                    📦
                </div>

                <h4 class="fw-bold">
                    Nenhum produto encontrado
                </h4>

                <p class="text-muted mb-0">

                    Cadastre um novo produto para começar.

                </p>
            </div>

        <?php else: ?>

            <!-- TABELA DESKTOP -->
            <div class="table-responsive d-none d-lg-block">

                <table class="table align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-4 py-3">
                                Produto
                            </th>

                            <th class="py-3">
                                Categoria
                            </th>

                            <th class="py-3 text-center">
                                Tolerância
                            </th>

                            <th class="py-3">
                                NFC
                            </th>

                            <th class="py-3">
                                Status
                            </th>

                            <th class="py-3 text-end pe-4">
                                Ações
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($produtos as $produto): ?>

                            <?php
                            $p = (object) $produto;

                            $tolerancia = (float)($p->toleranciaPeso ?? 0);

                            $ativo = (bool)($p->ativo ?? false);

                            $imagem = $p->imagem ?? null;
                            ?>

                            <tr>

                                <!-- PRODUTO -->
                                <td class="px-4 py-3">

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="rounded-3 overflow-hidden bg-light d-flex align-items-center justify-content-center"
                                             style="
                                                width:56px;
                                                height:56px;
                                             ">

                                            <?php if (!empty($imagem)): ?>

                                                <img src="<?= htmlspecialchars($imagem) ?>"
                                                     alt="Produto"
                                                     class="w-100 h-100 object-fit-cover">

                                            <?php else: ?>

                                                <span style="font-size:24px;">
                                                    📦
                                                </span>

                                            <?php endif; ?>
                                        </div>

                                        <div>

                                            <div class="fw-bold">

                                                <?= htmlspecialchars($p->nome ?? '-') ?>

                                            </div>

                                            <small class="text-muted">

                                                SKU:
                                                <?= htmlspecialchars($p->sku ?? '-') ?>

                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- CATEGORIA -->
                                <td>

                                    <span class="badge bg-primary-subtle text-dark">

                                        <?= htmlspecialchars($p->categoria ?? '-') ?>

                                    </span>
                                </td>

                                <!-- TOLERÂNCIA -->
                                <td class="text-center">

                                    <span class="fw-bold <?= $tolerancia <= 1 ? 'text-danger' : 'text-success' ?>">

                                        ± <?= number_format($tolerancia, 1, ',', '.') ?>%

                                    </span>
                                </td>

                                <!-- NFC -->
                                <td>

                                    <span class="font-monospace">

                                        <?= htmlspecialchars($p->codigoNfc ?? '-') ?>

                                    </span>
                                </td>

                                <!-- STATUS -->
                                <td>

                                    <?php if ($ativo): ?>

                                        <span class="badge bg-success-subtle text-success">

                                            ATIVO
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary-subtle text-secondary">

                                            INATIVO
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <!-- AÇÕES -->
                                <td class="text-end pe-4">

                                    <div class="d-flex justify-content-end gap-2">

                                        <!-- EDITAR -->
                                        <a href="/?action=editar-produto&id=<?= (int)($p->id ?? 0) ?>"
                                           class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">

                                            Editar
                                        </a>

                                        <!-- EXCLUIR -->
                                        <form method="POST"
                                              action="/?action=excluir-produto"
                                              onsubmit="return confirm('Deseja excluir este produto?')">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= (int)($p->id ?? 0) ?>">

                                            <button type="submit"
                                                    class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none"
                                                    style="background:#dc3545;border-color:#dc3545;color:#fff;">

                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

            <!-- MOBILE -->
            <div class="d-flex d-lg-none flex-column gap-3 p-3">

                <?php foreach ($produtos as $produto): ?>

                    <?php
                    $p = (object) $produto;

                    $tolerancia = (float)($p->toleranciaPeso ?? 0);

                    $ativo = (bool)($p->ativo ?? false);
                    ?>

                    <div class="card border rounded-4 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start mb-3">

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        <?= htmlspecialchars($p->nome ?? '-') ?>

                                    </h5>

                                    <small class="text-muted">

                                        SKU:
                                        <?= htmlspecialchars($p->sku ?? '-') ?>

                                    </small>
                                </div>

                                <?php if ($ativo): ?>

                                    <span class="badge bg-success-subtle text-success">

                                        ATIVO
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary-subtle text-secondary">

                                        INATIVO
                                    </span>

                                <?php endif; ?>
                            </div>

                            <div class="row g-3 mb-3">

                                <div class="col-6">

                                    <small class="text-muted d-block">
                                        Categoria
                                    </small>

                                    <strong>

                                        <?= htmlspecialchars($p->categoria ?? '-') ?>

                                    </strong>
                                </div>

                                <div class="col-6">

                                    <small class="text-muted d-block">
                                        Tolerância
                                    </small>

                                    <strong class="<?= $tolerancia <= 1 ? 'text-danger' : 'text-success' ?>">

                                        ± <?= number_format($tolerancia, 1, ',', '.') ?>%

                                    </strong>
                                </div>

                                <div class="col-12">

                                    <small class="text-muted d-block">
                                        NFC
                                    </small>

                                    <span class="font-monospace">

                                        <?= htmlspecialchars($p->codigoNfc ?? '-') ?>

                                    </span>
                                </div>
                            </div>

                            <div class="d-flex gap-2">

                                <a href="/?action=editar-produto&id=<?= (int)($p->id ?? 0) ?>"
                                   class="btn btn-sm rounded-3 text-white"
                                    style="background:#1e293b;border:none;">

                                    Editar
                                </a>

                                <form method="POST"
                                      action="/?action=excluir-produto"
                                      class="flex-fill"
                                      onsubmit="return confirm('Deseja excluir este produto?')">

                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int)($p->id ?? 0) ?>">

                                    <button type="submit"
                                            class="btn btn-outline-danger btn-sm w-100">

                                        Excluir
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

            <!-- PAGINAÇÃO -->
            <?php if ($totalPaginas > 1): ?>

                <div class="card-footer bg-white border-top px-4 py-3">

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                        <div class="text-muted">

                            Exibindo
                            <?= (($pagina - 1) * $porPagina) + 1 ?>
                            até
                            <?= min($pagina * $porPagina, $total) ?>
                            de
                            <?= $total ?>
                            produtos

                        </div>

                        <div class="d-flex gap-2">

                            <!-- anterior -->
                            <?php if ($pagina > 1): ?>

                                <a href="/?action=produtos&pagina=<?= $pagina - 1 ?>"
                                   class="btn btn-outline-secondary btn-sm">

                                    ← Anterior
                                </a>

                            <?php else: ?>

                                <button class="btn btn-outline-secondary btn-sm"
                                        disabled>

                                    ← Anterior
                                </button>

                            <?php endif; ?>

                            <!-- página -->
                            <button class="btn btn-dark btn-sm"
                                    disabled>

                                <?= $pagina ?> / <?= $totalPaginas ?>

                            </button>

                            <!-- próxima -->
                            <?php if ($pagina < $totalPaginas): ?>

                                <a href="/?action=produtos&pagina=<?= $pagina + 1 ?>"
                                   class="btn btn-outline-secondary btn-sm">

                                    Próxima →
                                </a>

                            <?php else: ?>

                                <button class="btn btn-outline-secondary btn-sm"
                                        disabled>

                                    Próxima →
                                </button>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>

            <?php endif; ?>

        <?php endif; ?>

    </section>

</div>

<?php
$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>