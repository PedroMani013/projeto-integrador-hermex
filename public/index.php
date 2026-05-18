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

require_once BASE_PATH . '/vendor/autoload.php';

use App\Controllers\DashboardController;
use App\Controllers\FilialController;
use App\Controllers\ProdutoController;
use App\Controllers\RelatorioController;

use App\Repositories\FilialRepository;
use App\Repositories\ProdutoRepository;

$action = $_GET['action'] ?? 'dashboard';

try {

    match ($action) {

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
        | EXPORTAR PDF
        |--------------------------------------------------------------------------
        */
        'exportar-relatorio' =>
            (new RelatorioController())->exportarPdf(),

        /*
        |--------------------------------------------------------------------------
        | 404
        |--------------------------------------------------------------------------
        */
        default =>
            call404(),
    };

} catch (\Throwable $e) {

    http_response_code(500);

    echo '<pre>';

    print_r($e);

    echo '</pre>';
}

/*
|--------------------------------------------------------------------------
| SALVAR PRODUTO
|--------------------------------------------------------------------------
*/
function salvarProduto(): void
{
    try {

        $repository = new ProdutoRepository();

        $repository->salvar($_POST);

        header('Location: /?action=produtos');

    } catch (\RuntimeException $e) {

        $_SESSION['erro'] = $e->getMessage();

        header('Location: /?action=cadastro-produto');
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| EXCLUIR PRODUTO
|--------------------------------------------------------------------------
*/
function excluirProduto(): void
{
    $id = trim($_POST['id'] ?? '');

    if ($id !== '') {
        $repository = new ProdutoRepository();
        $repository->excluir($id);
    }

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
    try {

        $repository = new FilialRepository();

        $repository->salvar($_POST);

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