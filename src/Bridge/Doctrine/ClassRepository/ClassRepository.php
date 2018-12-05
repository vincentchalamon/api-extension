<?php

declare(strict_types=1);

namespace ApiExtension\Bridge\Doctrine\ClassRepository;

use ApiExtension\ClassRepository\ClassRepositoryNotFoundException;
use ApiExtension\ClassRepository\ClassRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassRepository implements ClassRepositoryInterface
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getReflectionClass(string $alias): \ReflectionClass
    {
        /** @var \ReflectionClass[] $allClasses */
        $allClasses = \array_map(function (ClassMetadata $metadata) {
            return $metadata->getReflectionClass();
        }, \array_merge_recursive(...\array_map(function (ObjectManager $manager) {
            return $manager->getMetadataFactory()->getAllMetadata();
        }, $this->registry->getManagers())));

        $classes = \array_filter($allClasses, function (\ReflectionClass $reflectionClass) use ($alias) {
            return \strtolower($alias) === \strtolower($reflectionClass->getShortName());
        });

        if (0 < \count($classes)) {
            return \array_shift($classes);
        }

        throw new ClassRepositoryNotFoundException();
    }
}
