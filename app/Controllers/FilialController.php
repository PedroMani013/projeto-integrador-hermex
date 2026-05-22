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

    public function editar(): void
    {
        $id     = $_GET['id'] ?? '';
        $filial = $this->repository->buscarPorId($id);

        if ($filial === null) {
            $_SESSION['erro'] = 'Filial não encontrada.';
            header('Location: /?action=filiais');
            exit;
        }

        require BASE_PATH . '/app/Views/filiais/editar-filial.php';
    }

    public function salvarEdicao(): void
    {
        $id = $_POST['id'] ?? '';

        try {
            $this->repository->atualizar($id, $_POST);

            $_SESSION['sucesso'] = 'Filial atualizada com sucesso!';
            header('Location: /?action=filiais');

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: /?action=editar-filial&id=' . urlencode($id));

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao atualizar filial. Tente novamente.';
            header('Location: /?action=editar-filial&id=' . urlencode($id));
        }

        exit;
    }

    public function desativar(): void
    {
        $id = $_POST['id'] ?? '';

        try {
            $this->repository->desativar($id);

            $_SESSION['sucesso'] = 'Filial desativada com sucesso.';

        } catch (\InvalidArgumentException $e) {
            $_SESSION['erro'] = $e->getMessage();

        } catch (\Throwable $e) {
            $_SESSION['erro'] = 'Erro ao desativar filial. Tente novamente.';
        }

        header('Location: /?action=filiais');
        exit;
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