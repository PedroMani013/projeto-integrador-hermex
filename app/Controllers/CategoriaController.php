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

    public function salvar(): void
    {
        try {
            $this->repository->salvar($_POST);

            $_SESSION['sucesso'] = 'Categoria cadastrada com sucesso!';
            header('Location: /?action=categorias');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: /?action=cadastro-categoria');

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao salvar categoria. Tente novamente.';
            header('Location: /?action=cadastro-categoria');
        }

        exit;
    }
}
