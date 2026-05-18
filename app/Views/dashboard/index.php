<?php
/**
 * view: Dashboard
 * variáveis recebidas do DashboardController:
 *   $indicadores       array
 *   $integridade14d    array de ['data', 'percentual']
 *   $alertasRecentes   AlertaRecente[]
 *   $paginacaoCaixas   array{total, pagina, porPagina, caixas: Caixa[]}
 */

use App\Models\Caixa;
use App\Models\AlertaRecente;

$tituloPagina = 'Dashboard';
$estilos      = ['/assets/css/dashboard.css'];
$scripts      = ['/assets/js/dashboard.js', '/assets/js/chart-integridade.js'];

// dados para o gráfico (serializado para JS)
$dadosGrafico = json_encode(array_map(fn($p) => [
    'data'       => $p['data'],
    'percentual' => $p['percentual'],
], $integridade14d));

// paginação
$pagina      = $paginacaoCaixas['pagina'];
$porPagina   = $paginacaoCaixas['porPagina'];
$total       = $paginacaoCaixas['total'];
$totalPaginas= (int) ceil($total / max(1, $porPagina));
$caixas      = $paginacaoCaixas['caixas'];

// timestamp atual formatado pt-BR
$agora = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
$meses = ['jan.','fev.','mar.','abr.','mai.','jun.','jul.','ago.','set.','out.','nov.','dez.'];
$dataFormatada = $agora->format('j') . ' de ' . $meses[(int)$agora->format('n') - 1] . ' de ' . $agora->format('Y') . ' às ' . $agora->format('H:i');

ob_start();
?>

<!-- cabeçalho da página -->
<div class="page-header">
    <div class="page-title-block">
        <h1 class="page-title">Visão geral da operação</h1>
        <p class="page-subtitle">
            Monitoramento de cadeia de custódia em tempo real
            <span class="bullet-sep" aria-hidden="true">•</span>
            <time datetime="<?= $agora->format('Y-m-d\TH:i') ?>"><?= htmlspecialchars($dataFormatada) ?></time>
        </p>
    </div>
    <div class="page-header-actions">
        <!-- HU01: conectar ao formulário/modal de cadastro de caixa quando implementado -->
        <button class="btn-hermex-primary" type="button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nova caixa
        </button>
    </div>
</div>

<!--faixa de alerta -->
<?php if ($indicadores['alertasAbertos'] > 0): ?>
<div class="faixa-alerta" role="alert" aria-live="polite">
    <span class="faixa-alerta-icone" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round"
             stroke-linejoin="round">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </span>
    <div class="faixa-alerta-texto">
        <p class="faixa-alerta-titulo">
            <?= $indicadores['alertasAbertos'] ?>
            <?= $indicadores['alertasAbertos'] === 1 ? 'caixa exige' : 'caixas exigem' ?> atenção
        </p>
        <p class="faixa-alerta-sub">Anomalias detectadas durante o trânsito. Verifique os detalhes antes de tomar uma ação.</p>
    </div>
    <div class="faixa-alerta-acoes">
        <!-- HU15: quando a tela de alertas existir, redirecionar para /?action=alertas em vez de filtrar caixas -->
        <a href="/?action=caixas&estado=violada" class="btn btn-sm btn-danger">
            Ver alertas
        </a>
    </div>
</div>
<?php endif; ?>

