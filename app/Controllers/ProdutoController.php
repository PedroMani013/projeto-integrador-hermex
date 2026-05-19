<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ProdutoRepository;

class ProdutoController
{
    private ProdutoRepository $repository;

    public function __construct()
    {
        $this->repository = new ProdutoRepository();
    }

    public function index(): void
    {
        $produtos = $this->repository->listar();

        $indicadores = [

            'totalSkus' => count($produtos),

            'insumosMedicos' => count(
                array_filter(
                    $produtos,
                    fn ($p) =>
                        strtolower($p['categoria'] ?? '') === 'médico'
                )
            ),

            'toleranciaCritica' => count(
                array_filter(
                    $produtos,
                    fn ($p) =>
                        (float)($p['toleranciaPeso'] ?? 0) <= 1
                )
            ),

            'tagsNfc' => count(
                array_filter(
                    $produtos,
                    fn ($p) =>
                        !empty($p['codigoNfc'])
                )
            ),
        ];

        $paginacao = [
            'pagina' => 1,
            'porPagina' => 10,
            'total' => count($produtos),
        ];

        require BASE_PATH . '/app/Views/produtos/index.php';
    }
}