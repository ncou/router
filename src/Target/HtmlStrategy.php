<?php

declare(strict_types=1);

// https://github.com/symfony/http-kernel/blob/3.3/Tests/Controller/ControllerResolverTest.php

namespace Chiron\Router\Target;

use Chiron\Container\InvokerInterface;
use Chiron\Router\Route;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
class HtmlStrategy implements RequestHandlerInterface
{
    /** ResponseFactoryInterface */
    private $responseFactory;
    /** InvokerInterface */
    private $invoker;
    /** ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container, ResponseFactoryInterface $responseFactory, InvokerInterface $invoker)
    {
        $this->responseFactory = $responseFactory;
        $this->invoker = $invoker;
        $this->container = $container;
    }

    // $handler => string ou callable
    // TODO : virer l'argument $request on utilisera celui qui se trouve dans le tableau $params !!!!
    public function invokeRouteHandler($handler, array $params, ServerRequestInterface $request): ResponseInterface
    {
        // Inject individual matched parameters.
        foreach ($params as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }
        $params[ServerRequestInterface::class] = $request;

        $result = return (new ReflectionResolver($this->container))->call($handler, $params);

        if (! $result instanceof ResponseInterface) {
            // TODO : gérer le cas ou l'objet a une méthode toString() =>    if (is_object($result) && method_exists($result, '__toString')              https://github.com/middlewares/utils/blob/master/src/CallableHandler.php#L74
            // TODO : mieux gérer les buffer avant de retourner la chaine de caractére => https://github.com/middlewares/utils/blob/master/src/CallableHandler.php#L61
            if (! is_string($result)) {
                throw new LogicException('Your controller should return a string or a ResponseInterface instance.');
            }

            return $this->createResponse($result, 200, ['Content-Type' => 'text/html']);
        }

        return $result;
    }

    // TODO : vérifier que cela ne pose pas de problémes si on passe un content à null, si c'est le cas initialiser ce paramétre avec chaine vide.
    private function createResponse(string $content = null, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        $response->getBody()->write($content);

        return $response;
    }
}
