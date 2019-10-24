<?php

declare(strict_types=1);

namespace Chiron\Router\Target;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Container\ReflectionResolver;

/**
 * Callback wraps arbitrary PHP callback into object matching [[MiddlewareInterface]].
 * Usage example:
 *
 * ```php
 * $middleware = new CallbackMiddleware(function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
 *     if ($request->getParams() === []) {
 *         return new Response();
 *     }
 *     return $handler->handle($request);
 * });
 * $response = $middleware->process(Yii::$app->getRequest(), $handler);
 * ```
 *
 * @see MiddlewareInterface
 */
final class Callback implements RequestHandlerInterface
{
    private $container;
    /**
     * @var callable a PHP callback matching signature of [RequestHandlerInterface->handle(ServerRequestInterface $request)]].
     */
    private $callback;

    public function __construct(ContainerInterface $container, callable $callback)
    {
        $this->container = $container;
        $this->callback = $callback;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO : il faut surement stocker la $request dans un tableau avec la clé = au nom de la classe pour permettre au Invoker de matcher la request avec via un Autowire avec ce paramétre du tableau
        return (new ReflectionResolver($this->container))->call($this->callback, [$request]);
    }
}
