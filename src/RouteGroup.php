<?php

declare(strict_types=1);

namespace Chiron\Router;

use Chiron\Router\Traits\MiddlewareAwareInterface;
use Chiron\Router\Traits\MiddlewareAwareTrait;
use Chiron\Router\Traits\RouteCollectionInterface;
use Chiron\Router\Traits\RouteCollectionTrait;
use Chiron\Router\Traits\RouteConditionHandlerInterface;
use Chiron\Router\Traits\RouteConditionHandlerTrait;

class RouteGroup implements MiddlewareAwareInterface, RouteCollectionInterface, RouteConditionHandlerInterface
{
    use MiddlewareAwareTrait;
    use RouteCollectionTrait;
    use RouteConditionHandlerTrait;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var \RouteCollectionInterface
     */
    protected $collection;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param string                   $prefix
     * @param callable                 $callback
     * @param RouteCollectionInterface $collection
     */
    // TODO : vérifier si on pas plutot utiliser un Closure au lieu d'un callable pour le typehint
    // TODO : permettre de passer un callback null, dans ce cas on initialisera avec une fonction vide !!! il faudrait que cela soit géré dans la fonction "group" de la routeCollecitonInterface
    // TODO : passer en paramétre un RouteCollectorInterface et non pas un RouteCollectionInterface. non ????
    public function __construct(string $prefix, callable $callback, RouteCollectionInterface $collection)
    {
        $this->callback = $callback;
        $this->collection = $collection;
        $this->prefix = sprintf('/%s', ltrim($prefix, '/'));
    }

    /**
     * Return the prefix of the group.
     *
     * @return string
     */
    // TODO : vérifier l'utilité de cette méthode !!!! de ma vision elle ne sert à rien !!!!
    /*
    public function getPrefix(): string
    {
        return $this->prefix;
    }*/

    /**
     * {@inheritdoc}
     */
    public function map(string $path, $handler): Route
    {
        $path = ($path === '/') ? $this->prefix : rtrim($this->prefix, '/') . sprintf('/%s', ltrim($path, '/'));

        $route = $this->collection->map($path, $handler);

        // TODO : on devrait vérifier si la route a un host/scheme ou port on ne doit pas écraser ces valeurs avec les valeurs du group !!!! non ?????
        if ($host = $this->getHost()) {
            $route->setHost($host);
        }
        if ($scheme = $this->getScheme()) {
            $route->setScheme($scheme);
        }
        if ($port = $this->getPort()) {
            $route->setPort($port);
        }

        if ($middlewares = $this->getMiddlewareStack()) {
            $route->middleware($middlewares);
        }

        return $route;
    }

    // TODO : vérifier si on pas plutot utiliser un Closure au lieu d'un callable pour le typehint.
    // TODO : il semble pôssible dans Slim de passer une string, ou un callable. Vérifier l'utilité de cette possibilité d'avoir un string !!!!
    public function group(string $prefix, callable $callback): RouteGroup
    {
        // TODO : vérifier si on doit pas utiliser un code comme ca : https://github.com/illuminate/routing/blob/master/RouteGroup.php#L58   =>    isset($new['prefix']) ? trim($old, '/').'/'.trim($new['prefix'], '/') : $old;
        // TODO : ou utiliser ce code : https://github.com/illuminate/routing/blob/master/Router.php#L560
        $prefix = ($prefix === '/') ? $this->prefix : rtrim($this->prefix, '/') . sprintf('/%s', ltrim($prefix, '/'));

        $group = $this->collection->group($prefix, $callback);

        // TODO : je pense que ce bout de code ne servira plus à rien si dans la classe Router on invoke() le group directement dans la méthode d'ajout au group ->group() executerai donc le invoke plutot que de le faire à la fin avec la méthode processGroup !!!
        // in cases of group of groups, we need to persist the settings from the previous group in the new one.
        if ($host = $this->getHost()) {
            $group->setHost($host);
        }
        if ($scheme = $this->getScheme()) {
            $group->setScheme($scheme);
        }
        if ($port = $this->getPort()) {
            $group->setPort($port);
        }

        // merge all the previous group middlewares in this last group.
        // TODO : créer une méthode setMiddlewareStack(array $middlewares) dans la classe MiddlewareAwareTrait.php pour remplacer le tableau de middleware ??? non ???
        // TODO : vérifier si il n'y a pas un bug, on dirait qu'on va ajouter au $group des middleware qu'il posséde déjà !!!! ou alors c'est que l'objet group nouvellement créé n'a pas de middleware mais dans ce cas c'est la méthode array_merge qui ne sert à rien !!!!
        if ($middlewares = $this->getMiddlewareStack()) {
            //$group->middleware(array_merge($middlewares, $group->getMiddlewareStack()));
            $group->middleware($middlewares);
        }

        return $group;
    }

    /**
     * Process the group and ensure routes are added to the collection.
     */
    // TODO : regarder aussi ici : https://github.com/slimphp/Slim/blob/3.x/Slim/RouteGroup.php#L38
    // TODO : créer plutot une méthode collectRoutes() qui rempplacerai le invoke et qui retournerai $this
    public function __invoke(): void
    {
        // TODO : voir si on fait un bind sur $this
        //call_user_func_array($this->callback->bindTo($this), [$this]);
        ($this->callback)($this);
    }

    /*
        public function collectRoutes(): RouteGroupInterface
        {
            $callable = $this->callableResolver->resolve($this->callable);
            $callable($this->routeCollectorProxy);
            return $this;
        }*/
}
