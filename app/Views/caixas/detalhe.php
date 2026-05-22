<?php

declare(strict_types=1);

$tituloPagina = 'Detalhe da Caixa';

$caixaId = (string) ($caixa['_id'] ?? '');
$estado  = (string) ($caixa['estado'] ?? '');

$badgeClasse = match($estado) {
    'em_transito' => 'badge-transito',
    'entregue'    => 'badge-entregue',
    'violada'     => 'badge-alerta',
    'lacrada'     => 'badge-lacrada',
    default       => 'badge bg-secondary',
};

$estadoLabel = match($estado) {
    'em_transito' => 'Em trânsito',
    'entregue'    => 'Entregue',
    'violada'     => 'Com alerta',
    'lacrada'     => 'Lacrada',
    'criada'      => 'Criada',
    default       => $estado,
};

$serieJson = json_encode($seriePeso, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);

$estilos = [
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css',
];

$scripts = [];

ob_start();
?>

<div class="container-fluid py-4 px-4">

    <?php if (!empty($_SESSION['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['sucesso'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">

        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <h1 class="fw-bold text-dark mb-0">
                    <?= htmlspecialchars((string) ($caixa['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <span class="<?= $badgeClasse ?>"><?= $estadoLabel ?></span>
            </div>
            <p class="text-secondary mb-0">
                <?= htmlspecialchars((string) ($caixa['filial_origem_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                →
                <?= htmlspecialchars((string) ($caixa['filial_destino_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                &nbsp;·&nbsp;
                <?= htmlspecialchars((string) ($caixa['transportadora'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <a href="<?= BASE_URL ?>?action=caixas" class="btn-hermex-secondary text-decoration-none d-inline-flex align-items-center gap-2">
            Voltar
        </a>

    </div>

    <!-- ALERTA ATIVO -->
    <?php if ($estado === 'violada'): ?>
        <?php $jaReconhecido = !empty($caixa['alerta_reconhecido']); ?>
        <div class="alert <?= $jaReconhecido ? 'alert-warning' : 'alert-danger' ?> mb-4" role="alert">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <strong><?= $jaReconhecido ? 'Alerta reconhecido' : 'Alerta ativo' ?>:</strong>
                    esta caixa apresentou anomalia detectada durante o transporte.
                </div>
                <?php if (!$jaReconhecido): ?>
                    <button class="btn btn-danger btn-sm" type="button"
                            data-bs-toggle="collapse" data-bs-target="#formReconhecimento">
                        Reconhecer alerta
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($jaReconhecido && !empty($caixa['ultimo_reconhecimento'])): ?>
                <?php $rec = (array) $caixa['ultimo_reconhecimento']; ?>
                <div class="small text-muted">
                    Classificado como <strong><?= htmlspecialchars((string) ($rec['classificacao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    por <?= htmlspecialchars((string) ($rec['operador'] ?? ''), ENT_QUOTES, 'UTF-8') ?>.
                    Obs.: <?= htmlspecialchars((string) ($rec['observacao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!$jaReconhecido): ?>
                <div class="collapse" id="formReconhecimento">
                    <form method="POST" action="<?= BASE_URL ?>?action=reconhecer-alerta" class="mt-3">
                        <input type="hidden" name="caixa_id" value="<?= htmlspecialchars($caixaId, ENT_QUOTES, 'UTF-8') ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Classificação <span class="text-danger">*</span></label>
                            <select name="classificacao" class="form-select" required>
                                <option value="" disabled selected>Selecione...</option>
                                <option value="violacao_confirmada">Violação confirmada</option>
                                <option value="conferencia_legitima_fora_de_ordem">Conferência legítima fora de ordem (abriu antes de bipar)</option>
                                <option value="investigacao_concluida_sem_violacao">Investigação concluída sem violação</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Observação <span class="text-danger">*</span></label>
                            <textarea name="observacao" class="form-control" rows="3" minlength="10" required
                                      placeholder="Descreva o resultado da investigação (mínimo 10 caracteres)..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger">Confirmar reconhecimento</button>
                            <button type="button" class="btn btn-outline-secondary"
                                    data-bs-toggle="collapse" data-bs-target="#formReconhecimento">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

    <!-- CARDS DE MÉTRICAS (H13) -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small text-secondary mb-1">Peso baseline</div>
                    <div class="fw-bold fs-5">
                        <?= number_format((float) ($caixa['peso_baseline'] ?? 0) / 1000, 3, ',', '.') ?> kg
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small text-secondary mb-1">Peso atual</div>
                    <div class="fw-bold fs-5 <?= (float)($caixa['peso_atual'] ?? 0) > (float)($caixa['peso_baseline'] ?? 0) * 1.05 ? 'text-danger' : '' ?>">
                        <?= number_format((float) ($caixa['peso_atual'] ?? 0) / 1000, 3, ',', '.') ?> kg
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small text-secondary mb-1">Tolerância efetiva</div>
                    <div class="fw-bold fs-5">
                        <?php $tol = $caixa['tolerancia_efetiva'] ?? null; ?>
                        <?= $tol !== null ? number_format((float) $tol, 1, ',', '.') . ' %' : '—' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small text-secondary mb-1">Total de itens</div>
                    <div class="fw-bold fs-5">
                        <?= (int) ($caixa['total_itens'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <!-- COLUNA ESQUERDA: NFs + gráfico -->
        <div class="col-lg-7">

            <!-- NOTAS FISCAIS E PRODUTOS (H14) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        Notas fiscais
                        <span class="badge bg-secondary ms-1">
                            <?= count((array) ($caixa['notas_fiscais'] ?? [])) ?>
                        </span>
                    </h5>

                    <?php $nfs = (array) ($caixa['notas_fiscais'] ?? []); ?>

                    <?php if (empty($nfs)): ?>
                        <p class="text-secondary">Nenhuma nota fiscal vinculada.</p>
                    <?php else: ?>

                        <div class="accordion accordion-flush" id="accordionNfs">

                            <?php foreach ($nfs as $i => $nf):
                                $nfArr   = (array) $nf;
                                $cliente = (array) ($nfArr['cliente_destinatario'] ?? []);
                                $endereco= (array) ($cliente['endereco'] ?? []);
                                $produtos= (array) ($nfArr['produtos'] ?? []);
                                $collapseId = 'nf-' . $i;
                            ?>

                                <div class="accordion-item border rounded-3 mb-2">

                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed rounded-3 fw-semibold"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#<?= $collapseId ?>">
                                            NF <?= htmlspecialchars((string) ($nfArr['numero_nf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            <span class="ms-2 text-secondary fw-normal small">
                                                — <?= htmlspecialchars((string) ($cliente['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </button>
                                    </h2>

                                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse">
                                        <div class="accordion-body pt-2">

                                            <div class="mb-3 small text-secondary">
                                                <strong>Destinatário:</strong>
                                                <?= htmlspecialchars((string) ($cliente['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                (<?= htmlspecialchars((string) ($cliente['documento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                                                <br>
                                                <?= htmlspecialchars(
                                                    ($endereco['logradouro'] ?? '') . ', ' .
                                                    ($endereco['numero'] ?? '') . ' — ' .
                                                    ($endereco['cidade'] ?? '') . '/' .
                                                    ($endereco['uf'] ?? ''),
                                                    ENT_QUOTES, 'UTF-8'
                                                ) ?>
                                            </div>

                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Produto</th>
                                                        <th>SKU</th>
                                                        <th>Categoria</th>
                                                        <th class="text-end">Qtd</th>
                                                        <th class="text-end">Peso unit. (g)</th>
                                                        <th class="text-end">Tol. %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($produtos as $produto):
                                                        $p = (array) $produto;
                                                    ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars((string) ($p['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                            <td><?= htmlspecialchars((string) ($p['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                            <td><?= htmlspecialchars((string) ($p['categoria'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                            <td class="text-end"><?= (int) ($p['quantidade'] ?? 0) ?></td>
                                                            <td class="text-end"><?= (int) ($p['peso_unitario'] ?? 0) ?></td>
                                                            <td class="text-end"><?= number_format((float) ($p['tolerancia'] ?? 0), 1, ',', '.') ?></td>
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

            <!-- GRÁFICO DE PESO (H16) -->
            <?php if (!empty($seriePeso)): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Histórico de peso</h5>

                        <canvas id="graficoPeso" height="120"></canvas>

                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- COLUNA DIREITA: timeline -->
        <div class="col-lg-5">

            <!-- TIMELINE DE EVENTOS (H15) -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        Timeline de eventos
                        <span class="badge bg-secondary ms-1"><?= count($eventos) ?></span>
                    </h5>

                    <?php if (empty($eventos)): ?>
                        <p class="text-secondary">Nenhum evento registrado.</p>
                    <?php else: ?>

                        <div class="timeline-list" style="max-height:520px;overflow-y:auto;">

                            <?php foreach ($eventos as $ev):
                                $evTipo  = (string) ($ev['tipo'] ?? '');
                                $ts      = $ev['timestamp'] ?? null;
                                $tsLabel = $ts instanceof \MongoDB\BSON\UTCDateTime
                                    ? $ts->toDateTime()->setTimezone(new \DateTimeZone('America/Sao_Paulo'))->format('d/m/Y H:i:s')
                                    : '—';

                                $icone = match($evTipo) {
                                    'peso'  => '⚖',
                                    'tampa' => '🔓',
                                    'nfc'   => '📡',
                                    default => '•',
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
                                        <?php endif; ?>

                                        <div class="text-secondary" style="font-size:11px;">
                                            <?= $tsLabel ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>
            </div>

        </div>

    </div>

</div>

<?php if (!empty($seriePeso)): ?>
<script>
(function () {
    const serie = <?= $serieJson ?>;
    const baseline = <?= (float) ($caixa['peso_baseline'] ?? 0) ?>;
    const tolerancia = <?= (float) ($caixa['tolerancia_efetiva'] ?? 0) ?>;

    const labels = serie.map(p => p.ts);
    const dados  = serie.map(p => +(p.valor / 1000).toFixed(3));
    const baselineKg = +(baseline / 1000).toFixed(3);
    const limSup = tolerancia > 0 ? +(baselineKg * (1 + tolerancia / 100)).toFixed(3) : null;
    const limInf = tolerancia > 0 ? +(baselineKg * (1 - tolerancia / 100)).toFixed(3) : null;

    const datasets = [
        {
            label: 'Peso (kg)',
            data: dados,
            borderColor: '#d4b54c',
            backgroundColor: 'rgba(212,181,76,0.1)',
            tension: 0.3,
            fill: true,
            pointRadius: 3,
        },
        {
            label: 'Baseline',
            data: labels.map(() => baselineKg),
            borderColor: '#6c757d',
            borderDash: [6, 3],
            pointRadius: 0,
            fill: false,
        },
    ];

    if (limSup !== null) {
        datasets.push({
            label: 'Limite superior',
            data: labels.map(() => limSup),
            borderColor: 'rgba(220,53,69,0.5)',
            borderDash: [4, 4],
            pointRadius: 0,
            fill: false,
        });
        datasets.push({
            label: 'Limite inferior',
            data: labels.map(() => limInf),
            borderColor: 'rgba(220,53,69,0.5)',
            borderDash: [4, 4],
            pointRadius: 0,
            fill: false,
        });
    }

    new Chart(document.getElementById('graficoPeso'), {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { title: { display: true, text: 'kg' } },
                x: { ticks: { maxTicksLimit: 8 } },
            },
        },
    });
})();
</script>
<?php endif; ?>

<?php
$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>
