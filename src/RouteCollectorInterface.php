<?php

declare(strict_types=1);

namespace Chiron\Router;

use Chiron\Router\Traits\RouteCollectionInterface;
use Psr\Http\Message\ServerRequestInterface;
use IteratorAggregate;

interface RouteCollectorInterface extends RouteCollectionInterface
{
    /**
     * Get route objects.
     *
     * @return Route[]
     */
    public function getRoutes(): array;

    /**
     * Get a named route.
     *
     * @param string $name Route name
     *
     * @throws \InvalidArgumentException If named route does not exist
     *
     * @return \Chiron\Router\Route
     */
    public function getNamedRoute(string $name): Route;

    /**
     * Remove named route.
     *
     * @param string $name Route name
     *
     * @throws \InvalidArgumentException If named route does not exist
     */
    public function removeNamedRoute(string $name): void;
}
