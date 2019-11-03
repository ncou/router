<?php

declare(strict_types=1);

namespace Chiron\Router\Handler;

use Psr\Http\Server\RequestHandlerInterface;

interface TargetInterface extends RequestHandlerInterface
{
    /**
     * Set of default values provided by the target.
     *
     * @return array
     */
    public function getDefaults(): array;

    /**
     * Set of constrains defines list of required keys and optional set of allowed values.
     *
     * Examples:
     * ["controller" => null, "action" => "login"]
     * ["controller" => "singup|signin"]
     * ["action" => "login|logout"]
     *
     * @return array
     */
    public function getConstrains(): array;
}
