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
 * ActionCaller maps a route to specified class instance and method.
 *
 * Dependencies are automatically injected into both method
 * and constructor based on types specified.
 */
final class ActionCaller implements RequestHandlerInterface
{
    private $class;
    private $method;
    private $container;

    public function __construct(ContainerInterface $container, string $class, string $method = 'index')
    {
        $this->class = $class;
        $this->method = $this->camelize($method);
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controller = $this->container->get($this->class);
        return (new ReflectionResolver($this->container))->call([$controller, $this->method], [$request]);
    }

    /**
     * Camelizes a word. This uses the classify() method and turns the first character to lowercase.
     */
    private function camelize(string $word) : string
    {
        return lcfirst($this->classify($word));
    }
    /**
     * Converts a word into the format for a normalized class name. Converts 'table_name' to 'TableName'.
     */
    private function classify(string $word) : string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }
}
