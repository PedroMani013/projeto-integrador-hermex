<?php

declare(strict_types=1);

$tituloPagina = 'Simulador';

$estilos = [
    '/assets/css/bootstrap.min.css',
    '/assets/css/hermex_pages.css',
    '/assets/css/dashboard.css',
];

$scripts = [
    '/assets/js/bootstrap.bundle.min.js',
];

ob_start();
?>

<div class="container-fluid py-4 px-4">

    <!-- HEADER -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Simulador</h1>
        </div>
    </div>

    <!-- FEEDBACK GLOBAL -->
    <div id="sim-feedback" class="alert d-none rounded-4 mb-4" role="alert" aria-live="polite"></div>

    <!-- TABELA DE CAIXAS SIMULÁVEIS -->
    <div class="tabela-wrapper shadow-sm">
        <table class="hermex-table align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Estado atual</th>
                    <th>Rota</th>
                    <th>Peso baseline</th>
                    <th class="text-end pe-4">Ações de simulação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($caixasSimulaveis)): ?>
                    <?php foreach ($caixasSimulaveis as $cx): ?>
                        <?php
                            $cxId      = (string) $cx['_id'];
                            $estado    = (string) ($cx['estado'] ?? '');
                            $baseline  = (int) ($cx['peso_baseline'] ?? 0);
                            $isDemo    = str_starts_with((string) ($cx['codigo'] ?? ''), 'DEMO-');

                            $badgeClass = match($estado) {
                                'lacrada'     => 'bg-secondary',
                                'em_transito' => 'bg-primary',
                                'violada'     => 'bg-danger',
                                default       => 'bg-light text-dark',
                            };
                        ?>
                        <tr <?= $isDemo ? 'class="table-warning"' : '' ?>>
                            <td>
                                <div class="fw-bold <?= $isDemo ? 'text-warning-emphasis' : '' ?>">
                                    <?= htmlspecialchars((string) ($cx['codigo'] ?? '')) ?>
                                    <?php if ($isDemo): ?>
                                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">DEMO</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-secondary"><?= htmlspecialchars((string) ($cx['transportadora'] ?? '')) ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?> rounded-pill">
                                    <?= htmlspecialchars($estado) ?>
                                </span>
                            </td>
                            <td>
                                <small>
                                    <?= htmlspecialchars((string) ($cx['filial_origem_nome'] ?? $cx['filial_origem_codigo'] ?? '')) ?>
                                    &rarr;
                                    <?= htmlspecialchars((string) ($cx['filial_destino_nome'] ?? $cx['filial_destino_codigo'] ?? '')) ?>
                                </small>
                            </td>
                            <td>
                                <small><?= number_format($baseline / 1000, 3, ',', '.') ?> kg</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">

                                    <?php if ($estado === 'lacrada'): ?>
                                        <button class="btn-sim btn-hermex-secondary"
                                                data-acao="despachar"
                                                data-id="<?= htmlspecialchars($cxId) ?>"
                                                data-label="Despachar">
                                            Despachar
                                        </button>
                                    <?php endif; ?>

                                    <?php if (in_array($estado, ['lacrada', 'em_transito'], true)): ?>
                                        <button class="btn-sim btn-hermex-secondary"
                                                data-acao="peso_normal"
                                                data-id="<?= htmlspecialchars($cxId) ?>"
                                                data-label="Leitura normal"
                                                style="color:#198754;border-color:#a3cfbb;">
                                            Leitura normal
                                        </button>
                                        <button class="btn-sim btn-hermex-secondary"
                                                data-acao="peso_anomalo"
                                                data-id="<?= htmlspecialchars($cxId) ?>"
                                                data-label="Peso anômalo"
                                                style="color:#dc3545;border-color:#fca5a5;">
                                            Peso anômalo
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($estado === 'em_transito'): ?>
                                        <button class="btn-sim btn-hermex-secondary"
                                                data-acao="abertura_tampa"
                                                data-id="<?= htmlspecialchars($cxId) ?>"
                                                data-label="Abrir tampa"
                                                style="color:#dc3545;border-color:#fca5a5;">
                                            Abrir tampa
                                        </button>
                                        <button class="btn-sim btn-hermex-secondary"
                                                data-acao="entrega_nfc"
                                                data-id="<?= htmlspecialchars($cxId) ?>"
                                                data-label="Entrega NFC"
                                                style="color:#0d6efd;border-color:#9ec5fe;">
                                            Entrega NFC
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($estado === 'violada'): ?>
                                        <a href="/?action=detalhe-caixa&id=<?= urlencode($cxId) ?>"
                                           class="btn-hermex-secondary d-inline-flex align-items-center gap-1 text-decoration-none"
                                           style="color:#dc3545;border-color:#fca5a5;">
                                            Ver alerta
                                        </a>
                                    <?php endif; ?>

                                    <a href="/?action=detalhe-caixa&id=<?= urlencode($cxId) ?>"
                                       class="btn-hermex-secondary d-inline-flex align-items-center gap-1 text-decoration-none">
                                        Detalhe
                                    </a>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-secondary">
                            Nenhuma caixa simulável. Execute <code>php scripts/seed.php</code> para popular o banco.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
(function () {
    const feedback = document.getElementById('sim-feedback');

    function mostrarFeedback(ok, mensagem) {
        feedback.className = 'alert rounded-4 mb-4 ' + (ok ? 'alert-success' : 'alert-danger');
        feedback.textContent = mensagem;
        feedback.classList.remove('d-none');
        feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(() => feedback.classList.add('d-none'), 6000);
    }

    document.querySelectorAll('.btn-sim').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const acao  = this.dataset.acao;
            const id    = this.dataset.id;
            const label = this.dataset.label;

            btn.disabled = true;
            btn.textContent = '...';

            const body = new URLSearchParams({ acao, caixa_id: id });

            fetch('/?action=simular-evento', { method: 'POST', body })
                .then(r => r.json())
                .then(data => {
                    mostrarFeedback(data.ok, data.ok ? data.mensagem : ('Erro: ' + data.erro));
                    if (data.ok) {
                        // recarrega para atualizar estados
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        btn.disabled = false;
                        btn.textContent = label;
                    }
                })
                .catch(() => {
                    mostrarFeedback(false, 'Erro de comunicação com o servidor.');
                    btn.disabled = false;
                    btn.textContent = label;
                });
        });
    });
})();
</script>

<?php
$conteudo = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
