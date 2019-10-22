<?php

declare(strict_types=1);

namespace Chiron\Router\Target;

//use Chiron\Http\Psr\Response;
use Chiron\Container\InvokerInterface;
use Chiron\Router\Route;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

//https://github.com/dmandrade/apli-core/blob/master/src/Core/Http/Response.php

//https://github.com/symfony/serializer/blob/master/Encoder/JsonEncode.php
//https://github.com/thetribeio/json/blob/master/src/encode.php

// TODO : ajouter le jsonP  https://github.com/yiisoft/yii2/blob/master/framework/web/JsonResponseFormatter.php

// TODO : gérer toJson et toArray    https://github.com/zendframework/zf1/blob/master/library/Zend/Json.php#L133    /      https://github.com/zendframework/zend-json/blob/master/src/Json.php#L78

/**
 * Route callback strategy with route parameters as individual arguments and the response is encoded in json.
 */
class JsonStrategy implements RequestHandlerInterface
{
    /** ResponseFactoryInterface */
    private $responseFactory;
    /** InvokerInterface */
    private $invoker;
    /** ContainerInterface */
    private $container;

    /**
     * Default flags for json_encode.
     * Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
     * Doesn't encode the slash /.
     *
     * JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
     */
    public const DEFAULT_JSON_FLAGS = 79;

    /** @var int */
    //private $jsonOptions = 0;

    /**
     * @var int
     */
    private $encodingOptions = self::DEFAULT_JSON_FLAGS;

    public function __construct(ContainerInterface $container, ResponseFactoryInterface $responseFactory, InvokerInterface $invoker)
    {
        $this->responseFactory = $responseFactory;
        $this->invoker = $invoker;
        $this->container = $container;
    }

    public function invokeRouteHandler($handler, array $params, ServerRequestInterface $request): ResponseInterface
    {
        // Inject individual matched parameters.
        foreach ($params as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }
        $params[ServerRequestInterface::class] = $request;

        $result = return (new ReflectionResolver($this->container))->call($handler, $params);

        // TODO : lever une exception si le retour renvoyé par le controller n'est pas : JsonSerializableInterface ou ArrayObject ou is_array
        if (! $result instanceof ResponseInterface) {
            $json = $this->jsonEncode($result);

            return $this->createResponse($json, 200, ['Content-Type' => 'application/json']);
        }

        return $result;
    }

    /**
     * Encode the provided data to JSON.
     *
     * @param mixed $data
     *
     * @throws InvalidArgumentException if unable to encode the $data to JSON.
     *
     * @return string
     */
    public function jsonEncode($data): string
    {
        // TODO : attendre la version PHP 7.3 pour utiliser le flag JSON_THROW_ON_ERROR => https://wiki.php.net/rfc/json_throw_on_error
        // https://github.com/aedart/athenaeum/blob/master/src/Utils/Json.php#L35
        $json = json_encode($data, $this->encodingOptions);

        if ($json === false) {
            throw new InvalidArgumentException(
                sprintf('Unable to encode data to JSON: %s', json_last_error_msg()),
                json_last_error());
        }

        return $json;
    }

    // TODO : vérifier que cela ne pose pas de problémes si on passe un content à null, si c'est le cas initialiser ce paramétre avec chaine vide.
    private function createResponse(string $content = null, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        $response->getBody()->write($content);

        return $response;
    }

    /**
     * Returns options used while encoding data to JSON.
     *
     * @return int
     */
    public function getEncodingOptions(): int
    {
        return $this->encodingOptions;
    }

    /**
     * Sets options used while encoding data to JSON.
     *
     * @param int $encodingOptions
     *
     * @return $this
     */
    public function setEncodingOptions(int $encodingOptions): self
    {
        $this->encodingOptions = $encodingOptions;

        return $this;
    }

    /*
     * Set options for JSON encoding
     *
     * @see http://php.net/manual/function.json-encode.php
     * @see http://php.net/manual/json.constants.php
     */
    /*
    public function jsonOptions(int $options): self
    {
        $this->jsonOptions = $options;
        return $this;
    }*/

    /*
    //https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/NormalizerFormatter.php#L97
        public function prettyPrint(bool $enable)
        {
            if ($enable) {
                $this->jsonEncodeOptions |= JSON_PRETTY_PRINT;
            } else {
                $this->jsonEncodeOptions ^= JSON_PRETTY_PRINT;
            }
        }
    */

    /*
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    /*
    protected function shouldBeJson($content)
    {
        return $content instanceof ArrayObject ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }*/

    /*
     * Check if the response can be converted to JSON
     *
     * Arrays can always be converted, objects can be converted if they're not a response already
     *
     * @param mixed $response
     *
     * @return bool
     */
    /*
    protected function isJsonEncodable($response) : bool
    {
        if ($response instanceof ResponseInterface) {
            return false;
        }
        return (is_array($response) || is_object($response));
    }*/

    /*
     * Encode a value to JSON using the PHP built-in json_encode function.
     *
     * Uses the encoding options:
     *
     * - JSON_HEX_TAG
     * - JSON_HEX_APOS
     * - JSON_HEX_QUOT
     * - JSON_HEX_AMP
     *
     * If $prettyPrint is boolean true, also uses JSON_PRETTY_PRINT.
     *
     * @param mixed $valueToEncode
     * @param bool $prettyPrint
     * @return string|false Boolean false return value if json_encode is not
     *     available, or the $useBuiltinEncoderDecoder flag is enabled.
     */
    /*
    private static function encodeViaPhpBuiltIn($valueToEncode, $prettyPrint = false)
    {
        if (! function_exists('json_encode')) {
            return false;
        }

        $encodeOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

        if ($prettyPrint) {
            $encodeOptions |= JSON_PRETTY_PRINT;
        }

        return json_encode($valueToEncode, $encodeOptions);
    }*/
}