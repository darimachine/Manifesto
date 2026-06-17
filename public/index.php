<?php

declare(strict_types=1);

define('MANIFESTO_ROOT', dirname(__DIR__));

if (is_file(MANIFESTO_ROOT . '/vendor/autoload.php')) {
    require MANIFESTO_ROOT . '/vendor/autoload.php';
} else {
    spl_autoload_register(static function (string $class): void {
        if (str_starts_with($class, 'Manifesto\\')) {
            $file = MANIFESTO_ROOT . '/src/' . str_replace('\\', '/', substr($class, 10)) . '.php';
            if (is_file($file)) {
                require $file;
            }
        }
    });
    require MANIFESTO_ROOT . '/src/Core/helpers.php';
}

use Manifesto\Core\Csrf;
use Manifesto\Core\Database;
use Manifesto\Core\EnvLoader;
use Manifesto\Core\Request;
use Manifesto\Core\Response;
use Manifesto\Core\Router;
use Manifesto\Core\Session;


EnvLoader::load(MANIFESTO_ROOT . '/.env');
$config = require MANIFESTO_ROOT . '/config/config.php';
Database::configure($config['db']);

ini_set('log_errors', '1');
ini_set('error_log', $config['paths']['logs'] . '/error.log');
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $e) use ($config): void {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if ($config['app']['debug']) {
        http_response_code(500);
        echo '<h1>500</h1><pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
        exit;
    }
    Response::abort(500);
});


Session::start();
$request = Request::capture();


if ($request->isPost() && !Csrf::verify($request->input('_csrf_token'))) {
    Response::abort(419, 'Invalid or expired CSRF token.');
}


$router = new Router(require MANIFESTO_ROOT . '/config/routes.php');
$router->dispatch($request);
