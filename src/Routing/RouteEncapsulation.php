<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\Routing;

use VerteXVaaR\BlueWeb\Routing\Attributes\Route;

readonly class RouteEncapsulation
{
    public function __construct(
        public Route $route,
        public string $controller,
        public string $action,
        public array $matches,
    ) {
    }
}
