<?php

declare(strict_types=1);

namespace ApiExtension\ObjectManager;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ObjectManagerChain implements ObjectManagerInterface
{
    /**
     * @var ObjectManagerInterface[]
     */
    private $objects;

    public function __construct(array $objects)
    {
        $this->objects = $objects;
    }

    public function getIdentifiers(\ReflectionClass $reflectionClass, string $value = null): array
    {
        foreach ($this->objects as $object) {
            if ($object->supports($reflectionClass)) {
                return $object->getIdentifiers($reflectionClass, $value);
            }
        }

        throw new ObjectManagerNotFoundException();
    }

    public function fake(\ReflectionClass $reflectionClass, array $values = []): object
    {
        foreach ($this->objects as $object) {
            if ($object->supports($reflectionClass)) {
                return $object->fake($reflectionClass, $values);
            }
        }

        throw new ObjectManagerNotFoundException();
    }

    public function getRequestData(\ReflectionClass $reflectionClass, array $values = []): array
    {
        foreach ($this->objects as $object) {
            if ($object->supports($reflectionClass)) {
                return $object->getRequestData($reflectionClass, $values);
            }
        }

        throw new ObjectManagerNotFoundException();
    }

    public function supports(\ReflectionClass $reflectionClass): bool
    {
        return true;
    }
}
