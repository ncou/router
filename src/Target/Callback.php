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
    /**
     * @var callable a PHP callback matching signature of [[MiddlewareInterface::process()]].
     */
    private $callback;
    private $container;

    public function __construct(ContainerInterface $container, callable $callback)
    {
        $this->callback = $callback;
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new ReflectionResolver($this->container))->call($this->callback, [$request]);
    }
}
