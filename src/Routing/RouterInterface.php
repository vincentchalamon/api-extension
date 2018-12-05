<?php

declare(strict_types=1);

namespace ApiExtension\Routing;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface RouterInterface
{
    public function getCollectionUri(\ReflectionClass $reflectionClass): string;

    public function getItemUri(\ReflectionClass $reflectionClass, array $identifiers): string;

    public function supports(\ReflectionClass $reflectionClass): bool;
}
