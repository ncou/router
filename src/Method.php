<?php

declare(strict_types=1);

namespace Chiron\Router;

final class Method
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const PATCH = 'PATCH';
    public const HEAD = 'HEAD';
    public const OPTIONS = 'OPTIONS';

    public const ANY = [
        self::GET,
        self::POST,
        self::PUT,
        self::DELETE,
        self::PATCH,
        self::HEAD,
        self::OPTIONS,
    ];
}
