<?php

declare(strict_types=1);

namespace Chiron\Router\Traits;

use Chiron\Router\Route;
use Chiron\Router\RouteGroup;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : on devrait pas aussi ajouter les méthodes map et group dans cette interface ?????

interface RouteCollectionInterface
{
    /**
     * Group a bunch of routes.
     *
     * @param string   $prefix
     * @param callable $group
     *
     * @return \Chiron\Router\RouteGroup
     */
    public function group(string $prefix, callable $group): RouteGroup;

    /**
     * Add a route to the map.
     *
     * @param string          $path
     * @param RequestHandlerInterface $handler
     *
     * @return \Chiron\Router\Route
     */
    public function map(string $path, RequestHandlerInterface $handler): Route;

    /**
     * Add GET route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.1
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.3
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function get(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Add HEAD route.
     *
     * HEAD was added to HTTP/1.1 in RFC2616
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.2
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function head(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Add POST route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.3
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.5
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function post(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Add PUT route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.4
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.6
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function put(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Add DELETE route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.5
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.7
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function delete(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Add OPTIONS route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.7
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function options(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Add TRACE route.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.3.9
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.9
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function trace(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Add PATCH route.
     *
     * PATCH was added to HTTP/1.1 in RFC5789
     *
     * @see http://tools.ietf.org/html/rfc5789
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function patch(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Add route for any (official or unofficial) HTTP method.
     * use ->seAllowedMethods([]) with an empty array to support ALL the values (for custom method).
     *
     * @param string          $pattern The route URI pattern
     * @param RequestHandlerInterface $handler The route callback routine
     *
     * @return \Chiron\Router\Route
     */
    public function any(string $pattern, RequestHandlerInterface $handler): Route;

    /**
     * Create a redirect from one URI to another.
     *
     * @param string $url
     * @param string $destination
     * @param int    $status
     *
     * @return \Chiron\Router\Route
     */
    public function redirect(string $url, string $destination, int $status = 302): Route;

    /**
     * Create a permanent redirect from one URI to another.
     *
     * @param string $url
     * @param string $destination
     *
     * @return \Chiron\Router\Route
     */
    public function permanentRedirect(string $url, string $destination): Route;

    /**
     * Register a new route that returns a view.
     *
     * @param string $url
     * @param string $view
     * @param array  $params
     *
     * @return \Chiron\Router\Route
     */
    public function view(string $url, string $view, array $params = []): Route;
}
