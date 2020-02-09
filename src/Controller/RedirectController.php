<?php

declare(strict_types=1);

namespace Chiron\Router\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class RedirectController
{
    /**
     * @var ResponseFactoryInterface
     */
    private $factory;

    /**
     * @param ResponseFactoryInterface $factory
     */
    public function __construct(ResponseFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $destination
     * @param int    $status
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function redirect(string $destination, int $status): ResponseInterface
    {
        $response = $this->factory->createResponse($status);

        return $response->withHeader('Location', $destination);
    }
}
