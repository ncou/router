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
final class Namespaced implements RequestHandlerInterface
{
    private $namespace;
    private $postfix;
    private $container;

    public function __construct(ContainerInterface $container, string $namespace, string $postfix = 'Controller')
    {
        $this->namespace = rtrim($namespace, '\\');
        $this->postfix = ucfirst($postfix);
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controllerName = $request->getAttribute('controller');

        if ($controllerName === null) {
            throw new \RuntimeException('Request does not contain controller attribute.');
        }

        if (preg_match('/[^a-z_0-9\-]/i', $controllerName)) {
            throw new \RuntimeException('Invalid namespace target, controller name not allowed.');
        }

        $class = sprintf(
            '%s\\%s%s',
            $this->namespace,
            $this->classify($controllerName),
            $this->postfix
        );

        $controller = $this->container->get($class);
        $action = $request->getAttribute('action');
        if ($action === null) {
            throw new \RuntimeException('Request does not contain action attribute.');
        }

        if (!method_exists($controller, $action)) {
            // TODO : utiliser une exception HTTP ici ???
            throw new \RuntimeException('Bad Request.');
            //return $handler->handle($request);
        }

        return (new ReflectionResolver($this->container))->call([$controller, $action], [$request]);
    }


    /**
     * Converts a word into the format for a Doctrine class name. Converts 'table_name' to 'TableName'.
     */
    private function classify(string $word) : string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }
}
