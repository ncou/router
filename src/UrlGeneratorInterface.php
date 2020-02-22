<?php

declare(strict_types=1);

namespace Chiron\Router;

use Chiron\Router\Traits\MiddlewareAwareInterface;
use Chiron\Router\Traits\StrategyAwareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

//https://github.com/yiisoft/router/blob/master/src/UrlGeneratorInterface.php
//https://github.com/slimphp/Slim/blob/cf68c2dede23b2c05ea9162379bf10ba6c913331/Slim/Routing/RouteParser.php#L112
interface UrlGeneratorInterface
{


    public function generate(string $routePath, array $substitutions = [], array $queryParams = []): string;

    //public function urlFor(string $routeName, array $substitutions = [], array $queryParams = []): string;

    //public function relativeUrlFor(string $routeName, array $substitutions = [], array $queryParams = []): string;



}
