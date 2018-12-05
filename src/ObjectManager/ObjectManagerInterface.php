<?php

declare(strict_types=1);

namespace ApiExtension\ObjectManager;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ObjectManagerInterface
{
    public function getIdentifiers(\ReflectionClass $reflectionClass, string $value = null): array;

    public function fake(\ReflectionClass $reflectionClass, array $values = []): object;

    public function getRequestData(\ReflectionClass $reflectionClass, array $values = []): array;

    public function supports(\ReflectionClass $reflectionClass): bool;
}
