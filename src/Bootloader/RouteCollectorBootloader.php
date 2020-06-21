<?php

namespace Chiron\Router\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Router\RouteCollector;

// TODO : transformer cette classe en un ServiceProvider et dans le binding du RouteCollector on en profiterai pour aussi initialiser le basePath ???? non ????
class RouteCollectorBootloader extends AbstractBootloader
{
    public function boot(RouteCollector $routeCollector, HttpConfig $httpConfig)
    {
        $routeCollector->setBasePath($httpConfig->getBasePath());
    }
}
