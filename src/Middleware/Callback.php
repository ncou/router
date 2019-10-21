<?php

declare(strict_types=1);

namespace Chiron\Router\Middleware;

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
final class Callback implements MiddlewareInterface
{
    /**
     * @var callable a PHP callback matching signature of [[MiddlewareInterface::process()]].
     */
    private $callback;
    private $container;
    public function __construct(callable $callback, ContainerInterface $container)
    {
        $this->callback = $callback;
        $this->container = $container;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return (new Injector($this->container))->invoke($this->callback, [$request, $handler]);
    }
}
