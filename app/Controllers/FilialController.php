<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\FilialRepository;

class FilialController
{
    private FilialRepository $repository;

    public function __construct()
    {
        $this->repository = new FilialRepository();
    }

    public function index(): void
    {
        $estado = $_GET['estado'] ?? null;
        $status = $_GET['status'] ?? null;
        $busca  = $_GET['busca'] ?? null;

        $filiais = $this->repository->listar(
            $estado,
            $status,
            $busca
        );

        require BASE_PATH . '/app/Views/filiais/index.php';
    }
}