<?php

/*
 * This file is part of the API Extension project.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Populator;

use ApiExtension\Exception\InvalidPropertyException;
use ApiExtension\Populator\Guesser\GuesserInterface;
use ApiExtension\Transformer\TransformerInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Populator
{
    /**
     * @var ResourceMetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var PropertyInfoExtractorInterface
     */
    private $propertyInfo;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var ManagerRegistry
     */
    private $registry;
    private $guesser;
    private $transformer;
    private $mapping = [];

    public function __construct(GuesserInterface $guesser, TransformerInterface $transformer)
    {
        $this->guesser = $guesser;
        $this->transformer = $transformer;
    }

    public function setMetadataFactory(ResourceMetadataFactoryInterface $metadataFactory): void
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function setPropertyInfo(PropertyInfoExtractorInterface $propertyInfo): void
    {
        $this->propertyInfo = $propertyInfo;
    }

    public function setAnnotationReader(Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    public function setRegistry(ManagerRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function getObject(\ReflectionClass $reflectionClass, array $values = [])
    {
        $className = $reflectionClass->getName();

        // Complete required properties
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        foreach (array_merge($classMetadata->getFieldNames(), $classMetadata->getAssociationNames()) as $property) {
            $mapping = $this->getMapping($classMetadata, $property);
            // Property is already filled, or is not required, or a primary key (except for association primary key)
            if (array_key_exists($property, $values) || $mapping['nullable'] || ($classMetadata->isIdentifier($property) && $classMetadata->hasField($property))) {
                continue;
            }
            $values[$property] = $this->guesser->getValue($mapping);
        }

        // Parse values & init object
        $object = $reflectionClass->newInstance();
        foreach ($values as $property => $value) {
            $value = $this->transformer->toObject($this->getMapping($classMetadata, $property), $value);
            if ($reflectionClass->hasMethod($property)) {
                call_user_func([$object, $property], $value);
            } elseif ($reflectionClass->hasMethod('set'.Inflector::camelize($property))) {
                call_user_func([$object, 'set'.Inflector::camelize($property)], $value);
            } elseif ($reflectionClass->hasProperty($property)) {
                $reflectionProperty = $reflectionClass->getProperty($property);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            } else {
                throw new InvalidPropertyException(sprintf('Property %s does not exist in class %s.', $property, $className));
            }
        }

        return $object;
    }

    public function getRequestData(\ReflectionClass $reflectionClass, string $operation, array $values = []): array
    {
        $className = $reflectionClass->getName();

        // Get serialization groups
        $resourceMetadata = $this->metadataFactory->create($className);
        $collectionOperations = $this->filterOperations($resourceMetadata->getCollectionOperations() ?: ['get', 'post'], $operation);
        $itemOperations = $this->filterOperations($resourceMetadata->getItemOperations() ?: ['get', 'put', 'delete'], $operation);
        if (0 < count($itemOperations)) {
            $methodName = 'getItemOperationAttribute';
        } else {
            $methodName = 'getCollectionOperationAttribute';
        }
        $groups = call_user_func([$resourceMetadata, $methodName], $operation, 'denormalization_context', [], true)['groups'] ?? [];
        $validationGroups = call_user_func([$resourceMetadata, $methodName], $operation, 'validation_groups', ['Default'], true);
        $originalValues = $values;

        // Complete required properties
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        foreach ($this->propertyInfo->getProperties($className, ['serializer_groups' => $groups ?? []]) as $property) {
            if (!$this->isRequired($reflectionClass->getProperty($property), $validationGroups) || array_key_exists($property, $values) || ('put' === $operation && 0 < count($originalValues))) {
                continue;
            }
            $values[$property] = $this->guesser->getValue($this->getMapping($classMetadata, $property));
        }

        // Parse values
        foreach ($values as $property => $value) {
            $values[$property] = $this->transformer->toScalar($this->getMapping($classMetadata, $property), $value);
        }
        $this->registry->getManager()->clear();

        return $values;
    }

    public function getMapping(ClassMetadataInfo $classMetadata, string $property): array
    {
        $className = $classMetadata->getName();
        if (isset($this->mapping[$className])) {
            if (isset($this->mapping[$className][$property])) {
                return $this->mapping[$className][$property];
            }
            throw new InvalidPropertyException(sprintf('Property %s does not exist in class %s.', $property, $className));
        }

        $this->mapping[$className] = [];
        foreach ($classMetadata->getAssociationMappings() as $name => $mapping) {
            $this->mapping[$className][$name] = $mapping + [
                    'nullable' => $mapping['joinColumns'][0]['nullable'] ?? true,
                ];
        }
        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $this->mapping[$className][$fieldName] = $classMetadata->getFieldMapping($fieldName);
        }
        foreach ($this->propertyInfo->getProperties($className) as $fieldName) {
            if (array_key_exists($fieldName, $this->mapping[$className])) {
                continue;
            }

            /** @var Type $type */
            $types = $this->propertyInfo->getTypes($className, $fieldName);
            if (!$types) {
                throw new \RuntimeException(sprintf('Cannot get type of field %s.%s', $className, $fieldName));
            }
            $this->mapping[$className][$fieldName] = [
                'type' => $types[0]->getBuiltinType(),
                'nullable' => $types[0]->isNullable(),
                'targetEntity' => $types[0]->getClassName(),
            ];
        }
        foreach ($this->mapping[$className] as $fieldName => $mapping) {
            $this->mapping[$className][$fieldName] = $mapping + [
                    'type' => null,
                    'fieldName' => $fieldName,
                    'nullable' => true,
                    'scale' => 0,
                    'length' => null,
                    'unique' => false,
                    'precision' => 0,
                    'columnName' => null,
                    'mappedBy' => null,
                    'targetEntity' => null,
                    'cascade' => [],
                    'orphanRemoval' => false,
                    'fetch' => null,
                    'inversedBy' => null,
                    'isOwningSide' => false,
                    'sourceEntity' => null,
                    'isCascadeRemove' => false,
                    'isCascadePersist' => false,
                    'isCascadeRefresh' => false,
                    'isCascadeMerge' => false,
                    'isCascadeDetach' => false,
                ];
        }

        return $this->getMapping($classMetadata, $property);
    }

    private function isRequired(\ReflectionProperty $reflectionProperty, $groups): bool
    {
        /** @var ClassMetadataInfo $classMetadata */
        $className = $reflectionProperty->getDeclaringClass()->getName();
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);

        foreach ([NotBlank::class, NotNull::class, Count::class] as $class) {
            $annotation = $this->annotationReader->getPropertyAnnotation($reflectionProperty, $class);
            if ($annotation && 0 < count(array_intersect($annotation->groups, $groups))) {
                return true;
            }
        }

        return !($this->getMapping($classMetadata, $reflectionProperty->getName())['nullable'] ?? false);
    }

    private function filterOperations(array $operations, string $operation): array
    {
        return array_filter($operations, function ($value, $key) use ($operation) {
            return $operation === (is_int($key) ? $value : $key);
        }, ARRAY_FILTER_USE_BOTH);
    }
}
