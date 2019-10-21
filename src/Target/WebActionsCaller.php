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
 * WebActionsCaller maps a route like /post/{action} to methods of
 * a class instance specified named as "action" parameter.
 *
 * Dependencies are automatically injected into both method
 * and constructor based on types specified.
 *
 * ```php
 * Route::anyMethod('/test/{action:\w+}')->to(new WebActionsCaller(TestController::class, $container)),
 * ```
 */
final class WebActionsCaller implements RequestHandlerInterface
{
    private $class;
    private $container;

    public function __construct(ContainerInterface $container, string $class)
    {
        $this->class = $class;
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controller = $this->container->get($this->class);
        $action = $request->getAttribute('action');
        if ($action === null) {
            throw new \RuntimeException('Request does not contain action attribute.');
        }

        $action = $this->camelize($action);

        if (!method_exists($controller, $action)) {
            // TODO : utiliser une exception HTTP ici ???
            throw new \RuntimeException('Bad Request.');
            //return $handler->handle($request);
        }

        return (new ReflectionResolver($this->container))->call([$controller, $action], [$request]);
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
