<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueWeb\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use VerteXVaaR\BlueWeb\Routing\Attributes\Route;

class DefaultController extends AbstractController
{
    #[Route(path: '/{everything}', priority: -100)]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $everything = $request->getAttribute('route')->matches['everything'];
        return $this->render('@vertexvaar_blueweb/index.html.twig', ['everything' => $everything]);
    }
}
