<?php

declare(strict_types=1);

namespace Chiron\Router;

use Psr\Container\ContainerInterface;

class RouterFactory
{
    private $routerFactory;
    private $routes;

    public function __construct(callable $routerFactory, $routes = [])
    {
        $this->routerFactory = $routerFactory;

        // Factory is wrapped in a closure in order to enforce return type safety.
        /*
        $this->routerFactory = function (ContainerInterface $container) use ($routerFactory) : RouterInterface {
            return $routerFactory($container);
        };*/

        $this->routes = $routes;
    }

    public function __invoke(ContainerInterface $container): RouterInterface
    {
        $router = $this->routerFactory($container);

        foreach ($this->routes as $route) {
            $router->addRoute($route);
        }

        return $router;
    }
}
