<?php

declare(strict_types=1);

/**
 * front controller ponto de entrada único da aplicação
 * rota via $_GET['action']. Sem action = dashboard (rota raiz).
 */

define('BASE_PATH', dirname(__DIR__));

// BASE_URL detectado automaticamente para suportar subpastas (ex: localhost/projeto/public/)
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');

require_once BASE_PATH . '/vendor/autoload.php';

use App\Controllers\DashboardController;

$action = $_GET['action'] ?? 'dashboard';

try {
    match($action) {
        'dashboard', '' => (new DashboardController())->index(),
        default         => call404(),
    };
} catch (\Throwable $e) {
    http_response_code(500);
    require_once BASE_PATH . '/app/Views/erros/500.php';
}

function call404(): void
{
    http_response_code(404);
    require_once BASE_PATH . '/app/Views/erros/404.php';
}
