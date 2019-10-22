<?php

declare(strict_types=1);

namespace Chiron\Router;

use Chiron\Router\Traits\MiddlewareAwareInterface;
use Chiron\Router\Traits\MiddlewareAwareTrait;
use Chiron\Router\Traits\RouteConditionHandlerInterface;
use Chiron\Router\Traits\RouteConditionHandlerTrait;
use Psr\Http\Server\RequestHandlerInterface;
use InvalidArgumentException;

//https://github.com/symfony/routing/blob/master/Route.php

class Route implements RouteConditionHandlerInterface, MiddlewareAwareInterface
{
    use MiddlewareAwareTrait;
    use RouteConditionHandlerTrait;

    /** @var array */
    private $requirements = [];

    /** @var array */
    private $defaults = [];

    /** @var string|null */
    private $name;

    /**
     * The route path pattern (The URL pattern (e.g. "article/[:year]/[i:category]")).
     *
     * @var string
     */
    private $path;

    /**
     * Handler assigned to be executed when route is matched.
     *
     * @var RequestHandlerInterface
     */
    private $handler;

    /**
     * List of supported HTTP methods for this route (GET, POST etc.).
     *
     * @var array
     */
    // Créer une RouteInterface et ajouter ces verbs dans l'interface : https://github.com/spiral/router/blob/master/src/RouteInterface.php#L26
    private $methods = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE'];

    /**
     * @param string $url
     * @param RequestHandlerInterface  $handler
     */
    public function __construct(string $path, RequestHandlerInterface $handler)
    {
        // A path must start with a slash and must not have multiple slashes at the beginning because it would be confused with a network path, e.g. '//domain.com/path'.
        $this->path = sprintf('/%s', ltrim($path, '/'));
        // TODO : ajouter une vérification pour que le $handler soit un callable ou une string
        $this->handler = $handler;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    // return : mixed => should be a string or a callable
    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }

    /**
     * Returns the defaults.
     *
     * @return array The defaults
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Sets the defaults.
     *
     * @param array $defaults The defaults
     *
     * @return $this
     */
    public function setDefaults(array $defaults): self
    {
        $this->defaults = [];

        return $this->addDefaults($defaults);
    }

    /**
     * Adds defaults.
     *
     * @param array $defaults The defaults
     *
     * @return $this
     */
    public function addDefaults(array $defaults): self
    {
        // TODO : faire un assert que $name est bien une string sinon lever une exception !!!!
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }

