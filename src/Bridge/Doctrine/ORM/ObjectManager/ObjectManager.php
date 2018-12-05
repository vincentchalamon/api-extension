<?php

declare(strict_types=1);

namespace ApiExtension\Bridge\Doctrine\ORM\ObjectManager;

use ApiExtension\Guesser\GuesserInterface;
use ApiExtension\ObjectManager\ObjectManagerInterface;
use ApiExtension\PropertyExtractor\PropertyExtractorInterface;
use ApiExtension\Transformer\TransformerInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ObjectManager implements ObjectManagerInterface
{
    private $registry;
    private $guesser;
    private $transformer;
    private $propertyInfoExtractor;
    private $propertyExtractor;

    public function __construct(ManagerRegistry $registry, GuesserInterface $guesser, TransformerInterface $transformer, PropertyInfoExtractorInterface $propertyInfoExtractor, PropertyExtractorInterface $propertyExtractor)
    {
        $this->registry = $registry;
        $this->guesser = $guesser;
        $this->transformer = $transformer;
        $this->propertyInfoExtractor = $propertyInfoExtractor;
        $this->propertyExtractor = $propertyExtractor;
    }

    public function getIdentifiers(\ReflectionClass $reflectionClass, string $value = null): array
    {
        $object = $this->transformer->toObject([
            'targetEntity' => $reflectionClass->name,
            'type' => ClassMetadataInfo::ONE_TO_ONE,
        ], $value);
        if (null === $object) {
            throw new EntityNotFoundException('Unable to find any existing object of class '.$reflectionClass);
        }
        $classMetadata = $this->registry->getManagerForClass($reflectionClass)->getClassMetadata($reflectionClass);

        return \array_combine($classMetadata->getIdentifierFieldNames(), $classMetadata->getIdentifierValues($object));
    }

    public function fake(\ReflectionClass $reflectionClass, array $values = []): object
    {
        $className = $reflectionClass->name;

        // Complete required properties & init object
        $object = $reflectionClass->newInstance();
        $em = $this->registry->getManagerForClass($className);
        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata = $em->getClassMetadata($className);
        $mapping = $this->getMapping($classMetadata);
        foreach (\array_merge($classMetadata->getFieldNames(), $classMetadata->getAssociationNames()) as $property) {
            // Property is already filled, or is not required, or a primary key (except for association primary key)
            if (\array_key_exists($property, $values) || $mapping[$property]['nullable'] || ($classMetadata->isIdentifier($property) && $classMetadata->hasField($property))) {
                continue;
            }
            if ($reflectionClass->hasProperty($property)) {
                $reflectionProperty = $reflectionClass->getProperty($property);
                $reflectionProperty->setAccessible(true);
                if ($reflectionProperty->getValue($object)) {
                    continue;
                }
            }
            $values[$property] = $this->guesser->getValue([
                'name' => $property,
                'type' => $mapping[$property]['type'],
                'targetEntity' => $mapping[$property]['targetEntity'],
            ]);
        }

        // Parse values
        foreach ($values as $property => $value) {
            $value = $this->transformer->toObject([
                'name' => $property,
                'type' => $mapping[$property]['type'],
                'targetEntity' => $mapping[$property]['targetEntity'],
            ], $value);
            if ($reflectionClass->hasMethod($property)) {
                \call_user_func([$object, $property], $value);
            } elseif ($reflectionClass->hasMethod('set'.Inflector::camelize($property))) {
                \call_user_func([$object, 'set'.Inflector::camelize($property)], $value);
            } elseif ($reflectionClass->hasProperty($property)) {
                $reflectionProperty = $reflectionClass->getProperty($property);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            } else {
                throw new InvalidPropertyException(sprintf('Property or method "%s" does not exist in class %s.', $property, $className));
            }
        }

        if (\array_intersect(\array_keys($values), $classMetadata->getIdentifierFieldNames())) {
            $idGenerator = $classMetadata->idGenerator;
            $classMetadata->setIdGenerator(new AssignedGenerator());
            $generatorType = $classMetadata->generatorType;
            $classMetadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        }
        $em->persist($object);
        $em->flush();
        $em->clear();
        if (isset($idGenerator) && isset($generatorType)) {
            $classMetadata->setIdGenerator($idGenerator);
            $classMetadata->setIdGeneratorType($generatorType);
        }

        return $object;
    }

    public function getRequestData(\ReflectionClass $reflectionClass, array $values = [], array $context = []): array
    {
        // Complete required properties
        foreach ($this->propertyExtractor->getProperties($reflectionClass, $context) as $property) {
            if (\array_key_exists($property, $values)) {
                continue;
            }
            $values[$property] = $this->guesser->getValue($context);
        }

        // Parse values
        foreach ($values as $property => $value) {
            $values[$property] = $this->transformer->toScalar($context, $value);
        }
        $this->registry->getManager()->clear();

        return $values;
    }

    public function supports(\ReflectionClass $reflectionClass): bool
    {
        if (null === ($em = $this->registry->getManagerForClass($reflectionClass->name))) {
            return false;
        }

        return $em->getClassMetadata($reflectionClass->name) instanceof ClassMetadataInfo;
    }

    private function getMapping(ClassMetadataInfo $classMetadata): array
    {
        $className = $classMetadata->getName();
        $mapping = [];
        foreach ($classMetadata->getAssociationMappings() as $name => $mapping) {
            $mapping[$name] = $mapping + [
                    'nullable' => $mapping['joinColumns'][0]['nullable'] ?? true,
                ];
        }
        foreach ($classMetadata->getFieldNames() as $name) {
            $mapping[$name] = $classMetadata->getFieldMapping($name);
        }
        foreach ($this->propertyInfoExtractor->getProperties($className) as $name) {
            if (\array_key_exists($name, $mapping)) {
                continue;
            }

            /** @var Type $type */
            $types = $this->propertyInfoExtractor->getTypes($className, $name);
            if (!$types) {
                throw new \RuntimeException(\sprintf('Cannot get type of field %s.%s', $className, $name));
            }
            $mapping[$name] = [
                'type' => $types[0]->getBuiltinType(),
                'nullable' => $types[0]->isNullable(),
                'targetEntity' => $types[0]->getClassName(),
            ];
        }
        foreach ($mapping as $name => $fieldMapping) {
            $mapping[$name] = $fieldMapping + [
                    'type' => null,
                    'name' => $name,
                    'nullable' => true,
                    'scale' => 0,
                    'length' => null,
                    'precision' => 0,
                    'targetEntity' => null,
                ];
        }

        return $mapping;
    }
}
