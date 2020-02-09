<?php

declare(strict_types=1);

namespace Chiron\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Router\Middleware\RoutingMiddleware;

class RoutingHandler implements RequestHandlerInterface
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
        $result = $request->getAttribute(MatchingResult::class);

        // if the user has not added the RoutingMiddleware at the bottom of the stack, we force the call.
        if ($result === null) {
            $routingMiddleware = new RoutingMiddleware($this->router);
            $request = $routingMiddleware->performRouting($request);
        }

        $result = $request->getAttribute(MatchingResult::class);

        return $result->handle($request);
    }

}
