<?php

declare(strict_types=1);

namespace ApiExtension\PropertyExtractor;

use ApiExtension\Bridge\Doctrine\PropertyExtractor\PropertyExtractorInterface;
use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\Constraint\Count;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class PropertyExtractor implements PropertyExtractorInterface
{
    private $metadataFactory;
    private $propertyInfo;
    private $annotationReader;

    public function __construct(ResourceMetadataFactoryInterface $metadataFactory, PropertyInfoExtractorInterface $propertyInfo, AnnotationReader $annotationReader)
    {
        $this->metadataFactory = $metadataFactory;
        $this->propertyInfo = $propertyInfo;
        $this->annotationReader = $annotationReader;
    }

    public function getProperties(\ReflectionClass $reflectionClass, array $context = []): array
    {
        $className = $reflectionClass->name;

        $resourceMetadata = $this->metadataFactory->create($className);
        $itemOperations = \array_filter($resourceMetadata->getItemOperations() ?: ['get', 'put', 'delete'], function ($value, $key) use ($context) {
            return $context['operation'] === (\is_int($key) ? $value : $key);
        }, ARRAY_FILTER_USE_BOTH);
        if (0 < \count($itemOperations)) {
            $methodName = 'getItemOperationAttribute';
        } else {
            $methodName = 'getCollectionOperationAttribute';
        }
        $groups = \call_user_func([$resourceMetadata, $methodName], $context['operation'], 'denormalization_context', [], true)['groups'] ?? [];

        // Get properties
        $properties = $this->propertyInfo->getProperties($className, $groups ? ['serializer_groups' => $groups] : []);

        // Filter properties to keep required ones
        $validationGroups = \call_user_func([$resourceMetadata, $methodName], $context['operation'], 'validation_groups', ['Default'], true);
        if (\is_string($validationGroups)) {
            $validationGroups = [];
        }

        return \array_filter($properties, function ($property) use ($reflectionClass, $validationGroups) {
            return $this->isPropertyRequired($reflectionClass->getProperty($property), $validationGroups);
        });
    }

    private function isPropertyRequired(\ReflectionProperty $reflectionProperty, array $groups = [], array $context = []): bool
    {
        foreach ([NotBlank::class, NotNull::class, Count::class] as $class) {
            $annotation = $this->annotationReader->getPropertyAnnotation($reflectionProperty, $class);
            if ($annotation && 0 < \count(array_intersect($annotation->groups, $groups))) {
                return true;
            }
        }

        return !($context['nullable'] ?? false);
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        try {
            $this->metadataFactory->create($reflectionClass->name);

            return true;
        } catch (ResourceClassNotFoundException $exception) {
            return false;
        }
    }
}
