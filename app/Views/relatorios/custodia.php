<?php

declare(strict_types=1);

$tituloPagina = 'Relatório de Custódia';

$estilos = [
    '/assets/css/dashboard.css',
    '/assets/css/hermex_pages.css',
];

$scripts = [];

ob_start();

$caixaDoc = $resultado['caixa']   ?? null;
$eventos  = $resultado['eventos'] ?? [];
$estadoLabel = match((string) ($caixaDoc['estado'] ?? '')) {
    'em_transito' => 'Em trânsito',
    'entregue'    => 'Entregue',
    'violada'     => 'Com alerta',
    'lacrada'     => 'Lacrada',
    'criada'      => 'Criada',
    default       => (string) ($caixaDoc['estado'] ?? '—'),
};
$badgeClasse = match((string) ($caixaDoc['estado'] ?? '')) {
    'em_transito' => 'badge-transito',
    'entregue'    => 'badge-entregue',
    'violada'     => 'badge-alerta',
    'lacrada'     => 'badge-lacrada',
    default       => 'badge bg-secondary',
};
?>

<div class="container-fluid py-4 px-4">

    <!-- HEADER -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Relatório de Custódia</h1>
            <p class="page-subtitle">Cadeia de custódia completa de uma caixa individual.</p>
        </div>
        <a href="/?action=relatorios"
           class="btn-hermex-secondary text-decoration-none d-inline-flex align-items-center gap-2">
            Operacional
        </a>
    </div>

    <!-- BUSCA -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET">
                <input type="hidden" name="action" value="relatorio-custodia">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label small text-uppercase fw-bold text-secondary">
                            Código da caixa
                        </label>
                        <input
                            type="text"
                            name="codigo"
                            class="form-control rounded-4 shadow-none"
                            placeholder="Ex: CG-2001"
                            value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>"
                            autofocus
                        >
                    </div>
                    <div class="col-md-4">
                        <button class="btn-hermex-primary w-100 d-flex justify-content-center">
                            Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($codigo !== '' && $resultado === null): ?>

        <div class="alert alert-warning">
            Nenhuma caixa encontrada com o código <strong><?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?></strong>.
        </div>

    <?php elseif ($resultado !== null): ?>

        <!-- CABEÇALHO DA CAIXA -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                    <h4 class="fw-bold mb-0">
                        <?= htmlspecialchars((string) ($caixaDoc['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </h4>
                    <span class="<?= $badgeClasse ?>"><?= $estadoLabel ?></span>

                    <a href="<?= htmlspecialchars(BASE_URL . '?action=exportar-custodia&codigo=' . urlencode($codigo), ENT_QUOTES, 'UTF-8') ?>"
                       class="btn-hermex-primary d-inline-flex align-items-center gap-2 text-decoration-none ms-auto">
                        Exportar PDF
                    </a>
                </div>

                <div class="row g-3">

                    <div class="col-sm-6 col-md-3">
                        <div class="small text-secondary">Rota</div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars((string) ($caixaDoc['filial_origem_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            →
                            <?= htmlspecialchars((string) ($caixaDoc['filial_destino_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <div class="small text-secondary">Transportadora</div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars((string) ($caixaDoc['transportadora'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <div class="small text-secondary">Lacrada em</div>
                        <div class="fw-semibold">
                            <?php
                            $lacradaEm = $caixaDoc['lacrada_em'] ?? null;
                            echo $lacradaEm instanceof \MongoDB\BSON\UTCDateTime
                                ? $lacradaEm->toDateTime()->setTimezone(new \DateTimeZone('America/Sao_Paulo'))->format('d/m/Y H:i')
                                : '—';
                            ?>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <div class="small text-secondary">Previsão de chegada</div>
                        <div class="fw-semibold">
                            <?php
                            $previsao = $caixaDoc['previsao_chegada'] ?? null;
                            echo $previsao instanceof \MongoDB\BSON\UTCDateTime
                                ? $previsao->toDateTime()->setTimezone(new \DateTimeZone('America/Sao_Paulo'))->format('d/m/Y H:i')
                                : '—';
                            ?>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="row g-4">

            <!-- NOTAS FISCAIS -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">
                            Notas fiscais
                            <span class="badge bg-secondary ms-1">
                                <?= count((array) ($caixaDoc['notas_fiscais'] ?? [])) ?>
                            </span>
                        </h5>

                        <?php $nfs = (array) ($caixaDoc['notas_fiscais'] ?? []); ?>

                        <?php if (empty($nfs)): ?>
                            <p class="text-secondary">Nenhuma nota fiscal vinculada.</p>
                        <?php else: ?>
                            <div class="accordion accordion-flush" id="accordionNfsCust">
                                <?php foreach ($nfs as $i => $nf):
                                    $nfArr   = (array) $nf;
                                    $cliente = (array) ($nfArr['cliente_destinatario'] ?? []);
                                    $produtos= (array) ($nfArr['produtos'] ?? []);
                                    $colId   = 'cnf-' . $i;
                                ?>
                                    <div class="accordion-item border rounded-3 mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed rounded-3 fw-semibold"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#<?= $colId ?>">
                                                NF <?= htmlspecialchars((string) ($nfArr['numero_nf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                <span class="ms-2 text-secondary fw-normal small">
                                                    — <?= htmlspecialchars((string) ($cliente['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="<?= $colId ?>" class="accordion-collapse collapse">
                                            <div class="accordion-body pt-2">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Produto</th>
                                                            <th class="text-end">Qtd</th>
                                                            <th class="text-end">Tol.%</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($produtos as $p):
                                                            $pArr = (array) $p;
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars((string) ($pArr['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td class="text-end"><?= (int) ($pArr['quantidade'] ?? 0) ?></td>
                                                                <td class="text-end"><?= number_format((float) ($pArr['tolerancia'] ?? 0), 1, ',', '.') ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- TIMELINE COMPLETA -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">
                            Cadeia de custódia
                            <span class="badge bg-secondary ms-1"><?= count($eventos) ?></span>
                        </h5>

                        <?php if (empty($eventos)): ?>
                            <p class="text-secondary">Nenhum evento registrado.</p>
                        <?php else: ?>
                            <div style="max-height:520px;overflow-y:auto;">
                                <?php foreach ($eventos as $ev):
                                    $evTipo  = (string) ($ev['tipo'] ?? '');
                                    $ts      = $ev['timestamp'] ?? null;
                                    $tsLabel = $ts instanceof \MongoDB\BSON\UTCDateTime
                                        ? $ts->toDateTime()->setTimezone(new \DateTimeZone('America/Sao_Paulo'))->format('d/m/Y H:i:s')
                                        : '—';
                                    $icone = match($evTipo) {
                                        'peso'      => '⚖',
                                        'tampa'     => '🔓',
                                        'nfc'       => '📡',
                                        'transicao' => '🔄',
                                        default     => '•',
                                    };
                                    $anomalo  = (bool) ($ev['peso_anomalo'] ?? false);
                                    $indevida = (bool) ($ev['abertura_indevida'] ?? false);
                                    $corBorda = ($anomalo || $indevida) ? 'border-danger' : 'border-secondary';
                                ?>
                                    <div class="d-flex gap-3 mb-3">
                                        <div class="flex-shrink-0 text-center" style="width:28px;">
                                            <span style="font-size:18px;"><?= $icone ?></span>
                                        </div>
                                        <div class="border-start border-2 ps-3 <?= $corBorda ?> flex-grow-1">
                                            <div class="fw-semibold small text-uppercase">
                                                <?= htmlspecialchars($evTipo, ENT_QUOTES, 'UTF-8') ?>
                                                <?php if ($anomalo): ?>
                                                    <span class="badge bg-danger ms-1">anomalia</span>
                                                <?php endif; ?>
                                                <?php if ($indevida): ?>
                                                    <span class="badge bg-danger ms-1">abertura indevida</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($evTipo === 'peso'): ?>
                                                <div class="small text-secondary">
                                                    <?= number_format((float) ($ev['valor'] ?? 0) / 1000, 3, ',', '.') ?> kg
                                                </div>
                                            <?php elseif ($evTipo === 'transicao'): ?>
                                                <div class="small text-secondary">
                                                    → <?= htmlspecialchars((string) ($ev['valor'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-secondary" style="font-size:11px;"><?= $tsLabel ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

<?php
$conteudo = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
