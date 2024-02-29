<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\ActionCache\DependencyInjection;

use Exception;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VerteXVaaR\BlueWeb\ActionCache\Attributes\ActionCache;
use VerteXVaaR\BlueWeb\ActionCache\Middleware\ActionCacheMiddleware;
use VerteXVaaR\BlueWeb\Routing\Attributes\Route;

use function array_keys;
use function count;
use function get_object_vars;

class ActionCacheCompilerPass implements CompilerPassInterface
{
    public function __construct(
        private readonly string $tagName,
    ) {
    }

    public function process(ContainerBuilder $container)
    {
        $cachedActions = [];

        $services = $container->findTaggedServiceIds($this->tagName);
        foreach (array_keys($services) as $controllerService) {
            $controllerDefinition = $container->findDefinition($controllerService);
            $class = $controllerDefinition->getClass();
            $reflectionClass = new ReflectionClass($class);
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $reflectionCacheAttributes = $reflectionMethod->getAttributes(ActionCache::class);
                if (1 === count($reflectionCacheAttributes)) {
                    $reflectionRouteAttributes = $reflectionMethod->getAttributes(Route::class);
                    if (empty($reflectionRouteAttributes)) {
                        throw new Exception(
                            'Can not cache an action without a route annotation.'
                            . ' Method: ' . $class . '::' . $reflectionMethod->getName(),
                        );
                    }
                    foreach ($reflectionRouteAttributes as $reflectionRouteAttribute) {
                        /** @var Route $routeAttribute */
                        $routeAttribute = $reflectionRouteAttribute->newInstance();
                        if ($routeAttribute->method !== 'GET') {
                            throw new Exception(
                                'Can not cache actions with non-GET routes.'
                                . ' Method: ' . $class . '::' . $reflectionMethod->getName()
                                . ' Conflicting Route: ' . $routeAttribute->method . ': ' . $routeAttribute->path,
                            );
                        }
                    }

                    $reflectionCacheAttribute = $reflectionCacheAttributes[0];
                    /** @var \VerteXVaaR\BlueWeb\ActionCache\Attributes\ActionCache $cacheAttribute */
                    $cacheAttribute = $reflectionCacheAttribute->newInstance();
                    $cachedActions[$class][$reflectionMethod->getName()] = get_object_vars($cacheAttribute);
                }
            }
        }

        $cachingMiddlewareDefinition = $container->findDefinition(ActionCacheMiddleware::class);
        $cachingMiddlewareDefinition->setArgument('$cachedActions', $cachedActions);
    }
}
