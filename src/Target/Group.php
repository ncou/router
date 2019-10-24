<?php

declare(strict_types=1);

namespace Chiron\Router\Target;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Container\ReflectionResolver;

/**
 * Group maps a route like /post/{action} to methods of
 * a class instance specified named as "action" parameter.
 *
 * Dependencies are automatically injected into both method
 * and constructor based on types specified.
 *
 * ```php
 * Route::anyMethod('/test/{action:\w+}')->to(new WebActionsCaller(TestController::class, $container)),
 * ```
 */
final class Group implements RequestHandlerInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var array */
    private $controllers;

    /**
     * @param ContainerInterface $container
     * @param array $controllers
     */
    public function __construct(ContainerInterface $container, array $controllers)
    {
        $this->container = $container;
        $this->controllers = $controllers;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controllerName = $request->getAttribute('controller');

        if ($controllerName === null) {
            throw new \RuntimeException('Request does not contain controller attribute.');
        }

        $controller = $this->controllers[$controllerName];

        $action = $request->getAttribute('action');
        if ($action === null) {
            throw new \RuntimeException('Request does not contain action attribute.');
        }

        if (!method_exists($controller, $action)) {
            // TODO : utiliser une exception HTTP ici ???
            throw new \RuntimeException('Bad Request.');
            //return $handler->handle($request);
        }

        return (new ReflectionResolver($this->container))->call([$controller, $action], [$request]);
    }
}
