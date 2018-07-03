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

use ApiExtension\Exception\MaxDepthException;
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

    const MAX_DEPTH = 5;

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
    private $path = [];

    public function __construct(ApiHelper $helper, Populator $populator, TypeGeneratorInterface $typeGenerator)
    {
        $this->helper = $helper;
        $this->populator = $populator;
        $this->typeGenerator = $typeGenerator;
    }

    public function setMetadataFactory(ResourceMetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function setPropertyInfo(PropertyInfoExtractorInterface $propertyInfo)
    {
        $this->propertyInfo = $propertyInfo;
    }

    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function supports(\ReflectionClass $reflectionClass, array $context = []): bool
    {
        return false === ($context['collection'] ?? false) && false === ($context['root'] ?? false);
    }

    public function generate(\ReflectionClass $reflectionClass, array $context = []): array
    {
        if (empty($context['depth'])) {
            $context['depth'] = 0;
        }
        if ($context['depth'] > self::MAX_DEPTH) {
            throw new MaxDepthException(sprintf('Maximum depth of %d has been reached. This could be caused by a circular reference due to serialization groups.%sPath: %s', self::MAX_DEPTH, PHP_EOL, implode('->', $this->path)));
        }
        ++$context['depth'];
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
        $classProperties = $this->propertyInfo->getProperties($className, $context);

        // On get collection ($context['depth'] = 1), we still need an object to be returned
        // even if there is no class properties
        if (empty($classProperties) && 1 < $context['depth']) {
            return [
                'type' => 'string',
                'pattern' => sprintf('^%s$', $this->helper->getItemUriPattern($reflectionClass)),
            ];
        }

        foreach ($classProperties as $property) {
            $mapping = $this->populator->getMapping($classMetadata, $property);
            // Prevent infinite loop & circular references
            if (!empty($mapping['targetEntity'])) {
                $this->path[$context['depth']] = $property;
            }
            if (($mapping['targetEntity'] ?? null) === $reflectionClass->getName()) {
                // todo Is there a better way to handle this case?
                $schema['properties'][$property] = $this->typeGenerator->generate($property, $mapping, ['serializer_groups' => []] + $context);
            } else {
                $schema['properties'][$property] = $this->typeGenerator->generate($property, $mapping, $context);
            }
            unset($this->path[$context['depth']]);
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
