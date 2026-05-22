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

use App\Controllers\AlertaController;
use App\Controllers\CaixaController;
use App\Controllers\CategoriaController;
use App\Controllers\DashboardController;
use App\Controllers\EventoController;
use App\Controllers\FilialController;
use App\Controllers\RelatorioController;

$action = $_GET['action'] ?? 'dashboard';

try {

    match ($action) {

        'dashboard', '' =>
            (new DashboardController())->index(),

        'caixas' =>
            (new CaixaController())->index(),

        'cadastro-caixa' =>
            (new CaixaController())->cadastro(),

        'salvar-caixa' =>
            (new CaixaController())->salvar(),

        'recepcao-nfc' =>
            require BASE_PATH . '/app/Views/recepcao/nfc.php',

        'detalhe-caixa' =>
            (new CaixaController())->detalhe(),

        'reconhecer-alerta' =>
            (new CaixaController())->reconhecerAlerta(),

        'lacrar-caixa' =>
            (new CaixaController())->lacrar(),

        'salvar-lacre' =>
            (new CaixaController())->confirmarLacre(),

        'despachar-caixa' =>
            (new CaixaController())->despachar(),

        'vincular-nf' =>
            (new CaixaController())->vincularNf(),

        'salvar-nf' =>
            (new CaixaController())->confirmarNf(),

        'alertas' =>
            (new AlertaController())->index(),

        'api-evento' =>
            (new EventoController())->receberEvento(),

        'filiais' =>
            (new FilialController())->index(),

        'cadastro-filial' =>
            require BASE_PATH . '/app/Views/filiais/cadastro-filial.php',

        'salvar-filial' =>
            (new FilialController())->salvar(),

        'editar-filial' =>
            (new FilialController())->editar(),

        'atualizar-filial' =>
            (new FilialController())->salvarEdicao(),

        'desativar-filial' =>
            (new FilialController())->desativar(),

        'categorias' =>
            (new CategoriaController())->index(),

        'cadastro-categoria' =>
            require BASE_PATH . '/app/Views/categorias/cadastro-categoria.php',

        'salvar-categoria' =>
            (new CategoriaController())->salvar(),

        'relatorios' =>
            (new RelatorioController())->index(),

        'relatorio-custodia' =>
            (new RelatorioController())->custodia(),

        'exportar-relatorio' =>
            (new RelatorioController())->exportarPdf(),

        'exportar-custodia' =>
            (new RelatorioController())->exportarCustodiaPdf(),

        default =>
            (function () {
                http_response_code(404);
                require_once BASE_PATH . '/app/Views/erros/404.php';
            })(),
    };

} catch (\Throwable $e) {

    http_response_code(500);

    echo '<pre>';

    print_r($e);

    echo '</pre>';
}
