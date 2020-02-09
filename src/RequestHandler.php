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

/**
 * RequestHandler
 *
 * @link https://www.php-fig.org/psr/psr-15/
 * @link https://www.php-fig.org/psr/psr-15/meta/
 */
// TODO : renommer la classe en "Pipeline"
final class RequestHandler implements RequestHandlerInterface
{

    /**
     * The request handler middleware queue
     *
     * @var array MiddlewareInterface[]
     */
    private $queue = [];

    /**
     * The request handler fallback when queue is empty
     *
     * @var RequestHandlerInterface
     */
    private $fallback;

    /**
     * Constructor of the class
     */
    public function __construct()
    {
        $this->fallback = new EmptyPipelineHandler();
    }

    /**
     * @param MiddlewareInterface $middleware Middleware to add at the end of the queue.
     */
    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->queue[] = $middleware;

        return $this;
    }

    /**
     * @param MiddlewareInterface $middleware Middleware to add at the end of the queue.
     */
    public function setFallback(RequestHandlerInterface $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $middleware = array_shift($this->queue);

        if (is_null($middleware)) {
            return $this->fallback->handle($request);
        }

        return $middleware->process($request, $this);
    }
}
