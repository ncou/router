<?php

declare(strict_types=1);

namespace Chiron\Router\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Container\ReflectionResolver;
use Chiron\Invoker\Invoker;
use InvalidArgumentException;

/**
 * Targets to specific controller action or actions.
 *
 * ```php
 * new Action(HomeController::class, "index");
 * new Action(SingUpController::class, ["login", "logout"]); // creates <action> constrain
 * ```
 *
 */
final class Action implements TargetInterface
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
        // TODO : lever une exception si le container>has() ne trouve pas le controller !!!!
        //$controller = $this->container->get($this->controller);

        // TODO : à virer c'est pour un test !!!!
        $this->container->add(ServerRequestInterface::class, $request);


/*
        $default = null;
        if (is_string($this->action)) {
            $default = $this->action;
        }
        $action = $request->getAttribute('action', $default);
        */

        $action = $request->getAttribute('action');

        //$resolver = new ControllerResolver();

        //$resolved = $resolver->getController([$controller, $action], $request);


        //return (new ReflectionResolver($this->container))->call([$controller, $action], [$request]);
        //return (new Invoker($this->container))->call([$controller, $action], [$request]);
        return (new Invoker($this->container))->call([$this->controller, $action], [$request]);
    }

    public function getDefaults(): array
    {
        if (is_string($this->action)) {
            return ['action' => $this->action];
        } else {
            return ['action' => null];
        }
    }

    public function getConstrains(): array
    {
        if (is_string($this->action)) {
            return ['action' => $this->action];
        } else {
            return ['action' => join('|', $this->action)];
        }
    }
}
