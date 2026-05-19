<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\RelatorioRepository;
use Mpdf\Mpdf;

class RelatorioController
{
    public function index(): void
    {
        $repository = new RelatorioRepository();

        $periodo = $_GET['periodo'] ?? 'semana';

        $dataInicio = $_GET['data_inicio'] ?? null;

        $dataFim = $_GET['data_fim'] ?? null;

        $relatorios = $repository->buscarPorPeriodo(
            $periodo,
            $dataInicio,
            $dataFim
        );

        /*
        |--------------------------------------------------------------------------
        | CARDS
        |--------------------------------------------------------------------------
        */
        $totalMovimentacoes = count($relatorios);

        $alertas = 0;

        foreach ($relatorios as $relatorio) {

            if (
                ($relatorio['estado'] ?? '')
                !== 'em_transito'
            ) {

                $alertas++;
            }
        }

        $integridade = 100;

        if ($totalMovimentacoes > 0) {

            $integridade = round(
                (
                    ($totalMovimentacoes - $alertas)
                    / $totalMovimentacoes
                ) * 100
            );
        }

        require BASE_PATH
            . '/app/Views/relatorios/index.php';
    }

    public function exportarPdf(): void
    {
        $repository = new RelatorioRepository();

        $periodo = $_GET['periodo'] ?? 'semana';

        $dataInicio = $_GET['data_inicio'] ?? null;

        $dataFim = $_GET['data_fim'] ?? null;

        $relatorios = $repository->buscarPorPeriodo(
            $periodo,
            $dataInicio,
            $dataFim
        );

        ob_start();

        require BASE_PATH
            . '/app/Views/relatorios/pdf.php';

        $html = ob_get_clean();

        $mpdf = new Mpdf();

        $mpdf->WriteHTML($html);

        $mpdf->Output(
            'relatorio-hermex.pdf',
            'I'
        );
    }
}