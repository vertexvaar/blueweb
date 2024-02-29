<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Dotenv\Dotenv;
use VerteXVaaR\BlueContainer\Generated\DI;
use VerteXVaaR\BlueWeb\Application;
use VerteXVaaR\BlueWeb\ErrorHandler\ErrorHandler;
use VerteXVaaR\BlueWeb\ResponseEmitter;

$root = getenv('VXVR_BS_ROOT');
if (!$root) {
    $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
    putenv('VXVR_BS_ROOT=' . $root);
}

if (file_exists($root . '/.env')) {
    $dotenv = new Dotenv();
    $dotenv->usePutenv();
    $dotenv->loadEnv($root . '/.env', null, 'dev', [], true);
}

if (empty(ini_get('date.timezone'))) {
    date_default_timezone_set('UTC');
}

$errorHandler = new ErrorHandler();
$errorHandler->register();

$request = ServerRequest::fromGlobals();

$di = new DI();
$di->set(ServerRequestInterface::class, $request);

$response = $di->get(Application::class)->run($request);
$di->get(ResponseEmitter::class)->emit($response);
