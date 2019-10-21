<?php

declare(strict_types=1);

namespace Chiron\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Router\Middleware\RoutingMiddleware;

// TODO : renommer en RouteHandler
class RouteRunner implements RequestHandlerInterface
{
    /**
     * @var RouterInterface
     */
    private $router;


    public function __construct(RouterInterface $router) {
        $this->router = $router;
    }

    /**
     * Process a server request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if ($routeResult === null) {
            $routingMiddleware = new RoutingMiddleware($this->router);
            $request = $routingMiddleware->performRouting($request);
        }

        $routeResult = $request->getAttribute(RouteResult::class);

        return $routeResult->handle($request);
    }

}
