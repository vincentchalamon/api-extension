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

namespace ApiExtension\SchemaGenerator\TypeGenerator;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityTypeGenerator implements TypeGeneratorInterface
{
    /**
     * @var PropertyInfoExtractorInterface
     */
    private $propertyInfo;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function setPropertyInfo(PropertyInfoExtractorInterface $propertyInfo)
    {
        $this->propertyInfo = $propertyInfo;
    }

    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function supports(array $context): bool
    {
        return null !== $context['targetEntity'] && \in_array($context['type'], [ClassMetadataInfo::ONE_TO_ONE, ClassMetadataInfo::MANY_TO_ONE], true);
    }

    public function generate(array $context): array
    {
        $reflectionClass = new \ReflectionClass($context['targetEntity']);
        if (0 < \count($this->propertyInfo->getProperties($context['targetEntity'], $context))) {
            $type = $this->container->get('schemaGenerator')->generate($reflectionClass, $context);
        } elseif ($this->reader->getClassAnnotation($reflectionClass, ApiResource::class)) {
            $type = [
                'type' => ['string'],
                // str_replace('{id}', '[\\w-;=]+', urldecode($this->getItemUri($reflectionClass, ['id' => '{id}'])))
                'pattern' => $this->container->get('helper')->getItemUriPattern(new \ReflectionClass($context['targetEntity'])),
            ];
        } else {
            throw new \LogicException('Entity '.$context['targetEntity'].' does not have any property to serialize with following groups: '.implode(', ', $context['serializer_groups']));
        }
        if ($context['nullable'] ?? true) {
            if (!\is_array($type['type'])) {
                $type['type'] = [$type['type']];
            }
            $type['type'][] = 'null';
            $type['type'] = array_unique($type['type']);
        }

        return $type;
    }
}