<!-- cards -->
<div class="cards-grid" role="list" aria-label="Indicadores da operação">

    <!-- caixas em trânsito -->
    <article class="card-indicador" role="listitem">
        <div class="card-indicador-topo">
            <div class="card-indicador-icone icone-transito" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
                </svg>
            </div>
            <canvas class="card-indicador-sparkline"
                    id="sparkline-transito"
                    width="60" height="30"
                    aria-hidden="true"></canvas>
        </div>
        <div>
            <p class="card-indicador-label">Caixas em trânsito</p>
            <p class="card-indicador-valor" aria-label="<?= $indicadores['caixasEmTransito'] ?> caixas em trânsito">
                <?= $indicadores['caixasEmTransito'] ?>
            </p>
        </div>
        <p class="card-indicador-comp">Monitoradas agora</p>
    </article>

    <!-- anomalias 24h -->
    <article class="card-indicador" role="listitem">
        <div class="card-indicador-topo">
            <div class="card-indicador-icone icone-anomalia" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <canvas class="card-indicador-sparkline"
                    id="sparkline-anomalias"
                    width="60" height="30"
                    aria-hidden="true"></canvas>
        </div>
        <div>
            <p class="card-indicador-label">Sinais detectados (24h)</p>
            <p class="card-indicador-valor" aria-label="<?= (int) ($indicadores['sinaisIsolados24h'] ?? 0) ?> sinais isolados nas últimas 24 horas">
                <?= (int) ($indicadores['sinaisIsolados24h'] ?? 0) ?>
            </p>
        </div>
        <p class="card-indicador-comp">Nos últimos eventos registrados</p>
    </article>

    <!-- entregues no mês -->
    <article class="card-indicador" role="listitem">
        <div class="card-indicador-topo">
            <div class="card-indicador-icone icone-entregue" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <canvas class="card-indicador-sparkline"
                    id="sparkline-entregues"
                    width="60" height="30"
                    aria-hidden="true"></canvas>
        </div>
        <div>
            <p class="card-indicador-label">Entregues no mês</p>
            <p class="card-indicador-valor" aria-label="<?= $indicadores['entreguesMes'] ?> caixas entregues este mês">
                <?= $indicadores['entreguesMes'] ?>
            </p>
        </div>
        <p class="card-indicador-comp">No mês corrente</p>
    </article>

    <!-- alertas abertos -->
    <article class="card-indicador" role="listitem">
        <div class="card-indicador-topo">
            <div class="card-indicador-icone icone-alerta" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
            </div>
            <canvas class="card-indicador-sparkline"
                    id="sparkline-alertas"
                    width="60" height="30"
                    aria-hidden="true"></canvas>
        </div>
        <div>
            <p class="card-indicador-label">Alertas abertos</p>
            <p class="card-indicador-valor" aria-label="<?= $indicadores['alertasAbertos'] ?> alertas abertos">
                <?= $indicadores['alertasAbertos'] ?>
            </p>
        </div>
        <p class="card-indicador-comp">Aguardando reconhecimento</p>
    </article>

</div>

<!-- bloco duplo: gráfico + alertas recentes -->
<div class="painel-duplo">

    <!-- gráfico de integridade -->
    <section class="painel-card" aria-labelledby="titulo-grafico">
        <div class="painel-card-header">
            <h2 class="painel-card-titulo" id="titulo-grafico">
                Integridade da cadeia nos últimos 14 dias
            </h2>
        </div>
        <div class="painel-card-body">
            <div class="chart-wrapper">
                <canvas id="graficoIntegridade"
                        aria-label="Gráfico de linha mostrando percentual de integridade da cadeia nos últimos 14 dias"
                        role="img"></canvas>
            </div>
        </div>
    </section>

    <!-- alertas recentes -->
    <section class="painel-card" aria-labelledby="titulo-alertas">
        <div class="painel-card-header">
            <h2 class="painel-card-titulo" id="titulo-alertas">Alertas recentes</h2>
            <a href="/?action=alertas" class="link-muted">Ver todos</a>
        </div>
        <div class="painel-card-body" style="padding: 0 20px;">

            <?php if (empty($alertasRecentes)): ?>
                <div class="estado-vazio">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <p>Nenhum alerta registrado.</p>
                </div>
            <?php else: ?>
                <ul class="alerta-lista" aria-label="Lista de alertas recentes">
                    <?php foreach ($alertasRecentes as $alerta): ?>
                        <?php /** @var AlertaRecente $alerta */ ?>
                        <li class="alerta-item">
                            <span class="alerta-bolinha bolinha-<?= htmlspecialchars($alerta->nivel) ?>"
                                  aria-label="Nível: <?= $alerta->nivel === 'critico' ? 'crítico' : 'anomalia leve' ?>">
                            </span>
                            <div class="alerta-texto">
                                <p class="alerta-titulo" title="<?= htmlspecialchars($alerta->titulo) ?>">
                                    <?= htmlspecialchars($alerta->titulo) ?>
                                </p>
                                <p class="alerta-meta">
                                    <?= htmlspecialchars($alerta->caixaCodigo) ?>
                                    <span aria-hidden="true"> • </span>
                                    <?= htmlspecialchars($alerta->filialOrigem) ?> para <?= htmlspecialchars($alerta->filialDestino) ?>
                                    <span aria-hidden="true"> • </span>
                                    <time datetime="<?= $alerta->timestamp->format('Y-m-d\TH:i') ?>">
                                        <?= $alerta->timestamp->format('d/m H:i') ?>
                                    </time>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </div>
    </section>

