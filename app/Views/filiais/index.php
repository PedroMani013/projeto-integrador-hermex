<?php

declare(strict_types=1);

$tituloPagina = 'Filiais';

$estilos = [
    '/assets/css/bootstrap.min.css',
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css'
    
];

$scripts = [
    '/assets/js/bootstrap.bundle.min.js'
];

ob_start();
?>

<div class="container-fluid py-4 px-4 filial-page">

    <!-- HEADER -->
    <div class="page-header">

        <div>

            <h1 class="page-title">
                Gestão de Filiais
            </h1>

            <p class="page-subtitle">
                Administre os pontos de coleta e distribuição da malha logística.
            </p>

        </div>

        <a href="<?= BASE_URL ?>?action=cadastro-filial"
           class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">

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

                <input type="hidden"
                       name="action"
                       value="filiais">

                <div class="row g-4">

                    <!-- ESTADO -->
                    <div class="col-lg-6">

                        <label class="form-label small text-uppercase fw-bold text-secondary">
                            Localização
                        </label>

                        <select
                            name="estado"
                            class="form-select rounded-4 shadow-none"
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

                    <!-- CEP -->
                    <div class="col-lg-6">

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

                        <label class="form-label small text-uppercase fw-bold text-secondary">
                            Buscar Filial
                        </label>

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

                            <button class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none">

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
    <div class="tabela-wrapper shadow-sm">

        <table class="hermex-table align-middle">

            <thead>

                <tr>

                    <th>
                        FILIAL
                    </th>

                    <th>
                        LOCALIZAÇÃO
                    </th>

                    <th>
                        CÓDIGO
                    </th>

                    <th class="text-end pe-4">
                        AÇÕES
                    </th>

                </tr>

            </thead>

            <tbody>

                <?php if (!empty($filiais)): ?>

                    <?php foreach ($filiais as $filial): ?>

                        <tr>

                            <!-- FILIAL -->
                            <td>

                                <div class="d-flex align-items-center gap-3">

                                    <div class="icone-filial">

                                    </div>

                                    <div>

                                        <div class="fw-bold">

                                            <?= htmlspecialchars($filial['nome'] ?? '') ?>

                                        </div>

                                        <small class="text-secondary">

                                            <?= htmlspecialchars($filial['descricao'] ?? '') ?>

                                        </small>

                                    </div>

                                </div>

                            </td>

                            <!-- LOCALIZAÇÃO -->
                            <td>

                                <div class="fw-semibold">

                                    <?= htmlspecialchars($filial['cidade'] ?? '') ?>,
                                    <?= htmlspecialchars($filial['uf'] ?? '') ?>

                                </div>

                                <small class="text-secondary">

                                    <?= htmlspecialchars(
                                        ($filial['logradouro'] ?? '')
                                        . ', ' .
                                        ($filial['numero'] ?? '')
                                    ) ?>

                                </small>

                            </td>

                            <!-- CÓDIGO -->
                            <td>

                                <span class="badge-status">

                                    <?= htmlspecialchars($filial['codigo'] ?? '') ?>

                                </span>

                            </td>

                            <!-- AÇÕES -->
                            <td class="text-end pe-4">

                                <div class="d-flex justify-content-end gap-2">

                                    <!-- EDITAR -->
                                    <a
                                        href="/?action=editar-filial&id=<?= urlencode((string)($filial['_id'] ?? $filial['id'] ?? '')) ?>"
                                        class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none"
                                        title="Editar filial"
                                    >

                                        <span class="material-symbols-outlined">
                                            Editar
                                        </span>

                                    </a>

                                    <!-- EXCLUIR -->
                                    <form
                                        method="POST"
                                        action="/?action=excluir-filial"
                                        onsubmit="return confirm('Deseja excluir esta filial?')"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= htmlspecialchars((string)($filial['_id'] ?? $filial['id'] ?? '')) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-hermex-primary d-flex align-items-center gap-2 text-decoration-none"
                                            style="background:#dc3545;border-color:#dc3545;color:#fff;"
                                            title="Excluir filial"
                                        >

                                            <span class="material-symbols-outlined">
                                                Excluir
                                            </span>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="4"
                            class="text-center py-5 text-secondary">

                            Nenhuma filial encontrada.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

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