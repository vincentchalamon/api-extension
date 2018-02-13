<?php

declare(strict_types=1);

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiExtension\Helper;

use ApiExtension\Exception\EntityNotFoundException;
use ApiExtension\Exception\InvalidPropertyException;
use ApiExtension\Populator\Guesser\GuesserInterface;
use ApiExtension\Transformer\TransformerInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ApiHelper
{
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var PropertyInfoExtractorInterface
     */
    private $propertyInfo;
    private $guesser;
    private $transformer;

    public function __construct(TransformerInterface $transformer, GuesserInterface $guesser)
    {
        $this->transformer = $transformer;
        $this->guesser = $guesser;
    }

    public function setIriConverter(IriConverterInterface $iriConverter): void
    {
        $this->iriConverter = $iriConverter;
    }

    public function setRegistry(ManagerRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function setPropertyInfo(PropertyInfoExtractorInterface $propertyInfo): void
    {
        $this->propertyInfo = $propertyInfo;
    }

    public function getUri(\ReflectionClass $reflectionClass): string
    {
        return $this->iriConverter->getIriFromResourceClass($reflectionClass->getName());
    }

    public function getItemUri(\ReflectionClass $reflectionClass, ?array $ids = null): string
    {
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass($reflectionClass->getName());
        $classMetadata = $em->getClassMetadata($reflectionClass->getName());
        if (null === $ids) {
            if (null === ($object = $em->getRepository($reflectionClass->getName())->findOneBy([]))) {
                throw new EntityNotFoundException('Unable to find an existing object of class '.$reflectionClass->getName());
            }
            $ids = array_combine($classMetadata->getIdentifierFieldNames(), $classMetadata->getIdentifierValues($object));
        }

        return $this->iriConverter->getItemIriFromResourceClass($reflectionClass->getName(), $ids);
    }

    public function getItemUriPattern(\ReflectionClass $reflectionClass): string
    {
        $classMetadata = $this->registry->getManagerForClass($reflectionClass->getName())->getClassMetadata($reflectionClass->getName());
        $identifiers = $classMetadata->getIdentifierFieldNames();

        return str_replace('{id}', '[\\w-]+', urldecode($this->getItemUri($reflectionClass, array_combine($identifiers, array_fill(0, count($identifiers), '{id}')))));
    }

    public function getReflectionClass(string $name): \ReflectionClass
    {
        $allClasses = array_map(function (ClassMetadataInfo $metadata) {
            return $metadata->getReflectionClass();
        }, $this->registry->getManager()->getMetadataFactory()->getAllMetadata());
        $clearName = strtolower(preg_replace('/[ \-\_]/', '', $name));
        foreach (array_unique([Inflector::singularize($clearName), Inflector::singularize($clearName), $clearName]) as $result) {
            $classes = array_filter($allClasses, function (\ReflectionClass $reflectionClass) use ($result) {
                return strtolower($result) === strtolower($reflectionClass->getShortName());
            });
            if (count($classes)) {
                return array_shift($classes);
            }
        }

        throw new EntityNotFoundException(sprintf('Unable to find an entity corresponding to name "%s"', $name));
    }

    public function getMapping(string $className, string $property): array
    {
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        if ($classMetadata->hasField($property)) {
            $mapping = $classMetadata->getFieldMapping($property);
        } elseif ($classMetadata->hasAssociation($property)) {
            $mapping = $classMetadata->getAssociationMapping($property);
            $mapping['nullable'] = $mapping['joinColumns'][0]['nullable'] ?? true;
        } else {
            /** @var Type $type */
            $type = $this->propertyInfo->getTypes($className, $property)[0];
            if (null === $type) {
                throw new \RuntimeException('Cannot get type of field '.$className.'.'.$property);
            }
            $mapping = [
                'type' => $type->getBuiltinType(),
                'nullable' => $type->isNullable(),
                'targetEntity' => $type->getClassName(),
            ];
        }

        return $mapping + [
            'type' => null,
            'fieldName' => $property,
            'nullable' => false,
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

    public function getAllMappings(string $className): array
    {
        $mappings = [];
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        foreach ($classMetadata->getFieldNames() as $name) {
            if ($classMetadata->isIdentifier($name)) {
                continue;
            }
            $mappings[$name] = $this->getMapping($className, $name);
        }
        foreach ($classMetadata->getAssociationNames() as $name) {
            $mappings[$name] = $this->getMapping($className, $name);
        }

        return $mappings;
    }

    public function createObject(\ReflectionClass $reflectionClass, array $values = [])
    {
        $object = $reflectionClass->newInstance();
        foreach ($this->getAllMappings($reflectionClass->getName()) as $property => $mapping) {
            if (!isset($values[$property]) && false === $mapping['nullable']) {
                $values[$property] = $this->guesser->getValue($mapping);
            }
        }
        foreach ($values as $property => $value) {
            $value = $this->transformer->transform($property, $this->getMapping($reflectionClass->getName(), $property), $value);
            if ($reflectionClass->hasMethod($property)) {
                call_user_func([$object, $property], $value);
            } elseif ($reflectionClass->hasMethod('set'.Inflector::camelize($property))) {
                call_user_func([$object, 'set'.Inflector::camelize($property)], $value);
            } elseif ($reflectionClass->hasProperty($property)) {
                $reflectionProperty = $reflectionClass->getProperty($property);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            } else {
                throw new InvalidPropertyException(sprintf('Property %s does not exist in class %s.', $property, $reflectionClass->getName()));
            }
        }

        return $object;
    }
}
