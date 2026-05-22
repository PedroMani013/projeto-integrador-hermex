<?php

declare(strict_types=1);

$tituloPagina = 'Alertas';

$estilos = [
    '/assets/css/dashboard.css',
    '/assets/css/alertas.css',
];

$scripts = [];

// helpers de tempo relativo
function tempoAberto(\MongoDB\BSON\UTCDateTime|null $ts): string
{
    if ($ts === null) return '—';
    $diff = time() - (int) ($ts->toDateTime()->getTimestamp());
    if ($diff < 60)        return 'Agora mesmo';
    if ($diff < 3600)      return floor($diff / 60) . ' min';
    if ($diff < 86400)     return floor($diff / 3600) . 'h ' . floor(($diff % 3600) / 60) . 'min';
    return floor($diff / 86400) . 'd ' . floor(($diff % 86400) / 3600) . 'h';
}

function urgencia(\MongoDB\BSON\UTCDateTime|null $ts): string
{
    if ($ts === null) return 'baixa';
    $diff = time() - (int) ($ts->toDateTime()->getTimestamp());
    if ($diff > 3 * 3600)  return 'critica';
    if ($diff > 3600)      return 'alta';
    if ($diff > 1800)      return 'media';
    return 'baixa';
}

ob_start();
?>

<div class="alertas-page">

    <!-- HEADER -->
    <div class="page-header mb-0">
        <div class="page-title-block">
            <h1 class="page-title d-flex align-items-center gap-2">
                <span class="alerta-icon-title" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </span>
                Central de Alertas
                <?php if ($totalAbertos > 0): ?>
                    <span class="alertas-contador-header" aria-label="<?= $totalAbertos ?> alertas não reconhecidos">
                        <?= $totalAbertos ?>
                    </span>
                <?php endif; ?>
            </h1>
            <p class="page-subtitle">Anomalias detectadas durante o trânsito que aguardam triagem</p>
        </div>
    </div>

    <!-- KPIs -->
    <div class="alertas-kpis">

        <div class="alerta-kpi alerta-kpi--danger">
            <div class="alerta-kpi-icone">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
            </div>
            <div>
                <p class="alerta-kpi-valor"><?= $totalAbertos ?></p>
                <p class="alerta-kpi-label">Aguardando triagem</p>
            </div>
        </div>

        <div class="alerta-kpi alerta-kpi--success">
            <div class="alerta-kpi-icone">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div>
                <p class="alerta-kpi-valor"><?= $reconhecidosHoje ?></p>
                <p class="alerta-kpi-label">Reconhecidos hoje</p>
            </div>
        </div>

        <div class="alerta-kpi alerta-kpi--neutral">
            <div class="alerta-kpi-icone">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <p class="alerta-kpi-valor">
                    <?= $tempoMedio !== null ? $tempoMedio . ' min' : '—' ?>
                </p>
                <p class="alerta-kpi-label">Tempo médio de resposta</p>
            </div>
        </div>

        <div class="alerta-kpi alerta-kpi--neutral">
            <div class="alerta-kpi-icone">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                </svg>
            </div>
            <div>
                <p class="alerta-kpi-valor"><?= count($alertas) ?></p>
                <p class="alerta-kpi-label">Total com filtro atual</p>
            </div>
        </div>

    </div>

    <!-- FILTROS -->
    <div class="alertas-filtros-card">

        <div class="alertas-filtros-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
            </svg>
            Filtros
            <?php
            $filtrosAtivos = array_filter($filtros, fn($v) => $v !== '');
            if (!empty($filtrosAtivos)):
            ?>
                <span class="filtros-badge-ativo"><?= count($filtrosAtivos) ?> ativo<?= count($filtrosAtivos) > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>

        <form method="GET" action="<?= BASE_URL ?>" class="alertas-filtros-form">
            <input type="hidden" name="action" value="alertas">

            <div class="filtro-grupo">
                <label class="filtro-label" for="f-origem">Filial de origem</label>
                <select id="f-origem" name="filial_origem" class="filtro-select">
                    <option value="">Todas</option>
                    <?php foreach ($filiais as $f): ?>
                        <option value="<?= htmlspecialchars((string) ($f['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($filtros['filial_origem'] === (string) ($f['codigo'] ?? '')) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($f['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtro-grupo">
                <label class="filtro-label" for="f-destino">Filial de destino</label>
                <select id="f-destino" name="filial_destino" class="filtro-select">
                    <option value="">Todas</option>
                    <?php foreach ($filiais as $f): ?>
                        <option value="<?= htmlspecialchars((string) ($f['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($filtros['filial_destino'] === (string) ($f['codigo'] ?? '')) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($f['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtro-grupo">
                <label class="filtro-label" for="f-transportadora">Transportadora</label>
                <select id="f-transportadora" name="transportadora" class="filtro-select">
                    <option value="">Todas</option>
                    <?php foreach ($transportadoras as $t): ?>
                        <option value="<?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($filtros['transportadora'] === $t) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtro-grupo">
                <label class="filtro-label" for="f-reconhecido">Status</label>
                <select id="f-reconhecido" name="reconhecido" class="filtro-select">
                    <option value="">Todos</option>
                    <option value="0" <?= ($filtros['reconhecido'] === '0') ? 'selected' : '' ?>>Aguardando triagem</option>
                    <option value="1" <?= ($filtros['reconhecido'] === '1') ? 'selected' : '' ?>>Reconhecido</option>
                </select>
            </div>

            <div class="filtro-grupo">
                <label class="filtro-label" for="f-inicio">De</label>
                <input type="date" id="f-inicio" name="data_inicio" class="filtro-input"
                       value="<?= htmlspecialchars($filtros['data_inicio'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="filtro-grupo">
                <label class="filtro-label" for="f-fim">Até</label>
                <input type="date" id="f-fim" name="data_fim" class="filtro-input"
                       value="<?= htmlspecialchars($filtros['data_fim'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="filtro-acoes">
                <button type="submit" class="btn-hermex-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    Filtrar
                </button>
                <?php if (!empty($filtrosAtivos)): ?>
                    <a href="<?= BASE_URL ?>?action=alertas" class="btn-hermex-secondary text-decoration-none">
                        Limpar
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- TABELA / LISTA -->
    <div class="tabela-card">

        <div class="tabela-card-header">
            <div class="tabela-titulo-wrap">
                <h2 class="tabela-titulo">Registros de anomalias</h2>
                <span class="tabela-contador" aria-label="<?= count($alertas) ?> registros"><?= count($alertas) ?></span>
            </div>
            <?php if ($totalAbertos > 0): ?>
                <span class="alerta-urgencia-legenda">
                    <span class="urgencia-dot urgencia-dot--critica"></span> +3h
                    <span class="urgencia-dot urgencia-dot--alta"></span> +1h
                    <span class="urgencia-dot urgencia-dot--media"></span> +30min
                    <span class="urgencia-dot urgencia-dot--baixa"></span> recente
                </span>
            <?php endif; ?>
        </div>

        <?php if (empty($alertas)): ?>
            <div class="estado-vazio">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <p class="fw-semibold mt-3 mb-1" style="color: var(--hermex-text);">Nenhum alerta encontrado</p>
                <p class="small">
                    <?php if (!empty($filtrosAtivos)): ?>
                        Tente ajustar os filtros aplicados.
                    <?php else: ?>
                        Todas as caixas estão em trânsito sem anomalias detectadas.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>

            <!-- tabela desktop -->
            <div class="tabela-wrapper">
                <table class="hermex-table" aria-label="Registros de alertas">
                    <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Caixa</th>
                            <th scope="col">Rota</th>
                            <th scope="col">Transportadora</th>
                            <th scope="col">Aberto há</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alertas as $alerta):
                            $id          = (string) ($alerta['_id'] ?? '');
                            $codigo      = (string) ($alerta['codigo'] ?? '');
                            $origem      = (string) ($alerta['filial_origem_nome'] ?? ($alerta['filial_origem_codigo'] ?? ''));
                            $destino     = (string) ($alerta['filial_destino_nome'] ?? ($alerta['filial_destino_codigo'] ?? ''));
                            $transp      = (string) ($alerta['transportadora'] ?? '');
                            $reconhecido = !empty($alerta['alerta_reconhecido']);
                            $tsEvento    = $alerta['ultimo_evento']['timestamp'] ?? null;
                            $urgClass    = $reconhecido ? 'reconhecido' : urgencia($tsEvento instanceof \MongoDB\BSON\UTCDateTime ? $tsEvento : null);
                            $tempo       = tempoAberto($tsEvento instanceof \MongoDB\BSON\UTCDateTime ? $tsEvento : null);
                            $rec         = $reconhecido ? (array) ($alerta['ultimo_reconhecimento'] ?? []) : [];
                        ?>
                        <tr class="<?= $reconhecido ? '' : 'linha-alerta' ?>">
                            <td class="ps-3" style="width:4px; padding-right:0;">
                                <span class="urgencia-barra urgencia-barra--<?= $urgClass ?>" aria-hidden="true"></span>
                            </td>
                            <td>
                                <div class="caixa-codigo"><?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if (!empty($alerta['tag_nfc'])): ?>
                                    <div class="caixa-nf">NFC: <?= htmlspecialchars((string) $alerta['tag_nfc'], ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="rota-cidades">
                                    <?= htmlspecialchars($origem, ENT_QUOTES, 'UTF-8') ?>
                                    <span aria-hidden="true"> → </span>
                                    <?= htmlspecialchars($destino, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($transp, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="tempo-aberto tempo-aberto--<?= $urgClass ?>">
                                    <?= htmlspecialchars($tempo, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($reconhecido): ?>
                                    <span class="badge-reconhecido">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        Reconhecido
                                    </span>
                                    <?php if (!empty($rec['classificacao'])): ?>
                                        <div class="classificacao-label">
                                            <?= htmlspecialchars(str_replace('_', ' ', (string) $rec['classificacao']), ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge-pendente">
                                        <span class="pulse-dot" aria-hidden="true"></span>
                                        Aguardando triagem
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= BASE_URL ?>?action=detalhe-caixa&id=<?= urlencode($id) ?>"
                                   class="btn-hermex-secondary text-decoration-none"
                                   aria-label="Ver detalhes da caixa <?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>">
                                    Ver detalhes
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- cards mobile -->
            <div class="tabela-cards-mobile" role="list" aria-label="Alertas">
                <?php foreach ($alertas as $alerta):
                    $id          = (string) ($alerta['_id'] ?? '');
                    $codigo      = (string) ($alerta['codigo'] ?? '');
                    $origem      = (string) ($alerta['filial_origem_nome'] ?? ($alerta['filial_origem_codigo'] ?? ''));
                    $destino     = (string) ($alerta['filial_destino_nome'] ?? ($alerta['filial_destino_codigo'] ?? ''));
                    $transp      = (string) ($alerta['transportadora'] ?? '');
                    $reconhecido = !empty($alerta['alerta_reconhecido']);
                    $tsEvento    = $alerta['ultimo_evento']['timestamp'] ?? null;
                    $urgClass    = $reconhecido ? 'reconhecido' : urgencia($tsEvento instanceof \MongoDB\BSON\UTCDateTime ? $tsEvento : null);
                    $tempo       = tempoAberto($tsEvento instanceof \MongoDB\BSON\UTCDateTime ? $tsEvento : null);
                ?>
                <div class="caixa-card-mobile <?= $reconhecido ? '' : 'alerta-card' ?>" role="listitem">
                    <div class="caixa-card-topo">
                        <div class="d-flex align-items-center gap-2">
                            <span class="urgencia-barra urgencia-barra--<?= $urgClass ?>" style="height:32px; width:3px;" aria-hidden="true"></span>
                            <strong class="caixa-codigo"><?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                        <?php if ($reconhecido): ?>
                            <span class="badge-reconhecido">Reconhecido</span>
                        <?php else: ?>
                            <span class="badge-pendente"><span class="pulse-dot"></span> Aguardando</span>
                        <?php endif; ?>
                    </div>
                    <div class="caixa-card-grid">
                        <div>
                            <div class="caixa-card-label">Origem</div>
                            <div class="caixa-card-val"><?= htmlspecialchars($origem, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div>
                            <div class="caixa-card-label">Destino</div>
                            <div class="caixa-card-val"><?= htmlspecialchars($destino, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div>
                            <div class="caixa-card-label">Transportadora</div>
                            <div class="caixa-card-val"><?= htmlspecialchars($transp, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div>
                            <div class="caixa-card-label">Aberto há</div>
                            <div class="caixa-card-val tempo-aberto--<?= $urgClass ?>"><?= htmlspecialchars($tempo, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>?action=detalhe-caixa&id=<?= urlencode($id) ?>"
                           class="btn-hermex-secondary text-decoration-none w-100 justify-content-center">
                            Ver detalhes
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php
$conteudo = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
