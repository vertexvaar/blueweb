<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\RequestHandler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VerteXVaaR\BlueWeb\Controller\Controller;

readonly class ControllerDispatcher implements RequestHandlerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $request->getAttribute('route');

        /** @var Controller $controller */
        $controller = $this->container->get($route->controller);
        return $controller->{$route->action}($request);
    }
}
