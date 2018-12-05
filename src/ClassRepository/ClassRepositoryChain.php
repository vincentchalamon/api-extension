<?php

declare(strict_types=1);

namespace ApiExtension\ClassRepository;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassRepositoryChain implements ClassRepositoryInterface
{
    /**
     * @var ClassRepositoryInterface[]
     */
    private $objects;

    public function __construct(array $objects)
    {
        $this->objects = $objects;
    }

    public function getReflectionClass(string $alias): \ReflectionClass
    {
        foreach ($this->objects as $object) {
            try {
                return $object->getReflectionClass($alias);
            } catch (ClassRepositoryNotFoundException $exception) {
                continue;
            }
        }

        throw new ClassRepositoryNotFoundException();
    }
}
