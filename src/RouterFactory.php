<?php

declare(strict_types=1);

namespace Chiron\Router;

use Psr\Container\ContainerInterface;

class RouterFactory
{
    private $engineFactory;
    private $routes;

    public function __construct(callable $engineFactory, $routes = [])
    {
        $this->engineFactory = $engineFactory;
        $this->routes = $routes;

        // Factory is wrapped in a closure in order to enforce return type safety.
        /*
        $this->engineFactory = function (ContainerInterface $container) use ($engineFactory) : RouterInterface {
            return $engineFactory($container);
        };*/
    }

    public function __invoke(ContainerInterface $container): RouterInterface
    {
        $factory = $this->engineFactory;

        /* @var $router RouterInterface */
        $router = $factory($container);
        foreach ($this->routes as $route) {
            $router->addRoute($route);
        }
        
        return $router;
    }
}
