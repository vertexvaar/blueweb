# VerteXVaaR.Bluesprints - Routing

## Abstract

Routing is an essential part of nearly any framework. Routing describes the concept to connect a certain URL or pattern
of URL to a predefined controller and action.

Routes in vertexvaar/blueweb are connected to controller actions via attributes. One action can have multiple routes.

See following example, which defines a catch-all route to the DefaultController::index action.

```php
<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\Controller;

use Psr\Http\Message\ResponseInterface;use VerteXVaaR\BlueWeb\Routing\Attributes\Route;

class DefaultController extends AbstractController
{
    #[Route(path: '/.*', method: Route::GET, priority: -100)]
    public function index(): ResponseInterface
    {
        return $this->render('@vertexvaar_blueweb/index.html.twig');
    }
}

```

## Route definition

### path

There is no special syntax by intention. Many routing packages have a billion options to choose from and sometimes
even more ways to configure your routes. This one is intentionally left as simple as possible, and so the path is simply
a PCRE regex, which is matched against the requested path.

### method

The default method is `'GET'` and can be omitted, when defining a route. You can use the constants defined in the Route
attribute for the method argument.

### priority

The higher the priority, the earlier the route is matched. Your index route `#[Route(path: '/')]` should always have the
highest priority, because it will be requested the most. Balancing the priority of all other routes can be used to
optimize, but it is probably not worth the effort.

The order of the routes is respected by the Router, so if you put the catch-all first any other route will not have an
effect. You should always have a catch-all which is the last rule in your configuration (very low or negative priority)
to prevent Routing errors (and also to implement your 404 behaviour).
