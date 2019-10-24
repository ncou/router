<?php

declare(strict_types=1);

namespace Chiron\Router\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Container\ReflectionResolver;
use InvalidArgumentException;

/**
 * Action maps a route to specified class instance and method.
 *
 * Dependencies are automatically injected into both method
 * and constructor based on types specified.
 *
 * ```php
 * new Action(HomeController::class, "index");
 * new Action(SingUpController::class, ["login", "logout"]); // creates <action> constrain
 * ```
 *
 */
final class Action implements RequestHandlerInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var string */
    private $controller;
    /** @var array|string */
    private $action;

    /**
     * @param ContainerInterface $container
     * @param string       $controller Controller class name.
     * @param string|array $action     One or multiple allowed actions.
     */
    public function __construct(ContainerInterface $container, string $controller, $action)
    {
        if (!is_string($action) && !is_array($action)) {
            throw new InvalidArgumentException(sprintf(
                'Action parameter must type string or array, `%s` given.',
                gettype($action)
            ));
        }

        $this->container = $container;
        $this->controller = $controller;
        $this->action = $action;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controller = $this->container->get($this->controller);

        $default = null;
        if (is_string($this->action)) {
            $default = $this->action;
        }
        $action = $request->getAttribute('action', $default);

        return (new ReflectionResolver($this->container))->call([$controller, $action], [$request]);
    }
}
