<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\Template\DependencyInjection;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Twig\Loader\FilesystemLoader;
use VerteXVaaR\BlueContainer\Generated\PackageExtras;
use VerteXVaaR\BlueWeb\Template\TwigFactory;

use function sprintf;
use function strtr;

class TemplateRendererCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var OutputInterface $output */
        $output = $container->get('_output');

        $output->writeln('Loading templates', OutputInterface::VERBOSITY_VERBOSE);

        $packageExtras = $container->get(PackageExtras::class);

        $templatePaths = [];
        foreach ($packageExtras->getPackageNames() as $packageName) {
            $absoluteViewPath = $packageExtras->getPath($packageName, 'view');

            if (null !== $absoluteViewPath) {
                if ($packageExtras->rootPackageName === $packageName) {
                    $namespace = FilesystemLoader::MAIN_NAMESPACE;
                } else {
                    $namespace = strtr($packageName, '/', '_');
                }
                $output->writeln(
                    sprintf(
                        'Identified templates root %s for namespace %s',
                        $absoluteViewPath,
                        $namespace,
                    ),
                    OutputInterface::VERBOSITY_VERBOSE,
                );
                $templatePaths[$namespace] = $absoluteViewPath;
            }
        }

        $definition = $container->getDefinition(TwigFactory::class);
        $definition->setArgument('$templatePaths', $templatePaths);

        $output->writeln('Loaded templates', OutputInterface::VERBOSITY_VERBOSE);
    }
}
