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
 * Namespaced maps a route like /post/{action} to methods of
 * a class instance specified named as "action" parameter.
 *
 * Dependencies are automatically injected into both method
 * and constructor based on types specified.
 *
 * ```php
 * Route::anyMethod('/test/{action:\w+}')->to(new WebActionsCaller(TestController::class, $container)),
 * ```
 */
final class Namespaced implements RequestHandlerInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var string */
    private $namespace;
    /** @var string */
    private $postfix;

    /**
     * @param ContainerInterface $container
     * @param string $namespace
     * @param string $postfix
     */
    public function __construct(ContainerInterface $container, string $namespace, string $postfix = 'Controller')
    {
        $this->container = $container;
        $this->namespace = rtrim($namespace, '\\');
        $this->postfix = ucfirst($postfix);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controllerName = $request->getAttribute('controller');

        if ($controllerName === null) {
            throw new \RuntimeException('Request does not contain controller attribute.');
        }

        $class = sprintf(
            '%s\\%s%s',
            $this->namespace,
            $this->classify($controllerName),
            $this->postfix
        );

        $controller = $this->container->get($class);
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


    /**
     * Converts a word into the format for a Doctrine class name. Converts 'table_name' to 'TableName'.
     */
    private function classify(string $word) : string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }
}
