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
        $busca  = $_GET['busca'] ?? null;

        $filiais = $this->repository->listar(
            $estado,
            $busca
        );

        require BASE_PATH . '/app/Views/filiais/index.php';
    }

    public function salvar(): void
    {
        try {
            $this->repository->salvar($_POST);

            $_SESSION['sucesso'] = 'Filial cadastrada com sucesso!';
            header('Location: /?action=filiais');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: /?action=cadastro-filial');

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao salvar filial. Tente novamente.';
            header('Location: /?action=cadastro-filial');
        }

        exit;
    }
}