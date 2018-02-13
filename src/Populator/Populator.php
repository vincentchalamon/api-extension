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

namespace ApiExtension\Populator;

use ApiExtension\Helper\ApiHelper;
use ApiExtension\Populator\Guesser\GuesserInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

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

    public function getData(\ReflectionClass $reflectionClass, string $method = 'post'): array
    {
        $data = [];
        $className = $reflectionClass->getName();
        $groups = $this->metadataFactory->create($className)->getCollectionOperationAttribute($method, 'denormalization_context', [], true)['groups'] ?? [];
        foreach ($this->propertyInfo->getProperties($className, ['serializer_groups' => $groups]) as $property) {
            // todo Filter with Assert\NotBlank Assert\NotNull
            $data[$property] = $this->guesser->getValue($this->helper->getMapping($className, $property));
            if (is_object($data[$property])) {
                $data[$property] = $this->iriConverter->getIriFromItem($data[$property]);
            } elseif (is_array($data[$property])) {
                foreach ($data[$property] as $key => $value) {
                    if (is_object($value)) {
                        $data[$property][$key] = $this->iriConverter->getIriFromItem($value);
                    }
                }
            }
        }

        return $data;
    }
}
