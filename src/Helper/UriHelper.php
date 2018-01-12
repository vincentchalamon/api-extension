<?php

/*
 * This file is part of the ApiExtension package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiExtension\Helper;

use ApiPlatform\Core\Api\IriConverterInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class UriHelper
{
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function setIriConverter(IriConverterInterface $iriConverter): void
    {
        $this->iriConverter = $iriConverter;
    }

    public function setRegistry(ManagerRegistry $registry): void
    {
        $this->registry = $registry;
    }

    public function getUri(\ReflectionClass $reflectionClass): string
    {
        return $this->iriConverter->getIriFromResourceClass($reflectionClass->getName());
    }

    public function getItemUri(\ReflectionClass $reflectionClass): string
    {
        $classMetadata = $this->registry->getManagerForClass($reflectionClass->getName())->getClassMetadata($reflectionClass->getName());
        dump($classMetadata->getIdentifierFieldNames());

        // todo Guess id
        return $this->iriConverter->getItemIriFromResourceClass($reflectionClass->getName(), ['id' => '']);
    }

    public function getReflectionClass(string $name): \ReflectionClass
    {
        $allClasses = array_map(function (ClassMetadata $metadata) {
            return $metadata->getReflectionClass();
        }, $this->registry->getManager()->getMetadataFactory()->getAllMetadata());
        $clearName = strtolower(str_replace(' ', '', $name));
        foreach (array_unique([Inflector::singularize($clearName), Inflector::singularize($clearName), $clearName]) as $result) {
            $classes = array_filter($allClasses, function (\ReflectionClass $reflectionClass) use ($result) {
                return strtolower($result) === strtolower($reflectionClass->getShortName());
            });
            if (count($classes)) {
                return array_shift($classes);
            }
        }

        throw new \LogicException(sprintf('Unable to find an entity corresponding to name "%s".', $name));
    }
}
