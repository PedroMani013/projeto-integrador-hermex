<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CategoriaRepository;

class CategoriaController
{
    private CategoriaRepository $repository;

    public function __construct()
    {
        $this->repository = new CategoriaRepository();
    }

    public function index(): void
    {
        $categorias = $this->repository->listar();

        require BASE_PATH . '/app/Views/categorias/index.php';
    }
}
