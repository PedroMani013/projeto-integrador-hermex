<?php

namespace App\Controllers;

use App\Repositories\FilialRepository;

class FilialController
{
    public function index(): void
    {
        $repository = new FilialRepository();

        $estado = $_GET['estado'] ?? null;
        $status = $_GET['status'] ?? null;
        $busca  = $_GET['busca'] ?? null;

        $filiais = $repository->listar(
            $estado,
            $status,
            $busca
        );

        require BASE_PATH . '/app/Views/filiais/index.php';
    }
}