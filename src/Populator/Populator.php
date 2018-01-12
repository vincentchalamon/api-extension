<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Populator;

use ApiExtension\Populator\Guesser\GuesserInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class Populator
{
    /**
     * @var ResourceMetadataFactoryInterface
     */
    private $resourceMetadataFactory;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var PropertyInfoExtractorInterface
     */
    private $propertyInfoExtractor;
    private $guesser;

    public function __construct(GuesserInterface $guesser)
    {
        $this->guesser = $guesser;
    }

    public function setResourceMetadataFactory(ResourceMetadataFactoryInterface $resourceMetadataFactory): void
    {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    public function setRegistry(ManagerRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function setPropertyInfoExtractor(PropertyInfoExtractorInterface $propertyInfoExtractor): void
    {
        $this->propertyInfoExtractor = $propertyInfoExtractor;
    }

    public function getData(\ReflectionClass $reflectionClass, string $method = 'post'): array
    {
        $data = [];
        $className = $reflectionClass->getName();
        $groups = $this->resourceMetadataFactory->create($className)->getCollectionOperationAttribute($method, 'denormalization_context', [], true)['groups'] ?? [];
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        foreach ($this->propertyInfoExtractor->getProperties($className, ['serializer_groups' => $groups]) as $property) {
            $data[$property] = $this->guesser->getValue($classMetadata->getFieldMapping($property) + ['type' => null, 'fieldName' => null]);
        }

        return $data;
    }
}
