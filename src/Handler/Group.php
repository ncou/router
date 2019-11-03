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
 * Provides ability to invoke from a given controller set:
 *
 * ```php
 * new Group(['signup' => SignUpController::class]);
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

    //return join('|', $values);
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

    public function getDefaults(): array
    {
        return ['controller' => null, 'action' => null];
    }

    public function getConstrains(): array
    {
        return ['controller' => join('|', array_keys($controllers)), 'action' => null];
    }
}
