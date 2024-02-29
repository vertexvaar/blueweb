<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function current;
use function end;
use function key;
use function next;
use function prev;
use function reset;

class MiddlewareChain implements RequestHandlerInterface
{
    /**
     * @param array<class-string<MiddlewareInterface>> $middlewares
     */
    public function __construct(
        protected ContainerInterface $container,
        protected array $middlewares,
        protected readonly RequestHandlerInterface $requestHandler,
    ) {
        reset($this->middlewares);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = current($this->middlewares);

        if (false !== $middleware) {
            next($this->middlewares);
            /** @var MiddlewareInterface $handler */
            $handler = $this->container->get($middleware);
            try {
                return $handler->process($request, $this);
            } finally {
                null === key($this->middlewares) ? end($this->middlewares) : prev($this->middlewares);
            }
        }

        return $this->requestHandler->handle($request);
    }
}