        return $this;
    }

    /**
     * Gets a default value.
     *
     * @param string $name A variable name
     *
     * @return mixed The default value or null when not given
     */
    public function getDefault(string $name)
    {
        return $this->defaults[$name] ?? null;
    }

    /**
     * Sets a default value.
     *
     * @param string $name    A variable name
     * @param mixed  $default The default value
     *
     * @return $this
     */
    public function setDefault(string $name, $default): self
    {
        $this->defaults[$name] = $default;

        return $this;
    }

    /**
     * Checks if a default value is set for the given variable.
     *
     * @param string $name A variable name
     *
     * @return bool true if the default value is set, false otherwise
     */
    public function hasDefault(string $name): bool
    {
        return array_key_exists($name, $this->defaults);
    }

    /**
     * Alias for setDefault.
     *
     * @param string $name    A variable name
     * @param mixed  $default The default value
     *
     * @return $this
     */
    public function value(string $variable, $default): self
    {
        return $this->setDefault($variable, $default);
    }

    /**
     * Returns the requirements.
     *
     * @return array The requirements
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * Sets the requirements.
     *
     * @param array $requirements The requirements
     *
     * @return $this
     */
    public function setRequirements(array $requirements): self
    {
        $this->requirements = [];

        return $this->addRequirements($requirements);
    }

    /**
     * Adds requirements.
     *
     * @param array $requirements The requirements
     *
     * @return $this
     */
    public function addRequirements(array $requirements): self
    {
        // TODO : lever une exception si la key et le $regex ne sont pas des strings !!!!!
        /*
        if (! is_string($regex)) {
            throw new InvalidArgumentException(sprintf('Routing requirement for "%s" must be a string.', $key));
        }*/

        foreach ($requirements as $key => $regex) {
            $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        }

        return $this;
    }

    /**
     * Returns the requirement for the given key.
     *
     * @param string $key The key
     *
     * @return string|null The regex or null when not given
     */
    public function getRequirement(string $key): ?string
    {
        return $this->requirements[$key] ?? null;
    }

    /**
     * Sets a requirement for the given key.
     *
     * @param string $key   The key
     * @param string $regex The regex
     *
     * @return $this
     */
    public function setRequirement(string $key, string $regex): self
    {
        $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);

        return $this;
    }

    /**
     * Checks if a requirement is set for the given key.
     *
     * @param string $key A variable name
     *
     * @return bool true if a requirement is specified, false otherwise
     */
    public function hasRequirement(string $key): bool
    {
        return array_key_exists($key, $this->requirements);
    }

    // TODO : avoir la possibilité de passer un tableau ? si on détecte que c'est un is_array dans le getargs() on appel la méthode addReqirements() pour un tableau, sinon on appel setRequirement()
    public function assert(string $key, string $regex): self
    {
        return $this->setRequirement($key, $regex);
    }

    // remove the char "^" at the start of the regex, and the final "$" char at the end of the regex
    private function sanitizeRequirement(string $key, string $regex): string
    {
        if ('' !== $regex && '^' === $regex[0]) {
            $regex = substr($regex, 1); // returns false for a single character
        }
        if ('$' === substr($regex, -1)) {
            $regex = substr($regex, 0, -1);
        }
        if ('' === $regex) {
            throw new InvalidArgumentException(sprintf('Routing requirement for "%s" cannot be empty.', $key));
        }

        return $regex;
    }

    /**
     * Get the route name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the route name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Alia function for "setName()".
     *
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name): self
    {
        return $this->setName($name);
    }

    /**
     * Get supported HTTP method(s).
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return array_unique($this->methods);
    }

    /**
     * Set supported HTTP method(s).
     *
     * @param array
     *
     * @return self
     */
    public function setAllowedMethods(array $methods): self
    {
        $this->methods = $this->validateHttpMethods($methods);

        return $this;
    }

    /*
    public function method(string $method, string ...$methods): self
    {
        array_unshift($methods, $method);

        return $this->setAllowedMethods($methods);
    }*/

    /**
     * Alia function for "setAllowedMethods()".
     *
     * @param string|array ...$middleware
     */
    // TODO : faire plutot des méthodes : getMethods() et setMethods()
    // TODO : à renommer en allows() ????
    public function method(...$methods): self
    {
        //$methods = is_array($methods[0]) ? $methods[0] : $methods;

        // Allow passing arrays of methods or individual lists of methods
        if (isset($methods[0])
            && is_array($methods[0])
            && count($methods) === 1
        ) {
            //$methods = array_shift($methods);
            $methods = $methods[0];
        }

        return $this->setAllowedMethods($methods);
    }

    /**
     * Validate the provided HTTP method names.
     *
     * Validates, and then normalizes to upper case.
     *
     * @param string[] An array of HTTP method names.
     *
     * @throws Exception InvalidArgumentException for any invalid method names.
     *
     * @return string[]
     */
    // TODO : regarder aussi ici pour une méthode de vérification : https://github.com/cakephp/cakephp/blob/master/src/Routing/Route/Route.php#L197
    private function validateHttpMethods(array $methods): array
    {
        if (empty($methods)) {
            throw new InvalidArgumentException(
                'HTTP methods argument was empty; must contain at least one method'
            );
        }
        if (false === array_reduce($methods, function ($valid, $method) {
            if (false === $valid) {
                return false;
            }
            if (! is_string($method)) {
                return false;
            }
            //if (! preg_match('/^[!#$%&\'*+.^_`\|~0-9a-z-]+$/i', $method)) {
            if (! preg_match("/^[!#$%&'*+.^_`|~0-9a-z-]+$/i", $method)) {
                return false;
            }

            return $valid;
        }, true)) {
            throw new InvalidArgumentException('One or more HTTP methods were invalid');
        }

        return array_map('strtoupper', $methods);
    }
}