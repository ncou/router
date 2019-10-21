<?php 

declare(strict_types=1);

namespace Chiron\Router;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;

/**
 * RequestHandler
 *
 * @link https://www.php-fig.org/psr/psr-15/
 * @link https://www.php-fig.org/psr/psr-15/meta/
 */
class RequestHandler implements RequestHandlerInterface
{

    /**
     * The request handler middleware queue
     *
     * @var array
     */
    protected $middlewares;

    /**
     * The request handler fallback when queue is empty
     *
     * @var RequestHandlerInterface
     */
    protected $fallback;

    /**
     * Constructor of the class
     */
    public function __construct(array $middlewares, RequestHandlerInterface $fallback)
    {
        $this->middlewares = $middlewares;
        $this->fallback = $fallback;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $middleware = array_shift($this->middlewares);

        if (is_null($middleware)) {
            return $this->fallback->handle($request);
        }

        // TODO : ajouter ici un check sur l'nstanceof de l'objet middleware et si ce n'est pas un objet de type MiddlewareInterface alors on léve un runtime exception

        return $middleware->process($request, $this);
    }
}
