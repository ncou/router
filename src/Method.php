<?php

declare(strict_types=1);

namespace Chiron\Router;

use function strtoupper;

// TODO : classe à renommer en HttpMethods ????
// TODO : enrichir la méthode ->any()    https://github.com/narrowspark/framework/blob/2866c328dfeec4cc78f8c25f412832bb2e9da5e2/src/Viserio/Component/Routing/Router.php#L191
final class Method
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const PATCH = 'PATCH';
    public const HEAD = 'HEAD';
    public const OPTIONS = 'OPTIONS';
    public const CONNECT = 'CONNECT';
    public const TRACE = 'TRACE';

    public const ANY = [
        self::GET,
        self::POST,
        self::PUT,
        self::DELETE,
        self::PATCH,
        self::HEAD,
        self::OPTIONS,
        self::CONNECT,
        self::TRACE
    ];

    /**
     * Standardize custom http method name
     * For the methods that are not defined in this enum
     *
     * @param string $method
     * @return string
     */
    // TODO : réfléchir si on conserve cette méthode !!!!
    public static function custom(string $method): string
    {
        return strtoupper($method);
    }
}
