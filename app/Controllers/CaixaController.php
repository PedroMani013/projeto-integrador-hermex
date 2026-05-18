<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CaixaRepository;
use App\Repositories\FilialRepository;

class CaixaController
{
    private CaixaRepository $repository;

    public function __construct()
    {
        $this->repository = new CaixaRepository();
    }

    public function index(): void
    {
        $caixas = $this->repository->listar();

        require BASE_PATH . '/app/Views/caixas/index.php';
    }

    public function cadastro(): void
    {
        $filialRepo = new FilialRepository();
        $filiais    = $filialRepo->listar();

        require BASE_PATH . '/app/Views/caixas/cadastro-caixa.php';
    }
}
