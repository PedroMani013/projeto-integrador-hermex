<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CaixaRepository;
use App\Repositories\CategoriaRepository;
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

    public function lacrar(): void
    {
        $id    = $_GET['id'] ?? '';
        $caixa = $this->repository->buscarPorId($id);

        if ($caixa === null || (string) $caixa['estado'] !== 'criada') {
            $_SESSION['erro'] = 'Caixa não encontrada ou não está em estado "criada".';
            header('Location: /?action=caixas');
            exit;
        }

        require BASE_PATH . '/app/Views/caixas/lacrar-caixa.php';
    }

    public function vincularNf(): void
    {
        $id    = $_GET['id'] ?? '';
        $caixa = $this->repository->buscarPorId($id);

        if ($caixa === null || (string) $caixa['estado'] !== 'criada') {
            $_SESSION['erro'] = 'Caixa não encontrada ou não está em estado "criada".';
            header('Location: /?action=caixas');
            exit;
        }

        $categorias = (new CategoriaRepository())->listar();

        require BASE_PATH . '/app/Views/caixas/vincular-nf.php';
    }
}