</div>

<!-- tabela de caixas em trânsito -->
<section class="tabela-card" aria-labelledby="titulo-tabela">

    <div class="tabela-card-header">
        <div class="tabela-titulo-wrap">
            <h2 class="tabela-titulo" id="titulo-tabela">Caixas em trânsito agora</h2>
            <span class="tabela-contador" aria-label="Total de <?= $total ?> caixas">
                <?= $total ?>
            </span>
        </div>
        <a href="/?action=caixas&estado=em_transito" class="link-muted">Ver todas</a>
    </div>

    <?php if (empty($caixas)): ?>
        <div class="estado-vazio">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
            </svg>
            <p>Nenhuma caixa em trânsito no momento.</p>
        </div>
    <?php else: ?>

        <!-- tabela (desktop) -->
        <div class="tabela-wrapper" role="region" aria-label="Tabela de caixas em trânsito" tabindex="0">
            <table class="hermex-table" aria-label="Caixas em trânsito">
                <thead>
                    <tr>
                        <th scope="col">Caixa</th>
                        <th scope="col">Origem para Destino</th>
                        <th scope="col">Transportadora</th>
                        <th scope="col">Peso atual</th>
                        <th scope="col">Status</th>
                        <th scope="col">Previsão de chegada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($caixas as $caixa): ?>
                        <?php /** @var Caixa $caixa */ ?>
                        <tr class="<?= $caixa->temAlerta() ? 'linha-alerta' : '' ?>">
                            <td>
                                <div class="caixa-codigo"><?= htmlspecialchars($caixa->codigo) ?></div>
                                <div class="caixa-nf">
                                    NF <?= htmlspecialchars($caixa->notaFiscal) ?>
                                    <span aria-hidden="true"> • </span>
                                    <?= $caixa->totalItens ?> itens
                                </div>
                            </td>
                            <td>
                                <div class="rota-cidades">
                                    <?= htmlspecialchars($caixa->filialOrigemNome) ?>
                                    <span aria-hidden="true"> → </span>
                                    <?= htmlspecialchars($caixa->filialDestinoNome) ?>
                                </div>
                                <div class="rota-codigos">
                                    <?= htmlspecialchars($caixa->filialOrigemCodigo) ?>
                                    <span aria-hidden="true"> / </span>
                                    <?= htmlspecialchars($caixa->filialDestinoCodigo) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($caixa->transportadora) ?></td>
                            <td>
                                <?php
                                $pesoKg = round($caixa->pesoAtual / 1000, 2);
                                $delta  = $caixa->deltaKg();
                                $deltaClasse = $delta < -0.1 ? 'delta-negativo' : ($delta > 0.1 ? 'delta-positivo' : 'delta-neutro');
                                $deltaLabel  = $delta > 0 ? "+{$delta}" : (string) $delta;
                                ?>
                                <span class="peso-valor"><?= $pesoKg ?> kg</span>
                                <br>
                                <span class="<?= $deltaClasse ?>" aria-label="Diferença de <?= $deltaLabel ?> kg em relação ao peso registrado">
                                    <?= $deltaLabel ?> kg
                                </span>
                            </td>
                            <td>
                                <span class="badge-estado <?= htmlspecialchars($caixa->badgeClasse()) ?>"
                                      aria-label="Estado: <?= htmlspecialchars($caixa->estadoLabel()) ?>">
                                    <?php if ($caixa->temAlerta()): ?>
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="12" y1="8" x2="12" y2="12"/>
                                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                                        </svg>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($caixa->estadoLabel()) ?>
                                </span>
                            </td>
                            <td>
                                <time datetime="<?= $caixa->previsaoChegada->format('Y-m-d\TH:i') ?>">
                                    <?= $caixa->previsaoChegada->format('d/m/Y H:i') ?>
                                </time>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- cards mobile -->
        <div class="tabela-cards-mobile" role="list" aria-label="Lista de caixas em trânsito">
            <?php foreach ($caixas as $caixa): ?>
                <?php
                $pesoKg = round($caixa->pesoAtual / 1000, 2);
                $delta  = $caixa->deltaKg();
                $deltaClasse = $delta < -0.1 ? 'delta-negativo' : ($delta > 0.1 ? 'delta-positivo' : 'delta-neutro');
                $deltaLabel  = $delta > 0 ? "+{$delta}" : (string) $delta;
                ?>
                <div class="caixa-card-mobile <?= $caixa->temAlerta() ? 'alerta-card' : '' ?>"
                     role="listitem">
                    <div class="caixa-card-topo">
                        <strong class="caixa-codigo"><?= htmlspecialchars($caixa->codigo) ?></strong>
                        <span class="badge-estado <?= htmlspecialchars($caixa->badgeClasse()) ?>">
                            <?= htmlspecialchars($caixa->estadoLabel()) ?>
                        </span>
                    </div>
                    <div class="caixa-card-grid">
                        <div>
                            <div class="caixa-card-label">Nota fiscal</div>
                            <div class="caixa-card-val"><?= htmlspecialchars($caixa->notaFiscal) ?></div>
                        </div>
                        <div>
                            <div class="caixa-card-label">Itens</div>
                            <div class="caixa-card-val"><?= $caixa->totalItens ?></div>
                        </div>
                        <div>
                            <div class="caixa-card-label">Origem</div>
                            <div class="caixa-card-val"><?= htmlspecialchars($caixa->filialOrigemNome) ?></div>
                        </div>
                        <div>
                            <div class="caixa-card-label">Destino</div>
                            <div class="caixa-card-val"><?= htmlspecialchars($caixa->filialDestinoNome) ?></div>
                        </div>
                        <div>
                            <div class="caixa-card-label">Peso atual</div>
                            <div class="caixa-card-val">
                                <?= $pesoKg ?> kg
                                <span class="<?= $deltaClasse ?>">(<?= $deltaLabel ?> kg)</span>
                            </div>
                        </div>
                        <div>
                            <div class="caixa-card-label">Previsão de chegada</div>
                            <div class="caixa-card-val">
                                <time datetime="<?= $caixa->previsaoChegada->format('Y-m-d\TH:i') ?>">
                                    <?= $caixa->previsaoChegada->format('d/m H:i') ?>
                                </time>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- paginação -->
        <?php if ($totalPaginas > 1): ?>
        <div class="paginacao" role="navigation" aria-label="Paginação da tabela de caixas">
            <span class="paginacao-info">
                Exibindo <?= (($pagina - 1) * $porPagina) + 1 ?> a <?= min($pagina * $porPagina, $total) ?> de <?= $total ?> caixas
            </span>
            <div class="paginacao-btns">
                <?php if ($pagina > 1): ?>
                    <a href="/?action=dashboard&pagina=<?= $pagina - 1 ?>"
                       class="paginacao-btn"
                       aria-label="Página anterior">
                        ← Anterior
                    </a>
                <?php else: ?>
                    <button class="paginacao-btn" disabled aria-disabled="true">← Anterior</button>
                <?php endif; ?>

                <span class="paginacao-btn" style="cursor:default; background:var(--hermex-surface-alt);"
                      aria-current="page" aria-label="Página <?= $pagina ?> de <?= $totalPaginas ?>">
                    <?= $pagina ?> / <?= $totalPaginas ?>
                </span>

                <?php if ($pagina < $totalPaginas): ?>
                    <a href="/?action=dashboard&pagina=<?= $pagina + 1 ?>"
                       class="paginacao-btn"
                       aria-label="Próxima página">
                        Próxima →
                    </a>
                <?php else: ?>
                    <button class="paginacao-btn" disabled aria-disabled="true">Próxima →</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</section>

<!-- dados pro Chart.js -->
<script>
    window.HERMEX_DADOS_INTEGRIDADE = <?= $dadosGrafico ?>;
</script>

<?php
$conteudo = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
