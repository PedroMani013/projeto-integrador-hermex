<style>

    body{
        font-family: Arial, Helvetica, sans-serif;
        background:#ececec;
        padding:20px;
        color:#444;
    }

    .container{
        background:#fff;
        padding:25px;
    }

    /* HEADER */

    .topo{
        width:100%;
        margin-bottom:20px;
    }

    .logo{
        width:70px;
        margin-bottom:10px;
    }

    .titulo{
        text-align:center;
        margin-top:-70px;
    }

    .titulo h1{
        font-size:38px;
        color:#d4b54c;
        margin:0;
    }

    .titulo p{
        font-size:15px;
        color:#666;
        font-weight:bold;
        margin-top:8px;
    }

    .linha{
        width:100%;
        height:2px;
        background:#d4b54c;
        margin-top:20px;
        margin-bottom:25px;
        opacity:.4;
    }

    /* TABELA */

    table{
        width:100%;
        border-collapse:collapse;
    }

    th{
        background:#d4b54c;
        color:#fff;
        padding:12px;
        border:1px solid #d8d8d8;
        font-size:14px;
    }

    td{
        border:1px solid #d8d8d8;
        padding:12px;
        text-align:center;
        font-size:13px;
    }

    tr:nth-child(even){
        background:#f7f7f7;
    }

    /* STATUS */

    .entregue{
        color:green;
        font-weight:bold;
    }

    .em_transito{
        color:#2563eb;
        font-weight:bold;
    }

    .anomalia,
    .violada{
        color:red;
        font-weight:bold;
    }

    /* FOOTER */

    .rodape{
        text-align:center;
        margin-top:30px;
        font-size:12px;
        color:#777;
    }

</style>

<div class="container">

    <!-- TOPO -->
    <div class="topo">

        <img
            class="logo"
            src="<?= BASE_PATH . '/public/assets/img/logo-hermex-transparente.png' ?>"
        >

        <div class="titulo">

            <h1>
                Relatório Operacional
            </h1>

            <p>
                Controle logístico e rastreamento de caixas • HERMEX
            </p>

        </div>

    </div>

    <div class="linha"></div>

    <!-- TABELA -->
    <table>

        <thead>

            <tr>

                <th>Código</th>
                <th>Status</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Total Itens</th>
                <th>Transportadora</th>
                <th>Data</th>

            </tr>

        </thead>

        <tbody>

            <?php foreach ($relatorios as $relatorio): ?>

                <?php

                    $estado = strtolower($relatorio['estado'] ?? '-');

                    $dataFormatada = '-';

                    if (!empty($relatorio['criado_em'])) {

                        $dataFormatada = $relatorio['criado_em']
                            ->toDateTime()
                            ->format('d/m/Y H:i');
                    }

                ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($relatorio['codigo'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td class="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars(str_replace('_', ' ', ucfirst($estado)), ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($relatorio['filial_origem_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($relatorio['filial_destino_nome'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= (int) ($relatorio['total_itens'] ?? 0) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($relatorio['transportadora'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td>
                        <?= $dataFormatada ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

    <div class="rodape">

        Documento gerado automaticamente pelo sistema HERMEX Chain of Custody

    </div>

</div>