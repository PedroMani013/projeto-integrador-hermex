<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\DashboardRepository;

class DashboardController
{
    private DashboardRepository $repo;

    public function __construct()
    {
        $this->repo = new DashboardRepository();
    }

    public function index(): void
    {
        try {
            $pagina    = max(1, (int) ($_GET['pagina'] ?? 1));
            $porPagina = 10;

            $indicadores = [
                'caixasEmTransito'    => $this->repo->contarCaixasEmTransito(),
                'sinaisIsolados24h'   => $this->repo->contarSinaisIsolados24h(),
                'entreguesMes'        => $this->repo->contarCaixasEntreguesMes(),
                'alertasAbertos'      => $this->repo->contarAlertasAbertos(),
            ];

            $integridade14d   = $this->repo->integridade14dias();
            $alertasRecentes  = $this->repo->alertasRecentes(5);
            $paginacaoCaixas  = $this->repo->caixasEmTransitoPaginado($pagina, $porPagina);

            require_once __DIR__ . '/../Views/dashboard/index.php';

        } catch (\Throwable $e) {
            http_response_code(500);
            echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
        }
    }
}
