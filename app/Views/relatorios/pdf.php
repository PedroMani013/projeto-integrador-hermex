<style>

body{
    font-family: Arial;
}

.logo{
    font-size:32px;
    font-weight:bold;
    color:#1e293b;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid #ccc;
    padding:10px;
}

th{
    background:#1e293b;
    color:#fff;
}

</style>

<div class="logo">
    hermeX
</div>

<h2>
    Relatório Operacional
</h2>

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

            <tr>

                <td>
                    <?= htmlspecialchars($relatorio['codigo'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                </td>

                <td>
                    <?= htmlspecialchars($relatorio['estado'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
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

                    <?php if (!empty($relatorio['criado_em'])): ?>

                        <?= $relatorio['criado_em']
                            ->toDateTime()
                            ->format('d/m/Y H:i') ?>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endforeach; ?>

    </tbody>

</table>