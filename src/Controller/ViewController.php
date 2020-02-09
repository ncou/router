<?php

declare(strict_types=1);

namespace Chiron\Router\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Chiron\Views\TemplateRendererInterface;

final class ViewController
{
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;
    /**
     * @var ResponseFactoryInterface
     */
    private $factory;

    /**
     * @param ResponseFactoryInterface $factory
     * @param TemplateRendererInterface $renderer
     */
    public function __construct(ResponseFactoryInterface $factory, TemplateRendererInterface $renderer)
    {
        $this->factory = $factory;
        $this->renderer = $renderer;
    }

    /**
     * @param string $template
     * @param array  $parameters
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function view(string $template, array $parameters): ResponseInterface
    {
        $content = $this->renderer->render($template, $parameters);

        $response = $this->factory->createResponse();
        // TODO : vérifier si il n'y a pas déjà un header content-type avant d'ajouter celui là.
        $response = $response->withHeader('Content-Type', 'text/html');

        $response->getBody()->write($content);

        return $response;
    }
}
