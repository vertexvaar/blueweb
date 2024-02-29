<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use VerteXVaaR\BlueWeb\Middleware\MiddlewareChain;

readonly class Application
{
    public function __construct(private MiddlewareChain $middlewareChain)
    {
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        return $this->middlewareChain->handle($request);
    }
}
