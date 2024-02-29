<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\Routing\Middleware;

use Exception;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VerteXVaaR\BlueWeb\Routing\RouteEncapsulation;

class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly array $data)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        $dispatcher = new GroupCountBased($this->data);
        $routingResult = $dispatcher->dispatch($method, $path);
        $matchedRoute = match ($routingResult[0]) {
            Dispatcher::FOUND => $routingResult[1],
            Dispatcher::NOT_FOUND => throw new Exception(
                'Could not resolve a route for path "' . $path . '"',
                1431887428,
            ),
            Dispatcher::METHOD_NOT_ALLOWED => throw new Exception(
                'Method not allowed for route "' . $path . '"',
                1699381583,
            ),
        };
        $routeEncapsulation = new RouteEncapsulation(
            new ($matchedRoute['class'])(...$matchedRoute['vars']),
            $matchedRoute['controller'],
            $matchedRoute['action'],
            $routingResult[2],
        );
        $request = $request->withAttribute('route', $routeEncapsulation);
        return $handler->handle($request);
    }
}
