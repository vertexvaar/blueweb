<?php

namespace PHPSTORM_META {

    override(
        \Psr\Http\Message\ServerRequestInterface::getAttribute(),
        map([
            'route' => \VerteXVaaR\BlueWeb\Routing\RouteEncapsulation::class,
        ]),
    );
}
