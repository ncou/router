<?php

declare(strict_types=1);

namespace Chiron\Router\Middleware;

// TODO : example : https://github.com/zendframework/zend-expressive-router/blob/master/src/Middleware/RouteMiddleware.php
// TODO : regarder ici https://github.com/zrecore/Spark/blob/master/src/Handler/RouteHandler.php    et https://github.com/equip/framework/blob/master/src/Handler/DispatchHandler.php

//namespace Middlewares;

use Chiron\Http\Exception\Client\MethodNotAllowedHttpException;
use Chiron\Http\Exception\Client\NotFoundHttpException;
//use Chiron\Http\Psr\Response;
use Chiron\Router\Route;
use Chiron\Router\MatchingResult;
use Chiron\Router\RouterInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    /** @var RouterInterface */
    private $router;

    // TODO : passer en paramétre une responsefactory et un streamfactory.
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     *
     * @throws NotFoundHttpException
     * @throws MethodNotAllowedHttpException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->performRouting($request);

        // Execute the next handler
        return $handler->handle($request);
    }

    /**
     * Perform routing (use 'public' visibility to be called by the RoutingHandler if needed)
     *
     * @param  ServerRequestInterface $request PSR7 Server Request
     * @return ServerRequestInterface
     *
     * @throws NotFoundHttpException
     * @throws MethodNotAllowedHttpException
     */
    public function performRouting(ServerRequestInterface $request) : ServerRequestInterface
    {
        // TODO : il faudrait peut etre récupérer la réponse via un $handle->handle() pour récupérer les headers de la réponse + le charset et version 1.1/1.0 pour le passer dans les exceptions (notfound+methodnotallowed) car on va recréer une nouvelle response !!!! donc si ca se trouve les headers custom genre X-Powered ou CORS vont être perdus lorsqu'on va afficher les messages custom pour l'exception 404 par exemple !!!!

        //$result = $this->getDispatchResult($request);
        $result = $this->router->match($request);

        // Http 405 error => Invalid Method
        if ($result->isMethodFailure()) {
            throw new MethodNotAllowedHttpException($result->getAllowedMethods());
        }
        // Http 404 error => Not Found
        if ($result->isFailure()) {
            throw new NotFoundHttpException();
        }

        // add some usefull information about the url used for the routing
        // TODO : faire plutot porter ces informations (method et uri utilisé) directement dans l'objet MatchingResult ??????
        //$request = $request->withAttribute('routeInfo', [$request->getMethod(), (string) $request->getUri()]);

        // Store the actual route result in the request attributes, to be used/executed by the router handler.
        return $request->withAttribute(MatchingResult::ATTRIBUTE, $result);
    }
}
