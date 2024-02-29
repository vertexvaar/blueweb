<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\Template;

use Twig\Cache\FilesystemCache;
use Twig\Environment as View;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use VerteXVaaR\BlueContainer\Generated\PackageExtras;
use VerteXVaaR\BlueSprints\Environment\Context;
use VerteXVaaR\BlueSprints\Environment\Environment;

use function CoStack\Lib\concat_paths;
use function getenv;

readonly class TwigFactory
{
    public function __construct(
        private array $templatePaths,
        private Environment $environment,
        private iterable $extensions,
        private PackageExtras $packageExtras,
    ) {
    }

    public function create(): View
    {
        $loader = new FilesystemLoader();
        foreach ($this->templatePaths as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }
        $cachePath = $this->packageExtras->getPath($this->packageExtras->rootPackageName, 'var/cache')
            ?? concat_paths(getenv('VXVR_BS_ROOT'), 'var/cache');
        $twigCachePath = concat_paths($cachePath, 'twig');
        $filesystemCache = new FilesystemCache($twigCachePath);
        $view = new View(
            $loader,
            [
                'cache' => $filesystemCache,
                'debug' => $this->environment->context === Context::Development,
            ],
        );
        foreach ($this->extensions as $extension) {
            $view->addExtension($extension);
        }
        if ($this->environment->context === Context::Development) {
            $view->addExtension(new DebugExtension());
        }
        return $view;
    }
}
