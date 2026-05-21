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

use App\Controllers\CaixaController;
use App\Controllers\CategoriaController;
use App\Controllers\DashboardController;
use App\Controllers\EventoController;
use App\Controllers\FilialController;
use App\Controllers\RelatorioController;

use App\Repositories\CaixaRepository;
use App\Repositories\CategoriaRepository;
use App\Repositories\FilialRepository;

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
        | CAIXAS
        |--------------------------------------------------------------------------
        */
        'caixas' =>
            (new CaixaController())->index(),

        'cadastro-caixa' =>
            (new CaixaController())->cadastro(),

        'salvar-caixa' =>
            salvarCaixa(),

        'recepcao-nfc' =>
            require BASE_PATH . '/app/Views/recepcao/nfc.php',

        'detalhe-caixa' =>
            (new CaixaController())->detalhe(),

        'reconhecer-alerta' =>
            reconhecerAlerta(),

        'lacrar-caixa' =>
            (new CaixaController())->lacrar(),

        'salvar-lacre' =>
            salvarLacre(),

        'vincular-nf' =>
            (new CaixaController())->vincularNf(),

        'salvar-nf' =>
            salvarNf(),

        /*
        |--------------------------------------------------------------------------
        | API — EVENTOS (H04/H05/H06)
        |--------------------------------------------------------------------------
        */
        'api-evento' =>
            (new EventoController())->receberEvento(),

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
        | CATEGORIAS
        |--------------------------------------------------------------------------
        */
        'categorias' =>
            (new CategoriaController())->index(),

        'cadastro-categoria' =>
            require BASE_PATH . '/app/Views/categorias/cadastro-categoria.php',

        'salvar-categoria' =>
            salvarCategoria(),

        /*
        |--------------------------------------------------------------------------
        | RELATÓRIOS
        |--------------------------------------------------------------------------
        */
        'relatorios' =>
            (new RelatorioController())->index(),

        'relatorio-custodia' =>
            (new RelatorioController())->custodia(),

        /*
        |--------------------------------------------------------------------------
        | EXPORTAR PDF
        |--------------------------------------------------------------------------
        */
        'exportar-relatorio' =>
            (new RelatorioController())->exportarPdf(),

        'exportar-custodia' =>
            (new RelatorioController())->exportarCustodiaPdf(),

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
| SALVAR CAIXA
|--------------------------------------------------------------------------
*/
function salvarCaixa(): void
{
    try {
        $repository = new CaixaRepository();
        $repository->salvar($_POST);

        $_SESSION['sucesso'] = 'Caixa cadastrada com sucesso!';
        header('Location: /?action=caixas');

    } catch (\InvalidArgumentException $e) {
        $_SESSION['erro'] = $e->getMessage();
        header('Location: /?action=cadastro-caixa');

    } catch (\Throwable $e) {
        $_SESSION['erro'] = 'Erro ao salvar caixa. Tente novamente.';
        header('Location: /?action=cadastro-caixa');
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| RECONHECER ALERTA (H25)
|--------------------------------------------------------------------------
*/
function reconhecerAlerta(): void
{
    $caixaId = $_POST['caixa_id'] ?? '';

    try {
        $repository = new CaixaRepository();
        $caixa = $repository->buscarPorId($caixaId);

        if ($caixa === null || (string) $caixa['estado'] !== 'violada') {
            throw new \InvalidArgumentException('Caixa não encontrada ou não está em estado "violada".');
        }

        $repository->atualizar($caixaId, ['estado' => 'em_transito']);

        $_SESSION['sucesso'] = 'Alerta reconhecido. Caixa retornou para em trânsito.';
        header('Location: /?action=detalhe-caixa&id=' . urlencode($caixaId));

    } catch (\InvalidArgumentException $e) {
        $_SESSION['erro'] = $e->getMessage();
        header('Location: /?action=detalhe-caixa&id=' . urlencode($caixaId));

    } catch (\Throwable $e) {
        $_SESSION['erro'] = 'Erro ao reconhecer alerta.';
        header('Location: /?action=detalhe-caixa&id=' . urlencode($caixaId));
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| SALVAR LACRE
|--------------------------------------------------------------------------
*/
function salvarLacre(): void
{
    $caixaId = $_POST['caixa_id'] ?? '';

    try {
        $repository = new CaixaRepository();
        $repository->lacrar($caixaId, $_POST);

        $_SESSION['sucesso'] = 'Caixa lacrada com sucesso!';
        header('Location: /?action=caixas');

    } catch (\InvalidArgumentException $e) {
        $_SESSION['erro'] = $e->getMessage();
        header('Location: /?action=lacrar-caixa&id=' . urlencode($caixaId));

    } catch (\Throwable $e) {
        $_SESSION['erro'] = 'Erro ao lacrar caixa. Tente novamente.';
        header('Location: /?action=lacrar-caixa&id=' . urlencode($caixaId));
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| SALVAR NF
|--------------------------------------------------------------------------
*/
function salvarNf(): void
{
    $caixaId = $_POST['caixa_id'] ?? '';

    try {
        $repository = new CaixaRepository();
        $repository->adicionarNf($caixaId, $_POST);

        $_SESSION['sucesso'] = 'Nota fiscal vinculada com sucesso!';
        header('Location: /?action=caixas');

    } catch (\InvalidArgumentException $e) {
        $_SESSION['erro'] = $e->getMessage();
        header('Location: /?action=vincular-nf&id=' . urlencode($caixaId));

    } catch (\Throwable $e) {
        $_SESSION['erro'] = 'Erro ao vincular NF. Tente novamente.';
        header('Location: /?action=vincular-nf&id=' . urlencode($caixaId));
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| SALVAR CATEGORIA
|--------------------------------------------------------------------------
*/
function salvarCategoria(): void
{
    try {
        $repository = new CategoriaRepository();
        $repository->salvar($_POST);

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