<?php

declare(strict_types=1);

namespace ApiExtension\Routing;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class RouterChain implements RouterInterface
{
    /**
     * @var RouterInterface[]
     */
    private $routers;

    public function __construct(array $routers)
    {
        $this->routers = $routers;
    }

    public function getCollectionUri(\ReflectionClass $reflectionClass): string
    {
        foreach ($this->routers as $router) {
            if ($router->supports($reflectionClass)) {
                return $router->getCollectionUri($reflectionClass);
            }
        }

        throw new RouterNotFoundException();
    }

    public function getItemUri(\ReflectionClass $reflectionClass, array $identifiers): string
    {
        foreach ($this->routers as $router) {
            if ($router->supports($reflectionClass)) {
                return $router->getItemUri($reflectionClass, $identifiers);
            }
        }

        throw new RouterNotFoundException();
    }

    public function supports(\ReflectionClass $reflectionClass): bool
    {
        return true;
    }
}
