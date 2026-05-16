<?php

declare(strict_types=1);

$tituloPagina = 'Filiais';

$estilos = [
    '/assets/css/bootstrap.min.css',
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css',
    '/assets/css/filiais.css'
    
];

$scripts = [
    '/assets/js/bootstrap.bundle.min.js'
];

ob_start();
?>

<div class="container-fluid py-4 px-4 filial-page">

    <!-- HEADER --->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <h1 class="fw-bold mb-1 titulo-filial">
                Gestão de Filiais
            </h1>

            <p class="text-secondary mb-0">
                Administre os pontos de coleta e distribuição da malha logística.
            </p>

        </div>
        

        <a href="<?= BASE_URL ?>?action=cadastro-filial"
             class="btn btn-primary btn-lg rounded-6 shadow-sm px-3 d-flex align-items-center gap-2">

                <span class="material-symbols-outlined">
                     +
                 </span>

                    Nova Filial

        </a>

    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <form method="GET">

                <input type="hidden" name="action" value="filiais">

                <div class="row g-4">

                    <!-- ESTADO -->
                    <div class="col-lg-4">

                        <label class="form-label small text-uppercase fw-bold text-secondary">
                            Localização
                        </label>

                        <select
                            name="estado"
                            class="form-select rounded-4 shadow-none"
                            onchange="this.form.submit()"
                        >

                            <option value="">
                                Todos os estados
                            </option>

                            <option
                                value="SP"
                                <?= ($_GET['estado'] ?? '') === 'SP' ? 'selected' : '' ?>
                            >
                                São Paulo
                            </option>

                            <option
                                value="MG"
                                <?= ($_GET['estado'] ?? '') === 'MG' ? 'selected' : '' ?>
                            >
                                Minas Gerais
                            </option>

                            <option
                                value="PR"
                                <?= ($_GET['estado'] ?? '') === 'PR' ? 'selected' : '' ?>
                            >
                                Paraná
                            </option>

                        </select>

                    </div>

                    <!-- STATUS -->
                    <div class="col-lg-4">

                        <label class="form-label small text-uppercase fw-bold text-secondary">
                            Status Operacional
                        </label>

                        <select
                            name="status"
                            class="form-select rounded-4 shadow-none"
                            onchange="this.form.submit()"
                        >

                            <option value="">
                                Todos os Status
                            </option>

                            <option
                                value="Ativa"
                                <?= ($_GET['status'] ?? '') === 'Ativa' ? 'selected' : '' ?>
                            >
                                Ativa
                            </option>

                            <option
                                value="Inativa"
                                <?= ($_GET['status'] ?? '') === 'Inativa' ? 'selected' : '' ?>
                            >
                                Inativa
                            </option>

                            <option
                                value="Manutenção"
                                <?= ($_GET['status'] ?? '') === 'Manutenção' ? 'selected' : '' ?>
                            >
                                Em manutenção
                            </option>

                        </select>

                    </div>

                    <!-- CEP -->
                    <div class="col-lg-4">

                        <label class="form-label small text-uppercase fw-bold text-secondary">
                            Buscar CEP
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-light border-0 rounded-start-4">

                                <span class="material-symbols-outlined">
                                    search
                                </span>

                            </span>

                            <input
                                type="text"
                                id="cep"
                                class="form-control border-0 bg-light rounded-end-4"
                                placeholder="Digite um CEP..."
                            >

                        </div>

                    </div>

                    <!-- BUSCA -->
                    <div class="col-12">

                        <div class="input-group">

                            <span class="input-group-text bg-light border-0">

                                <span class="material-symbols-outlined">
                                    search
                                </span>

                            </span>

                            <input
                                type="text"
                                name="busca"
                                class="form-control border-0 bg-light"
                                placeholder="Buscar filial, cidade ou código..."
                                value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>"
                            >

                            <button class="btn btn-dark px-4">

                                Pesquisar

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- RESULTADO CEP -->
    <div
        class="alert alert-primary rounded-4 shadow-sm d-none"
        id="resultadoCep"
    ></div>

    <!-- TABELA -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th class="py-4 px-4">
                            FILIAL
                        </th>

                        <th class="py-4">
                            LOCALIZAÇÃO
                        </th>

                        <th class="py-4">
                            CÓDIGO
                        </th>

                        <th class="py-4">
                            STATUS
                        </th>

                        <th class="py-4 text-end pe-4">
                            AÇÕES
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!empty($filiais)): ?>

                        <?php foreach ($filiais as $filial): ?>

                            <tr>

                                <td class="px-4 py-4">

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="icone-filial">

                                            <span class="material-symbols-outlined">
                                                location_on
                                            </span>

                                        </div>

                                        <div>

                                            <h6 class="fw-bold mb-1">
                                                <?= htmlspecialchars($filial['nome'] ?? '') ?>
                                            </h6>

                                            <small class="text-secondary">
                                                <?= htmlspecialchars($filial['descricao'] ?? '') ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>

                                <td>

                                    <div class="fw-semibold">

                                        <?= htmlspecialchars($filial['cidade'] ?? '') ?>,
                                        <?= htmlspecialchars($filial['uf'] ?? '') ?>

                                    </div>

                                    <small class="text-secondary">

                                        <?= htmlspecialchars(
                                         ($filial['logradouro'] ?? '') .
                                        ', ' .
                                        ($filial['numero'] ?? '')) ?>

                                    </small>

                                </td>

                                <td>

                                    <span class="codigo-box">

                                        <?= htmlspecialchars($filial['codigo'] ?? '') ?>

                                    </span>

                                </td>

                                <td>

                                <?php if ($filial['status'] === 'Ativa'): ?>

                                    <span class="badge-status status-ativa">
                                        <?= $filial['status'] ?>
                                    </span>

                                <?php else: ?>

                                    <span class="badge-status status-inativa">
                                     <?= $filial['status'] ?>
                                    </span>

                                <?php endif; ?>

                                </td>

                                <td class="text-end pe-4">

                                    <button class="btn btn-light btn-sm rounded-3">

                                        <span class="material-symbols-outlined">
                                            edit
                                        </span>

                                    </button>

                                    <button class="btn btn-light btn-sm rounded-3">

                                        <span class="material-symbols-outlined">
                                            more_vert
                                        </span>

                                    </button>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5" class="text-center py-5 text-secondary">

                                Nenhuma filial encontrada.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- VIA CEP -->
<script>

document.getElementById('cep').addEventListener('keyup', async function () {

    let cep = this.value.replace(/\D/g, '');

    if (cep.length !== 8) {
        return;
    }

    let resultado = document.getElementById('resultadoCep');

    resultado.classList.remove('d-none');

    resultado.innerHTML = 'Buscando CEP...';

    try {

        let response = await fetch(
            `https://viacep.com.br/ws/${cep}/json/`
        );

        let data = await response.json();

        if (data.erro) {

            resultado.classList.remove('alert-primary');
            resultado.classList.add('alert-danger');

            resultado.innerHTML = `
                CEP não encontrado.
            `;

            return;
        }

        resultado.classList.remove('alert-danger');
        resultado.classList.add('alert-primary');

        resultado.innerHTML = `
            <strong>Endereço encontrado:</strong><br>

            ${data.logradouro}<br>
            ${data.bairro}<br>
            ${data.localidade} - ${data.uf}
        `;

    } catch (erro) {

        resultado.classList.remove('alert-primary');
        resultado.classList.add('alert-danger');

        resultado.innerHTML = `
            Erro ao consultar CEP.
        `;

    }

});

</script>

<?php

$conteudo = ob_get_clean();

require_once __DIR__ . '/../layouts/base.php';
?>