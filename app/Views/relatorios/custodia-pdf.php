<style>
    body { font-family: Arial, Helvetica, sans-serif; background: #ececec; padding: 20px; color: #444; }
    .container { background: #fff; padding: 25px; }
    .topo { width: 100%; margin-bottom: 20px; }
    .logo { width: 70px; margin-bottom: 10px; }
    .titulo { text-align: center; margin-top: -70px; }
    .titulo h1 { font-size: 32px; color: #d4b54c; margin: 0; }
    .titulo p { font-size: 13px; color: #666; font-weight: bold; margin-top: 6px; }
    .linha { width: 100%; height: 2px; background: #d4b54c; margin: 20px 0; opacity: .4; }
    .cabecalho-caixa { margin-bottom: 20px; }
    .cabecalho-caixa table { width: 100%; border-collapse: collapse; }
    .cabecalho-caixa td { padding: 6px 10px; font-size: 13px; border: 1px solid #e0e0e0; }
    .cabecalho-caixa td:first-child { font-weight: bold; background: #f7f7f7; width: 30%; }
    h2 { font-size: 16px; color: #444; margin: 20px 0 8px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #d4b54c; color: #fff; padding: 10px; border: 1px solid #d8d8d8; font-size: 13px; }
    td { border: 1px solid #d8d8d8; padding: 8px 10px; font-size: 12px; }
    tr:nth-child(even) { background: #f7f7f7; }
    .badge-anomalia { color: red; font-weight: bold; }
    .rodape { text-align: center; margin-top: 30px; font-size: 11px; color: #777; }
</style>

<?php
$caixaDoc = $resultado['caixa']   ?? null;
$eventos  = $resultado['eventos'] ?? [];

if ($caixaDoc === null): ?>
    <p>Nenhuma caixa encontrada.</p>
<?php return; endif; ?>

<div class="container">

    <div class="topo">
        <img class="logo" src="<?= BASE_PATH . '/public/assets/img/logo-hermex-transparente.png' ?>">
        <div class="titulo">
            <h1>Relatório de Custódia</h1>
            <p>Cadeia de custódia individual • HERMEX</p>
        </div>
    </div>

    <div class="linha"></div>

    <!-- CABEÇALHO DA CAIXA -->
    <div class="cabecalho-caixa">
        <table>
            <tr>
                <td>Código</td>
                <td><?= htmlspecialchars((string) ($caixaDoc['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>Estado</td>
                <td><?= htmlspecialchars((string) ($caixaDoc['estado'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <td>Origem</td>
                <td><?= htmlspecialchars((string) ($caixaDoc['filial_origem_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>Destino</td>
                <td><?= htmlspecialchars((string) ($caixaDoc['filial_destino_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <td>Transportadora</td>
                <td><?= htmlspecialchars((string) ($caixaDoc['transportadora'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>Total itens</td>
                <td><?= (int) ($caixaDoc['total_itens'] ?? 0) ?></td>
            </tr>
            <tr>
                <td>Lacrada em</td>
                <td><?php
                    $le = $caixaDoc['lacrada_em'] ?? null;
                    echo $le instanceof \MongoDB\BSON\UTCDateTime
                        ? $le->toDateTime()->format('d/m/Y H:i') : '—';
                ?></td>
                <td>Previsão chegada</td>
                <td><?php
                    $pc = $caixaDoc['previsao_chegada'] ?? null;
                    echo $pc instanceof \MongoDB\BSON\UTCDateTime
                        ? $pc->toDateTime()->format('d/m/Y H:i') : '—';
                ?></td>
            </tr>
        </table>
    </div>

    <!-- NOTAS FISCAIS -->
    <?php $nfs = (array) ($caixaDoc['notas_fiscais'] ?? []); ?>
    <?php if (!empty($nfs)): ?>
        <h2>Notas Fiscais</h2>
        <?php foreach ($nfs as $nf):
            $nfArr   = (array) $nf;
            $cliente = (array) ($nfArr['cliente_destinatario'] ?? []);
            $produtos= (array) ($nfArr['produtos'] ?? []);
        ?>
            <p style="margin:6px 0 4px;font-size:13px;">
                <strong>NF <?= htmlspecialchars((string) ($nfArr['numero_nf'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                — <?= htmlspecialchars((string) ($cliente['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                (<?= htmlspecialchars((string) ($cliente['documento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
            </p>
            <table style="margin-bottom:12px;">
                <thead>
                    <tr><th>Produto</th><th>SKU</th><th>Qtd</th><th>Peso unit. (g)</th><th>Tol.%</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $p):
                        $pArr = (array) $p;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($pArr['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($pArr['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) ($pArr['quantidade'] ?? 0) ?></td>
                            <td><?= (int) ($pArr['peso_unitario'] ?? 0) ?></td>
                            <td><?= number_format((float) ($pArr['tolerancia'] ?? 0), 1, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- TIMELINE DE EVENTOS -->
    <h2>Cadeia de Custódia — Eventos (<?= count($eventos) ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Anomalia</th>
                <th>Abertura indevida</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventos as $ev):
                $ts = $ev['timestamp'] ?? null;
            ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($ev['tipo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php
                        $tipo = (string) ($ev['tipo'] ?? '');
                        $val  = $ev['valor'] ?? null;
                        if ($tipo === 'peso' && $val !== null) {
                            echo number_format((float) $val / 1000, 3, ',', '.') . ' kg';
                        } elseif ($val !== null) {
                            echo htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td class="<?= (bool)($ev['peso_anomalo'] ?? false) ? 'badge-anomalia' : '' ?>">
                        <?= (bool)($ev['peso_anomalo'] ?? false) ? 'Sim' : 'Não' ?>
                    </td>
                    <td class="<?= (bool)($ev['abertura_indevida'] ?? false) ? 'badge-anomalia' : '' ?>">
                        <?= (bool)($ev['abertura_indevida'] ?? false) ? 'Sim' : 'Não' ?>
                    </td>
                    <td>
                        <?= $ts instanceof \MongoDB\BSON\UTCDateTime
                            ? $ts->toDateTime()->format('d/m/Y H:i:s')
                            : '—' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="rodape">
        Documento gerado automaticamente pelo sistema HERMEX Chain of Custody
    </div>

</div>
