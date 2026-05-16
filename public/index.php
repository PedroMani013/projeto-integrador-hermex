<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

define(
    'BASE_URL',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
        ? 'https'
        : 'http')
    . '://'
    . $_SERVER['HTTP_HOST']
    . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')
    . '/'
);

session_start();
$totalMovimentacoes = $totalMovimentacoes ?? 0;

$alertas = $alertas ?? 0;

$integridade = $integridade ?? 0;

$relatorios = $relatorios ?? [];

require_once BASE_PATH . '/vendor/autoload.php';

use App\Controllers\DashboardController;
use App\Controllers\FilialController;
use App\Controllers\ProdutoController;
use App\Controllers\RelatorioController;

use App\Repositories\ProdutoRepository;

$action = $_GET['action'] ?? 'dashboard';

try {

    match ($action) {

<<<<<<< HEAD
        // dashboard
        'dashboard', '' =>
            (new DashboardController())->index(),

        // filiais
        'filiais', '' =>
            (new FilialController())->index(),

        // produtos
        'produtos' =>
            (new ProdutoController())->index(),

        // cadastro produto
        'cadastro-produto' =>
            require BASE_PATH . '/app/Views/produtos/cadastro-produto.php',

        // salvar produto
        'salvar-produto' =>
            salvarProduto(),

        // excluir produto
        'excluir-produto' =>
            excluirProduto(),

        // 404
=======
        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */
        'dashboard', '' =>
            (new DashboardController())->index(),

        /*
        |--------------------------------------------------------------------------
        | FILIAIS
        |--------------------------------------------------------------------------
        */
        'filiais' =>
            (new FilialController())->index(),

        /*
        |--------------------------------------------------------------------------
        | CADASTRO FILIAL
        |--------------------------------------------------------------------------
        */
        'cadastro-filial' =>
            require BASE_PATH . '/app/Views/filiais/cadastro-filial.php',

        /*
        |--------------------------------------------------------------------------
        | SALVAR FILIAL
        |--------------------------------------------------------------------------
        */
        'salvar-filial' =>
            salvarFilial(),

        /*
        |--------------------------------------------------------------------------
        | PRODUTOS
        |--------------------------------------------------------------------------
        */
        'produtos' =>
            (new ProdutoController())->index(),

        /*
        |--------------------------------------------------------------------------
        | CADASTRO PRODUTO
        |--------------------------------------------------------------------------
        */
        'cadastro-produto' =>
            require BASE_PATH . '/app/Views/produtos/cadastro-produto.php',

        /*
        |--------------------------------------------------------------------------
        | SALVAR PRODUTO
        |--------------------------------------------------------------------------
        */
        'salvar-produto' =>
            salvarProduto(),

        /*
        |--------------------------------------------------------------------------
        | EXCLUIR PRODUTO
        |--------------------------------------------------------------------------
        */
        'excluir-produto' =>
            excluirProduto(),

        /*
        |--------------------------------------------------------------------------
        | RELATÓRIOS
        |--------------------------------------------------------------------------
        */
        'relatorios' =>
            (new RelatorioController())->index(),

        /*
        |--------------------------------------------------------------------------
        | EXPORTAR PDF RELATÓRIO
        |--------------------------------------------------------------------------
        */
        'exportar-relatorio' =>
            (new RelatorioController())->exportarPdf(),

        /*
        |--------------------------------------------------------------------------
        | 404
        |--------------------------------------------------------------------------
        */
>>>>>>> 8bbfce1 (Adiciona novas rotas para gerenciamento de filiais e produtos, incluindo salvar e excluir funcionalidades)
        default =>
            call404(),
    };

} catch (\Throwable $e) {

    http_response_code(500);

    echo '<pre>';

    print_r([
        'mensagem' => $e->getMessage(),
        'arquivo'  => $e->getFile(),
        'linha'    => $e->getLine(),
        'trace'    => $e->getTrace(),
    ]);

    echo '</pre>';
<<<<<<< HEAD
}

function salvarProduto(): void
{
    $repository = new ProdutoRepository();

    $repository->salvar($_POST);

    header('Location: /?action=produtos');

    exit;
}

function excluirProduto(): void
{
    $id = (int)($_POST['id'] ?? 0);

    $repository = new ProdutoRepository();

    $repository->excluir($id);

    header('Location: /?action=produtos');

    exit;
=======
>>>>>>> 8bbfce1 (Adiciona novas rotas para gerenciamento de filiais e produtos, incluindo salvar e excluir funcionalidades)
}

/*
|--------------------------------------------------------------------------
| SALVAR PRODUTO
|--------------------------------------------------------------------------
*/
function salvarProduto(): void
{
    $repository = new ProdutoRepository();

    $repository->salvar($_POST);

    header('Location: /?action=produtos');

    exit;
}

/*
|--------------------------------------------------------------------------
| EXCLUIR PRODUTO
|--------------------------------------------------------------------------
*/
function excluirProduto(): void
{
    $id = (int) ($_POST['id'] ?? 0);

    $repository = new ProdutoRepository();

    $repository->excluir($id);

    header('Location: /?action=produtos');

    exit;
}

/*
|--------------------------------------------------------------------------
| SALVAR FILIAL
|--------------------------------------------------------------------------
*/
function salvarFilial(): void
{
    $_SESSION['sucesso'] = 'Filial cadastrada com sucesso!';

    header('Location: /?action=filiais');

    exit;
}

/*
|--------------------------------------------------------------------------
| 404
|--------------------------------------------------------------------------
*/
function call404(): void
{
    http_response_code(404);

    require_once BASE_PATH . '/app/Views/erros/404.php';
}