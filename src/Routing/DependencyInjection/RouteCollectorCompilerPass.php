<?php

namespace VerteXVaaR\BlueWeb\Routing\DependencyInjection;

use Composer\IO\IOInterface;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VerteXVaaR\BlueWeb\Routing\Attributes\Route;
use VerteXVaaR\BlueWeb\Routing\Attributes\RouteAttribute;
use VerteXVaaR\BlueWeb\Routing\Middleware\RoutingMiddleware;

use function array_keys;
use function array_merge;
use function get_class;
use function get_object_vars;
use function is_object;
use function krsort;
use function sprintf;

class RouteCollectorCompilerPass implements CompilerPassInterface
{
    public function __construct(
        private readonly string $tagName,
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        /** @var OutputInterface $output */
        $output = $container->get('_output');
        $errorOutput = $output instanceof ConsoleOutput ? $output->getErrorOutput() : $output;
        $output->writeln('Loading routes from controller attributes', OutputInterface::VERBOSITY_VERBOSE);

        $collectedRoutes = [];
        $controllers = $container->findTaggedServiceIds($this->tagName);
        foreach (array_keys($controllers) as $controller) {
            $definition = $container->findDefinition($controller);
            $definition->setPublic(true);
            $controllerClass = $definition->getClass();
            try {
                $reflection = new ReflectionClass($controllerClass);
            } catch (ReflectionException $exception) {
                $errorOutput->writeln(
                    sprintf(
                        'Could not reflect controller "%s". Exception: %s',
                        $controllerClass,
                        $exception->getMessage(),
                    ),
                );
                continue;
            }
            $reflectionMethods = $reflection->getMethods();
            if (empty($reflectionMethods)) {
                $output->writeln(
                    sprintf('Controller "%s" does not define any methods', $controllerClass),
                    OutputInterface::VERBOSITY_VERBOSE,
                );
                continue;
            }
            foreach ($reflectionMethods as $reflectionMethod) {
                $attributes = $reflectionMethod->getAttributes(
                    Route::class,
                    ReflectionAttribute::IS_INSTANCEOF,
                );
                foreach ($attributes as $attribute) {
                    /** @var Route $route */
                    $route = $attribute->newInstance();
                    $methodName = $reflectionMethod->getName();
                    $output->writeln(
                        sprintf(
                            'Found route [%d][%s] "%s" in controller "%s" method "%s"',
                            $route->priority,
                            $route->method,
                            $route->path,
                            $controllerClass,
                            $methodName,
                        ),
                        OutputInterface::VERBOSITY_VERBOSE,
                    );
                    $collectedRoutes[$route->priority][] = [
                        'controller' => $controllerClass,
                        'action' => $methodName,
                        'class' => get_class($route),
                        'vars' => get_object_vars($route),
                    ];
                }
            }
        }
        krsort($collectedRoutes);
        $collectedRoutes = array_merge([], ...$collectedRoutes);

        $parser = new Std();
        $generator = new GroupCountBased();
        $routeCollector = new RouteCollector($parser, $generator);
        foreach ($collectedRoutes as $route) {
            $routeCollector->addRoute($route['vars']['method'], $route['vars']['path'], $route);
        }
        $data = $routeCollector->getData();

        $definition = $container->getDefinition(RoutingMiddleware::class);
        $definition->setArgument('$data', $data);

        $output->writeln('Loaded routes from controller attributes', OutputInterface::VERBOSITY_VERBOSE);
    }
}
