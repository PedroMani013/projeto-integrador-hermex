<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AlertaRepository;
use App\Repositories\FilialRepository;

class AlertaController
{
    private AlertaRepository $repository;

    public function __construct()
    {
        $this->repository = new AlertaRepository();
    }

    public function index(): void
    {
        $filtros = [
            'filial_origem'   => $_GET['filial_origem']   ?? '',
            'filial_destino'  => $_GET['filial_destino']  ?? '',
            'transportadora'  => $_GET['transportadora']  ?? '',
            'reconhecido'     => $_GET['reconhecido']     ?? '',
            'data_inicio'     => $_GET['data_inicio']     ?? '',
            'data_fim'        => $_GET['data_fim']        ?? '',
        ];

        $alertas        = $this->repository->listar($filtros);
        $totalAbertos   = $this->repository->contarAbertos();
        $reconhecidosHoje = $this->repository->contarReconhecidosHoje();
        $tempoMedio     = $this->repository->tempoMedioReconhecimentoMin();

        $filialRepo     = new FilialRepository();
        $filiais        = $filialRepo->listar();
        $transportadoras = $this->repository->transportadorasUnicas();

        require BASE_PATH . '/app/Views/alertas/index.php';
    }
}
