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

use ApiExtension\Helper\ApiHelper;
use ApiExtension\Populator\Guesser\GuesserInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
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
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * @var Reader
     */
    private $annotationReader;
    private $guesser;
    private $helper;

    public function __construct(GuesserInterface $guesser, ApiHelper $helper)
    {
        $this->guesser = $guesser;
        $this->helper = $helper;
    }

    public function setMetadataFactory(ResourceMetadataFactoryInterface $metadataFactory): void
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function setPropertyInfo(PropertyInfoExtractorInterface $propertyInfo): void
    {
        $this->propertyInfo = $propertyInfo;
    }

    public function setIriConverter(IriConverterInterface $iriConverter): void
    {
        $this->iriConverter = $iriConverter;
    }

    public function setAnnotationReader(Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    public function getData(\ReflectionClass $reflectionClass, string $apiResourceOperation, array $values = []): array
    {
        $className = $reflectionClass->getName();
        $resourceMetadata = $this->metadataFactory->create($className);
        if (in_array($apiResourceOperation, $resourceMetadata->getCollectionOperations())) {
            $methodName = 'getCollectionOperationAttribute';
        } elseif (in_array($apiResourceOperation, $resourceMetadata->getItemOperations())) {
            $methodName = 'getItemOperationAttribute';
        } else {
            throw new \LogicException('Unknown operation '.$apiResourceOperation.' on ApiResource '.$className);
        }
        $groups = call_user_func([$resourceMetadata, $methodName], $apiResourceOperation, 'denormalization_context', [], true)['groups'] ?? [];
        foreach ($this->propertyInfo->getProperties($className, ['serializer_groups' => $groups ?? []]) as $property) {
            $annotations = $this->annotationReader->getPropertyAnnotations($reflectionClass->getProperty($property));
            // Property is not required or already filled
            if (!array_intersect([NotBlank::class, NotNull::class], $annotations) || array_key_exists($property, $values)) {
                continue;
            }
            $data[$property] = $this->guesser->getValue($this->helper->getMapping($className, $property));
        }
        foreach ($data as $property => $value) {
            if (is_object($value)) {
                $data[$property] = $this->iriConverter->getIriFromItem($value);
            } elseif (is_array($value)) {
                foreach ($value as $key => $subValue) {
                    if (is_object($subValue)) {
                        $data[$property][$key] = $this->iriConverter->getIriFromItem($subValue);
                    }
                }
            }
            if ('boolean' === ($this->helper->getMapping($reflectionClass->getName(), $property)['type'] ?? null)) {
                $values[$property] = 'true' === $value;
            }
        }

        return $data;
    }
}
