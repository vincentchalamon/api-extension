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

namespace ApiExtension\Helper;

use ApiExtension\Exception\EntityNotFoundException;
use ApiPlatform\Core\Api\IriConverterInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

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

    public function getItemUri(\ReflectionClass $reflectionClass, array $ids = null): string
    {
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass($reflectionClass->getName());
        if (null === $ids) {
            if (null === ($object = $em->getRepository($reflectionClass->getName())->findOneBy([]))) {
                throw new EntityNotFoundException('Unable to find an existing object of class '.$reflectionClass->getName());
            }
            $ids = $this->getObjectIdentifiers($object);
        }

        return $this->iriConverter->getItemIriFromResourceClass($reflectionClass->getName(), $ids);
    }

    public function getItemUriPattern(\ReflectionClass $reflectionClass): string
    {
        $identifiers = $this->getClassMetadata($reflectionClass->getName())->getIdentifierFieldNames();

        return str_replace('{id}', '[\\w-]+', urldecode($this->getItemUri($reflectionClass, array_combine($identifiers, array_fill(0, count($identifiers), '{id}')))));
    }

    public function getObjectIdentifiers($object): array
    {
        $classMetadata = $this->getClassMetadata(ClassUtils::getClass($object));

        return array_combine($classMetadata->getIdentifierFieldNames(), $classMetadata->getIdentifierValues($object));
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

    public function getClassMetadata(string $className): ClassMetadataInfo
    {
        return $this->registry->getManagerForClass($className)->getClassMetadata($className);
    }
}
