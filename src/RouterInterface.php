<?php

declare(strict_types=1);

namespace Chiron\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{

    public function match(ServerRequestInterface $request): MatchingResult;

    public function addRoute(Route $route): void;

    /**
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     */
    //public function setBasePath(string $basePath): void;

    /**
     * Get the router base path.
     * Useful if you are running your application from a subdirectory.
     */
    //public function getBasePath(): string;

    // TODO : ajouter les méthodes : generateUri / getRoutes   => attention pas la peine de mettre la méthode addRoute car c'est géré via map() pour ajouter une route.
    // TODO : réflaichir si on doit ajouter les méthodes : getNamedRoute/removeNamedRoute dans cette interface.
}
