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

namespace ApiExtension\SchemaGenerator;

use ApiExtension\Helper\ApiHelper;
use ApiExtension\Populator\Populator;
use ApiExtension\SchemaGenerator\TypeGenerator\TypeGeneratorInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ObjectSchemaGenerator implements SchemaGeneratorInterface, SchemaGeneratorAwareInterface
{
    use SchemaGeneratorAwareTrait;

    /**
     * @var ResourceMetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var PropertyInfoExtractorInterface
     */
    private $propertyInfo;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var Reader
     */
    private $reader;
    private $helper;
    private $populator;
    private $typeGenerator;

    public function __construct(ApiHelper $helper, Populator $populator, TypeGeneratorInterface $typeGenerator)
    {
        $this->helper = $helper;
        $this->populator = $populator;
        $this->typeGenerator = $typeGenerator;
    }

    public function setMetadataFactory(ResourceMetadataFactoryInterface $metadataFactory): void
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function setPropertyInfo(PropertyInfoExtractorInterface $propertyInfo): void
    {
        $this->propertyInfo = $propertyInfo;
    }

    public function setRegistry(ManagerRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function setAnnotationReader(Reader $reader): void
    {
        $this->reader = $reader;
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return false === ($context['collection'] ?? false) && false === ($context['root'] ?? false);
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        $className = $reflectionClass->getName();
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];
        if ($this->reader->getClassAnnotation($reflectionClass, ApiResource::class)) {
            $schema['properties']['@id'] = [
                'type' => 'string',
                'pattern' => sprintf('^%s$', $this->helper->getItemUriPattern($reflectionClass)),
            ];
            $schema['properties']['@type'] = [
                'type' => 'string',
                'pattern' => sprintf('^%s$', $reflectionClass->getShortName()),
            ];
            $schema['required'][] = '@id';
            $schema['required'][] = '@type';
            $context = $context + [
                    'serializer_groups' => $this->metadataFactory->create($className)->getItemOperationAttribute('get', 'normalization_context', [], true)['groups'] ?? [],
                ];
        }

        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        foreach ($this->propertyInfo->getProperties($className, $context) as $property) {
            $mapping = $this->populator->getMapping($classMetadata, $property);
            // Prevent infinite loop
            if ($reflectionClass->getName() === ($mapping['targetEntity'] ?? null)) {
                // todo Is there a better way to handle this case?
                $schema['properties'][$property] = $this->typeGenerator->generate($property, $mapping, ['serializer_groups' => []] + $context);
            } else {
                $schema['properties'][$property] = $this->typeGenerator->generate($property, $mapping, $context);
            }
            if (false === ($mapping['nullable'] ?? true)) {
                $schema['required'][] = $property;
            } else {
                if (!is_array($schema['properties'][$property]['type'])) {
                    $schema['properties'][$property]['type'] = [$schema['properties'][$property]['type']];
                }
                $schema['properties'][$property]['type'][] = 'null';
                $schema['properties'][$property]['type'] = array_unique($schema['properties'][$property]['type']);
            }
            if (null !== ($description = $this->propertyInfo->getShortDescription($className, $property))) {
                $schema['properties'][$property]['description'] = $description;
            }
        }

        return $schema;
    }
}
