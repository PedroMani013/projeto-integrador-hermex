<?php
/**
 * hermeX - Gestão de Itens Custodiados
 * View: app/Views/produtos/index.php
 */

use App\Models\Produto;

$tituloPagina = 'Produtos';

// Configuração de estilos e scripts
$estilos = [
    '/assets/css/dashboard.css',
    '/assets/css/produtos.css'
];

$scripts = [
    '/assets/js/produtos.js'
];

// Dados padrão
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

ob_start();
?>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: "#040c1c",
                background: "#f7fafc",
                "on-surface": "#181c1e",
                "on-surface-variant": "#45474c",
                "outline-variant": "#c6c6cd",
                "primary-fixed": "#dae2fa",
                "secondary-fixed": "#ffe08b",
                "tertiary-fixed": "#a3f69c",
                "on-tertiary-fixed-variant": "#005312",
                error: "#ba1a1a",
                "error-container": "#ffdad6"
            }
        }
    }
}
</script>

<style>
.material-symbols-outlined {
    font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}

.herme-x-ui-wrapper {
    font-family: 'Inter', sans-serif;
}
</style>

<div class="herme-x-ui-wrapper min-h-screen bg-background text-on-surface p-8">

    <!-- HEADER -->
    <div class="flex justify-between items-end mb-8">

        <div>
            <h1 class="text-[24px] font-bold text-primary leading-tight">
                Gestão de Itens Custodiados
            </h1>

            <p class="text-on-surface-variant text-[14px]">
                Gerencie o catálogo de produtos de alto valor assegurado.
            </p>
        </div>

        <!-- BOTÃO NOVO PRODUTO -->
        <a href="<?= BASE_URL ?>?action=cadastro-produto"
           class="bg-primary text-white px-6 py-3 rounded-lg flex items-center gap-2 text-[14px] font-semibold hover:opacity-90 transition-all shadow-lg shadow-primary/10">

            <span class="material-symbols-outlined text-[20px]">
                add
            </span>

            Novo Produto
        </a>
    </div>

    <!-- CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

        <!-- TOTAL -->
        <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm flex flex-col gap-4">

            <div class="w-10 h-10 rounded-lg bg-primary-fixed/30 flex items-center justify-center">

                <span class="material-symbols-outlined text-primary"
                      style="font-variation-settings: 'FILL' 1;">
                    inventory
                </span>
            </div>

            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                    TOTAL DE SKUS
                </p>

                <h3 class="text-[28px] font-bold text-primary">
                    <?= $indicadores['totalSkus'] ?>
                </h3>

                <p class="text-on-surface-variant text-[11px]">
                    Produtos registrados
                </p>
            </div>
        </div>

        <!-- MÉDICOS -->
        <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm flex flex-col gap-4">

            <div class="w-10 h-10 rounded-lg bg-tertiary-fixed/30 flex items-center justify-center">

                <span class="material-symbols-outlined text-on-tertiary-fixed-variant"
                      style="font-variation-settings: 'FILL' 1;">
                    medical_services
                </span>
            </div>

            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                    INSUMOS MÉDICOS
                </p>

                <h3 class="text-[28px] font-bold text-primary">
                    <?= $indicadores['insumosMedicos'] ?>
                </h3>

                <p class="text-on-surface-variant text-[11px]">
                    Itens críticos
                </p>
            </div>
        </div>

        <!-- TOLERÂNCIA -->
        <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm flex flex-col gap-4">

            <div class="w-10 h-10 rounded-lg bg-secondary-fixed/30 flex items-center justify-center">

                <span class="material-symbols-outlined text-[#745b00]"
                      style="font-variation-settings: 'FILL' 1;">
                    precision_manufacturing
                </span>
            </div>

            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                    TOLERÂNCIA CRÍTICA
                </p>

                <h3 class="text-[28px] font-bold text-primary">
                    <?= $indicadores['toleranciaCritica'] ?>
                </h3>

                <p class="text-on-surface-variant text-[11px]">
                    Margem ≤ 1%
                </p>
            </div>
        </div>

        <!-- NFC -->
        <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm flex flex-col gap-4">

            <div class="w-10 h-10 rounded-lg bg-error-container/50 flex items-center justify-center">

                <span class="material-symbols-outlined text-error"
                      style="font-variation-settings: 'FILL' 1;">
                    nfc
                </span>
            </div>

            <div>
                <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">
                    TAGS NFC
                </p>

                <h3 class="text-[28px] font-bold text-primary">
                    <?= $indicadores['tagsNfc'] ?>
                </h3>

                <p class="text-on-surface-variant text-[11px]">
                    Dispositivos ativos
                </p>
            </div>
        </div>
    </div>

    <!-- TABELA -->
    <section class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">

        <!-- HEADER TABELA -->
        <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center">

            <div class="flex items-center gap-3">

                <h2 class="text-[18px] font-semibold text-primary">
                    Catálogo de Produtos
                </h2>

                <span class="bg-background px-2 py-0.5 rounded text-[11px] font-bold text-on-surface-variant">
                    <?= $total ?> itens
                </span>
            </div>

            <!-- BUSCA -->
            <form method="GET" class="relative">

                <input type="hidden" name="action" value="produtos">

                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">
                    search
                </span>

                <input
                    type="text"
                    name="busca"
                    placeholder="Buscar produto..."
                    value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>"
                    class="bg-background border-none rounded-lg pl-10 pr-4 py-2 text-[13px] focus:ring-1 focus:ring-primary/20 w-64"
                >
            </form>
        </div>

        <!-- TABELA -->
        <table class="w-full text-left border-collapse">

            <thead>
                <tr class="bg-background/50">

                    <th class="px-6 py-4 text-[12px] font-bold text-on-surface-variant uppercase">
                        Produto
                    </th>

                    <th class="px-6 py-4 text-[12px] font-bold text-on-surface-variant uppercase">
                        Categoria
                    </th>

                    <th class="px-6 py-4 text-[12px] font-bold text-on-surface-variant uppercase text-center">
                        Tolerância
                    </th>

                    <th class="px-6 py-4 text-[12px] font-bold text-on-surface-variant uppercase">
                        NFC
                    </th>

                    <th class="px-6 py-4 text-[12px] font-bold text-on-surface-variant uppercase">
                        Status
                    </th>

                    <th class="px-6 py-4 text-[12px] font-bold text-on-surface-variant uppercase text-right">
                        Ações
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-outline-variant">

                <?php if (empty($produtos)): ?>

                    <tr>
                        <td colspan="6"
                            class="px-6 py-12 text-center text-on-surface-variant">

                            <span class="material-symbols-outlined text-[48px] opacity-20 block mb-2">
                                inventory_2
                            </span>

                            Nenhum produto cadastrado.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($produtos as $produto): ?>

                        <?php $p = (object) $produto; ?>

                        <?php
                        $tolerancia = (float)($p->toleranciaPeso ?? 0);

                        $ativo = (bool)($p->ativo ?? false);

                        $categoria = $p->categoria ?? '-';

                        $codigoNfc = $p->codigoNfc ?? '-';

                        $imagem = $p->imagem ?? null;
                        ?>

                        <tr class="hover:bg-background/30 transition-colors">

                            <!-- PRODUTO -->
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-4">

                                    <div class="w-10 h-10 rounded border border-outline-variant overflow-hidden bg-background flex items-center justify-center">

                                        <?php if (!empty($imagem)): ?>

                                            <img
                                                src="<?= htmlspecialchars($imagem) ?>"
                                                class="w-full h-full object-cover"
                                            >

                                        <?php else: ?>

                                            <span class="material-symbols-outlined text-on-surface-variant/40">
                                                package_2
                                            </span>

                                        <?php endif; ?>
                                    </div>

                                    <div class="flex flex-col">

                                        <span class="text-[13px] font-bold text-primary uppercase">
                                            <?= htmlspecialchars($p->nome ?? '-') ?>
                                        </span>

                                        <span class="text-on-surface-variant text-[11px]">
                                            SKU:
                                            <?= htmlspecialchars($p->sku ?? '-') ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- CATEGORIA -->
                            <td class="px-6 py-4">

                                <span class="bg-primary-fixed/40 text-primary px-3 py-1 rounded-full text-[10px] font-bold uppercase">

                                    <?= htmlspecialchars($categoria) ?>
                                </span>
                            </td>

                            <!-- TOLERÂNCIA -->
                            <td class="px-6 py-4 text-center">

                                <span class="font-mono text-[13px] font-bold <?= $tolerancia <= 1 ? 'text-error' : 'text-primary' ?>">

                                    ± <?= number_format($tolerancia, 1, ',', '.') ?>%
                                </span>
                            </td>

                            <!-- NFC -->
                            <td class="px-6 py-4 font-mono text-[12px] text-on-surface-variant">

                                <?= htmlspecialchars($codigoNfc) ?>
                            </td>

                            <!-- STATUS -->
                            <td class="px-6 py-4">

                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold <?= $ativo ? 'bg-tertiary-fixed/30 text-on-tertiary-fixed-variant' : 'bg-outline-variant/30 text-on-surface-variant' ?>">

                                    <span class="w-1.5 h-1.5 rounded-full <?= $ativo ? 'bg-on-tertiary-fixed-variant' : 'bg-on-surface-variant' ?>">
                                    </span>

                                    <?= $ativo ? 'ATIVO' : 'INATIVO' ?>
                                </span>
                            </td>

                            <!-- AÇÕES -->
                            <td class="px-6 py-4 text-right">

                                <div class="flex justify-end gap-2">

                                    <!-- EDITAR -->
                                    <a href="/?action=editar-produto&id=<?= (int)($p->id ?? 0) ?>"
                                       class="p-2 hover:bg-background rounded-lg text-on-surface-variant transition-colors">

                                        <span class="material-symbols-outlined text-[20px]">
                                            edit
                                        </span>
                                    </a>

                                    <!-- EXCLUIR -->
                                    <form method="POST"
                                          action="/?action=excluir-produto"
                                          onsubmit="return confirm('Deseja excluir este produto?')">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)($p->id ?? 0) ?>"
                                        >

                                        <button type="submit"
                                                class="p-2 hover:bg-error/10 rounded-lg text-error transition-colors">

                                            <span class="material-symbols-outlined text-[20px]">
                                                delete
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php
$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>