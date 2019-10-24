<?php

declare(strict_types=1);

namespace Chiron\Router\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Container\ReflectionResolver;

/**
 * Controller maps a route like /post/{action} to methods of
 * a class instance specified named as "action" parameter.
 *
 * Dependencies are automatically injected into both method
 * and constructor based on types specified.
 *
 * ```php
 * Route::anyMethod('/test/{action:\w+}')->to(new WebActionsCaller(TestController::class, $container)),
 * ```
 */
final class Controller implements RequestHandlerInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var string */
    private $controller;

    /**
     * @param ContainerInterface $container
     * @param string $controller
     */
    public function __construct(ContainerInterface $container, string $controller)
    {
        $this->container = $container;
        $this->controller = $controller;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controller = $this->container->get($this->controller);
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
